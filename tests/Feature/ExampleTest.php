<?php

it('returns a successful response', function () {
    // The bare address sends a visitor to the shop in their language.
    $this->get('/')->assertRedirect(route('furnish.index'));

    $this->get(route('furnish.index'))->assertStatus(200);
});
