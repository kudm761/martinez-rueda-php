<?php
namespace MartinezRueda;

/**
 * Class Polygon
 * @package MartinezRueda
 */
class Polygon
{
    public $contours = [];

    /**
     * Polygon constructor.
     * Get array of contours (each is array of points and each point is 2-size array)
     *
     * contours_xy = [[]contour1, []contour2]
     * []contour = [[]point1, []point2]
     * []point = [x, y]
     *
     * @example $contours_xy = [[[1, 4], [4, 5], [6, 4], [1, 1]], [...]]
     * @param array $contours_xy
     */
    public function __construct(array $contours_xy)
    {
        foreach ($contours_xy as $contour_xy) {
            $contour_points = [];

            foreach ($contour_xy as $xy) {
                $contour_points[] = new Point($xy[0], $xy[1]);
            }

            $this->push_back(new Contour($contour_points));
        }
    }

    /**
     * @param int $index
     * @return Contour
     */
    public function contour(int $index) : Contour
    {
        return $this->contours[$index];
    }

    /**
     * @return int
     */
    public function ncontours() : int
    {
        return sizeof($this->contours);
    }

    /**
     * @return int
     */
    public function nvertices() : int
    {
        $nv = 0;

        for ($i = 0; $i < $this->ncontours(); $i++) {
            $nv += $this->contours[$i]->nvertices();
        }

        return $nv;
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

        for ($i = 0; $i < $this->ncontours(); $i++) {
            $box = $this->contours[$i]->getBoundingBox();

            $min_tmp = $box['min'];
            $max_tmp = $box['max'];

            if ($min_tmp->x < $min_x) {
                $min_x = $min_tmp->x;
            }

            if ($max_tmp->x > $max_x) {
                $max_x = $max_tmp->x;
            }

            if ($min_tmp->y < $min_y) {
                $min_y = $min_tmp->y;
            }

            if ($max_tmp->y > $max_y) {
                $max_y = $max_tmp->y;
            }
        }

        return [
            'min' => new Point($min_x, $min_y),
            'max' => new Point($max_x, $max_y)
        ];
    }

    /**
     * @param float $x
     * @param float $y
     * @return void
     */
    public function move(float $x, float $y)
    {
        for ($i = 0; $this->ncontours(); $i++) {
            $this->contours[$i]->move($x, $y);
        }
    }

    /**
     * @param Contour $contour
     */
    public function push_back(Contour $contour)
    {
        $this->contours[] = $contour;
    }

    public function pop_back()
    {
        array_pop($this->contours);
    }

    /**
     * @param int $index
     * @return void
     */
    public function erase(int $index)
    {
        unset($this->contours[$index]);
    }

    /**
     * Empty the polygon
     */
    public function clear()
    {
        unset($this->contours);
    }

    public function toArray() : array
    {
        if (empty($this->contours)) {
            return [];
        }

        $contours_xy = [];

        foreach ($this->contours as $contour) {
            $points_xy = [];

            foreach ($contour->points as $point) {
                $points_xy[] = [$point->x, $point->y];
            }

            $contours_xy[] = $points_xy;
        }

        return $contours_xy;
    }
}