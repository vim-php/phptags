<?php
/**
 * This file is part of phptags.
 *
 * phptags is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * phptags is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phptags. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category phptags
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 * @copyright Copyright 2011 Evan Coury (http://evan.pro/)
 * @package TagBuilder
 */

namespace phptags;

use Zend\EventManager\EventCollection,
    Zend\EventManager\EventManager;

class TagBuilder
{
    /**
     * @var EventCollection
     */
    protected $events;

    /**
     * @var TagEvent
     */
    protected $event;

    /**
     * @var array
     */
    protected $files;

    /**
     * __construct
     *
     * @param string $globPath
     * @return void
     */
    public function __construct($globPath)
    {
        $this->files = $this->globRecursive($globPath);
    }

    /**
     * execute
     *
     * @return void
     */
    public function execute()
    {
        $this->tagFileHeader();
        foreach ($this->files as $file) {
            $this->processFile($file);
        }
    }

    /**
     * Render the header lines for the tag file
     *
     * @access protected
     * @return void
     */
    protected function tagFileHeader()
    {
        $this->rawLine("!_TAG_FILE_FORMAT\t2\t/extended format/");
        // @TODO: Support sorting
        $this->rawLine("!_TAG_FILE_SORTED\t0\t/0=unsorted, 1=sorted, 2=foldcase/");
        $this->rawLine("!_TAG_PROGRAM_AUTHOR\tEvan Coury\t/me@evancoury.com/");
        $this->rawLine("!_TAG_PROGRAM_NAME\tphptags\t//");
        $this->rawLine("!_TAG_PROGRAM_URL\thttps://github.com/EvanDotPro/phptags\t/official site/");
        $this->rawLine("!_TAG_PROGRAM_VERSION\t0.1\t//");
    }

    /**
     * processFile
     *
     * @param string $file
     * @return void
     */
    protected function processFile($file)
    {
        $contents = file($file, FILE_IGNORE_NEW_LINES);
        $scanner = new Scanner(token_get_all(implode("\n", $contents)));
        $classes = $scanner->getClassesInfo();
        $e       = $this->getEvent();
        foreach ($classes as $class) {
            $e->setTagName($class['name']);
            $e->setTagPath($file);
            $e->setTagType('c');
            $e->setSearchPattern($contents[$class['lineStart']-1]);
            $this->events()->trigger('renderTag', $e);
        }
    }

    /**
     * Convenience method for triggering a raw line event
     *
     * @param string $line
     * @return TagBuilder
     */
    protected function rawLine($line)
    {
        $this->events()->trigger('renderRawLine', $this->getEvent()->setRawLine($line));
        return $this;
    }

    /**
     * getEvent
     *
     * @return TagEvent
     */
    public function getEvent()
    {
        if (null === $this->event) {
            $this->event = new TagEvent;
        }
        return $this->event;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManager $events
     * @return mixed
     */
    public function setEventManager(EventManager $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManager
     */
    public function events()
    {
        if (!$this->events instanceof EventManager) {
            $identifiers = array(__CLASS__, get_class($this));
            if (isset($this->eventIdentifier)) {
                if ((is_string($this->eventIdentifier))
                    || (is_array($this->eventIdentifier))
                    || ($this->eventIdentifier instanceof Traversable)
                ) {
                    $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
                } elseif (is_object($this->eventIdentifier)) {
                    $identifiers[] = $this->eventIdentifier;
                }
                // silently ignore invalid eventIdentifier types
            }
            $this->setEventManager(new EventManager($identifiers));
        }
        return $this->events;
    }

    /**
     * Simple recursive glob(). Does not support GLOB_BRACE
     *
     * @see http://www.php.net/manual/en/function.glob.php#106595
     * @param string $pattern
     * @param int $flags
     * @return array
     */
    protected function globRecursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->globRecursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}
