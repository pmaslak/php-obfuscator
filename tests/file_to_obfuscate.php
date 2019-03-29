<?php

/**
 * example file for tests
 */

$variable1 = 'something';
$variable2 = (string)$variable1;

class ExampleClass
{
    private $var1 = 1;
    public $var2 = '2';

    public function __construct()
    {
        $this->var1 = 2;
    }

    public function process()
    {

    }
}

$class = new ExampleClass();
$class->process();

