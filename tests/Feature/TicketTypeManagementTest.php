<?php

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('organizer can create a ticket type for an event', function () {
    $user = User::factory()->organizer()->create();
    $this->actingAs($user, 'sanctum');

    $event = Event::factory()->for($user)->create();

    $payload = [
        'name' => 'VIP',
        'code' => 'VIP-TEST-1',
        'price' => 50,
        'total_quantity' => 50,
        'description' => 'VIP pass',
    ];

    $response = $this->postJson("/api/events/{$event->id}/tickets", $payload);

    $response->assertStatus(201)
        ->assertJsonPath('name', 'VIP')
        ->assertJsonPath('available_quantity', 50)
        ->assertJsonPath('total_quantity', 50);

    $this->assertDatabaseHas('ticket_types', [
        'event_id' => $event->id,
        'code' => 'VIP-TEST-1',
        'available_quantity' => 50,
    ]);
});

it('cannot create ticket types with duplicate codes', function () {
    $user = User::factory()->organizer()->create();
    $this->actingAs($user, 'sanctum');

    $event = Event::factory()->for($user)->create();

    TicketType::factory()->for($event)->create(['code' => 'DUPLICATE-CODE']);

    $payload = [
        'name' => 'Another',
        'code' => 'DUPLICATE-CODE',
        'price' => 30,
        'total_quantity' => 20,
    ];

    $this->postJson("/api/events/{$event->id}/tickets", $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

it('updating total_quantity adjusts available_quantity and prevents lowering below sold', function () {
    $user = User::factory()->organizer()->create();
    $this->actingAs($user, 'sanctum');

    $event = Event::factory()->for($user)->create();

    // Create a ticket type that has 10 sold tickets (100 total, 90 available)
    $ticketType = TicketType::factory()->for($event)->create([
        'total_quantity' => 100,
        'available_quantity' => 90, // sold = 10
    ]);

    // Increase total by 50 => available should increase by 50 (new available = new_total - sold)
    $this->putJson("/api/tickets/{$ticketType->id}", ['total_quantity' => 150])
        ->assertSuccessful()
        ->assertJsonPath('total_quantity', 150)
        ->assertJsonPath('available_quantity', 140);

    $ticketType->refresh();
    expect($ticketType->available_quantity)->toBe(140);

    // Attempt to reduce total below sold (sold = 10) => should fail
    $this->putJson("/api/tickets/{$ticketType->id}", ['total_quantity' => 5])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Total quantity cannot be less than already sold tickets');

    // Reduce total to be equal to sold -> available should become 0
    $this->putJson("/api/tickets/{$ticketType->id}", ['total_quantity' => 10])
        ->assertSuccessful()
        ->assertJsonPath('total_quantity', 10)
        ->assertJsonPath('available_quantity', 0);
});

it('cannot delete a ticket type with sold tickets', function () {
    $user = User::factory()->organizer()->create();
    $this->actingAs($user, 'sanctum');

    $event = Event::factory()->for($user)->create();

    $ticketType = TicketType::factory()->for($event)->create([
        'total_quantity' => 100,
        'available_quantity' => 90, // sold = 10
    ]);

    $this->deleteJson("/api/tickets/{$ticketType->id}")
        ->assertStatus(422)
        ->assertJsonPath('message', 'Cannot delete ticket type with sold tickets. Consider marking it sold out instead.');

    $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id]);
});

it('cannot delete the last ticket type of an event', function () {
    $user = User::factory()->organizer()->create();
    $this->actingAs($user, 'sanctum');

    $event = Event::factory()->for($user)->create();

    $ticketType = TicketType::factory()->for($event)->create([
        'total_quantity' => 50,
        'available_quantity' => 50,
    ]);

    $this->deleteJson("/api/tickets/{$ticketType->id}")
        ->assertStatus(422)
        ->assertJsonPath('message', 'An event must have at least one ticket type');

    $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id]);
});

it('organizer can delete a ticket type when there are no sold tickets and event has multiple ticket types', function () {
    $user = User::factory()->organizer()->create();
    $this->actingAs($user, 'sanctum');

    $event = Event::factory()->for($user)->create();

    $tt1 = TicketType::factory()->for($event)->create(['code' => 'A', 'total_quantity' => 50, 'available_quantity' => 50]);
    $tt2 = TicketType::factory()->for($event)->create(['code' => 'B', 'total_quantity' => 20, 'available_quantity' => 20]);

    $this->deleteJson("/api/tickets/{$tt2->id}")->assertSuccessful();

    $this->assertDatabaseMissing('ticket_types', ['id' => $tt2->id]);
});
