<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $events = Event::with('ticketTypes', 'user')
            ->where('sale_end', '>=', now())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json($events);
    }

    /**
     * Display featured events.
     */
    public function featured(): JsonResponse
    {
        $events = Event::with('ticketTypes', 'user')
            ->where('sale_end', '>=', now())
            ->limit(6)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($events);
    }

    /**
     * Display events created by the authenticated user.
     */
    public function mine(Request $request): JsonResponse
    {
        $events = $request->user()->events()
            ->with('ticketTypes', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Note: An event must have at least one ticket type at creation.
     * Ticket types are created atomically with the event.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        // Only organizers can create events — double-check at controller level.
        if (! $request->user()?->is_organizer) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'sale_start' => 'required|date',
            'sale_end' => 'required|date|after:sale_start',
            'location' => 'nullable|string|max:255',
            'image_url' => 'nullable|url',
            // Ticket types are required when creating an event
            'ticket_types' => 'required|array|min:1',
            'ticket_types.*.name' => 'required|string|max:255',
            'ticket_types.*.code' => 'required|string|distinct|unique:ticket_types,code',
            'ticket_types.*.price' => 'required|numeric|min:0',
            'ticket_types.*.total_quantity' => 'required|integer|min:1',
            'ticket_types.*.description' => 'nullable|string',
        ]);

        // Create event and ticket types atomically
        $event = DB::transaction(function () use ($validated) {
            $event = Event::create([
                ...$validated,
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['ticket_types'] as $tt) {
                $event->ticketTypes()->create([
                    'name' => $tt['name'],
                    'code' => $tt['code'],
                    'price' => $tt['price'],
                    'total_quantity' => $tt['total_quantity'],
                    // initial available equals total
                    'available_quantity' => $tt['total_quantity'],
                    'description' => $tt['description'] ?? null,
                ]);
            }

            return $event->load('ticketTypes');
        });

        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): JsonResponse
    {
        $event->load('ticketTypes', 'user');

        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'sale_start' => 'date',
            'sale_end' => 'date|after:sale_start',
            'location' => 'nullable|string|max:255',
            'image_url' => 'nullable|url',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
