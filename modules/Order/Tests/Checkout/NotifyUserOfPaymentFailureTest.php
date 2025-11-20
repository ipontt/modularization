<?php

use App\Models\User;
use Modules\Order\Checkout\NotifyUserOfPaymentFailure;
use Modules\Order\Checkout\PaymentForOrderFailed;
use Modules\Order\Contracts\OrderDTO;
use Modules\Order\Order;
use Modules\Payment\PaymentFailed;
use Modules\User\UserDTO;

it('notifies the user of the payment failure', function () {

    Mail::fake();

    $order = Order::factory()->create();
    $orderDto = OrderDTO::fromEloquentModel($order);
    $userDto = UserDTO::fromEloquentModel(User::factory()->create());

    $event = new PaymentFailed($orderDto, $userDto, 'Payment failed.');
    $this->app->make(NotifyUserOfPaymentFailure::class)->handle($event);

    Mail::assertQueued(PaymentForOrderFailed::class, $userDto->email);
});
