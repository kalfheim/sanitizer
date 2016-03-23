<?php

namespace Alfheim\Sanitizer\Laravel;

use Alfheim\Sanitizer\Sanitizer;
use Illuminate\Http\Request;

/*
 * This trait can be used on any class. However, you'll probably mostly use it
 * on your controllers. Similarly to the `ValidatesRequests` trait, this will
 * allow for:
 *
 *     $input = $this->sanitize($request, [ rules ... ]);
 *
 * Of course, the variable can be named whatever you see fit.
 *
 * Keep in mind that doing this will NOT commit changes to the request object,
 * which means the sanitized input is only accessible through the variable it
 * is stored onto.
 *
 * So, to fetch request input, do the following:
 *
 *     $userEmail = $input['email'];
 *
 * As opposed to what you may be used to:
 *
 *     $userEmail = $request->input('email');
 *     //            Request::input('email')
 */
trait SanitizesRequests
{
    /**
     * Run a sanitizer on a request object and return the sanitized data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array                     $ruleset
     *
     * @return array
     */
    public function sanitize(Request $request, array $ruleset)
    {
        $factory = app(Sanitizer::class)->rules($ruleset);

        return $factory->sanitize($request->all());
    }
}
