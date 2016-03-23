<?php declare(strict_types=1);

namespace Alfheim\Sanitizer\Laravel;

use Alfheim\Sanitizer\Sanitizer;
use Illuminate\Http\Request;

class SanitizesRequestsTraitTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_sanitize_input()
    {
        $request = Request::createFromBase(Request::create('foo', 'POST', [
            'foo' => 'foo',
            'bar' => 'Bar ',
            'qux' => 'qux',
        ]));

        $expected = [
            'foo' => 'FOO',
            'bar' => 'bar',
            'qux' => 'qux',
        ];

        $controller = new FakeController;

        $this->assertEquals($expected, $controller->getData($request));
    }
}

final class FakeController
{
    use SanitizesRequests;

    public function getData(Request $request): array
    {
        $input = $this->sanitize($request, [
            'foo' => 'strtoupper',
            'bar' => 'trim|strtolower',
        ]);

        return $input;
    }
}

if (!function_exists('Alfheim\Sanitizer\Laravel\app')) {
    function app(string $abstract): Sanitizer
    {
        if ($abstract === Sanitizer::class) {
            return new Sanitizer;
        }
    }
}
