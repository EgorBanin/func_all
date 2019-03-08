<?php

class ArrTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider providerUsearch
     */
    public function testUsearch($arr, $func, $expectedResult)
    {
        $key = \func_all\arr_usearch($arr, $func);
        $this->assertSame($expectedResult, $key);
    }

    public function providerUsearch()
    {
        return [
            [
                [
                    '/' => 'index.php',
                    '/(?<dir>[^/]+)' => '{dir}/index.php',
                    '/(?<dir>[^/]+)/(?<id>\d+)' => '{dir}/view.php',
                ],
                function ($key, $value) {
                    $result = preg_match(
                        '~^' . $key . '$~',
                        '/fooBar'
                    );
                    return $result;
                },
                '/(?<dir>[^/]+)'
            ],
            [
                ['foo', 'bar', 'baz'],
                function ($key, $value) {
                    return (
                        $key % 2 === 0
                        && strpos($value, 'b') === 0
                    );
                },
                2
            ],
            [
                ['foo', '_bar', '_baz'],
                function ($key, $value) {
                    return (
                        $key % 2 === 0
                        && strpos($value, 'b') === 0
                    );
                },
                false
            ],
            [
                [],
                function ($key, $value) {
                    return true;
                },
                false
            ],
        ];
    }
}