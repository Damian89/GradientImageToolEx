<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

class GradientImageToolTest extends PHPUnit_Framework_TestCase
{

    private $tool;
    private $data = [];

    public function __construct()
    {
        $this->tool = new DamianSchwyrz\GradientImageTool\GradientImageTool(155, 155);
        $this->get_interal_data();
    }

    private function get_interal_data()
    {
        $this->data = $this->tool->get_internal_data();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Fehler: Das Bild kann nicht in irgendeiner Dimension 0 Pixel groß sein!
     */
    public function testConstruct()
    {

        new DamianSchwyrz\GradientImageTool\GradientImageTool(0, 0);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Fehler: Position ist außerhalb des gültigen Bereichs (0 bis 100 Prozent)
     */
    public function testAddPositionToSmall()
    {
        $this->tool->add_position(-1);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Fehler: Position ist außerhalb des gültigen Bereichs (0 bis 100 Prozent)
     */
    public function testAddPositionToBig()
    {
        $this->tool->add_position(101);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Fehler: Bitte HEX-Wert ohne Raute angeben!
     */
    public function testAddColorHex()
    {
        $this->tool->add_color('#000000');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Fehler: Länge des Hex-Strings stimmt nicht!
     */
    public function testAddColorLen()
    {
        $this->tool->add_color('0000000');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Fehler: Es fehlen Positionen für diese Farbe
     */
    public function testAddColorNoPos()
    {
        $this->tool->add_color('000000');
    }

    /**
     * @covers GradientImageTool::__construct
     */
    public function testWidthHeight()
    {
        $this->assertEquals(155, $this->data['width']);
        $this->assertEquals(155, $this->data['height']);
    }

    /**
     * @covers GradientImageTool::add_position
     */
    public function testPositionSet()
    {

        $this->tool->add_position(11);
        $this->get_interal_data();

        $this->assertEquals(11, $this->data['colored_points'][0]['position']);
        $this->assertEquals(1, count($this->data['colored_points']));

        $this->tool->add_position(22);
        $this->get_interal_data();

        $this->assertEquals(22, $this->data['colored_points'][1]['position']);
        $this->assertEquals(2, count($this->data['colored_points']));
    }

    /**
     * @covers GradientImageTool::add_color
     */
    public function testColorSet()
    {

        $this->tool->add_position(11)->add_color('fffaaa');
        $this->get_interal_data();

        $this->assertEquals(11, $this->data['colored_points'][0]['position']);
        $this->assertEquals(255, $this->data['colored_points'][0]['r']);
        $this->assertEquals(250, $this->data['colored_points'][0]['g']);
        $this->assertEquals(170, $this->data['colored_points'][0]['b']);

        $this->tool->add_position(100)->add_color('fffaaa');
        $this->get_interal_data();

        $this->assertEquals(100, $this->data['colored_points'][1]['position']);
        $this->assertEquals(255, $this->data['colored_points'][1]['r']);
        $this->assertEquals(250, $this->data['colored_points'][1]['g']);
        $this->assertEquals(170, $this->data['colored_points'][1]['b']);
    }

    /**
     * @covers GradientImageTool::calculate_rel_to_abs
     */
    public function testCalcAbsolutes()
    {

        $this->tool->add_position(20)->add_color('000');

        $this->tool->add_position(66)->add_color('fff');

        $this->tool->add_position(88)->add_color('f5f5f5');

        $this->tool->calculate_rel_to_abs();

        $this->get_interal_data();

        $this->assertEquals(31, $this->data['colored_points'][0]['absolute']);
        $this->assertEquals(102, $this->data['colored_points'][1]['absolute']);
        $this->assertEquals(136, $this->data['colored_points'][2]['absolute']);
    }

    /**
     * @covers GradientImageTool::set_min_value
     */
    public function testSetMinValue()
    {

        $this->tool->add_position(20)->add_color('000');
        $this->tool->add_position(66)->add_color('fff');
        $this->tool->add_position(88)->add_color('f5f5f5');

        $this->tool->calculate_rel_to_abs();

        $this->tool->set_min_value(22);

        $this->get_interal_data();

        $this->assertEquals(22, $this->data['min']);
        $this->assertEquals(22, $this->data['colored_points'][0]['value']);
    }

    /**
     * @covers GradientImageTool::set_max_value
     */
    public function testSetMaxValue()
    {

        $this->tool->add_position(20)->add_color('000');
        $this->tool->add_position(66)->add_color('fff');
        $this->tool->add_position(88)->add_color('f5f5f5');

        $this->tool->calculate_rel_to_abs();

        $this->tool->set_max_value(99);

        $this->get_interal_data();

        $this->assertEquals(99, $this->data['max']);
        $this->assertEquals(99, $this->data['colored_points'][2]['value']);
    }

    /**
     * @covers GradientImageTool::set_value
     */
    public function testSetValue()
    {

        $this->tool->add_position(20)->add_color('000');
        $this->tool->add_position(66)->add_color('fff');
        $this->tool->add_position(88)->add_color('f5f5f5');

        $this->tool->calculate_rel_to_abs();

        $this->tool->set_min_value(22);
        $this->tool->set_max_value(99);
        $this->tool->set_value(55);

        $this->get_interal_data();
        $this->assertEquals(55, $this->data['value']);
    }

    /**
     * @covers GradientImageTool::create_image
     */
    public function testCreateImage()
    {

        $this->tool->add_position(20)->add_color('000');
        $this->tool->add_position(66)->add_color('fff');
        $this->tool->add_position(88)->add_color('f5f5f5');

        $this->tool->calculate_rel_to_abs();
        $this->tool->create_image();

        $this->get_interal_data();

        $is_res = is_resource($this->data['image']);
        $this->assertEquals(true, $is_res);
    }

    /**
     * @covers GradientImageTool::fill_with_gradient
     */
    public function testCreateImageWithGrad()
    {

        $this->tool->add_position(20)->add_color('000');
        $this->tool->add_position(66)->add_color('fff');
        $this->tool->add_position(88)->add_color('f5f5f5');

        $this->tool->calculate_rel_to_abs();
        $this->tool->create_image();
        $this->tool->fill_with_gradient();

        $this->get_interal_data();

        $is_res = is_resource($this->data['image']);
        $this->assertEquals(true, $is_res);
    }

    /**
     * @covers GradientImageTool::hex_to_rgb
     */
    public function testHexToRGB()
    {
        $rgb = $this->tool->hex_to_rgb('abfba5a');

        $this->assertArraySubset([ 'r' => 171, 'g' => 251, 'b' => 165], $rgb);
    }
    
    

}
