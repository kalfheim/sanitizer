<?php

namespace Alfheim\Sanitizer\Laravel;

use Alfheim\Sanitizer\Sanitizer;
use Symfony\Component\HttpFoundation\Request;

class FormRequestTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_sanitize_all()
    {
        $request = $this->newFormRequest(TrimFooRequest::class, [
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
        $request = $this->newFormRequest(TrimFooRequest::class, [
            'foo' => ' foo',
            'bar' => ' bar',
        ]);

        $this->assertEquals('foo', $request->foo);
        $this->assertEquals(' bar', $request->bar);
    }

    /** @test */
    public function it_should_sanitize_only()
    {
        $request = $this->newFormRequest(TrimFooRequest::class, [
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
        $request = $this->newFormRequest(TrimFooRequest::class, [
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
        $request = $this->newFormRequest(YellRequest::class, [
            'foo' => 'hi',
            'bar' => 'hello',
        ]);

        $expected = [
            'foo' => 'HI',
            'bar' => 'HELLO',
        ];

        $this->assertEquals($expected, $request->all());
    }

    /** @test */
    public function it_should_do_nothing_when_no_rules()
    {
        $request = $this->newFormRequest(NoRulesRequest::class, [
            'foo' => 'hi',
            'bar' => 'hello',
        ]);

        $expected = [
            'foo' => 'hi',
            'bar' => 'hello',
        ];

        $this->assertEquals($expected, $request->all());
    }

    private function newFormRequest($kind, array $input)
    {
        $base = Request::create('foo', 'POST', $input);

        $form = new $kind(
            $base->query->all(), $base->request->all(), $base->attributes->all(),
            $base->cookies->all(), [], $base->server->all(), $base->getContent()
        );

        return $form;
    }
}

class TrimFooRequest extends FormRequest
{
    public function sanitize()
    {
        return [
            'foo' => 'trim',
        ];
    }
}

class YellRequest extends FormRequest
{
    public function sanitize()
    {
        return 'strtoupper';
    }
}

class NoRulesRequest extends FormRequest
{
    //
}

if (! function_exists('Alfheim\Sanitizer\Laravel\app')) {
    function app($abstract)
    {
        if ($abstract === Sanitizer::class) {
            return new Sanitizer;
        }
    }
}
