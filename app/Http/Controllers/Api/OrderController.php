<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(): JsonResponse
    {
        $orders = auth()->user()->orders()
            ->with('items.ticketType')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Store a newly created order from cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = new Order;
        $order->user_id = auth()->id();
        $order->order_number = 'ORD-'.Str::upper(Str::random(10));
        $order->total_amount = 0;
        $order->status = 'pending';

        $totalAmount = 0;

        foreach ($validated['items'] as $item) {
            $ticketType = TicketType::findOrFail($item['ticket_type_id']);

            if ($ticketType->available_quantity < $item['quantity']) {
                return response()->json([
                    'message' => "Not enough tickets available for {$ticketType->name}",
                ], 422);
            }

            $subtotal = $ticketType->price * $item['quantity'];
            $totalAmount += $subtotal;
        }

        $order->total_amount = $totalAmount;
        $order->save();

        foreach ($validated['items'] as $item) {
            $ticketType = TicketType::findOrFail($item['ticket_type_id']);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => $item['quantity'],
                'unit_price' => $ticketType->price,
                'subtotal' => $ticketType->price * $item['quantity'],
            ]);

            // Create tickets for each quantity purchased
            for ($i = 0; $i < $item['quantity']; $i++) {
                \App\Models\Ticket::create([
                    'order_item_id' => $orderItem->id,
                    'ticket_type_id' => $ticketType->id,
                    'unique_code' => strtoupper(Str::random(16)),
                    'redeemed_at' => null,
                ]);
            }
        }

        $order->load('items.ticketType', 'items.tickets.ticketType');

        return response()->json($order, 201);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load('items.ticketType', 'items.tickets.ticketType');

        return response()->json($order);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled'], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json($order);
    }
}
