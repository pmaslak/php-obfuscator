<?php

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak;


class Obfuscator implements ObfuscatorInterface
{
    private $direcotry = '';

    public $processedFiles = 0;
    public $lastProcessTime = 0;
    private $configuration = [
        'debug' => false,
        'target' => '',
        'obfuscation_options' => []
    ];

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
        $this->direcotry = implode(DIRECTORY_SEPARATOR, $arr);
    }

    /**
     * Force stop the script before running on the wrong version
     * @throws \Exception
     */
    private function checkPhpVersion()
    {
        if (PHP_VERSION_ID < 70000) {
            throw new \Exception('PHP version under 7.0Please run this on PHP >= 7.0');
        }
    }

    public function setConfiguration(array $config)
    {
        $this->injectConfiguration($config);
    }

    /**
     * inject new configuration into current object
     * @param array $config
     */
    private function injectConfiguration(array $config)
    {
        if (isset($config['debug']) && $config['debug']) {
            $this->configuration['debug'] = true;
        }

        if (isset($config['target']) && !empty($config['target'])) {
            $this->configuration['target'] = (string)$config['target'];
        }

        if (isset($config['obfuscation_options'])) {
            $this->configuration['obfuscation_options'] = Configuration::getFilteredOptions($config['obfuscation_options']);
        }
    }

    public function obfuscateFile(string $path, string $newName)
    {
        $this->validateFile($path);
        $this->processFile($path, $newName);
    }

    public function obfuscateDirectory(string $path, $recursive = true)
    {
        $this->validateDirectory($path);
        $this->processDirectory($path, $this->configuration['target'], $recursive);
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

                if ($mimetype == 'text/x-php') {
                    $this->runFileObfuscation($directory . $fileName, $target . $fileName);
                }
            }
        }
    }

    /**
     * @param $file
     * @throws \Exception
     */
    private function processFile($file, $newName = null)
    {
        $arr = explode(DIRECTORY_SEPARATOR, $file);
        $filename = array_pop($arr);

        if (empty($newName)) {
            $newName = $filename;
        }

        $this->runFileObfuscation($file, $this->configuration['target'] . $newName);
    }

    private function runFileObfuscation($source, $target)
    {
        $parameters = $this->configuration['obfuscation_options'];
        $parameters = array_map('trim', $parameters);

        if (empty($parameters)) {
            $parameters = '';
        } else {
            $parameters = '--' . implode(' --', $parameters);
        }

        if ($this->configuration['debug']) {
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

    private function getBaseCommand()
    {
        return 'php ' . $this->direcotry . '/src/yakpro/yakpro-po.php ';
    }
}

