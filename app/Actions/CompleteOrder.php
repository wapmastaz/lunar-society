<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Jobs\SubscribeToBeehiiv;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Services\Registry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Turn a completed PayPal capture into a paid order. Safe to call more than
 * once for the same order (return URL and webhook may both arrive).
 */
class CompleteOrder
{
    public function __construct(protected Registry $registry) {}

    /**
     * @param  array<string, mixed>  $paypalOrder  Capture response or order details from PayPal.
     */
    public function handle(Order $order, array $paypalOrder): bool
    {
        $capture = Arr::get($paypalOrder, 'purchase_units.0.payments.captures.0', []);

        if (Arr::get($capture, 'status') !== 'COMPLETED') {
            return false;
        }

        $paidCents = (int) round(((float) Arr::get($capture, 'amount.value')) * 100);

        if ($paidCents !== $order->amount_cents) {
            Log::critical('PayPal capture amount mismatch', [
                'order' => $order->uuid,
                'expected' => $order->amount_cents,
                'paid' => $paidCents,
            ]);

            return false;
        }

        $completed = DB::transaction(function () use ($order, $paypalOrder, $capture) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->isPaid()) {
                return false;
            }

            $order->update([
                'status' => OrderStatus::Paid,
                'paypal_capture_id' => Arr::get($capture, 'id'),
                'payer_first_name' => Arr::get($paypalOrder, 'payer.name.given_name'),
                'payer_last_name' => Arr::get($paypalOrder, 'payer.name.surname'),
                'shipping' => Arr::get($paypalOrder, 'purchase_units.0.shipping'),
                'paid_at' => now(),
                'locks_at' => $order->isFounder() ? now()->addHours((int) config('lunar.name_lock_hours')) : null,
                'hold_expires_at' => null,
            ]);

            if ($order->isFounder()) {
                $this->registry->assignSlot($order);
            }

            return true;
        });

        if ($completed) {
            $order->refresh();
            Mail::to($order->email)->queue(new OrderConfirmation($order));

            // Newsletter only for buyers who ticked the permission box.
            if ($order->marketing_opt_in) {
                SubscribeToBeehiiv::dispatch($order->email);
            }
        }

        return true;
    }
}
