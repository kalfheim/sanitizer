<?php

namespace Alfheim\Sanitizer\Laravel;

use Alfheim\Sanitizer\Sanitizer;
use Illuminate\Http\Request;

class SanitizesFormRequestTraitTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_sanitize_all()
    {
        $request = $this->newRequest(TrimFooRequest::class, [
            'foo' => ' foo',
            'bar' => ' bar',
        ]);

        $expected = [
            'foo' => 'foo',
            'bar' => ' bar',
        ];

        $this->assertEquals($expected, $request->all());
    }

    /** @test */
    public function it_should_sanitize_dynamic_properties()
    {
        $request = $this->newRequest(TrimFooRequest::class, [
            'foo' => ' foo',
            'bar' => ' bar',
        ]);

        $this->assertEquals('foo', $request->foo);
        $this->assertEquals(' bar', $request->bar);
    }

    /** @test */
    public function it_should_sanitize_only()
    {
        $request = $this->newRequest(TrimFooRequest::class, [
            'foo' => ' foo',
            'bar' => ' bar',
            'baz' => 'baz',
        ]);

        $expected = [
            'foo' => 'foo',
            'baz' => 'baz',
        ];

        $this->assertEquals($expected, $request->only(['foo', 'baz']));
    }

    /** @test */
    public function it_should_sanitize_except()
    {
        $request = $this->newRequest(TrimFooRequest::class, [
            'foo' => ' foo',
            'bar' => ' bar',
            'baz' => 'baz',
        ]);

        $expected = [
            'foo' => 'foo',
            'baz' => 'baz',
        ];

        $this->assertEquals($expected, $request->except(['bar']));
    }

    /** @test */
    public function it_should_sanitize_with_global_rule()
    {
        $request = $this->newRequest(YellRequest::class, [
            'foo' => 'hi',
            'bar' => 'hello',
        ]);

        $expected = [
            'foo' => 'HI',
            'bar' => 'HELLO',
        ];

        $this->assertEquals($expected, $request->all());
    }

    private function newRequest($kind, array $input)
    {
        return $kind::createFromBase(Request::create('foo', 'POST', $input));
    }
}

class TrimFooRequest extends Request
{
    use SanitizesFormRequest;

    public function sanitation()
    {
        return [
            'foo' => 'trim',
        ];
    }
}

class YellRequest extends Request
{
    use SanitizesFormRequest;

    public function sanitation()
    {
        return 'strtoupper';
    }
}

if (! function_exists('Alfheim\Sanitizer\Laravel\app')) {
    function app($abstract)
    {
        if ($abstract === Sanitizer::class) {
            return new Sanitizer;
        }
    }
}
