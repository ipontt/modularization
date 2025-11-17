<?php

namespace Modules\Order\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Order\Actions\PurchaseItems;
use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request, PurchaseItems $purchaseItems, #[Authenticated] User $user): JsonResponse
    {
        $cartItems = CartItemCollection::fromCheckoutData($request->products());

        try {
            $order = $purchaseItems->handle(
                $cartItems,
                PayBuddy::make(),
                (string) $request->safe()->string('payment_token'),
                $user->id,
                $user->email,
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
