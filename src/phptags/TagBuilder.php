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
     * @param array $files 
     * @return void
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * execute 
     * 
     * @return void
     */
    public function execute()
    {
        foreach ($this->files as $file) {
            $this->processFile($file);
        }
    }

    /**
     * processFile 
     * 
     * @param string $file 
     * @return void
     */
    protected function processFile($file)
    {
        $scanner = new Scanner(token_get_all(file_get_contents($file)));
        $info = $scanner->getClassesInfo();
        $this->getEvent()->setRawLine(print_r($info, 1));
        $this->events()->trigger('renderRawLine', $this->getEvent());
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
     * @param  EventCollection $events 
     * @return mixed
     */
    public function setEventManager(EventCollection $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     * 
     * @return EventCollection
     */
    public function events()
    {
        if (!$this->events instanceof EventCollection) {
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
}
