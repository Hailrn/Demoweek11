<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_loads_without_database(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Demo Route, Middleware & Controller');
    }
}
