<?php
/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak\PhpObfuscator;


class Core
{
    public function obfuscateFile(string $fileName, $configuration = [])
    {
        $source = '';

        try {
            $source = php_strip_whitespace($fileName);
        } catch (\Exception $e) {
            throw new \Exception('Cannot read file ' . $fileName);
        }

        if ($source === '') {
            if ($conf->allow_and_overwrite_empty_files) return $source;
            throw new Exception("Error obfuscating [$src_filename]: php_strip_whitespace returned an empty string!");
        }
    }

    function obfuscate($filename)
    {
        global $conf;
        global $parser, $traverser, $prettyPrinter;
        global $debug_mode;

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
                if ($conf->allow_and_overwrite_empty_files) return $source;
                throw new Exception("Error obfuscating [$src_filename]: php_strip_whitespace returned an empty string!");
            }

            try {
                $stmts  = $parser->parse($source);  // PHP-Parser returns the syntax tree
            }
            catch (PhpParser\Error $e)                              // if an error occurs, then redo it without php_strip_whitespace, in order to display the right line number with error!
            {
                $source = file_get_contents($filename);
                $stmts  = $parser->parse($source);
            }
            if ($debug_mode===2)                                    //  == 2 is true when debug_mode is true!
            {
                $source = file_get_contents($filename);
                $stmts  = $parser->parse($source);
            }
            if ($debug_mode) {
                var_dump($stmts);
            }

            $stmts  = $traverser->traverse($stmts);                 //  Use PHP-Parser function to traverse the syntax tree and obfuscate names
            if ($conf->shuffle_stmts && (count($stmts)>2) )
            {
                $last_inst  = array_pop($stmts);
                $last_use_stmt_pos = -1;
                foreach($stmts as $i => $stmt)                      // if a use statement exists, do not shuffle before the last use statement
                {                                                   //TODO: enhancement: keep all use statements at their position, and shuffle all sub-parts
                    if ( $stmt instanceof PhpParser\Node\Stmt\Use_ ) $last_use_stmt_pos = $i;
                }

                if ($last_use_stmt_pos<0)   { $stmts_to_shuffle = $stmts;                                   $stmts = array();                                       }
                else                        { $stmts_to_shuffle = array_slice($stmts,$last_use_stmt_pos+1); $stmts = array_slice($stmts,0,$last_use_stmt_pos+1);    }

                $stmts      = array_merge($stmts,shuffle_statements($stmts_to_shuffle));
                $stmts[]    = $last_inst;
            }
            // if ($debug_mode) var_dump($stmts);


            $code   = trim($prettyPrinter->prettyPrintFile($stmts));            //  Use PHP-Parser function to output the obfuscated source, taking the modified obfuscated syntax tree as input

            if (isset($conf->strip_indentation) && $conf->strip_indentation)    // self-explanatory
            {
                $code = remove_whitespaces($code);
            }
            $endcode = substr($code,6);

            $code  = '<?php'.PHP_EOL;
//        $code .= $conf->get_comment();                                          // comment obfuscated source
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
        }
        catch (Exception $e)
        {
            fprintf(STDERR,"Obfuscator Parse Error [%s]:%s\t%s%s", $filename,PHP_EOL, $e->getMessage(),PHP_EOL);
            return null;
        }
    }
}
