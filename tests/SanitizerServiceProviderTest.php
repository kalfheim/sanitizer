<?php declare(strict_types=1);

use Mockery as m;

use Alfheim\Sanitizer\{
    Sanitizer,
    SanitizerServiceProvider,
    Registrar\RegistrarInterface
};

use Illuminate\Contracts\Foundation\Application;

class SanitizerServiceProviderTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $provider;

    public function setUp()
    {
        $this->app = m::mock(Application::class);

        $this->provider = new SanitizerServiceProvider($this->app);
    }

    /** @test */
    public function it_should_be_deferred()
    {
        $this->assertTrue($this->provider->isDeferred());
    }

    /** @test */
    public function it_should_tell_what_it_provides()
    {
        $this->assertEquals(
            [Sanitizer::class, RegistrarInterface::class],
            $this->provider->provides()
        );
    }

    /** @test */
    public function it_should_register_the_services()
    {
        $this->app->shouldReceive('bind')->with(Sanitizer::class, m::type('closure'))->once();
        $this->app->shouldReceive('singleton')->with(RegistrarInterface::class, m::type('closure'))->once();

        $this->provider->register();
    }
}
