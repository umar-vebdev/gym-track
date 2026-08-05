<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductSale;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Продажи товаров
 */
class ProductSaleController extends Controller
{
    /**
     * Список продаж товаров
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductSale::with(['client', 'product']);

        if ($request->query('client_id')) {
            $query->where('client_id', $request->query('client_id'));
        }

        if ($request->query('date')) {
            $query->whereDate('created_at', $request->query('date'));
        }

        $sales = $query->orderByDesc('created_at')->paginate($request->query('per_page', 15));

        return ApiResponse::make()->success($sales);
    }

    /**
     * Оформить продажу товара
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        
        if (!$product->is_active) {
            return ApiResponse::make()->error('Товар недоступен для продажи', 'PRODUCT_INACTIVE', [], 422);
        }

        $data['total_price'] = $product->price * $data['quantity'];

        $sale = ProductSale::create($data);
        $sale->load(['client', 'product']);

        return ApiResponse::make()->created($sale);
    }

    /**
     * Просмотр продажи
     */
    public function show(int $id): JsonResponse
    {
        $sale = ProductSale::with(['client', 'product'])->find($id);

        if (!$sale) {
            return ApiResponse::make()->notFound('Продажа не найдена');
        }

        return ApiResponse::make()->success($sale);
    }
}
