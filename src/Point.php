<?php
namespace MartinezRueda;

/**
 * Class Point
 * @package MartinezRueda
 */
class Point
{
    public $x = null;
    public $y = null;

    /**
     * Point constructor.
     *
     * @param float $x
     * @param float $y
     */
    public function __construct(float $x = 0, float $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param Point $p
     * @return bool
     */
    public function equalsTo(Point $p) : bool
    {
        return ($this->x === $p->x && $this->y === $p->y);
    }

    /**
     * @param Point $p
     * @return float
     */
    public function distanceTo(Point $p) : float
    {
        $dx = $this->x - $p->x;
        $dy = $this->y - $p->y;

        return sqrt($dx * $dx + $dy * $dy);
    }
}