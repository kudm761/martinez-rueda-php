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
        $front = reset($this->segments);
        $back = end($this->segments);

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
    public function linkChain(PointChain &$other)
    {
        $front = $this->segments[0];
        $back = $this->segments[sizeof($this->segments) - 1];

        $other_front = $other->segments[0];
        $other_back = $other->segments[sizeof($other->segments) - 1];

        if ($other_front->equalsTo($back)) {
            $append = $other->segments;
            array_shift($append);

            $this->segments = array_merge($this->segments, $append);

            $other->segments[] = new Point();

            return true;
        }

        if ($other_back->equalsTo($front)) {
            $append = $this->segments;
            array_shift($append);

            $other->segments = array_merge($other->segments, $append);

            $other->segments[] = new Point();

            return true;
        }

        if ($other_front->equalsTo($front)) {
            $append = $this->segments;
            array_shift($append);

            $this->segments = array_merge(array_reverse($other->segments), $append);

            $other->segments[] = new Point();

            return true;
        }

        if ($other_back->equalsTo($back)) {
            $append = $this->segments;
            array_pop($append);

            $this->segments = array_merge($append, array_reverse($other->segments));

            $other->segments[] = new Point();

            return true;
        }

        return false;
    }
}