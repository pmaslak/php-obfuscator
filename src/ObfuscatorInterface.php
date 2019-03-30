<?php
declare(strict_types=1);

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak\PhpObfuscator;


interface ObfuscatorInterface
{
    public function __construct(array $config);

    public function obfuscateFile(string $path, string $target);
    public function obfuscateDirectory(string $path, string $target, $recursive);
}
