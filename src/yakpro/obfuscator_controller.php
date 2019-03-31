<?php
/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

$t_scrambler = [];
$parserElementTypes = ['variable','function','method','property','class','class_constant','constant','label'];

foreach ($parserElementTypes as $scramble_what) {
    $t_scrambler[$scramble_what] = new Scrambler($scramble_what, $conf, ($process_mode == 'directory') ? $target_directory : null);
}

use PhpParser\NodeTraverser;
$traverser = new NodeTraverser;
$traverser->addVisitor(new MyNodeVisitor);

$obfuscated_str = obfuscate($source_file);

if ($obfuscated_str === null) {
    exit;
}

if ($target_file === '') {
    echo $obfuscated_str . PHP_EOL . PHP_EOL;
    exit;
}

file_put_contents($target_file, $obfuscated_str);
