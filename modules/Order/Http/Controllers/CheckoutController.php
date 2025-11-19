<?php

namespace Modules\Order\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Order\Actions\PurchaseItems;
use Modules\Order\DTOs\PendingPayment;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\PaymentGateway;
use Modules\Product\CartItemCollection;
use Modules\User\UserDTO;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __invoke(
        PurchaseItems $purchaseItems,
        PaymentGateway $paymentGateway,
        CheckoutRequest $request,
        #[Authenticated] User $user,
    ): JsonResponse
    {
        $cartItems = CartItemCollection::fromCheckoutData($request->products());

        $pendingPayment = new PendingPayment(
            provider: $paymentGateway,
            paymentToken: (string) $request->safe()->string('payment_token'),
        );
        $userDTO = UserDTO::fromEloquentModel($user);

        try {
            $order = $purchaseItems->handle(
                items: $cartItems,
                pendingPayment: $pendingPayment,
                user: $userDTO,
            );
        } catch (PaymentFailedException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.',
            ]);
        }

        return new JsonResponse(data: [
            'data' => [
                'order_url' => $order->url,
            ],
        ], status: Response::HTTP_CREATED);
    }
}
