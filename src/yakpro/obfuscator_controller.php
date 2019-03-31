<?php
/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

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
