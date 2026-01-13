<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        $ticketType->update($validated);

        return response()->json($ticketType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketType $ticketType): JsonResponse
    {
        $this->authorize('delete', $ticketType->event);

        $ticketType->delete();

        return response()->json(['message' => 'Ticket type deleted successfully']);
    }
}
