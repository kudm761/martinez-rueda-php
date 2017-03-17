<?php
namespace MartinezRueda;

/**
 * Event - X coordinate at which something interesting happens:
 * left, right endpoint or edge intersection
 *
 * Class SweepEvent
 * @package MartinezRueda
 */
class SweepEvent
{
    /**
     * Point associated with the event
     *
     * @var Point|null
     */
    public $p = null;

    /**
     * Event associated to the other endpoint of the edge
     *
     * @var SweepEvent|null
     */
    public $other = null;

    /**
     * Is the point the left endpoint of the edge (p, other->p)
     *
     * @var bool|null
     */
    public $is_left = null;

    /**
     * Indicates if the edge belongs to subject or clipping polygon
     *
     * @var int|null
     */
    public $polygon_type = null;

    /**
     * Inside-outside transition into the polygon
     *
     * @var bool|null
     */
    public $in_out = null;

    /**
     * Is the edge (p, other->p) inside the other polygon
     *
     * @var bool|null
     */
    public $inside = null;

    /**
     * Used for overlapped edges
     *
     * @var int|null
     */
    public $edge_type = null;

    /**
     * For sorting, increases monotonically
     *
     * @var int
     */
    public $id = 0;

    /**
     * @deprecated
     * @var null
     */
    public $pos = null; // in s

    /**
     * SweepEvent constructor.
     * @param Point $p
     * @param bool $is_left
     * @param int $associated_polygon
     * @param null $other
     * @param int $edge_type
     */
    public function __construct(
        Point $p,
        bool $is_left,
        int $associated_polygon,
        $other = null,
        $edge_type = Algorithm::EDGE_TYPE_NORMAL
    ) {
        $this->p = $p;
        $this->is_left = $is_left;
        $this->polygon_type = $associated_polygon;
        $this->other = $other;
        $this->edge_type = $edge_type;

        static $id = 0;

        $this->id = ++$id;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return Segment
     */
    public function segment() : Segment
    {
        return new Segment($this->p, $this->other->p);
    }

    /**
     * @param Point $point
     * @return bool
     */
    public function below(Point $point) : bool
    {
        return $this->is_left
            ? Helper::signedArea($this->p, $this->other->p, $point) > 0
            : Helper::signedArea($this->other->p, $this->p, $point) > 0;
    }

    /**
     * @param Point $point
     * @return bool
     */
    public function above(Point $point) : bool
    {
        return !$this->below($point);
    }

    /**
     * @param SweepEvent $event
     * @return bool
     */
    public function equalsTo(SweepEvent $event) : bool
    {
        return $this->getId() === $event->getId();
    }

    /**
     * @param SweepEvent $event
     * @return bool
     */
    public function lessThan(SweepEvent $event) : bool
    {
        return $this->getId() < $event->getId();
    }
}