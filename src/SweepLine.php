<?php
namespace MartinezRueda;

/**
 * We use a vertical line to sweep the plane from left to right.
 * At every moment the edges that intersect the sweep-line are stored here,
 * ordered from bottom to top as they intersect the sweep-line.
 *
 * It consists of the ordered sequence of the edges of both polygons
 * intersecting the sweep-line.
 *
 * For more explanation see sweep-line algorithm.
 * @link https://en.wikipedia.org/wiki/Sweep_line_algorithm
 *
 * Class SweepLine
 * @package MartinezRueda
 */
class SweepLine
{
    /**
     * @var array of SweepEvents
     */
    public $events = [];

    public function size() : int
    {
        return sizeof($this->events);
    }

    /**
     * @param $index
     * @return SweepEvent
     */
    public function get($index) : SweepEvent
    {
        if (!isset($this->events[$index])) {
            throw new \InvalidArgumentException(sprintf('Undefined SweepLine->events offset `%s`', $index));
        }

        return $this->events[$index];
    }

    /**
     * @param SweepEvent $removable
     * @return void
     */
    public function remove(SweepEvent $removable)
    {
        foreach ($this->events as $index => $item) {
            if ($item->equalsTo($removable)) {
                Helper::removeElementWithShift($this->events, $index);
                break;
            }
        }
    }

    /**
     * @param SweepEvent $event
     * @return int
     */
    public function insert(SweepEvent $event) : int
    {
        if (sizeof($this->events) == 0) {
            $this->events[] = $event;
            return 0;
        }

        // priority queue is sorted, shift elements to the right and find place for event
        for ($i = sizeof($this->events) - 1; $i >= 0 && $this->compare($event, $this->events[$i]); $i--) {
            $this->events[$i + 1] = $this->events[$i];
        }

        $this->events[$i + 1] = $event;

        return $i + 1;
    }

    /**
     * @param SweepEvent $event1
     * @param SweepEvent $event2
     * @return bool
     */
    public function compare(SweepEvent $event1, SweepEvent $event2) : bool
    {
        return Helper::compareSegments($event1, $event2);
    }
}