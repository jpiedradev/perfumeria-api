<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Listar pedidos del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $orders = $request->user()
                ->orders()
                ->with('items.product')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total' => (float) $order->total,
                        'status' => $order->status,
                        'status_text' => $order->status_text,
                        'status_badge' => $order->status_badge,
                        'shipping_address' => $order->shipping_address,
                        'phone' => $order->phone,
                        'items_count' => $order->items->count(),
                        'total_items' => $order->total_items,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->name,
                                'quantity' => $item->quantity,
                                'price' => (float) $item->price,
                                'subtotal' => (float) $item->subtotal,
                            ];
                        }),
                    ];
                }),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pedidos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ver detalle de un pedido específico
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $order = $request->user()
                ->orders()
                ->with('items.product.category')
                ->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'total' => (float) $order->total,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                    'status_badge' => $order->status_badge,
                    'shipping_address' => $order->shipping_address,
                    'phone' => $order->phone,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'product_slug' => $item->product->slug,
                            'product_brand' => $item->product->brand,
                            'product_image' => $item->product->image_url,
                            'category_name' => $item->product->category->name,
                            'quantity' => $item->quantity,
                            'price' => (float) $item->price,
                            'subtotal' => (float) $item->subtotal,
                        ];
                    }),
                    'summary' => [
                        'items_count' => $order->items->count(),
                        'total_items' => $order->total_items,
                        'subtotal' => (float) $order->total,
                        'total' => (float) $order->total,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un nuevo pedido
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'shipping_address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            // Iniciar transacción
            DB::beginTransaction();

            $total = 0;
            $orderItems = [];

            // Validar stock y calcular total
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                // Verificar que existe y tiene stock
                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ["El producto con ID {$item['product_id']} no existe."]
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock insuficiente para {$product->name}. Stock disponible: {$product->stock}"]
                    ]);
                }

                // Calcular subtotal
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                // Guardar para crear después
                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Crear el pedido
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => $total,
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Crear los items del pedido y actualizar stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Reducir stock del producto
                $item['product']->decrement('stock', $item['quantity']);
            }

            // Commit de la transacción
            DB::commit();

            // Cargar relaciones para la respuesta
            $order->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'total' => (float) $order->total,
                        'status' => $order->status,
                        'status_text' => $order->status_text,
                        'shipping_address' => $order->shipping_address,
                        'phone' => $order->phone,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->name,
                                'quantity' => $item->quantity,
                                'price' => (float) $item->price,
                                'subtotal' => (float) $item->subtotal,
                            ];
                        }),
                    ],
                ],
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancelar un pedido (solo si está pending)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $id)
    {
        try {
            $order = $request->user()
                ->orders()
                ->with('items.product')
                ->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado',
                ], 404);
            }

            // Solo se puede cancelar si está pendiente
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar pedidos pendientes',
                ], 400);
            }

            DB::beginTransaction();

            // Devolver stock a los productos
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Cambiar estado del pedido
            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado exitosamente',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
