<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOrder;
use App\Models\Order;
use App\Services\PayPal;
use App\Services\Registry;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * JSON endpoints called by the PayPal JS buttons on the claim page.
 */
class CheckoutController extends Controller
{
    public function __construct(protected PayPal $paypal, protected Registry $registry) {}

    public function create(Order $order): JsonResponse
    {
        if ($order->isPaid()) {
            return response()->json(['error' => 'This order is already paid.'], 409);
        }

        if (! $this->registry->confirmHold($order)) {
            return response()->json(['error' => 'Sorry, your reserved slot was released and this tier is now full.'], 409);
        }

        if ($order->paypal_order_id) {
            return response()->json(['id' => $order->paypal_order_id]);
        }

        $order->update(['paypal_order_id' => $this->paypal->createOrder($order->loadMissing('tier', 'product'))]);

        return response()->json(['id' => $order->paypal_order_id]);
    }

    public function capture(Order $order, CompleteOrder $complete): JsonResponse
    {
        if ($order->isPaid()) {
            return response()->json(['redirect' => route('orders.show', $order)]);
        }

        if (! $order->paypal_order_id) {
            return response()->json(['error' => 'Payment was not started.'], 422);
        }

        if (! $this->registry->confirmHold($order)) {
            return response()->json(['error' => 'Sorry, your reserved slot was released and this tier is now full. You have not been charged.'], 409);
        }

        try {
            $result = $this->paypal->captureOrder($order->paypal_order_id);
        } catch (RequestException $e) {
            $issue = $e->response->json('details.0.issue');

            if ($issue === 'INSTRUMENT_DECLINED') {
                return response()->json(['restart' => true]);
            }

            Log::error('PayPal capture failed', ['order' => $order->uuid, 'body' => $e->response->json()]);

            return response()->json(['error' => 'PayPal could not complete the payment. You have not been charged.'], 502);
        }

        if (! $complete->handle($order, $result)) {
            return response()->json(['error' => 'Your payment is being reviewed by PayPal. We will email you once it clears.'], 202);
        }

        return response()->json(['redirect' => route('orders.show', $order)]);
    }
}
