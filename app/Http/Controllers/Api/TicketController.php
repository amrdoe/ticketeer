<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->tickets()
            ->with('orderItem.order', 'ticketType.event.user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Scan a ticket by code (organizer only).
     */
    public function scan(Request $request, string $code): JsonResponse
    {
        $ticket = Ticket::with('ticketType.event.user')->where('unique_code', $code)->first();

        if (! $ticket) {

            return response()->json(['message' => 'Ticket not found.'], Response::HTTP_NOT_FOUND);

        }

        $event = $ticket->ticketType->event;

        if ($request->user()->id !== $event->user_id) {

            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);

        }

        return response()->json([

            'ticket' => $ticket,

            'event' => $event,

            'redeemed' => ! is_null($ticket->redeemed_at),

        ]);
    }

    /**
     * Redeem a ticket by code (organizer only, confirmation required).
     */
    public function redeem(Request $request, string $code): JsonResponse
    {
        $ticket = Ticket::with('ticketType.event.user')->where('unique_code', $code)->first();

        if (! $ticket) {

            return response()->json(['message' => 'Ticket not found.'], Response::HTTP_NOT_FOUND);

        }

        $event = $ticket->ticketType->event;

        if ($request->user()->id !== $event->user_id) {

            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);

        }

        if ($ticket->redeemed_at) {

            return response()->json(['message' => 'Ticket already redeemed.'], Response::HTTP_CONFLICT);

        }

        // Confirm parameter required

        if (! $request->boolean('confirm')) {

            return response()->json([

                'message' => 'Confirmation required to redeem ticket.',

                'requires_confirmation' => true,

            ], Response::HTTP_PRECONDITION_REQUIRED);

        }

        $ticket->redeemed_at = now();

        $ticket->save();

        return response()->json([

            'message' => 'Ticket redeemed successfully.',

            'ticket' => $ticket,

        ]);
    }
}
