<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Listar productos con filtros
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with('category')->where('stock', '>', 0);

            // Filtro por categoría
            if ($request->has('category_id') && $request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            // Filtro por búsqueda (nombre o marca)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // Filtro por rango de precio
            if ($request->has('min_price') && $request->min_price) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price') && $request->max_price) {
                $query->where('price', '<=', $request->max_price);
            }

            // Filtro por marca
            if ($request->has('brand') && $request->brand) {
                $query->where('brand', $request->brand);
            }

            // Filtro por destacados
            if ($request->has('featured') && $request->featured == 1) {
                $query->where('featured', true);
            }

            // Ordenamiento
            $order = $request->get('order', 'latest');
            switch ($order) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'oldest':
                    $query->oldest();
                    break;
                default:
                    $query->latest();
                    break;
            }

            // Paginación
            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products->map(function ($product) {
                        return [
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
                                'slug' => $product->category->slug,
                            ],
                        ];
                    }),
                    'pagination' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'from' => $products->firstItem(),
                        'to' => $products->lastItem(),
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

    /**
     * Obtener producto por slug
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        try {
            $product = Product::with('category')
                ->where('slug', $slug)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            // Productos relacionados (de la misma categoría)
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('stock', '>', 0)
                ->inRandomOrder()
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => [
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
                            'slug' => $product->category->slug,
                        ],
                        'in_stock' => $product->stock > 0,
                    ],
                    'related_products' => $relatedProducts->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'slug' => $p->slug,
                            'price' => (float) $p->price,
                            'brand' => $p->brand,
                            'image_url' => $p->image_url,
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener productos destacados
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function featured()
    {
        try {
            $products = Product::with('category')
                ->where('featured', true)
                ->where('stock', '>', 0)
                ->orderBy('price', 'desc')
                ->limit(8)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'price' => (float) $product->price,
                        'stock' => $product->stock,
                        'brand' => $product->brand,
                        'size' => $product->size,
                        'image_url' => $product->image_url,
                        'category' => [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ],
                    ];
                }),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos destacados',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener marcas disponibles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function brands()
    {
        try {
            $brands = Product::select('brand')
                ->distinct()
                ->whereNotNull('brand')
                ->orderBy('brand')
                ->pluck('brand');

            return response()->json([
                'success' => true,
                'data' => $brands,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener marcas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
