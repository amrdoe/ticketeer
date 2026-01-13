<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'organizer',
        'start_date',
        'end_date',
        'sale_start',
        'sale_end',
        'location',
        'image_url',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
    ];

    /**
     * @return BelongsTo<User,Event>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TicketType,Event>
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }
}
