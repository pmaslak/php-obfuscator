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
    protected $var_4 = ['some_string', "some_string_2"];

    public function __construct()
    {
        $this->var1 = 2;
    }

    //comment
    public function process()
    {

    }


    public function __wakeup()
    {
        $this->var2 = 'wake Up!';
    }

    public function __call($name, $arguments)
    {
        throw new \Exception('Cannot call ' . $name);
    }
}

$class = new ExampleClass();
$class->process();

