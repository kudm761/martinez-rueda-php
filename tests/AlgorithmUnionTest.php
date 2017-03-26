<?php
use \MartinezRueda\Algorithm;
use MartinezRueda\Helper;

class AlgorithmUnionTest extends \PHPUnit\Framework\TestCase
{
    protected $implementation = null;

    public function setUp()
    {
        $this->implementation = new Algorithm();
    }

    /**
     * Test simple union of two intersected polygons
     *
     * @link https://gist.github.com/kudm761/944eb9bbbd088e69f87421a1afa7218b
     */
    public function testSimpleCase()
    {
        $data = [[[-5.69091796875, 75.50265886674975], [-6.218261718749999, 75.29215785826014], [-6.87744140625, 74.8219342035653], [-5.38330078125, 74.61344527005673], [-3.27392578125, 74.78737860165963], [-2.83447265625, 75.26423875224219], [-3.251953125, 75.59040636514479], [-5.69091796875, 75.50265886674975]]];
        $subject = new \MartinezRueda\Polygon($data);

        $data = [[[-1.4501953125, 75.1125778338579], [-1.9116210937499998, 75.40331785380344], [-3.2958984375, 75.49165372814439], [-3.80126953125, 75.33672086232664], [-5.5810546875, 74.95939165894974], [-7.31689453125, 74.62510096387147], [-5.515136718749999, 74.15208909789665], [-4.19677734375, 74.86215220305225], [-2.373046875, 74.55503734449476], [-1.4501953125, 75.1125778338579]]];
        $clipping = new \MartinezRueda\Polygon($data);

        $result = $this->implementation->getUnion($subject, $clipping);
        $tested = $result->toArray();

        $this->assertNotEmpty($tested, 'Union result of two polygons is empty, array of arrays of points is expected.');

        // correct result
        $expected = [[[-1.91162109375, 75.403317853803], [-3.1104029643672, 75.479816573632], [-3.251953125, 75.590406365145], [-5.69091796875, 75.50265886675], [-6.21826171875, 75.29215785826], [-6.87744140625, 74.821934203565], [-6.5396028340834, 74.774792989124], [-7.31689453125, 74.625100963871], [-5.51513671875, 74.152089097897], [-4.5275307386443, 74.684009742754], [-3.5953601631731, 74.760873995822], [-2.373046875, 74.555037344495], [-1.4501953125, 75.112577833858], [-1.91162109375, 75.403317853803]]];

        $this->assertEquals(
            sizeof($tested),
            sizeof($expected),
            sprintf('Result multipolygon should contain one polygon, but contains %d', sizeof($tested))
        );

        // one polygon is expected in this union
        $expected_size = sizeof($expected[0]);
        $tested_size = sizeof($tested[0]);

        $this->assertEquals(
            $expected_size,
            $tested_size,
            sprintf('Size of result polygon is %d, but %d expected', $tested_size, $expected_size)
        );

        $compare = Helper::compareMultiPolygons($expected, $tested);

        $this->assertTrue($compare['success'], $compare['reason']);
    }

    /**
     * https://gist.github.com/kudm761/5b566e98698e8f8cdf2fe7cfdab04b58
     */
    public function testSimpleCaseWithHole()
    {
        $data = [[[-4.1748046875, 75.52464464250062], [-6.701660156249999, 75.52464464250062], [-6.74560546875, 74.44346576284508], [-3.75732421875, 74.44935750063425], [-3.7353515625, 74.76429887097666], [-4.8779296875, 74.76718570583334], [-4.866943359375, 75.30331101068566], [-3.8452148437499996, 75.30331101068566], [-3.8452148437499996, 75.52464464250062], [-4.1748046875, 75.52464464250062]]];
        $subject = new \MartinezRueda\Polygon($data);

        $data = [[[-4.383544921875, 75.59587329063447], [-4.427490234375, 74.36371391783985], [-2.6806640625, 74.36667478672423], [-2.65869140625, 75.59860599198842], [-4.383544921875, 75.59587329063447]]];
        $clipping = new \MartinezRueda\Polygon($data);

        $result = $this->implementation->getUnion($subject, $clipping);
        $tested = $result->toArray();

        $this->assertNotEmpty($tested, 'Union result of two polygons is empty, array of arrays of points is expected.');

        // correct result
        $expected = [
            [[-4.866943359375, 75.303311010686], [-4.8779296875, 74.767185705833], [-4.4131421817925, 74.766011374956], [-4.3939792383295, 75.303311010686], [-4.866943359375, 75.303311010686]],
            [[-4.383544921875, 75.595873290634], [-4.386085311755, 75.524644642501], [-6.70166015625, 75.524644642501], [-6.74560546875, 74.443465762845], [-4.424482645139, 74.448042121598], [-4.427490234375, 74.36371391784], [-2.6806640625, 74.366674786724], [-2.65869140625, 75.598605991988], [-4.383544921875, 75.595873290634]]
        ];

        $this->assertEquals(
            sizeof($tested),
            sizeof($expected),
            sprintf('Result multipolygon should contain two polygons, but contains %d', sizeof($tested))
        );

        $compare = Helper::compareMultiPolygons($expected, $tested);

        $this->assertTrue($compare['success'], $compare['reason']);
    }
}