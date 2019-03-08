<?php

class StrTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @dataProvider providerTemplate
     */
    public function testTemplate($template, $vars, $expected)
    {
        $result = \func_all\str_template($template, $vars);
        $this->assertSame($expected, $result);
    }

    public function providerTemplate()
    {
        return [
            [
                'foo {bar} baz',
                ['bar' => 123],
                'foo 123 baz'
            ],
            [
                '{foo} bar baz',
                ['bar' => 123],
                '{foo} bar baz'
            ],
            [
                'foo {bar} baz',
                [],
                'foo {bar} baz'
            ],
        ];
    }

}