<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Validate cart items and return pricing information.
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $items = [];
        $totalAmount = 0;

        foreach ($validated['items'] as $item) {
            $ticketType = TicketType::with('event')->findOrFail($item['ticket_type_id']);

            if ($ticketType->available_quantity < $item['quantity']) {
                return response()->json([
                    'message' => "Not enough tickets available for {$ticketType->name}",
                    'available' => $ticketType->available_quantity,
                ], 422);
            }

            $subtotal = $ticketType->price * $item['quantity'];
            $totalAmount += $subtotal;

            $items[] = [
                'ticket_type_id' => $ticketType->id,
                'name' => $ticketType->name,
                'code' => $ticketType->code,
                'quantity' => $item['quantity'],
                'unit_price' => $ticketType->price,
                'subtotal' => $subtotal,
                'event' => [
                    'id' => $ticketType->event->id,
                    'title' => $ticketType->event->title,
                ],
            ];
        }

        return response()->json([
            'items' => $items,
            'total' => $totalAmount,
        ]);
    }
}
