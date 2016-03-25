<?php

namespace Alfheim\Sanitizer\Laravel;

use Alfheim\Sanitizer\Sanitizer;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

/*
 * This class is an extension of the `Illuminate\Foundation\Http\FormRequest`
 * class. It provides an easy way to abstract away the sanitation logic from
 * your controllers.
 *
 * A quick example of how it may be used:
 *
 * To keep it simple, I'll tell our base `App\Http\Requests\Request` class to
 * extend `Alfheim\Sanitizer\Laravel\FormRequest` instead of the default
 * `Illuminate\Foundation\Http\FormRequest`. Your base request class should look
 * something like this:
 *
 *     <?php
 *     // app/Http/Requests/Request.php
 *
 *     namespace App\Http\Requests;
 *
 *     use Alfheim\Sanitizer\Laravel\FormRequest;
 *
 *     abstract class Request extends FormRequest
 *     {
 *         //
 *     }
 *     ?>
 *
 *     <?php
 *     // app/Http/Requests/FooRequest.php
 *
 *     namespace App\Http\Requests;
 *
 *     class FooRequest extends Request
 *     {
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
 */
abstract class FormRequest extends BaseFormRequest
{
    /**
     * Perform the sanitation by overriding the
     * `Symfony\Component\HttpFoundation::initialize` method. The `$request`
     * argument will be sanitized according to the rules defined in the
     * `static::sanitize` method.
     *
     * {@inheritdoc}
     */
    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        if (! empty($request) && ($rules = $this->sanitize())) {
            $sanitizer = app(Sanitizer::class)->rules($rules);

            $request = $sanitizer->sanitize($request);
        }

        parent::initialize(
            $query, $request, $attributes, $cookies, $files, $server, $content
        );
    }

    /**
     * Get the sanitation rules for this form request.
     *
     * @return array
     */
    public function sanitize()
    {
        return [];
    }
}
