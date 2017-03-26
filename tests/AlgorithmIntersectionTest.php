<?php
use \MartinezRueda\Algorithm;
use MartinezRueda\Helper;

class AlgorithmIntersectionTest extends \PHPUnit\Framework\TestCase
{
    protected $implementation = null;

    public function setUp()
    {
        $this->implementation = new Algorithm();
    }

    /**
     * Test simple intersection result of two intersected polygons.
     *
     * @link https://gist.github.com/kudm761/b4aeb62e5c36b596396df8503c01be38
     */
    public function testSimplePositiveCase()
    {
        $data = [[[-3.09814453125, 75.2250649237144], [-4.5703125, 75.12950410894491], [-7.822265625000001, 74.5081553020789], [-7.8662109375, 74.11003203722439], [-3.27392578125, 74.78737860165963], [-.263671875, 75.31445589169716], [-.63720703125, 75.55208098028335], [-1.8017578124999998, 75.53562529096112], [-3.09814453125, 75.2250649237144]]];
        $subject = new \MartinezRueda\Polygon($data);

        $data = [[[-6.26220703125, 75.29773546875684], [-6.17431640625, 75.17454893148678], [-5.09765625, 75.27541260821627], [-4.482421875, 75.03901279805076], [-6.04248046875, 74.9536886200003], [-5.625, 74.70065320517152], [-4.5263671875, 74.7180368083091], [-4.8779296875, 74.58426829888151], [-3.8232421874999996, 74.54332982677906], [-1.99951171875, 74.17008033257684], [-1.494140625, 74.58426829888151], [-1.2084960937499998, 75.13514201950775], [-3.75732421875, 75.18578927942626], [-4.72412109375, 75.40885422846455], [-6.26220703125, 75.29773546875684]]];
        $clipping = new \MartinezRueda\Polygon($data);

        $result = $this->implementation->getIntersection($subject, $clipping);
        $tested = $result->toArray();

        $this->assertNotEmpty($tested, 'Intersection result of two polygons is empty, array of arrays of points is expected.');

        // correct result
        $expected = [[[-1.2796928252687, 75.136556755688], [-3.27392578125, 74.78737860166], [-4.6982590500119, 74.577294250758], [-4.8779296875, 74.584268298882], [-4.5263671875, 74.718036808309], [-5.625, 74.700653205172], [-5.9101740444242, 74.873497536896], [-5.2691008760483, 74.99598701721], [-4.482421875, 75.039012798051], [-4.6689021667529, 75.110666638215], [-4.5703125, 75.129504108945], [-3.7158913374922, 75.184965974832], [-1.2796928252687, 75.136556755688]]];

        $this->assertEquals(
            sizeof($tested),
            sizeof($expected),
            sprintf('Result multipolygon should contain one polygon, but contains %d', sizeof($tested))
        );

        $compare = Helper::compareMultiPolygons($expected, $tested);

        $this->assertTrue($compare['success'], $compare['reason']);
    }
}