<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Order\Actions\PurchaseItems;
use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request, PurchaseItems $purchaseItems): JsonResponse
    {
        $cartItems = CartItemCollection::fromCheckoutData($request->products());
        /** @var int $userId */
        $userId = Auth::id();

        try {
            $order = $purchaseItems->handle(
                $cartItems,
                PayBuddy::make(),
                (string) $request->safe()->string('payment_token'),
                $userId,
            );
        } catch (PaymentFailedException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.',
            ]);
        }

        return new JsonResponse(data: [
            'data' => [
                'order_url' => $order->url(),
            ],
        ], status: Response::HTTP_CREATED);
    }
}
