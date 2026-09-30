<?php

use Inertia\Testing\AssertableInertia as Assert;

it('shows the usage guide to a signed-in user', function () {
    $this->actingAs(clubUser());

    $this->get('/guide')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Guide'));
});

it('sends guests from the guide to login', function () {
    $this->get('/guide')->assertRedirect(route('login'));
});
