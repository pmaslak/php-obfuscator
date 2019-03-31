<?php
//========================================================================
// Author:  Pascal KISSIAN
// Resume:  http://pascal.kissian.net
//
// Copyright (c) 2015-2019 Pascal KISSIAN
//
// Published under the MIT License
//          Consider it as a proof of concept!
//          No warranty of any kind.
//          Use and abuse at your own risks.
//========================================================================

use PhpParser\ParserFactory;

function obfuscate($filename)
{
    global $conf;
    global $traverser, $prettyPrinter;

    $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);

    $src_filename = $filename;
    $tmp_filename = $first_line = '';
    $t_source = file($filename);

    if (substr($t_source[0], 0, 2) == '#!') {
        $first_line = array_shift($t_source);
        $tmp_filename = tempnam(sys_get_temp_dir(), 'po-');
        file_put_contents($tmp_filename, implode(PHP_EOL, $t_source));
        $filename = $tmp_filename; // override 
    }

    try {
        $source = php_strip_whitespace($filename);
//        fprintf(STDERR,"Obfuscating %s%s",$src_filename,PHP_EOL);
        //var_dump( token_get_all($source));    exit;
        if ($source === '') {
            if ($conf->allow_and_overwrite_empty_files) {
                return $source;
            }

            throw new Exception("Error obfuscating [$src_filename]: php_strip_whitespace returned an empty string!");
        }

        try {
            // PHP-Parser returns the syntax tree
            $stmts = $parser->parse($source);
        }
        // if an error occurs, then redo it without php_strip_whitespace, in order to display the right line number with error!
        catch (PhpParser\Error $e) {
            $source = file_get_contents($filename);
            $stmts  = $parser->parse($source);
        }

        if ($conf::isDebug()) {
            $source = file_get_contents($filename);
            $stmts = $parser->parse($source);
            var_dump($stmts);
        }

        //Use PHP-Parser function to traverse the syntax tree and obfuscate names
        $stmts = $traverser->traverse($stmts);

        if ($conf->shuffle_stmts && (count($stmts) > 2))
        {
            $last_inst  = array_pop($stmts);
            $last_use_stmt_pos = -1;

            foreach($stmts as $i => $stmt)                      // if a use statement exists, do not shuffle before the last use statement
            {                                                   //TODO: enhancement: keep all use statements at their position, and shuffle all sub-parts
                if ( $stmt instanceof PhpParser\Node\Stmt\Use_ ) $last_use_stmt_pos = $i;
            }

            if ($last_use_stmt_pos < 0) {
                $stmts_to_shuffle = $stmts;
                $stmts = [];
            } else {
                $stmts_to_shuffle = array_slice($stmts, $last_use_stmt_pos + 1);
                $stmts = array_slice($stmts, 0, $last_use_stmt_pos + 1);
            }

            $stmts = array_merge($stmts, shuffle_statements($stmts_to_shuffle));
            $stmts[] = $last_inst;
        }

        //Use PHP-Parser function to output the obfuscated source, taking the modified obfuscated syntax tree as input
        $code = trim($prettyPrinter->prettyPrintFile($stmts));

        if (isset($conf->strip_indentation) && $conf->strip_indentation) {
            $core = new \pmaslak\PhpObfuscator\Core();
            $code = $core->minifyPhp($code);
        }

        $endcode = substr($code,6);

        $code  = '<?php' . PHP_EOL;
         // comment obfuscated source
//        $code .= $conf->get_comment();
        if (isset($conf->extract_comment_from_line) && isset($conf->extract_comment_to_line)) {
            $t_source = file($filename);
            for ($i = $conf->extract_comment_from_line - 1; $i < $conf->extract_comment_to_line; ++$i) $code .= $t_source[$i];
        }

        if (isset($conf->user_comment)) {
            $code .= '/*' . PHP_EOL . $conf->user_comment . PHP_EOL . '*/' . PHP_EOL;
        }

        $code .= $endcode;

        if (($tmp_filename != '') && ($first_line != '')) {
            $code = $first_line . $code;
            unlink($tmp_filename);
        }

        return trim($code);
    } catch (Exception $e) {
        fprintf(STDERR,"Obfuscator Parse Error [%s]:%s\t%s%s", $filename,PHP_EOL, $e->getMessage(),PHP_EOL);
        return null;
    }
}

function check_preload_file($filename)
{
    for ($ok = false; ;) {
        if (!file_exists($filename)) {
            return false;
        }

        if (!is_readable($filename)) {
            fprintf(STDERR, "Warning:[%s] is not readable!%s", $filename, PHP_EOL);
            return false;
        }

        $fp     = fopen($filename,"r"); if($fp===false) break;
        $line   = trim(fgets($fp));     if ($line!='<?php')                                     { fclose($fp); break; }
        $line   = trim(fgets($fp));     if ($line!='// YAK Pro - Php Obfuscator: Preload File') { fclose($fp); break; }
        fclose($fp);
        $ok     = true;
        break;
    }
    if (!$ok) fprintf(STDERR,"Warning:[%s] is not a valid yakpro-po preload file!%s\tCheck if file is php, and if magic line is present!%s",$filename,PHP_EOL,PHP_EOL);
    return $ok;
}

function check_config_file($filename)
{
    for ($ok = false; ;) {
        if (!file_exists($filename)) return false;
        if (!is_readable($filename)) {
            fprintf(STDERR,"Warning:[%s] is not readable!%s",$filename,PHP_EOL);
            return false;
        }
        $fp     = fopen($filename,"r"); if($fp===false) break;
        $line   = trim(fgets($fp));     if ($line!='<?php')                                     { fclose($fp); break; }
        $line   = trim(fgets($fp));     if ($line!='// YAK Pro - Php Obfuscator: Config File')  { fclose($fp); break; }
        fclose($fp);
        $ok     = true;
        break;
    }
    if (!$ok) fprintf(STDERR,"Warning:[%s] is not a valid yakpro-po config file!%s\tCheck if file is php, and if magic line is present!%s",$filename,PHP_EOL,PHP_EOL);
    return $ok;
}

function create_context_directories($target_directory)
{
    foreach (["$target_directory/yakpro-po", "$target_directory/yakpro-po/obfuscated", "$target_directory/yakpro-po/context"] as $dummy => $dir) {
        if (!file_exists($dir)) mkdir($dir,0777,true);

        if (!file_exists($dir)) {
            fprintf(STDERR,"Error:\tCannot create directory [%s]%s",$dir,PHP_EOL);
            exit(-1);
        }
    }
    $target_directory = realpath($target_directory);
    if (!file_exists("$target_directory/yakpro-po/.yakpro-po-directory")) touch("$target_directory/yakpro-po/.yakpro-po-directory");
}

function shuffle_get_chunk_size(&$stmts)
{
    global $conf;

    $n = count($stmts);
    switch($conf->shuffle_stmts_chunk_mode)
    {
        case 'ratio':
            $chunk_size = sprintf("%d",$n/$conf->shuffle_stmts_chunk_ratio)+0;
            if ($chunk_size<$conf->shuffle_stmts_min_chunk_size) $chunk_size = $conf->shuffle_stmts_min_chunk_size;
            break;
        case 'fixed':
            $chunk_size = $conf->shuffle_stmts_min_chunk_size;
            break;
        default:
            $chunk_size =  1;       // should never occur!
    }
    return $chunk_size;
}

function shuffle_statements($stmts)
{
    global $conf;
    global $t_scrambler;

    if (!$conf->shuffle_stmts) {
        return $stmts;
    }

    $chunk_size = shuffle_get_chunk_size($stmts);
    if ($chunk_size <= 0) {
        return $stmts; // should never occur!
    }

    $n = count($stmts);
    if ($n < (2 * $chunk_size)) {
        return $stmts;
    }

    $scrambler = $t_scrambler['label'];
    $label_name_prev = $scrambler->scramble($scrambler->generate_label_name());
    $first_goto = new PhpParser\Node\Stmt\Goto_($label_name_prev);
    $t = [];
    $t_chunk = [];

    for ($i = 0; $i < $n; ++$i) {
        $t_chunk[] = $stmts[$i];

        if (count($t_chunk) >= $chunk_size) {
            $label = [new PhpParser\Node\Stmt\Label($label_name_prev)];
            $label_name = $scrambler->scramble($scrambler->generate_label_name());
            $goto = [new PhpParser\Node\Stmt\Goto_($label_name)];
            $t[] = array_merge($label, $t_chunk, $goto);
            $label_name_prev = $label_name;
            $t_chunk = [];
        }
    }
    if (count($t_chunk) > 0) {
        $label = [new PhpParser\Node\Stmt\Label($label_name_prev)];
        $label_name = $scrambler->scramble($scrambler->generate_label_name());
        $goto = [new PhpParser\Node\Stmt\Goto_($label_name)];
        $t[] = array_merge($label, $t_chunk, $goto);
        $label_name_prev = $label_name;
        $t_chunk = [];
    }

    $last_label = new PhpParser\Node\Stmt\Label($label_name);
    shuffle($t);
    $stmts = [];
    $stmts[] = $first_goto;

    foreach ($t as $dummy => $stmt) {
        foreach($stmt as $dummy => $inst) $stmts[] = $inst;
    }
    $stmts[] = $last_label;

    return $stmts;
}

