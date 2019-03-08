<?php

class ObTest extends \PHPUnit\Framework\TestCase
{

    public function testInclude()
    {
        $params = [
            'name' => 'Евгений',
        ];
        $result = \func_all\ob_include(__DIR__ . '/ob_include.phtml', $params);
        $expected = "<h1>ObTest</h1>\n\nHello Евгений";
        $this->assertSame($expected, $result);
    }

}