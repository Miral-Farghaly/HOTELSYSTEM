<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate:fresh', ['--env' => 'testing']);

        // Run the permission seeder
        $this->artisan('db:seed', [
            '--class' => 'RolesAndPermissionsSeeder',
            '--env' => 'testing'
        ]);
    }
}
