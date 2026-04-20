<?php

test('home route redirects to the setup page', function () {
    $this->get(route('home'))
        ->assertRedirect(route('budget.setup'));
});