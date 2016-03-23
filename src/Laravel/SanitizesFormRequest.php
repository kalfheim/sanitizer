<?php declare(strict_types=1);

namespace Alfheim\Sanitizer\Laravel;

use Alfheim\Sanitizer\Sanitizer;
use Illuminate\Http\Request;

/*
 * This trait should only be used on FormRequest objects. It provides an easy
 * way to abstract away the sanitation logic from your controllers.
 *
 * A quick example of how it may be used:
 *
 *     <?php
 *     // app/Http/Requests/FooRequest.php
 *
 *     namespace App\Http\Requests;
 *
 *     class FooRequest extends Request
 *     {
 *         use SanitizesFormRequest;
 *
 *         public function sanitize()
 *         {
 *             // This is where you define the rules which will be passed on to
 *             // the sanitizer.
 *             return [
 *                 'name'  => 'trim|ucwords',
 *                 'email' => 'trim|mb_strtolower',
 *             ];
 *         }
 *     }
 *     ?>
 *
 *     <?php
 *     // app/Http/Controllers/FooController.php
 *
 *     namespace App\Controllers;
 *
 *     use App\Http\Requests\FooRequest;
 *
 *     class FooController
 *     {
 *         public function store(FooRequest $request)
 *         {
 *             // At this point, the $request will be both sanitized and
 *             // validated. So you may go ahead and access the input as usual:
 *
 *             $request->all();
 *             $request->input('name');
 *             $request->only(['name', 'email']);
 *             // etc...
 *         }
 *     }
 *     ?>
 *
 * This trait is magic.
 */
trait SanitizesFormRequest
{
    /** @var bool */
    private $hasSanitized = false;

    /**
     * Get the sanitation rules for this request.
     *
     * @return string|array
     */
    abstract public function sanitation();

    /**
     * Override `Illuminate\Http\Request::getInputSource()` to perform the
     * sanitation.
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function getInputSource()
    {
        if ($this->hasSanitized) {
            return parent::getInputSource();
        }

        $factory = app(Sanitizer::class)->rules($this->sanitation());

        $input = parent::getInputSource();

        foreach ($factory->sanitize($input->all()) as $key => $value) {
            $input->set($key, $value);
        }

        $this->hasSanitized = true;

        return $input;
    }
}
