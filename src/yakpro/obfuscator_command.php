#!/usr/bin/env php
<?php
/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

require __DIR__ . '/../../../../../vendor/autoload.php';

require_once 'include/get_default_defined_objects.php';

use pmaslak\PhpObfuscator;
use PhpParser\Error;
use PhpParser\PrettyPrinter;

require_once 'include/classes/scrambler.php';
require_once 'include/functions.php';
include      'include/retrieve_config_and_arguments.php';
require_once 'include/classes/parser_extensions/my_pretty_printer.php';
require_once 'include/classes/parser_extensions/my_node_visitor.php';

if ($conf->obfuscate_string_literal) {
    $prettyPrinter = new myPrettyprinter;
} else {
    $prettyPrinter = new PrettyPrinter\Standard;
}



require_once 'obfuscator_controller.php';
