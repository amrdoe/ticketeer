<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'organizer' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'sale_start' => 'required|date',
            'sale_end' => 'required|date|after:sale_start',
            'location' => 'nullable|string|max:255',
            'image_url' => 'nullable|url',
        ]);

        $event = Event::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

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
            'organizer' => 'string|max:255',
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
