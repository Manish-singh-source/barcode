<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\BarcodeController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_code_is_prefixed_with_app_url(): void
    {
        config()->set('app.url', 'https://example.test');

        $controller = new BarcodeController();
        $method = new \ReflectionMethod($controller, 'generateUniqueCode');
        $method->setAccessible(true);

        $code = $method->invoke($controller);

        $this->assertStringStartsWith('https://example.test/BC', $code);
    }
}
