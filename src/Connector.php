<?php
namespace MartinezRueda;

/**
 * Class Connector
 * @package MartinezRueda
 */
class Connector
{
    public $open_polygons = [];
    public $closed_polygons = [];

    /**
     * @var bool
     */
    protected $closed = false;

    public function isClosed() : bool
    {
        return $this->closed;
    }

    /**
     * @param Segment $segment
     * @return void
     */
    public function add(Segment $segment)
    {
        $size = sizeof($this->open_polygons);

        for ($j = 0; $j < $size; $j++) {
            $chain = $this->open_polygons[$j];

            if (!$chain->linkSegment($segment)) {
                continue;
            }

            if ($chain->closed) {
                if (sizeof($chain->segments) == 2) {
                    $chain->closed = false;

                    return;
                }

                $this->closed_polygons[] = $this->open_polygons[$j];

                Helper::removeElementWithShift($this->open_polygons, $j);

                return;
            }

            // if chain not closed
            $k = sizeof($this->open_polygons);

            for ($i = $j + 1; $i < $k; $i++) {
                $v = $this->open_polygons[$i];

                if ($chain->linkChain($v)) {
                    Helper::removeElementWithShift($this->open_polygons, $i);

                    return;
                }
            }

            return;
        }

        $new_chain = new PointChain();
        $new_chain->init($segment);

        $this->open_polygons[] = $new_chain;
    }

    /**
     * @return Polygon
     */
    public function toPolygon() : Polygon
    {
        $contours = [];

        foreach ($this->closed_polygons as $closed_polygon) {
            $contour_points = [];

            foreach ($closed_polygon->segments as $point) {
                $contour_points[] = [$point->x, $point->y];
            }

            // close contour
            $first = reset($contour_points);
            $last = end($contour_points);

            if ($last != $first) {
                $contour_points[] = $first;
            }

            $contours[] = $contour_points;
        }

        return new Polygon($contours);
    }
}