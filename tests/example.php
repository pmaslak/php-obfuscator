<?php

require __DIR__ . '/../vendor/autoload.php';

require '../src/ObfuscatorInterface.php';
require '../src/Configuration.php';
require '../src/Obfuscator.php';

$obfuscator = new pmaslak\Obfuscator([
    'debug' => false,
    'target' => '/Users/pawelmaslak/Projects/obfuscator_git/obfuscation_result/',
    'obfuscation_options' => ['no-obfuscate-variable-name', 'no-obfuscate-method-name', 'no-obfuscate-class-name', 'no-obfuscate-property-name']
]);

//$obfuscator->obfuscateFile('to_obfuscate/file_to_obfuscate.php', 'new_name.php');

//$obfuscator->obfuscateDirectory('/Users/xyz/Projects/obfuscator_git/tests/to_obfuscate/');

