<?php
namespace MartinezRueda;

/**
 * Class Helper
 * @package MartinezRueda
 */
class Helper
{
    /**
     * Signed area of the triangle (p0, p1, p2)
     *
     * @param Point $p0
     * @param Point $p1
     * @param Point $p2
     * @return float
     */
    public static function signedArea(Point $p0, Point $p1, Point $p2) : float
    {
        return ($p0->x - $p2->x) * ($p1->y - $p2->y) - ($p1->x - $p2->x) * ($p0->y - $p2->y);
    }

    /**
     * Used for sorting SweepEvents in PriorityQueue
     * If same x coordinate - from bottom to top.
     * If two endpoints share the same point - rights are before lefts.
     * If two left endpoints share the same point then they must be processed
     * in the ascending order of their associated edges in SweepLine
     *
     *
     * @param SweepEvent $event1
     * @param SweepEvent $event2
     * @return bool
     */
    public static function compareSweepEvents(SweepEvent $event1, SweepEvent $event2) : bool
    {
        // x is not the same
        if ($event1->p->x > $event2->p->x) {
            return true;
        }

        // x is not the same too
        if ($event2->p->x > $event1->p->x) {
            return false;
        }

        // x is the same, but y is not
        // the event with lower y-coordinate is processed first
        if (!$event1->p->equalsTo($event2->p)) {
            return $event1->p->y > $event2->p->y;
        }

        // x and y are the same, but one is a left endpoint and the other a right endpoint
        // the right endpoint is processed first
        if ($event1->is_left != $event2->is_left) {
            return $event1->is_left;
        }

        // x and y are the same and both points are left or right
        return $event1->above($event2->other->p);
    }

    /**
     * @param SweepEvent $event1
     * @param SweepEvent $event2
     * @return bool
     */
    public static function compareSegments(SweepEvent $event1, SweepEvent $event2) : bool
    {
        if ($event1->equalsTo($event2)) {
            return false;
        }

        if (self::signedArea($event1->p, $event1->other->p, $event2->p) != 0
            || self::signedArea($event1->p, $event1->other->p, $event2->other->p) != 0) {
            if ($event1->p->equalsTo($event2->p)) {
                return $event1->below($event2->other->p);
            }

            if (self::compareSweepEvents($event1, $event2)) {
                return $event2->above($event1->p);
                //return $event1->below($event2->p);
            }

            return $event1->below($event2->p);
            //return $event2->above($event1->p);
        }

        if ($event1->p->equalsTo($event2->p)) {
            //return $event1->lessThan($event2);
            return false;
        }

        return self::compareSweepEvents($event1, $event2);
    }

    /**
     * Remove $index element and maintain indexing.
     *
     * @param array $array
     * @param int $index
     * @return void
     */
    public static function removeElementWithShift(array &$array, int $index)
    {
        if (!isset($array[$index])) {
            $message = sprintf('Undefined index offset: `%s` in array %s.', $index, print_r($array, true));
            throw new \InvalidArgumentException($message);
        }

        unset($array[$index]);
        $array = array_values($array);

        return;
    }

    /**
     * @param array $expected_multipolygon
     * @param array $tested_multipolygon
     * @return int
     */
    public static function compareMultiPolygons(array $expected_multipolygon, array $tested_multipolygon) : array
    {
        if (sizeof($expected_multipolygon) != sizeof($tested_multipolygon)) {
            return ['success' => false, 'reason' => 'different count of polygons'];
        }

        if (sizeof($expected_multipolygon) == 0 && sizeof($tested_multipolygon) == 0) {
            return ['success' => true, 'reason' => ''];
        }

        for ($i = 0; $i < sizeof($expected_multipolygon); $i++) {
            $expected_polygon = $expected_multipolygon[$i];

            if (!isset($tested_multipolygon[$i])) {
                return [
                    'success' => false,
                    'reason' => sprintf('Tested multipolygon has not polygon with index: `%s`, check indexation', $i),
                ];
            }

            $tested_polygon = $tested_multipolygon[$i];

            // walk through the points
            for ($j = 0, $size = sizeof($expected_polygon); $j < $size; $j++) {
                if (!isset($tested_polygon[$j])) {
                    return [
                        'success' => false,
                        'reason' => sprintf(
                            'Tested polygon with index: `%d` has not point with index: `%d`, check indexation',
                            $i,
                            $j
                        ),
                    ];
                }

                $expected_point = $expected_polygon[$j];
                $tested_point = $tested_polygon[$j];

                if (bccomp($expected_point[0], $tested_point[0], 6) !== 0) {
                    return [
                        'success' => false,
                        'reason' => sprintf(
                            'X coordinates of points are not equal: expected `%f` but `%s` given at index %d',
                            $expected_point[0],
                            $tested_point[0],
                            $j
                        )
                    ];
                }

                if (bccomp($expected_point[1], $tested_point[1], 6) !== 0) {
                    return [
                        'success' => false,
                        'reason' => sprintf(
                            'Y coordinates of points are not equal: expected `%f` but `%s` given at index %d',
                            $expected_point[1],
                            $tested_point[1],
                            $j
                        )
                    ];
                }
            }
        }

        return ['success' => true, 'reason' => ''];
    }
}