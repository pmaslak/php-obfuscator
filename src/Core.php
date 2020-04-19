<?php
/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak\PhpObfuscator;

use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\Label;
use pmaslak\PhpObfuscator\Config;
use Oil\Exception;
use PhpParser\Node\Stmt\Use_;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\NodeTraverser;


class Core
{
//    private $shebangs = ['#!/usr/bin/env php', '#!/usr/bin/php'];
    private $currentFileShebang = '';
    private $parser = null;
    private $traverser = null;
    private $prettyPrinter = null;

    function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser;
        $this->traverser->addVisitor(new MyNodeVisitor);

//        if ($conf->obfuscate_string_literal) {
            $this->prettyPrinter = new MyPrettyPrinter;
//        } else {
//            $this->>prettyPrinter = new PrettyPrinter\Standard;
//        }
    }

    /**
     * @param string $content
     * @return bool
     */
    private function isConsoleScript(string $content)
    {
        if ($content === '') {
            return false;
        }

        if (substr($content[0], 0, 2) == '#!') {
            return true;
        }

        return false;
    }

    public function obfuscateFile(string $fileName, array $configuration = [])
    {
        try {
            $content = file_get_contents($fileName);
        } catch (\Exception $e) {
            throw new \Exception('Cannot read file ' . $fileName);
        }

        $tmpFilename = '';
        $isConsoleScript = $this->isConsoleScript($content);

        /**
         * there is the thing, php_strip_whitespace() read only from file
         * so we need to craft file without shebang
         */
        if ($isConsoleScript) {
            $source = file($fileName);
            $this->currentFileShebang = array_shift($source);
            $tmpFilename = tempnam(sys_get_temp_dir(), 'php-obfucator');
            file_put_contents($tmpFilename, implode(PHP_EOL, $source));
            $fileName = $tmpFilename;
        }

        try {
            $content = php_strip_whitespace($fileName);
            $obfuscatedSource = $this->obfuscate($content);
        } catch (\Exception $e) {
            throw new \Exception('Cannot proceed file ' . $fileName);
        }
//        try {
//            $parsedSource = $this->parser->parse($source);
//        } catch (\Exception $e) {
//            throw new \Exception('Cannot parse file ' . $fileName);
//        }

        $obfuscatedSource = $this->minifyPhp($obfuscatedSource);

        if ($isConsoleScript) {
            $obfuscatedSource = $this->currentFileShebang . PHP_EOL . $obfuscatedSource;
            unlink($tmpFilename);
        }


        return $obfuscatedSource;
    }

    public function minifyPhp(string $code)
    {
        $tmp_filename = @tempnam(sys_get_temp_dir(), 'php-obfuscator-');
        file_put_contents($tmp_filename, $code);
        $result = php_strip_whitespace($tmp_filename);
        unlink($tmp_filename);

        return $result;
    }

    /**
     * @param string $fileContent
     * @return string
     * @throws \Exception
     */
    private function obfuscate(string $fileContent)
    {
        try {
            $parsed = $this->parser->parse($fileContent);
        } catch (Exception $e) {
            throw new \Exception('Cannot parse file.');
        }

        $obfuscatedSource = $this->traverser->traverse($parsed);
        return trim($this->prettyPrinter->prettyPrintFile($obfuscatedSource));
    }
}
