<?php


require __DIR__ . '/../vendor/autoload.php';

require '../src/ObfuscatorInterface.php';
require '../src/Configuration.php';
require '../src/Obfuscator.php';

$obfuscator = new pmaslak\Obfuscator([
    'debug' => false,
    'files_target' => 'obfuscated_file.php',
    'obfuscation_options' => ['no-obfuscate-variable-name', 'no-obfuscate-method-name', 'no-obfuscate-class-name', 'no-obfuscate-property-name']
]);
$obfuscator->obfuscateFile('file_to_obfuscate.php');

//php yakpro-po.php ../../tests/file_to_obfuscate.php --debug --file_to_obfuscate2.php

