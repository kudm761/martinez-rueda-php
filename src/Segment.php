<?php
namespace MartinezRueda;

/**
 * Class Segment
 * @package MartinezRueda
 */
class Segment
{
    public $p1 = null;
    public $p2 = null;

    public function __construct(Point $p1, Point $p2)
    {
        $this->setBegin($p1);
        $this->setEnd($p2);
    }

    /**
     * @param Point $p
     */
    public function setBegin(Point $p)
    {
        $this->p1 = $p;
    }

    /**
     * @param Point $p
     */
    public function setEnd(Point $p)
    {
        $this->p2 = $p;
    }

    public function begin() : Point
    {
        return $this->p1;
    }

    public function end() : Point
    {
        return $this->p2;
    }

    public function changeOrientation()
    {
        $tmp = $this->p1;
        $this->p1 = $this->p2;
        $this->p2 = $tmp;
    }
}