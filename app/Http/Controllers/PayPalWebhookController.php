<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOrder;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\PayPal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

/**
 * Backup path for payments (buyer closed the tab before capture returned)
 * and the only path for refunds issued from the PayPal dashboard.
 */
class PayPalWebhookController extends Controller
{
    public function __invoke(Request $request, PayPal $paypal, CompleteOrder $complete): Response
    {
        $event = $request->json()->all();

        if (! $paypal->verifyWebhook($request->headers->all(), $event)) {
            return response('invalid signature', 400);
        }

        $resource = Arr::get($event, 'resource', []);

        match (Arr::get($event, 'event_type')) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->completed($resource, $paypal, $complete),
            'PAYMENT.CAPTURE.REFUNDED', 'PAYMENT.CAPTURE.REVERSED' => $this->refunded($resource, $event['event_type']),
            default => null,
        };

        return response('ok');
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function completed(array $resource, PayPal $paypal, CompleteOrder $complete): void
    {
        $paypalOrderId = Arr::get($resource, 'supplementary_data.related_ids.order_id');
        $order = $paypalOrderId ? Order::firstWhere('paypal_order_id', $paypalOrderId) : null;

        if ($order && ! $order->isPaid()) {
            $complete->handle($order, $paypal->showOrder($paypalOrderId));
        }
    }

    /**
     * Refund events carry the refund object (linked "up" to its capture);
     * reversal events carry the capture itself.
     *
     * @param  array<string, mixed>  $resource
     */
    protected function refunded(array $resource, string $type): void
    {
        $captureId = $type === 'PAYMENT.CAPTURE.REVERSED'
            ? Arr::get($resource, 'id')
            : collect(Arr::get($resource, 'links', []))
                ->firstWhere('rel', 'up')['href'] ?? null;

        if ($captureId && str_contains($captureId, '/')) {
            $captureId = basename($captureId);
        }

        $order = $captureId ? Order::firstWhere('paypal_capture_id', $captureId) : null;

        if (! $order) {
            return;
        }

        // Partial refunds (e.g. a shipping adjustment) keep the slot.
        $refundedCents = (int) round(((float) Arr::get($resource, 'amount.value', 0)) * 100);

        if ($type === 'PAYMENT.CAPTURE.REFUNDED' && $refundedCents < $order->amount_cents) {
            return;
        }

        $order->update([
            'status' => OrderStatus::Refunded,
            'refunded_at' => now(),
            'slot_number' => null,
        ]);
    }
}
