<?php

use Alfheim\Sanitizer\Sanitizer;
use Alfheim\Sanitizer\Registrar\LaravelRegistrar;

use Mockery as m;
use Illuminate\Contracts\Container\Container;

class LaravelRegistrarTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_resolve_from_the_laravel_container()
    {
        $FUNCNAME = 'mySpecialFunction';

        $container = m::mock(Container::class);
        $container->shouldReceive('bound')->with($FUNCNAME)->once()->andReturn(true);
        $container->shouldReceive('make')->with($FUNCNAME)->twice()->andReturn('trim');

        $registrar = new LaravelRegistrar($container);

        $registrar->register($FUNCNAME, 'trim');

        $this->assertSame('trim', $registrar->resolve($FUNCNAME));

        $factory = Sanitizer::make($FUNCNAME)->setRegistrar($registrar);

        $this->assertSame('foo', $factory->sanitize('foo '));
    }

    /** @test */
    public function it_should_call_method_on_object_using_at_notation()
    {
        $container = m::mock(Container::class);
        $container->shouldReceive('bound')->with('MyCoolClass')->once()->andReturn(true);
        $container->shouldReceive('make')->with('MyCoolClass')->once()->andReturn(new MyCoolClass);

        $registrar = new LaravelRegistrar($container);

        $registrar->register('myOtherSpecialFunction', 'MyCoolClass@coolMethod');

        $factory = Sanitizer::make('myOtherSpecialFunction')->setRegistrar($registrar);
        $this->assertSame('FOO', $factory->sanitize('foo'));
    }

    /** @test */
    public function it_should_pass_call_to_base_registrar()
    {
        $container = m::mock(Container::class);
        $container->shouldReceive('bound')->with('fancyTrim')->once()->andReturn(false);

        $registrar = new LaravelRegistrar($container);

        $registrar->register('fancyTrim', 'trim');

        $factory = Sanitizer::make('fancyTrim')->setRegistrar($registrar);
        $this->assertSame('foo', $factory->sanitize(' foo'));
    }
}

class MyCoolClass
{
    public function coolMethod($value)
    {
        return strtoupper($value);
    }

    public function filterFoo($value)
    {
        return 'foo'.$value;
    }
}
