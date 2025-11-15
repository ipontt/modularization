<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItem;
use Modules\Product\CartItemCollection;
use Modules\Product\Models\Product;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        $cartItems = CartItemCollection::fromCheckoutData($request->products());

        $total = $cartItems->total();

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

        foreach ($cartItems->items as $cartItem) {
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
