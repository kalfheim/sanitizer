<?php

use Alfheim\Sanitizer\Sanitizer;

class SanitizerTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_trim_the_string()
    {
        $original = ['str' => 'lorem ipsum '];
        $expected = ['str' => 'lorem ipsum'];

        $factory = Sanitizer::make([
            'str' => 'trim',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_trim_and_lower_the_string()
    {
        $original = ['str' => 'Lorem Ipsum '];
        $expected = ['str' => 'lorem ipsum'];

        $factory = Sanitizer::make([
            'str' => 'trim|strtolower',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_get_the_last_character_and_uppercase_it()
    {
        $original = ['str' => 'Lorem'];
        $expected = ['str' => 'M'];

        $factory = Sanitizer::make([
            'str' => 'substr:-1:1|strtoupper',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_reposition_the_value_in_the_arguments_list()
    {
        $original = ['str' => 'foo'];
        $expected = ['str' => 'onefootwo'];

        $factory = Sanitizer::make([
            'str' => 'someFunction:one:{{ VALUE }}:two',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_sanitize_multiple_fields()
    {
        $original = ['foo' => 'LOREM IPSUM ', 'bar' => 'foo'];
        $expected = ['foo' => 'lorem ipsum',  'bar' => 'bar'];

        $factory = Sanitizer::make([
            'foo' => 'trim|strtolower',
            'bar' => 'str_replace:foo:bar:{{ VALUE }}',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_accept_arrays()
    {
        $original = ['str' => 'Lorem Ipsum '];
        $expected = ['str' => 'lorem ipsum'];

        $factory = Sanitizer::make([
            'str' => ['trim', 'strtolower'],
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_sanitise_nested_arrays()
    {
        $original = [
            'subarray' => [
                'field'       => ' test ',
                'subsubarray' => [
                    'field' => ' test ',
                ],
            ],
        ];
        $expected = [
            'subarray' => [
                'field'       => 'TEST',
                'subsubarray' => [
                    'field' => 'TEST',
                ],
            ],
        ];

        $factory = Sanitizer::make([
            '*'                              => 'trim',
            'subarray.*.field'               => 'strtoupper',
            'subarray.*.subsubarray.*.field' => 'strtoupper',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_sanitize_data_by_reference()
    {
        $original = ['str' => 'Lorem Ipsum '];
        $expected = ['str' => 'lorem ipsum'];

        $factory = Sanitizer::make([
            'str' => ['trim', 'strtolower'],
        ]);

        $factory->sanitizeByRef($original);

        $this->assertEquals($expected, $original);
    }

    /** @test */
    public function it_should_sanitize_all_fields_with_global_rules()
    {
        $original = ['foo' => 'LOREM IPSUM ', 'bar' => ' Foo'];
        $expected = ['foo' => 'lorem ipsum',  'bar' => 'foo'];

        $factory = Sanitizer::make([
            '*' => 'trim|strtolower',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_apply_global_rules_first()
    {
        $original = ['bar' => 'foo '];
        $expected = ['bar' => 'changed'];

        $factory = Sanitizer::make([
            'bar' => 'trim',
            '*'   => 'str_replace:foo :changed :{{ VALUE }}',
        ]);

        $this->assertEquals($expected, $factory->sanitize($original));
    }

    /** @test */
    public function it_should_apply_rules_to_single_variable()
    {
        $original = 'foo ';
        $expected = 'foo';

        $value = Sanitizer::make([
            '*' => 'trim',
        ])->sanitize($original);

        $this->assertEquals($expected, $value);
    }

    /** @test */
    public function it_should_convert_single_rule_into_global_rule()
    {
        $original = 'foo ';
        $expected = 'foo';

        $value = Sanitizer::make('trim')->sanitize($original);

        $this->assertEquals($expected, $value);
    }

    /** @test */
    public function it_should_accept_closure()
    {
        $original = 'foo ';
        $expected = 'foo';

        $value = Sanitizer::make(function ($value) {
            return trim($value);
        })->sanitize($original);

        $this->assertEquals($expected, $value);
    }

    /** @test */
    public function it_should_accept_closure_as_part_of_an_array()
    {
        $original = 'foo ';
        $expected = 'FOO';

        $value = Sanitizer::make([
            '*' => [
                'strtoupper',
                function ($value) {
                    return trim($value);
                },
            ],
        ])->sanitize($original);

        $this->assertEquals($expected, $value);
    }

    /** @test */
    public function it_should_apply_rules_to_single_variable_by_reference()
    {
        $original = 'Foo ';
        $expected = 'foo';

        Sanitizer::make('trim|strtolower')->sanitizeByRef($original);

        $this->assertEquals($expected, $original);
    }

    /** @test */
    public function it_should_be_possible_to_explicitly_bind_the_value_to_first_position()
    {
        $original = 'foo';
        $expected = 'f';

        $value = Sanitizer::make('substr:{{ VALUE }}:0:1')->sanitize($original);

        $this->assertEquals($expected, $value);
    }

    /** @test */
    public function it_should_call_filter_method_on_object()
    {
        $original = ['foo' => 'bar'];
        $expected = ['foo' => 'foobar'];

        $value = Sanitizer::make([
            'foo' => new MyFilterClass,
        ])->sanitize($original);

        $this->assertEquals($expected, $value);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not resolve callable for [fe437bb_IWillNeverExist]
     */
    public function it_should_throw_exception_of_invalid_rule()
    {
        $factory = Sanitizer::make([
            'foo' => 'fe437bb_IWillNeverExist',
        ]);

        $factory->sanitize(['foo' => 'bar']);
    }

    /** @test */
    public function it_should_set_rules_on_the_object()
    {
        $factory = (new Sanitizer)->rules('strtoupper');
        $this->assertEquals('HELLO', $factory->sanitize('hello'));
    }

    /** @test */
    public function it_should_chain()
    {
        $value = (new Sanitizer)->rules('strtoupper')->sanitize('hello');
        $this->assertEquals('HELLO', $value);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Sanitation rules are already defined for field [*]
     */
    public function it_should_throw_exception_if_rule_already_exists()
    {
        $factory = new Sanitizer;

        $factory->rules('trim'); // sets a global rule

        $factory->rules(['*' => 'iwillfail']);
    }
}

function someFunction($one, $value, $two)
{
    return $one.$value.$two;
}

class MyFilterClass
{
    public function filterFoo($value)
    {
        return 'foo'.$value;
    }
}
