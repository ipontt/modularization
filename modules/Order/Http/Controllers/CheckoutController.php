<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\DTOs\CartItemDTO;
use Modules\Product\Models\Product;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        /** @var Collection<int, array{id: int, quantity: int}> $products */
        $products = $request->safe()->collect('products');
        /** @var Collection<int, CartItemDTO> $cartItems */
        $cartItems = $products
            ->map(fn (array $productDetail) => new CartItemDTO(
                product: Product::query()->findOrFail($productDetail['id']),
                quantity: $productDetail['quantity'],
            ));

        $total = $cartItems->sum(fn (CartItemDTO $cartItem) => $cartItem->quantity * $cartItem->product->price);

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

        foreach ($cartItems as $cartItem) {
            $cartItem->product->decrement('stock', $cartItem->quantity);
            $order->lines()->create([
                'product_id' => $cartItem->product->id,
                'product_price' => $cartItem->product->price,
                'quantity' => $cartItem->quantity,
            ]);
        }

        return new JsonResponse(data: [], status: Response::HTTP_CREATED);
    }
}
