<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Token;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
