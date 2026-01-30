<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    // The app redirects / to /places
    $response->assertRedirect('/places');
});
