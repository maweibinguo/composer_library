<?php

namespace Noodlehaus;

use Noodlehaus\Exception\FileNotFoundException;
use Noodlehaus\Exception\UnsupportedFormatException;
use Noodlehaus\Exception\EmptyDirectoryException;

/**
 * Config
 *
 * @package    Config
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @link       https://github.com/noodlehaus/config
 * @license    MIT
 */
class Config extends AbstractConfig
{
    /**
     * All file formats supported by Config
     *
     * @var array
     */
    private $supportedFileParsers = array(
        'Noodlehaus\FileParser\Php',
        'Noodlehaus\FileParser\Ini',
        'Noodlehaus\FileParser\Json',
        'Noodlehaus\FileParser\Xml',
        'Noodlehaus\FileParser\Yaml'
    );

    /**
     * Static method for loading a Config instance.
     * 获取一个配置文件的事例对象
     *
     * @param  string|array $path
     * 文件或者文件夹的路径, 可以是字符串，也可以是数组
     *
     * @return Config
     * 返回当前Config类的事例对象
     * 如果文件夹或者文件路径不存在的话，那么将会抛出异常
     */
    public static function load($path)
    {
        return new static($path);
    }

    /**
     * Loads a supported configuration file format.
     * 加载支持的配置文件，目前支持Php  Ini Json Xml Yaml 这四中格式的配置文件
     *
     * @param  string|array $path
     * 文件或者文件夹的路径, 可以是字符串，也可以是数组
     *
     * @throws EmptyDirectoryException    If `$path` is an empty directory
     * 返回当前Config类的事例对象
     * 如果文件夹或者文件路径不存在的话，那么将会抛出异常
     */
    public function __construct($path)
    {
        //获取制定文件夹或制定文件的路径，文件夹的话是以数组的形式返回每个文件的列表
        $paths      = $this->getValidPath($path);

        $this->data = array();

        //遍历处理每一个文件，基于扩展名找到对应的解析类解析配置
        foreach ($paths as $path) {

            // Get file information
            $info      = pathinfo($path);
            $parts = explode('.', $info['basename']);
            $extension = array_pop($parts);
            if ($extension === 'dist') {
                $extension = array_pop($parts);
            }

            $parser    = $this->getParser($extension);

            // Try and load file
            // 尝试加载配置, 将新加载进来的数据和原有的数据进行合并
            // 由此可见，在数组中元素越靠后的数据，优先级越高
            $this->data = array_replace_recursive($this->data, (array) $parser->parse($path));
        }

        //调用父类的构造函数，将该配置同默认的配置进行合并，当前数据的优先级大于默认数据的优先级
        parent::__construct($this->data);
    }

    /**
     * Gets a parser for a given file extension
     * 基于扩展名获取对应的解析对象
     *
     * @param  string $extension
     * 扩展名称, 目前只支持 Php Ini Json Xml Yaml
     *
     * @return Noodlehaus\FileParser\FileParserInterface
     * 返回一个实现了Noodlehaus\FileParser\FileParserInterface接口的事例对象
     *
     * @throws UnsupportedFormatException If `$path` is an unsupported file format
     * 如果说并不是支持的扩展类型文件，那么会将会抛出异常
     */
    private function getParser($extension)
    {
        $parser = null;

        foreach ($this->supportedFileParsers as $fileParser) {
            $tempParser = new $fileParser;

            if (in_array($extension, $tempParser->getSupportedExtensions($extension))) {
                $parser = $tempParser;
                continue;
            }

        }

        // If none exist, then throw an exception
        if ($parser === null) {
            throw new UnsupportedFormatException('Unsupported configuration format');
        }

        return $parser;
    }

    /**
     * Gets an array of paths
     *
     * @param  array $path
     *
     * @return array
     *
     * @throws FileNotFoundException   If a file is not found at `$path`
     */
    private function getPathFromArray($path)
    {
        $paths = array();

        foreach ($path as $unverifiedPath) {
            try {
                // Check if `$unverifiedPath` is optional
                // If it exists, then it's added to the list
                // If it doesn't, it throws an exception which we catch
                if ($unverifiedPath[0] !== '?') {
                    $paths = array_merge($paths, $this->getValidPath($unverifiedPath));
                    continue;
                }
                $optionalPath = ltrim($unverifiedPath, '?');
                $paths = array_merge($paths, $this->getValidPath($optionalPath));

            } catch (FileNotFoundException $e) {
                // If `$unverifiedPath` is optional, then skip it
                if ($unverifiedPath[0] === '?') {
                    continue;
                }
                // Otherwise rethrow the exception
                throw $e;
            }
        }

        return $paths;
    }

    /**
     * Checks `$path` to see if it is either an array, a directory, or a file
     *
     * @param  string|array $path
     *
     * @return array
     *
     * @throws EmptyDirectoryException If `$path` is an empty directory
     *
     * @throws FileNotFoundException   If a file is not found at `$path`
     */
    private function getValidPath($path)
    {
        // If `$path` is array
        if (is_array($path)) {
            return $this->getPathFromArray($path);
        }

        // If `$path` is a directory
        if (is_dir($path)) {
            $paths = glob($path . '/*.*');
            if (empty($paths)) {
                throw new EmptyDirectoryException("Configuration directory: [$path] is empty");
            }
            return $paths;
        }

        // If `$path` is not a file, throw an exception
        if (!file_exists($path)) {
            throw new FileNotFoundException("Configuration file: [$path] cannot be found");
        }
        return array($path);
    }
}
