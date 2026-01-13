<?php

use App\Models\User;

test('guests are redirected to login when accessing my tickets', function () {
    $page = visit('/my-tickets');

    $page->assertSee('Sign in to your account')->assertNoJavascriptErrors();
});

test('authenticated users can visit my tickets', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit('/my-tickets');

    $page->assertSee('My Tickets')->assertNoJavascriptErrors();
});
