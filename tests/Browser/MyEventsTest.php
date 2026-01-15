<?php

namespace Tests\Browser;

use App\Models\User;

test('guests are redirected to login when accessing my events', function () {
    $page = visit('/my-events');

    $page->assertSee('Sign in to your account')->assertNoJavascriptErrors();
});

test('non-organizers are redirected to home when accessing my events', function () {
    /* @var $this */

    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit('/my-events');

    $page->assertSee('Discover Amazing Events')->assertNoJavascriptErrors();
});

test('organizers can visit my events', function () {
    /* @var $this */

    $user = User::factory()->organizer()->create();
    $this->actingAs($user);

    $page = visit('/my-events');

    $page->assertSee('My Events')->assertSee('Create Event')->assertNoJavascriptErrors();
});

test('organizers see My Events link in navbar', function () {
    /* @var $this */

    $user = User::factory()->organizer()->create();
    $this->actingAs($user);

    $page = visit('/');

    $page->assertSee('My Events')->assertNoJavascriptErrors();
});

test('non-organizers do not see My Events link in navbar', function () {
    /* @var $this */

    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit('/');

    $page->assertDontSee('My Events')->assertNoJavascriptErrors();
});
