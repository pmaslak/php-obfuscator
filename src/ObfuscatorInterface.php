<?php
declare(strict_types=1);

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak;


interface ObfuscatorInterface
{
    public function __construct(array $config);

    public function obfuscateFile(string $path, string $newName);
    public function obfuscateDirectory(string $path, $recursive);
}
