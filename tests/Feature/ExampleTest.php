<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_shows_the_guest_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('MSA Go')
            ->assertSee(route('login'), false);
    }
}
