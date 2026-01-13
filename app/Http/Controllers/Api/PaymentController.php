<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\PaymentIntent;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent for an order.
     */
    public function createIntent(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order cannot be paid'], 422);
        }

        try {
            $intent = PaymentIntent::create([
                'amount' => (int)($order->total_amount * 100),
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            $order->update(['stripe_payment_intent_id' => $intent->id]);

            Payment::create([
                'order_id' => $order->id,
                'stripe_payment_intent_id' => $intent->id,
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
                'paymentIntentId' => $intent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirm payment and update order status.
     */
    public function confirmPayment(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $intent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($intent->status !== 'succeeded') {
                return response()->json(['message' => 'Payment not succeeded'], 422);
            }

            $payment = Payment::where('stripe_payment_intent_id', $validated['payment_intent_id'])->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment record not found'], 404);
            }

            $payment->update([
                'status' => 'succeeded',
                'stripe_response' => $intent->toArray(),
            ]);

            $order->update(['status' => 'completed']);

            // Reduce available ticket quantities
            foreach ($order->items as $item) {
                $ticketType = $item->ticketType;
                $ticketType->decrement('available_quantity', $item->quantity);
            }

            return response()->json([
                'message' => 'Payment confirmed successfully',
                'order' => $order->load('items.ticketType'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment status.
     */
    public function status(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $payment = $order->payment()->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json($payment);
    }
}
