<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'ticket_type_id',
        'unique_code',
        'redeemed_at',
    ];

    /**
     * Get the order item that owns the ticket.
     *
     * @return BelongsTo<OrderItem,Ticket>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the ticket type for the ticket.
     *
     * @return BelongsTo<TicketType,Ticket>
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }
}
