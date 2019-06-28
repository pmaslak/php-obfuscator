#!/usr/bin/env php
<?php
/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

require __DIR__ . '/../../vendor/autoload.php';

require_once 'include/get_default_defined_objects.php';
use pmaslak\PhpObfuscator;
use pmaslak\PhpObfuscator\MyPrettyPrinter;
use pmaslak\PhpObfuscator\Config;
use PhpParser\Error;
use PhpParser\PrettyPrinter;

Config::$preDefinedClasses = array_flip(array_map('strtolower', get_declared_classes()));
Config::$preDefinedInterfaces = array_flip(array_map('strtolower', get_declared_interfaces()));
Config::$preDefinedTraits = function_exists('get_declared_traits') ? array_flip(array_map('strtolower', get_declared_traits())) : [];
Config::$preDefinedClasses = array_merge(Config::$preDefinedClasses, Config::$preDefinedInterfaces, Config::$preDefinedTraits);


require_once 'include/functions.php';
include      'include/retrieve_config_and_arguments.php';

if ($conf->obfuscate_string_literal) {
    $prettyPrinter = new MyPrettyPrinter;
} else {
    $prettyPrinter = new PrettyPrinter\Standard;
}



require_once 'obfuscator_controller.php';
