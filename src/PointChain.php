<?php
namespace MartinezRueda;

/**
 * Class PointChain
 * @package MartinezRueda
 */
class PointChain
{
    public $segments = [];
    public $closed = false;

    /**
     * PointChain constructor.
     * @param array $segments
     */
    public function __construct(array $segments = [])
    {
        $this->segments = $segments;
    }

    /**
     * @param Segment $segment
     */
    public function init(Segment $segment)
    {
        $this->segments[] = $segment->begin();
        $this->segments[] = $segment->end();
    }

    /**
     * @return mixed
     */
    public function begin()
    {
        return $this->segments[0];
    }

    /**
     * @return mixed
     */
    public function end()
    {
        return $this->segments[$this->size() - 1];
    }

    /**
     * @return int
     */
    public function size() : int
    {
        return sizeof($this->segments);
    }

    /**
     * @param Segment $segment
     * @return bool
     */
    public function linkSegment(Segment $segment)
    {
        $front = $this->begin();
        $back = $this->end();

        if ($segment->begin()->equalsTo($front)) {
            if ($segment->end()->equalsTo($back)) {
                $this->closed = true;
            } else {
                array_unshift($this->segments, $segment->end());
            }

            return true;
        }

        if ($segment->end()->equalsTo($back)) {
            if ($segment->begin()->equalsTo($front)) {
                $this->closed = true;
            } else {
                $this->segments[] = $segment->begin();
            }

            return true;
        }

        if ($segment->end()->equalsTo($front)) {
            if ($segment->begin()->equalsTo($back)) {
                $this->closed = true;
            } else {
                array_unshift($this->segments, $segment->begin());
            }

            return true;
        }

        if ($segment->begin()->equalsTo($back)) {
            if ($segment->end()->equalsTo($front)) {
                $this->closed = true;
            } else {
                $this->segments[] = $segment->end();
            }

            return true;
        }

        return false;
    }

    /**
     * @param PointChain $other
     * @return bool
     */
    public function linkChain(PointChain $other)
    {
        $front = $this->begin();
        $back = $this->end();

        $other_front = $other->begin();
        $other_back = $other->end();

        if ($other_front->equalsTo($back)) {
            array_shift($other->segments);

            // insert at the end of $this->segments
            $this->segments = array_merge($this->segments, $other->segments);

            return true;
        }

        if ($other_back->equalsTo($front)) {
            array_shift($this->segments);

            // insert at the beginning of $this->segments
            $this->segments = array_merge($other->segments, $this->segments);

            return true;
        }

        if ($other_front->equalsTo($front)) {
            array_shift($this->segments);

            $other->segments = array_reverse($other->segments);
            // insert reversed at the beginning of $this->segments
            $this->segments = array_merge($other->segments, $this->segments);

            return true;
        }

        if ($other_back->equalsTo($back)) {
            array_pop($this->segments);

            $other->segments = array_reverse($other->segments);

            // insert reversed at the end of $this->segments
            $this->segments = array_merge($this->segments, $other->segments);

            return true;
        }

        return false;
    }
}