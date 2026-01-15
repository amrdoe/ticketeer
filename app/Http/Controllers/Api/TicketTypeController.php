<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event): JsonResponse
    {
        $ticketTypes = $event->ticketTypes;

        return response()->json($ticketTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:ticket_types',
            'price' => 'required|numeric|min:0',
            'total_quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $ticketType = $event->ticketTypes()->create([
            ...$validated,
            'available_quantity' => $validated['total_quantity'],
        ]);

        return response()->json($ticketType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TicketType $ticketType): JsonResponse
    {
        return response()->json($ticketType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TicketType $ticketType): JsonResponse
    {
        $this->authorize('update', $ticketType->event);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'total_quantity' => 'integer|min:1',
            'description' => 'nullable|string',
        ]);

        // If total_quantity is being updated, ensure it is not less than the number of tickets already sold.
        if (array_key_exists('total_quantity', $validated)) {
            $currentTotal = $ticketType->total_quantity;
            $currentAvailable = $ticketType->available_quantity;
            $sold = $currentTotal - $currentAvailable;

            if ($validated['total_quantity'] < $sold) {
                return response()->json(['message' => 'Total quantity cannot be less than already sold tickets'], 422);
            }

            // Recalculate available quantity so that sold tickets remain unchanged.
            $validated['available_quantity'] = $validated['total_quantity'] - $sold;
        }

        $ticketType->update($validated);

        return response()->json($ticketType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketType $ticketType): JsonResponse
    {
        $this->authorize('delete', $ticketType->event);

        // Prevent deletion if tickets have been sold for this ticket type.
        $sold = $ticketType->total_quantity - $ticketType->available_quantity;
        if ($sold > 0) {
            return response()->json([
                'message' => 'Cannot delete ticket type with sold tickets. Consider marking it sold out instead.',
            ], 422);
        }

        // Ensure event always has at least one ticket type.
        if ($ticketType->event->ticketTypes()->count() <= 1) {
            return response()->json(['message' => 'An event must have at least one ticket type'], 422);
        }

        $ticketType->delete();

        return response()->json(['message' => 'Ticket type deleted successfully']);
    }
}
