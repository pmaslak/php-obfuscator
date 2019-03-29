<?php

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak;
use pmaslak\Configuration;


class Obfuscator implements ObfuscatorInterface
{
    private $direcotry = '';

    public $processedFiles = 0;
    public $lastProcessTime = 0;
    private $configuration = [
        'debug' => false,
        'files_target' => '',
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
        $arr = explode('/', __DIR__);
        array_pop($arr);
        $this->direcotry = implode('/', $arr);
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

        if (isset($config['files_target']) && !empty($config['files_target'])) {
            $this->configuration['files_target'] = (string)$config['files_target'];
        }

        if (isset($config['obfuscation_options'])) {
            $this->configuration['obfuscation_options'] = Configuration::getFilteredOptions($config['obfuscation_options']);
        }
    }

    public function obfuscateFile(string $path)
    {
        $this->validateFile($path);
        $this->processFile($path);
    }

    public function obfuscateDirectory(string $path, bool $recursive = true)
    {
        $this->validateDirectory($path);
        $this->processDirectory($path, $recursive);
    }

    /**
     * @todo throw exception if dsomething wrong -- also relative to configuration of plugin
     * @param $path
     */
    private function validateDirectory($path)
    {
        if (!file_exists($path)) {
            throw new Exception('File ' . $path  . ' does not exists or is not readable.');
        }
    }

    /**
     * @todo throw exception if dsomething wrong -- also relative to configuration of plugin
     * @param $path
     */
    private function validateFile($path)
    {
        if (!is_readable($path)) {
            throw new Exception('File ' . $path  . ' does not exists or is not readable.');
        }
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    private function processDirectory($directory, bool $recursive = true)
    {
        foreach (new \DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                if ($recursive) {
                    $this->processDirectory($fileInfo->getPathName() . DIRECTORY_SEPARATOR, $recursive);
                }
                continue;
            }

            if ($fileInfo->isFile()) {
                $fileName = $fileInfo->getFilename();
                $mimetype = mime_content_type($directory . $fileName);

                if ($mimetype == 'text/x-php') {
                    $this->processFile($directory . $fileName);
                }
            }
        }
    }

    /**
     * @param $file
     * @return bool
     */
    private function processFile($file)
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

        echo "\n\nRunning command: ". $command . $file . ' -o '  . $this->configuration['files_target'].$parameters."\n\n";

        try {
            exec($command . $file . ' -o ' . $this->configuration['files_target'] . $parameters);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function getBaseCommand()
    {
        return 'php ' . $this->direcotry . '/src/yakpro/yakpro-po.php ';
    }

//    private function done()
//    {
//        $executionTime = (microtime(true) - $this->timeStart);
//        echo PHP_EOL . PHP_EOL . 'Processed ' . $this->processedFiles . ' files';
//        echo PHP_EOL .'Total Execution Time: ' . $executionTime .' s';
//        echo PHP_EOL . 'Done!' . PHP_EOL;
//    }

}

