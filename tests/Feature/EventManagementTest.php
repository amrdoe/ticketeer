<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('organizer can create, update, and delete an event', function () {
    $user = User::factory()->organizer()->create();

    $this->actingAs($user, 'sanctum');

    $payload = [
        'title' => 'My Event Title',
        'description' => 'An awesome event',
        'start_date' => now()->addDays(10)->toDateTimeString(),
        'end_date' => now()->addDays(11)->toDateTimeString(),
        'sale_start' => now()->toDateTimeString(),
        'sale_end' => now()->addDays(9)->toDateTimeString(),
        'location' => 'NYC',
        'ticket_types' => [
            [
                'name' => 'General Admission',
                'code' => 'GA-'.Str::upper(Str::random(6)),
                'price' => 25.00,
                'total_quantity' => 100,
            ],
        ],
    ];

    $response = $this->postJson('/api/events', $payload);

    $response->assertCreated()
        ->assertJsonPath('title', 'My Event Title')
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('ticket_types.0.name', 'General Admission');

    $this->assertDatabaseHas('events', [
        'title' => 'My Event Title',
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('ticket_types', [
        'event_id' => $response->json('id'),
        'code' => $payload['ticket_types'][0]['code'],
        'available_quantity' => $payload['ticket_types'][0]['total_quantity'],
    ]);

    $eventId = $response->json('id');

    // update
    $updateResponse = $this->putJson("/api/events/{$eventId}", ['title' => 'Updated Title']);
    $updateResponse->assertSuccessful()->assertJsonPath('title', 'Updated Title');

    $this->assertDatabaseHas('events', ['id' => $eventId, 'title' => 'Updated Title']);

    // delete
    $deleteResponse = $this->deleteJson("/api/events/{$eventId}");
    $deleteResponse->assertSuccessful();

    $this->assertDatabaseMissing('events', ['id' => $eventId]);
});

it('non-organizers cannot create events', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $payload = [
        'title' => 'My Event Title',
        'description' => 'An awesome event',
        'start_date' => now()->addDays(10)->toDateTimeString(),
        'end_date' => now()->addDays(11)->toDateTimeString(),
        'sale_start' => now()->toDateTimeString(),
        'sale_end' => now()->addDays(9)->toDateTimeString(),
        'location' => 'NYC',
        'ticket_types' => [
            [
                'name' => 'General Admission',
                'code' => 'GA-'.Str::upper(Str::random(6)),
                'price' => 25.00,
                'total_quantity' => 100,
            ],
        ],
    ];

    $this->postJson('/api/events', $payload)->assertForbidden();
});

it('organizer must provide at least one ticket type when creating an event', function () {
    $user = User::factory()->organizer()->create();

    $this->actingAs($user, 'sanctum');

    $payload = [
        'title' => 'My Event Title',
        'description' => 'An awesome event',
        'start_date' => now()->addDays(10)->toDateTimeString(),
        'end_date' => now()->addDays(11)->toDateTimeString(),
        'sale_start' => now()->toDateTimeString(),
        'sale_end' => now()->addDays(9)->toDateTimeString(),
        'location' => 'NYC',
        // no ticket_types provided
    ];

    $this->postJson('/api/events', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors('ticket_types');
});

it('user can fetch their own events', function () {
    $user = User::factory()->organizer()->create();
    $other = User::factory()->organizer()->create();

    Event::factory()->for($user)->count(3)->create();
    Event::factory()->for($other)->count(2)->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/user/events');

    $response->assertSuccessful()
        ->assertJsonPath('total', 3);

    $data = $response->json('data');

    foreach ($data as $event) {
        expect($event['user_id'])->toBe($user->id);
    }
});

it('cannot update or delete events they do not own', function () {
    $owner = User::factory()->organizer()->create();
    $attacker = User::factory()->organizer()->create();

    $event = Event::factory()->for($owner)->create();

    $this->actingAs($attacker, 'sanctum');

    $this->putJson("/api/events/{$event->id}", ['title' => 'Hacked'])->assertForbidden();
    $this->deleteJson("/api/events/{$event->id}")->assertForbidden();
});
