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

    /**
     * @dataProvider providerFlatten
     * @param array $arr
     * @param array $expected
     */
    public function testFlatten(array $arr, array $expected)
    {
        $this->assertSame($expected, \func_all\arr_flatten($arr));
    }

    public function providerFlatten()
    {
        return [
            [
                [],
                [],
            ],
            [
                [1, 2, 'foo'],
                [1, 2, 'foo'],
            ],
            [
                ['x' => 1, 'y' => 2, ['foo', 'bar', []]],
                [1, 2, 'foo', 'bar'],
            ],
            [
                ['x' => 1, 'y' => 2, ['foo', 'bar', ['x' => 2]]],
                [1, 2, 'foo', 'bar', 2],
            ],
        ];
    }

    /**
     * @dataProvider providerFlattenUseKeys
     * @param array $arr
     * @param array $expected
     */
    public function testFlattenUseKeys(array $arr, array $expected)
    {
        $this->assertSame($expected, \func_all\arr_flatten($arr, true));
    }

    public function providerFlattenUseKeys()
    {
        return [
            [
                [],
                [],
            ],
            [
                [1, 2, 'foo'],
                [1, 2, 'foo'],
            ],
            [
                ['x' => 1, 'y' => 2, ['foo', 'bar', []]],
                ['x' => 1, 'y' => 2, 'foo', 'bar'],
            ],
            [
                ['x' => 1, 'y' => 2, ['foo', 'bar', ['x' => 2]]],
                ['x' => 2, 'y' => 2, 'foo', 'bar'],
            ],
        ];
    }
}