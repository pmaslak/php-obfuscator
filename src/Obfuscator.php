<?php

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak\PhpObfuscator;


class Obfuscator implements ObfuscatorInterface
{
    private $directory = '';

    public $processedFiles = 0;
    public $lastProcessTime = 0;

    function __construct(array $config)
    {
        $this->checkPhpVersion();
        $this->setObfuscatorDir();
        $this->injectConfiguration($config);
    }

    private function setObfuscatorDir(): void
    {
        $arr = explode(DIRECTORY_SEPARATOR, __DIR__);
        array_pop($arr);
        $this->directory = implode(DIRECTORY_SEPARATOR, $arr);
    }

    /**
     * Force stop the script before running on the wrong version
     * @throws \Exception
     */
    private function checkPhpVersion()
    {
        if (PHP_VERSION_ID < 70100) {
            throw new \Exception('PHP version under 7.1, please run this on PHP >= 7.1');
        }
    }

    public function setConfiguration(array $config): void
    {
        $this->injectConfiguration($config);
    }

    /**
     * inject new configuration into current object
     * @param array $config
     */
    private function injectConfiguration(array $config): void
    {
        if (isset($config['debug']) && $config['debug']) {
            Config::enableDebug();
        }

        if (isset($config['obfuscation_options'])) {
            Config::setObfuscationOptions(Config::getFilteredOptions($config['obfuscation_options']));
        }
    }

    public function obfuscateFile(string $path, string $target)
    {
        $this->validateFile($path);
        $this->processFile($path, $target);
    }

    public function obfuscateDirectory(string $path, string $target, $recursive = true)
    {
        $this->validateDirectory($path);
        $this->processDirectory($path, $target, $recursive);
    }

    /**
     * @param $path
     * @throws \Exception
     */
    private function validateDirectory($path)
    {
        if (!file_exists($path)) {
            throw new \Exception('File ' . $path  . ' does not exists or is not readable.');
        }
    }

    /**
     * @param $path
     * @throws \Exception
     */
    private function validateFile($path)
    {
        if (!is_readable($path)) {
            throw new \Exception('File ' . $path  . ' does not exists or is not readable.');
        }
    }

    /**
     * @param string $directory
     */
    private function createDirectory(string $directory)
    {
        if (is_readable($directory)) {
            return;
        }

        mkdir($directory);
    }

    /**
     * @param $directory
     * @param $target
     * @param bool $recursive
     * @throws \Exception
     */
    private function processDirectory($directory, $target, $recursive = true)
    {
        $this->createDirectory($target);

        foreach (new \DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $newDir = $target . $fileInfo->getBasename() . DIRECTORY_SEPARATOR;
                if ($recursive) {
                    $this->processDirectory($fileInfo->getPathName() . DIRECTORY_SEPARATOR, $newDir, $recursive);
                }
                continue;
            }

            if ($fileInfo->isFile()) {
                $fileName = $fileInfo->getFilename();
                $mimetype = mime_content_type($directory . DIRECTORY_SEPARATOR . $fileName);

                if (in_array($mimetype, Config::getAllowedMimeTypes())) {
                    $this->processFile($directory . $fileName, $target . $fileName);
                }
            }
        }
    }

    /**
     * @param $source
     * @param $target
     * @throws \Exception
     */
    private function processFile($source, $target)
    {
        $parameters = Config::getObfuscationOptions();
        $parameters = array_map('trim', $parameters);

        if (empty($parameters)) {
            $parameters = '';
        } else {
            $parameters = '--' . implode(' --', $parameters);
        }

        if (Config::isDebug()) {
            $parameters = ' --debug ' . $parameters;
        } else {
            $parameters = ' --silent ' . $parameters;
        }

        $command = $this->getBaseCommand();

        try {
            exec($command . $source . ' -o ' . $target . $parameters);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function getBaseCommand(): string
    {
        return 'php ' . $this->directory . '/src/yakpro/obfuscator_command.php ';
    }
}

