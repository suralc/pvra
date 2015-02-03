<?php
/**
 * FileFinderBuilder.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\Console\Services;


use RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * Class FileFinderBuilder
 *
 * @package Pvra\Console\Services
 */
class FileFinderBuilder implements \IteratorAggregate
{
    const SORT_BY_NAME = 'n',
        SORT_BY_CHANGED_TIME = 'c',
        SORT_BY_TYPE = 't',
        SORT_BY_ACCESSED_TIME = 'a',
        SORT_BY_MODIFIED_TIME = 'm';

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    private $finder;

    /**
     * @param string $dir The base directory
     * @param \Symfony\Component\Finder\Finder $finder The finder instance to be used. If none is given a new will be
     *     created
     */
    public function __construct($dir, Finder $finder = null)
    {
        if (!is_dir($dir) || !is_readable($dir)) {
            throw new RuntimeException(sprintf('"%s" is not a valid directory', $dir));
        }

        if ($finder === null) {
            $this->finder = new Finder();
        } else {
            $this->finder = $finder;
        }
        $this->finder->files()
            ->in($dir)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);
    }

    /**
     * @param bool $includeDotFiles
     * @return $this
     */
    public function includeDotFiles($includeDotFiles = true)
    {
        $this->finder->ignoreDotFiles(!$includeDotFiles);

        return $this;
    }

    /**
     * @param bool $isRecursive
     * @return $this
     */
    public function isRecursive($isRecursive = true)
    {
        if ($isRecursive === false) {
            $this->finder->depth(0);
        } else {
            $this->finder->depth('>= 0');
        }

        return $this;
    }

    /**
     * @param callable|string $by
     * @return $this
     */
    public function sortBy($by = self::SORT_BY_NAME)
    {
        if (is_callable($by)) {
            $this->finder->sort($by);
            return $this;
        }

        switch (strtolower($by)) {
            case self::SORT_BY_NAME:
            case 'name':
                $this->finder->sortByName();
                break;
            case self::SORT_BY_CHANGED_TIME:
            case 'ctime':
                $this->finder->sortByChangedTime();
                break;
            case self::SORT_BY_ACCESSED_TIME:
            case 'atime':
                $this->finder->sortByAccessedTime();
                break;
            case self::SORT_BY_TYPE:
            case 'type':
                $this->finder->sortByType();
                break;
            case self::SORT_BY_MODIFIED_TIME:
            case 'mtime':
                $this->finder->sortByModifiedTime();
                break;
            default:
                throw new \InvalidArgumentException($by . ' is not a supported argument for sorting.');
        }


        return $this;
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function withFilters(array $filters)
    {
        if (!empty($filters)) {
            foreach ($filters as $currentFilter) {
                if (!strpos($currentFilter, ':')) {
                    throw new \InvalidArgumentException(sprintf('The filter "%s" is not a valid filter. A valid filter has the format <name>:<value>.',
                        $currentFilter));
                }

                $currentFilterElements = explode(':', $currentFilter, 2);

                switch (trim($currentFilterElements[0])) {
                    case 'exclude':
                        $this->finder->exclude($currentFilterElements[1]);
                        break;
                    case 'name':
                        $this->finder->name($currentFilterElements[1]);
                        break;
                    case 'notName':
                        $this->finder->notName($currentFilterElements[1]);
                        break;
                    case 'path':
                        $this->finder->path($currentFilterElements[1]);
                        break;
                    case 'size':
                        $this->finder->size($currentFilterElements[1]);
                }
            }
        }
        return $this;
    }

    /**
     * @return \Symfony\Component\Finder\Finder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * @return \Iterator|\SplFileInfo[]
     */
    public function getIterator()
    {
        return $this->finder->getIterator();
    }
}
