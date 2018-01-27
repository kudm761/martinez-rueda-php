<?php
namespace MartinezRueda;

/**
 * Time complexity O((n+k) log n)
 * n is number of all edges of all polygons in operation
 * k is number of intersections of all polygon edges
 *
 * Also Bentley - Ottmann algorithm is used for search of intersections
 * @link https://en.wikipedia.org/wiki/Bentley%E2%80%93Ottmann_algorithm
 *
 * Class Algorithm
 * @package MartinezRueda
 */
class Algorithm
{
    const OPERATION_INTERSECTION = 'INTERSECTION';
    const OPERATION_UNION = 'UNION';
    const OPERATION_DIFFERENCE = 'DIFFERENCE';
    const OPERATION_XOR = 'XOR';

    const POLYGON_TYPE_SUBJECT = 1;
    const POLYGON_TYPE_CLIPPING = 2;

    const EDGE_TYPE_NORMAL = 1;
    const EDGE_TYPE_NON_CONTRIBUTING = 2;
    const EDGE_TYPE_SAME_TRANSITION = 3;
    const EDGE_TYPE_DIFFERENT_TRANSITION = 4;

    /**
     * Deque
     *
     * @var array
     * @deprecated
     */
    protected $event_holder = [];

    /**
     * @var null|PriorityQueue
     */
    protected $eq = null;

    public function __construct()
    {
        $this->eq = new PriorityQueue();
    }

    /**
     * @param Polygon $subject
     * @param Polygon $clipping
     * @return Polygon
     */
    public function getDifference(Polygon $subject, Polygon $clipping) : Polygon
    {
        return $this->compute($subject, $clipping, self::OPERATION_DIFFERENCE);
    }

    /**
     * @param Polygon $subject
     * @param Polygon $clipping
     * @return Polygon
     */
    public function getUnion(Polygon $subject, Polygon $clipping) : Polygon
    {
        return $this->compute($subject, $clipping, self::OPERATION_UNION);
    }

    /**
     * @param Polygon $subject
     * @param Polygon $clipping
     * @return Polygon
     */
    public function getIntersection(Polygon $subject, Polygon $clipping) : Polygon
    {
        return $this->compute($subject, $clipping, self::OPERATION_INTERSECTION);
    }

    /**
     * @param Polygon $subject
     * @param Polygon $clipping
     * @return Polygon
     */
    public function getXor(Polygon $subject, Polygon $clipping) : Polygon
    {
        return $this->compute($subject, $clipping, self::OPERATION_XOR);
    }

    /**
     * @param Polygon $subject
     * @param Polygon $clipping
     * @param string $operation
     * @return Polygon
     */
    protected function compute(Polygon $subject, Polygon $clipping, string $operation) : Polygon
    {
        // Test for 1 trivial result case
        if ($subject->ncontours() * $clipping->ncontours() == 0) {
            if ($operation == self::OPERATION_DIFFERENCE) {
                $result = $subject;
            }

            if ($operation == self::OPERATION_UNION || $operation == self::OPERATION_XOR) {
                $result = ($subject->ncontours() == 0) ? $clipping : $subject;
            }

            return $result;
        }

        // Test 2 for trivial result case
        $box = $subject->getBoundingBox();
        $minsubj = $box['min'];
        $maxsubj = $box['max'];

        $box = $clipping->getBoundingBox();
        $minclip = $box['min'];
        $maxclip = $box['max'];

        if ($minsubj->x > $maxclip->x || $minclip->x > $maxsubj->x
            || $minsubj->y > $maxclip->y || $minclip->y > $maxsubj->y) {
            // the bounding boxes do not overlap
            if ($operation == self::OPERATION_DIFFERENCE) {
                $result = $subject;
            }

            if ($operation == self::OPERATION_UNION || $operation == self::OPERATION_XOR) {
                $result = $subject;

                for ($i = 0; $i < $clipping->ncontours(); $i++) {
                    $result->push_back($clipping->contour($i));
                }
            }

            return $result;
        }

        // Boolean operation is not trivial

        // Insert all the endpoints associated to the line segments into the event queue
        for ($i = 0; $i < $subject->ncontours(); $i++) {
            for ($j = 0; $j < $subject->contour($i)->nvertices(); $j++) {
                $this->processSegment($subject->contour($i)->segment($j), self::POLYGON_TYPE_SUBJECT);
            }
        }

        for ($i = 0; $i < $clipping->ncontours(); $i++) {
            for ($j = 0; $j < $clipping->contour($i)->nvertices(); $j++) {
                $this->processSegment($clipping->contour($i)->segment($j), self::POLYGON_TYPE_CLIPPING);
            }
        }

        $connector = new Connector();
        $sweepline = new SweepLine();

        $min_max_x = min($maxsubj->x, $maxclip->x);

        Debug::debug(
            function () {
                echo 'Initial queue:', PHP_EOL;

                $i = 0;

                foreach ($this->eq->events as $sweep_event) {
                    echo "\t", ++$i, ' - ', Debug::gatherSweepEventData($sweep_event), PHP_EOL;
                }
            }
        );

        while (!$this->eq->isEmpty()) {
            $e = $this->eq->dequeue();

            Debug::debug(
                function () use ($e) {
                    echo 'Process event:', PHP_EOL;
                    echo "\t", Debug::gatherSweepEventData($e), PHP_EOL;
                }
            );

            if (($operation == self::OPERATION_INTERSECTION && ($e->p->x > $min_max_x))
                || ($operation == self::OPERATION_DIFFERENCE && ($e->p->x > $maxsubj->x))) {
                $result = $connector->toPolygon();
                return $result;
            }

            if ($e->is_left) {
                $position = $sweepline->insert($e);
                $prev = null;

                if ($position > 0) {
                    $prev = $sweepline->get($position - 1);
                }

                $next = null;

                if ($position < $sweepline->size() - 1) {
                    $next = $sweepline->get($position + 1);
                }

                if (is_null($prev)) {
                    $e->inside = false;
                    $e->in_out = false;
                } elseif ($prev->edge_type != self::EDGE_TYPE_NORMAL) {
                    if ($position - 2 < 0) {
                        $e->inside = false;
                        $e->in_out = false;

                        if ($prev->polygon_type != $e->polygon_type) {
                            $e->inside = true;
                        } else {
                            $e->in_out = true;
                        }
                    } else {
                        $prev2 = $sweepline->get($position - 2);

                        if ($prev->polygon_type == $e->polygon_type) {
                            $e->in_out = !$prev->in_out;
                            $e->inside = !$prev2->in_out;
                        } else {
                            $e->in_out = !$prev2->in_out;
                            $e->inside = !$prev->in_out;
                        }
                    }
                } elseif ($e->polygon_type == $prev->polygon_type) {
                    $e->inside = $prev->inside;
                    $e->in_out = !$prev->in_out;
                } else {
                    $e->inside = !$prev->in_out;
                    $e->in_out = $prev->inside;
                }

                Debug::debug(
                    function () use ($sweepline) {
                        echo 'Status line after insertion: ', PHP_EOL;

                        $i = 0;

                        foreach ($sweepline->events as $sweep_event) {
                            echo "\t", ++$i, ' - ', Debug::gatherSweepEventData($sweep_event), PHP_EOL;
                        }
                    }
                );

                if (!is_null($next)) {
                    $this->possibleIntersection($e, $next);
                }

                if (!is_null($prev)) {
                    $this->possibleIntersection($prev, $e);
                }
            } else { // not left, the line segment must be removed from S
                $other_pos = -1;

                foreach ($sweepline->events as $index => $item) {
                    if ($item->equalsTo($e->other)) {
                        $other_pos = $index;
                        break;
                    }
                }

                if ($other_pos != -1) {
                    $prev = null;

                    if ($other_pos > 0) {
                        $prev = $sweepline->get($other_pos - 1);
                    }

                    $next = null;

                    if ($other_pos < sizeof($sweepline->events) - 1) {
                        $next = $sweepline->get($other_pos + 1);
                    }
                }

                // Check if the line segment belongs to the Boolean operation
                switch ($e->edge_type) {
                    case self::EDGE_TYPE_NORMAL:
                        switch ($operation) {
                            case self::OPERATION_INTERSECTION:
                                if ($e->other->inside) {
                                    $connector->add($e->segment());
                                }

                                break;

                            case self::OPERATION_UNION:
                                if (!$e->other->inside) {
                                    $connector->add($e->segment());
                                }

                                break;

                            case self::OPERATION_DIFFERENCE:
                                if ($e->polygon_type == self::POLYGON_TYPE_SUBJECT && !$e->other->inside
                                    || $e->polygon_type == self::POLYGON_TYPE_CLIPPING && $e->other->inside) {
                                    $connector->add($e->segment());
                                }

                                break;

                            case self::OPERATION_XOR:
                                $connector->add($e->segment());
                                break;
                        }

                        break; // end of EDGE_TYPE_NORMAL

                    case self::EDGE_TYPE_SAME_TRANSITION:
                        if ($operation == self::OPERATION_INTERSECTION || $operation == self::OPERATION_UNION) {
                            $connector->add($e->segment());
                        }

                        break;

                    case self::EDGE_TYPE_DIFFERENT_TRANSITION:
                        if ($operation == self::OPERATION_DIFFERENCE) {
                            $connector->add($e->segment());
                        }

                        break;
                } // end switch ($e->edge_type)

                if ($other_pos != -1) {
                    $sweepline->remove($sweepline->get($other_pos));
                }

                if (!is_null($next) && !is_null($prev)) {
                    $this->possibleIntersection($next, $prev);
                }

                Debug::debug(
                    function () use ($connector) {
                        echo 'Connector:', PHP_EOL;
                        echo Debug::gatherConnectorData($connector), PHP_EOL;
                    }
                );
            }
        }

        return $connector->toPolygon();
    }

    /**
     * @param Segment $segment0
     * @param Segment $segment1
     * @param Point $pi0
     * @param Point $pi1
     * @return int
     */
    protected function findIntersection(Segment $segment0, Segment $segment1, Point &$pi0, Point &$pi1) : int
    {
        $p0 = $segment0->begin();
        $d0 = new Point($segment0->end()->x - $p0->x, $segment0->end()->y - $p0->y);

        $p1 = $segment1->begin();
        $d1 = new Point($segment1->end()->x - $p1->x, $segment1->end()->y - $p1->y);

        $sqr_epsilon = 1e-7; // it was 1e-3 before
        $E = new Point($p1->x - $p0->x, $p1->y - $p0->y);
        $kross = $d0->x * $d1->y - $d0->y * $d1->x;
        $sqr_kross = $kross * $kross;
        $sqr_len0 = $d0->x * $d0->x + $d0->y * $d0->y;
        $sqr_len1 = $d1->x * $d1->x + $d1->y * $d1->y;

        if ($sqr_kross > $sqr_epsilon * $sqr_len0 * $sqr_len1) {
            $s = ($E->x * $d1->y - $E->y * $d1->x) / $kross;

            if ($s < 0 || $s > 1) {
                return 0;
            }

            $t = ($E->x * $d0->y - $E->y * $d0->x) / $kross;

            if ($t < 0 || $t > 1) {
                return 0;
            }

            // intersection of lines is a point an each segment
            $pi0 = new Point($p0->x + $s * $d0->x, $p0->y + $s * $d0->y);

            if ($pi0->distanceTo($segment0->begin()) < 1e-8) {
                $pi0 = $segment0->begin();
            }

            if ($pi0->distanceTo($segment0->end()) < 1e-8) {
                $pi0 = $segment0->end();
            }

            if ($pi0->distanceTo($segment1->begin()) < 1e-8) {
                $pi0 = $segment1->begin();
            }

            if ($pi0->distanceTo($segment1->end()) < 1e-8) {
                $pi0 = $segment1->end();
            }

            return 1;
        }

        $sqr_len_e = $E->x * $E->x + $E->y * $E->y;
        $kross = $E->x * $d0->y - $E->y * $d0->x;
        $sqr_kross = $kross * $kross;

        if ($sqr_kross > $sqr_epsilon * $sqr_len0 * $sqr_len_e) {
            return 0;
        }

        $s0 = ($d0->x * $E->x + $d0->y * $E->y) / $sqr_len0;
        $s1 = $s0 + ($d0->x * $d1->x + $d0->y * $d1->y) / $sqr_len0;

        $smin = min($s0, $s1);
        $smax = max($s0, $s1);

        $w = [];
        $imax = $this->findIntersection2(0.0, 1.0, $smin, $smax, $w);

        if ($imax > 0) {
            $pi0 = new Point($p0->x + $w[0] * $d0->x, $p0->y + $w[0] * $d0->y);

            if ($pi0->distanceTo($segment0->begin()) < 1e-8) {
                $pi0 = $segment0->begin();
            }

            if ($pi0->distanceTo($segment0->end()) < 1e-8) {
                $pi0 = $segment0->end();
            }

            if ($pi0->distanceTo($segment1->begin()) < 1e-8) {
                $pi0 = $segment1->begin();
            }

            if ($pi0->distanceTo($segment1->end()) < 1e-8) {
                $pi0 = $segment1->end();
            }

            if ($imax > 1) {
                $pi1 = new Point($p0->x + $w[1] * $d0->x, $p0->y + $w[1] * $d0->y);
            }
        }

        return $imax;
    }

    /**
     * @param float $u0
     * @param float $u1
     * @param float $v0
     * @param float $v1
     * @param array $w
     * @return int
     */
    protected function findIntersection2(float $u0, float $u1, float $v0, float $v1, array &$w) : int
    {
        if ($u1 < $v0 || $u0 > $v1) {
            return 0;
        }

        if ($u1 > $v0) {
            if ($u0 < $v1) {
                $w[0] = $u0 < $v0 ? $v0 : $u0;
                $w[1] = $u1 > $v1 ? $v1 : $u1;

                return 2;
            } else {
                $w[0] = $u0;
                return 1;
            }
        } else {
            $w[0] = $u1;
            return 1;
        }
    }

    /**
     * @param SweepEvent $event1
     * @param SweepEvent $event2
     * @throws \Exception
     */
    protected function possibleIntersection(SweepEvent $event1, SweepEvent $event2)
    {
        // uncomment these two lines if self-intersecting polygons are not allowed
        // if ($event1->polygon_type == $event2->polygon_type) {
        //    return false;
        // }

        $ip1 = new Point();
        $ip2 = new Point();

        $intersections = $this->findIntersection($event1->segment(), $event2->segment(), $ip1, $ip2);

        if (empty($intersections)) {
            return;
        }

        if ($intersections == 1 && ($event1->p->equalsTo($event2->p) || $event1->other->p->equalsTo($event2->other->p))) {
            return;
        }

        // the line segments overlap, but they belong to the same polygon
        // the program does not work with this kind of polygon
        if ($intersections == 2 && $event1->polygon_type == $event2->polygon_type) {
            throw new \Exception('Polygon has overlapping edges.');
        }

        if ($intersections == 1) {
            if (!$event1->p->equalsTo($ip1) && !$event1->other->p->equalsTo($ip1)) {
                $this->divideSegment($event1, $ip1);
            }

            if (!$event2->p->equalsTo($ip1) && !$event2->other->p->equalsTo($ip1)) {
                $this->divideSegment($event2, $ip1);
            }

            return;
        }

        // The line segments overlap
        $sorted_events = [];

        if ($event1->p->equalsTo($event2->p)) {
            $sorted_events[] = 0;
        } elseif (Helper::compareSweepEvents($event1, $event2)) {
            $sorted_events[] = $event2;
            $sorted_events[] = $event1;
        } else {
            $sorted_events[] = $event1;
            $sorted_events[] = $event2;
        }

        if ($event1->other->p->equalsTo($event2->other->p)) {
            $sorted_events[] = 0;
        } elseif (Helper::compareSweepEvents($event1->other, $event2->other)) {
            $sorted_events[] = $event2->other;
            $sorted_events[] = $event1->other;
        } else {
            $sorted_events[] = $event1->other;
            $sorted_events[] = $event2->other;
        }

        if (sizeof($sorted_events) == 2) {
            $event1->edge_type = $event1->other->edge_type = self::EDGE_TYPE_NON_CONTRIBUTING;
            $event2->edge_type = $event2->other->edge_type = ($event1->in_out == $event2->in_out)
                ? self::EDGE_TYPE_SAME_TRANSITION
                : self::EDGE_TYPE_DIFFERENT_TRANSITION;

            return;
        }

        if (sizeof($sorted_events) == 3) {
            $sorted_events[1]->edge_type = $sorted_events[1]->other->edge_type = self::EDGE_TYPE_NON_CONTRIBUTING;

            if ($sorted_events[0]) {
                $sorted_events[0]->other->edge_type = ($event1->in_out == $event2->in_out)
                    ? self::EDGE_TYPE_SAME_TRANSITION
                    : self::EDGE_TYPE_DIFFERENT_TRANSITION;
            } else {
                $sorted_events[2]->other->edge_type = ($event1->in_out == $event2->in_out)
                    ? self::EDGE_TYPE_SAME_TRANSITION
                    : self::EDGE_TYPE_DIFFERENT_TRANSITION;
            }

            $this->divideSegment($sorted_events[0] ? $sorted_events[0] : $sorted_events[2]->other, $sorted_events[1]->p);

            return;
        }

        if (!$sorted_events[0]->equalsTo($sorted_events[3]->other)) {
            $sorted_events[1]->type = self::EDGE_TYPE_NON_CONTRIBUTING;
            $sorted_events[2]->type = ($event1->in_out == $event2->in_out)
                ? self::EDGE_TYPE_SAME_TRANSITION
                : self::EDGE_TYPE_DIFFERENT_TRANSITION;

            $this->divideSegment($sorted_events[0], $sorted_events[1]->p);
            $this->divideSegment($sorted_events[1], $sorted_events[2]->p);

            return;
        }

        $sorted_events[1]->type = $sorted_events[1]->other->type = self::EDGE_TYPE_NON_CONTRIBUTING;
        $this->divideSegment($sorted_events[0], $sorted_events[1]->p);

        $sorted_events[3]->other->type = ($event1->in_out == $event2->in_out)
            ? self::EDGE_TYPE_SAME_TRANSITION
            : self::EDGE_TYPE_DIFFERENT_TRANSITION;
        $this->divideSegment($sorted_events[3]->other, $sorted_events[2]->p);
    }

    /**
     * Add element to the end of dequeue
     *
     * @param SweepEvent $event
     * @return SweepEvent
     */
    protected function storeSweepEvent(SweepEvent $event) : SweepEvent
    {
        $this->event_holder[] = $event;

        return $this->event_holder[sizeof($this->event_holder) - 1];
    }

    protected function divideSegment(SweepEvent $event, Point $point)
    {
        $right = new SweepEvent($point, false, $event->polygon_type, $event, $event->edge_type);
        $left = new SweepEvent($point, true, $event->polygon_type, $event->other, $event->other->edge_type);

        if (Helper::compareSweepEvents($left, $event->other)) {
            $event->other->is_left = true;
            $left->is_left = false;
        }

        //if (Helper::compareSweepEvents($event, $right)) {
            // nothing
        //}

        $event->other->other = $left;
        $event->other = $right;

        $this->eq->enqueue($left);
        $this->eq->enqueue($right);
    }

    /**
     * @param Segment $segment
     * @param int $polygon_type
     * @return void
     */
    protected function processSegment(Segment $segment, int $polygon_type)
    {
        // if the two edge endpoints are equal the segment is discarded
        if ($segment->begin()->equalsTo($segment->end())) {
            return;
        }

        $e1 = new SweepEvent($segment->begin(), true, $polygon_type);
        $e2 = new SweepEvent($segment->end(), true, $polygon_type, $e1);
        $e1->other = $e2;

        if ($e1->p->x < $e2->p->x) {
            $e2->is_left = false;
        } elseif ($e1->p->x > $e2->p->x) {
            $e1->is_left = false;
        } elseif ($e1->p->y < $e2->p->y) {
            $e2->is_left = false;
        } else {
            $e1->is_left = false;
        }

        $this->eq->enqueue($e1);
        $this->eq->enqueue($e2);
    }
}
