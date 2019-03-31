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
            return $source;
//            throw new Exception("Error obfuscating [$src_filename]: php_strip_whitespace returned an empty string!");
        }
    }

    public function minifyPhp(string $code)
    {
        $tmp_filename = @tempnam(sys_get_temp_dir(), 'php-obfuscator-');
        file_put_contents($tmp_filename, $code);
        $result = php_strip_whitespace($tmp_filename);
        unlink($tmp_filename);

        return $result;
    }
}
