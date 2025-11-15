<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\Models\Product;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request)
    {
        $products = $request->safe()
            ->collect('products')
            ->map(fn ($productDetail) => [
                'product' => Product::query()->find($productDetail['id']),
                'quantity' => $productDetail['quantity'],
            ]);

        $total = $products->sum(fn ($productDetail) => $productDetail['quantity'] * $productDetail['product']->price);

        $paymentGateway = PayBuddy::make();

        try {
            $charge = $paymentGateway->charge(
                token: $request->safe()->string('payment_token'),
                amount: $total,
                description: 'Test payment',
            );
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.',
            ]);
        }

        $order = Order::query()->create([
            'payment_id' => $charge['id'],
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'total' => $total,
            'user_id' => Auth::id(),
        ]);

        foreach ($products as $product) {
            $product['product']->decrement('stock', $product['quantity']);
            $order->lines()->create([
                'product_id' => $product['product']->id,
                'product_price' => $product['product']->price,
                'quantity' => $product['quantity'],
            ]);
        }

        return new JsonResponse(data: [], status: Response::HTTP_CREATED);
    }
}
