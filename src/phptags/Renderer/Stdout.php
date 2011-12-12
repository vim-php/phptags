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
 * @package Renderer
 * @subpackage Stdout
 */

namespace phptags\Renderer;

use phptags\TagEvent,
    Zend\EventManager\EventCollection;

class Stdout extends AbstractRenderer
{
    /**
     * renderTag 
     * 
     * @param TagEvent $e 
     * @return void
     */
    public function renderTag(TagEvent $e)
    {
        echo $this->tagEventToString($e);
    }

    /**
     * renderRawLine 
     * 
     * @param TagEvent $e 
     * @return void
     */
    public function renderRawLine(TagEvent $e)
    {
        echo $e->getRawLine() . "\n";
    }

    /**
     * Attach one or more listeners
     *
     * @param EventCollection $events
     * @return Stdout
     */
    public function attach(EventCollection $events)
    {
        $events->attach('renderTag', array($this, 'renderTag'));
        $events->attach('renderRawLine', array($this, 'renderRawLine'));
        return $this;
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventCollection $events
     * @return Stdout
     */
    public function detach(EventCollection $events)
    {
        foreach ($this->listeners as $key => $listener) {
            $events->detach($listener);
            unset($this->listeners[$key]);
        }
        $this->listeners = array();
        return $this;
    }
}
