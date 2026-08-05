<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Товары (Спортпит)
 */
class ProductController extends Controller
{
    /**
     * Список товаров
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $onlyActive = !$request->boolean('all');

        $query = Product::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $products = $query->orderBy('name')->paginate($request->query('per_page', 15));

        return ApiResponse::make()->success($products);
    }

    /**
     * Создать товар
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($data);

        return ApiResponse::make()->created($product);
    }

    /**
     * Просмотр товара
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::make()->notFound('Товар не найден');
        }

        return ApiResponse::make()->success($product);
    }

    /**
     * Обновить товар
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::make()->notFound('Товар не найден');
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $product->update($data);

        return ApiResponse::make()->success($product);
    }

    /**
     * Переключить активность товара
     */
    public function toggle(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::make()->notFound('Товар не найден');
        }

        $product->update(['is_active' => !$product->is_active]);

        return ApiResponse::make()->success($product);
    }

    /**
     * Удалить товар
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::make()->notFound('Товар не найден');
        }

        $product->delete();

        return ApiResponse::make()->noContent();
    }
}
