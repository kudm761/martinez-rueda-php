<?php
namespace MartinezRueda;

/**
 * Priority queue that holds sweep-events sorted from left to right.
 *
 * Class PriorityQueue
 * @package MartinezRueda
 */
class PriorityQueue
{
    /**
     * Array of SweepEvents sorted from left to right
     *
     * @var array
     */
    public $events = [];

    protected $sorted = false;

    /**
     * @return int
     */
    public function size() : int
    {
        return sizeof($this->events);
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->events);
    }

    /**
     * @param SweepEvent $event
     */
    public function enqueue(SweepEvent $event)
    {
        if (!$this->isSorted()) {
            $this->events[] = $event;
            return;
        }

        if (sizeof($this->events) <= 0) {
            $this->events[] = $event;
            return;
        }

        // priority queue is sorted, shift elements to the right and find place for event
        for ($i = sizeof($this->events) - 1; $i >= 0 && $this->compare($event, $this->events[$i]); $i--) {
            $this->events[$i + 1] = $this->events[$i];
        }

        $this->events[$i + 1] = $event;
    }

    /**
     * @return mixed
     */
    public function dequeue() : SweepEvent
    {
        if (!$this->isSorted()) {
            $this->sort();
            $this->sorted = true;
        }

        return array_pop($this->events);
    }

    /**
     * @return void
     */
    public function sort()
    {
        uasort(
            $this->events,
            function ($event1, $event2) {
                return $this->compare($event1, $event2) ? -1 : 1;
            }
        );

        // We should actualize indexes, because of hash-table nature.
        // array_values() is faster than juggling with indexes.
        $this->events = array_values($this->events);
    }

    /**
     * @return bool
     */
    public function isSorted() : bool
    {
        return $this->sorted;
    }

    /**
     * @param SweepEvent $event1
     * @param SweepEvent $event2
     * @return bool
     */
    protected function compare(SweepEvent $event1, SweepEvent $event2) : bool
    {
        return Helper::compareSweepEvents($event1, $event2);
    }
}