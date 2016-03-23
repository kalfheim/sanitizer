<?php

use Alfheim\Sanitizer\Sanitizer;
use Alfheim\Sanitizer\Registrar\BaseRegistrar;

class RegistrarTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_have_registred_the_function()
    {
        $registrar = $this->makeRegistrar();

        $this->assertTrue($registrar->isRegistred('specialTrim'));
        $this->assertTrue($registrar->isRegistred('withArgs'));

        $this->assertFalse($registrar->isRegistred('forGoodMeasure'));
    }

    /** @test */
    public function it_should_sanitize_the_input()
    {
        $original = 'foo ';
        $expected = 'foo';

        $factory = Sanitizer::make('specialTrim')->setRegistrar(
            $this->makeRegistrar()
        );

        $this->assertSame($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_sanitize_with_array_input()
    {
        $original = ['str' => 'foo '];
        $expected = ['str' => 'FOO'];

        $factory = Sanitizer::make([
            'str' => 'specialTrim|strtoupper',
        ])->setRegistrar($this->makeRegistrar());

        $this->assertSame($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_sanitize_with_args()
    {
        $original = ['str' => 'foo'];
        $expected = ['str' => 'FOOFOOBAR'];

        $factory = Sanitizer::make([
            'str' => 'withArgs:foo:{{ VALUE }}:bar|strtoupper',
        ])->setRegistrar($this->makeRegistrar());

        $this->assertSame($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_sanitize_with_array_rules()
    {
        $original = ['str' => 'foo'];
        $expected = ['str' => 'FOOFOOBAR'];

        $factory = Sanitizer::make([
            'str' => [
                'withArgs:foo:{{ VALUE }}:bar',
                function ($value) {
                    return strtoupper($value);
                },
            ],
        ])->setRegistrar($this->makeRegistrar());

        $this->assertSame($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_return_false_if_already_registred()
    {
        $registrar = new BaseRegistrar;

        $this->assertTrue($registrar->register('foo', 'bar'));
        $this->assertFalse($registrar->register('foo', 'bar'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectecExceptionMessage Could not resolve [idontexist] from the registrar.
     */
    public function it_should_throw_exception_if_unable_to_resolve()
    {
        $registrar = new BaseRegistrar;

        $registrar->resolve('idontexist');
    }

    private function makeRegistrar()
    {
        $registrar = new BaseRegistrar;

        $registrar->register('specialTrim', function ($value) {
            return trim($value);
        });

        $registrar->register('withArgs', function ($foo, $value, $bar) {
            return $foo.$value.$bar;
        });

        return $registrar;
    }
}
