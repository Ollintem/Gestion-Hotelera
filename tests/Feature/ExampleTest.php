<?php

test('los usuarios pueden ver la landing page', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});