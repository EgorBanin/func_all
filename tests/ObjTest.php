<?php

class ObjTest extends \PHPUnit\Framework\TestCase
{

    public function testInit()
    {
        $obj = new class
        {
            private $foo = 'foo';

            public function getFoo()
            {
                return $this->foo;
            }
        };
        \func_all\obj_init($obj, ['foo' => 'bar']);
        $this->assertSame('bar', $obj->getFoo()); // ]:->
    }

    public function testToArray()
    {
        $obj = new class
        {
            public $foo = 'foo';
            private $bar = 'bar';
        };
        $result = \func_all\obj_to_array($obj);
        $this->assertSame(['foo' => 'foo', 'bar' => 'bar'], $result); // ]:->
    }

}