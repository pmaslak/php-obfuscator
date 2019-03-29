<?php
declare(strict_types=1);

namespace pmaslak;



interface ObfuscatorInterface
{
    public function __construct(array $config);

    public function obfuscateFile(string $path);
    public function obfuscateDirectory(string $path, bool $recursive);
}
