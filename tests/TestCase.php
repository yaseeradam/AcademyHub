<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('ACADEMYHUB_ADMIN_EMAIL=admin@academyhub.local');
        $_ENV['ACADEMYHUB_ADMIN_EMAIL'] = 'admin@academyhub.local';
        $_SERVER['ACADEMYHUB_ADMIN_EMAIL'] = 'admin@academyhub.local';

        \Illuminate\Support\Facades\Cache::flush();
    }
}
