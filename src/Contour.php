<?php
namespace MartinezRueda;

/**
 * Contour represents a sequence of vertices connected by line segments, forming a closed shape.
 *
 * Class Contour
 * @package MartinezRueda
 */
class Contour
{
    public $points = [];
    protected $holes = [];

    protected $is_external = false;
    protected $cc = false;

    protected $precomputed_cc = null;

    public function __construct(array $points)
    {
        foreach ($points as $point) {
            $this->add($point);
        }
    }

    public function add(Point $p)
    {
        $this->points[] = $p;
    }

    public function erase(int $index)
    {
        if (!isset($this->points[$index])) {
            throw new \InvalidArgumentException(sprintf('Undefined points offset `%s`', $index));
        }

        unset($this->points[$index]);
    }

    public function clear()
    {
        $this->points = [];
        $this->holes = [];
    }

    public function addHole(int $index)
    {
        $this->holes[] = $index;
    }

    /**
     * Get the p-th vertex of the external contour
     *
     * @param int $p
     * @return Point
     */
    public function vertex(int $p) : Point
    {
        if (!isset($this->points[$p])) {
            throw new \InvalidArgumentException('Undefined index offset.');
        }

        return $this->points[$p];
    }

    /**
     * @param int $p
     * @return Segment
     */
    public function segment(int $p) : Segment
    {
        if ($p == $this->nvertices() - 1) {
            // last, first
            return new Segment($this->points[sizeof($this->points) - 1], $this->points[0]);
        }

        return new Segment($this->points[$p], $this->points[$p + 1]);
    }

    /**
     * @return int
     */
    public function nvertices() : int
    {
        return sizeof($this->points);
    }

    /**
     * @return int
     */
    public function nedges() : int
    {
        return sizeof($this->points);
    }

    /**
     * @return int
     */
    public function nholes() : int
    {
        return sizeof($this->holes);
    }

    /**
     * @param int $index
     * @return mixed
     */
    public function hole(int $index)
    {
        if (!isset($this->holes[$index])) {
            throw new \InvalidArgumentException(sprintf('Undefined holes offset `%s`', $index));
        }

        return $this->holes[$index];
    }

    /**
     * Get minimum bounding rectangle
     *
     * @return array ['min' => Point, 'max' => Point]
     */
    public function getBoundingBox() : array
    {
        $min_x = PHP_INT_MAX;
        $min_y = PHP_INT_MAX;

        $max_x = PHP_INT_MIN;
        $max_y = PHP_INT_MIN;

        foreach ($this->points as $k => $point) {
            if ($point->x < $min_x) {
                $min_x = $point->x;
            }

            if ($point->x > $max_x) {
                $max_x = $point->x;
            }

            if ($point->y < $min_y) {
                $min_y = $point->y;
            }

            if ($point->y > $max_y) {
                $max_y = $point->y;
            }
        }

        return [
            'min' => new Point($min_x, $min_y),
            'max' => new Point($max_x, $max_y)
        ];
    }

    public function counterClockwise() : bool
    {
        if (!is_null($this->precomputed_cc)) {
            return $this->precomputed_cc;
        }

        $this->precomputed_cc = true;

        $area = 0.0;

        for ($c = 0; $c < $this->nvertices() - 1; $c++) {
            $area = $area + $this->vertex($c)->x * $this->vertex($c + 1)->y
                - $this->vertex($c + 1)->x * $this->vertex($c)->y;
        }

        $area = $area + $this->vertex($this->nvertices() - 1)->x * $this->vertex(0)->y
            - $this->vertex(0)->x * $this->vertex($this->nvertices() - 1)->y;

        $this->cc = $area >= 0.0;

        return $this->cc;
    }

    public function clockwise() : bool
    {
        return !$this->counterClockwise();
    }

    public function changeOrientation()
    {
        $this->points = array_reverse($this->points);
        $this->cc = !$this->cc;
    }

    public function setClockwise()
    {
        if ($this->counterClockwise()) {
            $this->changeOrientation();
        }
    }

    public function setCounterClockwise()
    {
        if ($this->clockwise()) {
            $this->changeOrientation();
        }
    }

    public function external() : bool
    {
        return $this->is_external;
    }

    public function setExternal(bool $flag)
    {
        $this->is_external = $flag;
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function move(float $x, float $y)
    {
        for ($i = 0; $i < $this->nvertices(); $i++) {
            $this->points[$i]->x += $x;
            $this->points[$i]->y += $y;
        }
    }
}