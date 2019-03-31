#!/usr/bin/env php
<?php
//========================================================================
// Author:  Pascal KISSIAN
// Resume:  http://pascal.kissian.net
//
// Copyright (c) 2015-2019 Pascal KISSIAN
//
// Published under the MIT License
//========================================================================

require __DIR__ . '/../../vendor/autoload.php';

require_once 'include/get_default_defined_objects.php';

use pmaslak\PhpObfuscator;
use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;

require_once 'include/classes/scrambler.php';
require_once 'include/functions.php';
include      'include/retrieve_config_and_arguments.php';
require_once 'include/classes/parser_extensions/my_pretty_printer.php';
require_once 'include/classes/parser_extensions/my_node_visitor.php';

$parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
$traverser = new NodeTraverser;

if ($conf->obfuscate_string_literal) {
    $prettyPrinter = new myPrettyprinter;
} else {
    $prettyPrinter = new PrettyPrinter\Standard;
}

$t_scrambler = [];
$parserElementTypes = ['variable','function','method','property','class','class_constant','constant','label'];

foreach ($parserElementTypes as $scramble_what) {
    $t_scrambler[$scramble_what] = new Scrambler($scramble_what, $conf, ($process_mode == 'directory') ? $target_directory : null);
}

if ($whatis !== '') {
    if ($whatis{0} == '$') {
        $whatis = substr($whatis, 1);
    }

    foreach ($parserElementTypes as $scramble_what) {
        if ( ( $s = $t_scrambler[$scramble_what]->unscramble($whatis)) !== '') {
            switch($scramble_what)
            {
                case 'variable':
                case 'property':
                    $prefix = '$';
                    break;
                default:
                    $prefix = '';
            }
            echo "$scramble_what: {$prefix}{$s}".PHP_EOL;
        }
    }
    exit;
}

$traverser->addVisitor(new MyNodeVisitor);

if ($process_mode == 'directory') {
    throw new \Exception('Trying to obfuscate all directory instead of use single file');
}

$obfuscated_str =  obfuscate($source_file);
if ($obfuscated_str === null) {
    exit;
}
if ($target_file === '') {
    echo $obfuscated_str . PHP_EOL . PHP_EOL;
    exit;
}

file_put_contents($target_file, $obfuscated_str);
