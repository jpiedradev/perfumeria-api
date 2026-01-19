<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Dashboard con estadísticas generales
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard()
    {
        try {
            // Estadísticas generales
            $stats = [
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_customers' => User::where('role', 'customer')->count(),
                'total_revenue' => (float) Order::where('status', 'completed')->sum('total'),

                // Pedidos por estado
                'orders_by_status' => [
                    'pending' => Order::where('status', 'pending')->count(),
                    'processing' => Order::where('status', 'processing')->count(),
                    'completed' => Order::where('status', 'completed')->count(),
                    'cancelled' => Order::where('status', 'cancelled')->count(),
                ],

                // Productos por categoría
                'products_by_category' => Category::withCount('products')->get()->map(function ($cat) {
                    return [
                        'category' => $cat->name,
                        'count' => $cat->products_count,
                    ];
                }),

                // Productos con bajo stock
                'low_stock_products' => Product::where('stock', '<', 10)->count(),

                // Últimos pedidos
                'recent_orders' => Order::with('user')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'customer' => $order->user->name,
                            'total' => (float) $order->total,
                            'status' => $order->status,
                            'status_text' => $order->status_text,
                            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),

                // Productos más vendidos
                'top_products' => Product::withCount(['orderItems as total_sold' => function ($query) {
                    $query->selectRaw('SUM(quantity)');
                }])
                    ->orderBy('total_sold', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'total_sold' => $product->total_sold ?? 0,
                            'stock' => $product->stock,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar todos los pedidos (admin)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orders(Request $request)
    {
        try {
            $query = Order::with(['user', 'items.product']);

            // Filtro por estado
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Búsqueda por cliente
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Ordenamiento
            $order = $request->get('order', 'latest');
            if ($order === 'oldest') {
                $query->oldest();
            } else {
                $query->latest();
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'customer' => [
                                'id' => $order->user->id,
                                'name' => $order->user->name,
                                'email' => $order->user->email,
                            ],
                            'total' => (float) $order->total,
                            'status' => $order->status,
                            'status_text' => $order->status_text,
                            'status_badge' => $order->status_badge,
                            'items_count' => $order->items->count(),
                            'total_items' => $order->total_items,
                            'shipping_address' => $order->shipping_address,
                            'phone' => $order->phone,
                            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),
                    'pagination' => [
                        'total' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                    ],
                ],
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
     * Ver detalle de pedido (admin)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderShow($id)
    {
        try {
            $order = Order::with(['user', 'items.product.category'])->find($id);

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
                    'customer' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                        'phone' => $order->user->phone,
                    ],
                    'total' => (float) $order->total,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                    'shipping_address' => $order->shipping_address,
                    'phone' => $order->phone,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'product_brand' => $item->product->brand,
                            'category' => $item->product->category->name,
                            'quantity' => $item->quantity,
                            'price' => (float) $item->price,
                            'subtotal' => (float) $item->subtotal,
                        ];
                    }),
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
     * Cambiar estado de pedido
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled',
            ]);

            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado',
                ], 404);
            }

            $order->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Estado del pedido actualizado',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear producto
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProduct(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'brand' => 'nullable|string|max:255',
                'size' => 'nullable|integer|min:1',
                'featured' => 'boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Generar slug
            $validated['slug'] = Str::slug($validated['name']);

            // Subir imagen si existe
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($validated['name']) . '.' . $image->extension();
                $image->storeAs('public/products', $imageName);
                $validated['image'] = $imageName;
            }

            $product = Product::create($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'brand' => $product->brand,
                    'size' => $product->size,
                    'featured' => $product->featured,
                    'image_url' => $product->image_url,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar producto
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            $validated = $request->validate([
                'category_id' => 'sometimes|exists:categories,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'stock' => 'sometimes|integer|min:0',
                'brand' => 'nullable|string|max:255',
                'size' => 'nullable|integer|min:1',
                'featured' => 'sometimes|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Actualizar slug si cambia el nombre
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Subir nueva imagen si existe
            if ($request->hasFile('image')) {
                // Eliminar imagen anterior
                if ($product->image) {
                    Storage::delete('public/products/' . $product->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($product->name) . '.' . $image->extension();
                $image->storeAs('public/products', $imageName);
                $validated['image'] = $imageName;
            }

            $product->update($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'brand' => $product->brand,
                    'size' => $product->size,
                    'featured' => $product->featured,
                    'image_url' => $product->image_url,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar producto
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProduct($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            // Eliminar imagen si existe
            if ($product->image) {
                Storage::delete('public/products/' . $product->image);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar todos los productos (admin, sin filtro de stock)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Request $request)
    {
        try {
            $query = Product::with('category');

            // Búsqueda
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%');
                });
            }

            // Filtro por categoría
            if ($request->has('category_id') && $request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            // Ordenamiento
            $order = $request->get('order', 'latest');
            switch ($order) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }

            $perPage = $request->get('per_page', 15);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'price' => (float) $product->price,
                            'stock' => $product->stock,
                            'brand' => $product->brand,
                            'size' => $product->size,
                            'featured' => $product->featured,
                            'image_url' => $product->image_url,
                            'category' => [
                                'id' => $product->category->id,
                                'name' => $product->category->name,
                            ],
                        ];
                    }),
                    'pagination' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
