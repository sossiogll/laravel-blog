<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // An instance may have real packages installed; core tests bring their own fixtures.
        config(['gllhub.packages.installed' => '/nonexistent/installed.json']);
    }
}
