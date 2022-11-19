<?php
/**
 * Roll.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Render extends Agent
{
    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init() {
        $this->width = 125;
        $this->height = $this->width;

        //test
//        $this->drawD20(2);

        $this->node_list = array("roll" => array("roll", "card"));
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->thing_report["info"] = "This rolls a dice.  See
                                https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        $this->thing_report['help'] = 
                'This is about dice with more than 6 sides.  Try "Roll d20". Or "Roll 3d20+17. Or "Card"';
$this->getRender();

foreach($this->triangles as $i=>$triangle) {

}


    }


    public function respondResponse() {
        $this->thing->flagGreen();

//        $choices = false;

        $this->thing_report["info"] = "This rolls a dice.  See
				https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] =
                'This is about dice with more than 6 sides.  Try "Roll d20". Or "Roll 3d20+17. Or "Card"';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

public function getRender() {

$x_max = 0;
$y_max = 0;
$z_max = 0;
$x_min = 0;
$y_min = 0;
$z_min = 0;

$this->triangles = array();

$filepath = $this->resource_path . 'stl/d20.STL';
$fp = fopen($filepath, "rb");
$section = file_get_contents($filepath, NULL, NULL, 0, 79);
fseek($fp, 80);
$data = fread($fp, 4);
$numOfFacets = unpack("I", $data);
for ($i = 0; $i < $numOfFacets[1]; $i++){
$triangle = null;
    //Start Normal Vector
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $normalVectorsX[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $normalVectorsY[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $normalVectorsZ[$i] = $hold[1];
    //End Normal Vector
    //Start Vertex1
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex1X[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex1Y[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex1Z[$i] = $hold[1];
    //End Vertex1
    //Start Vertex2
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex2X[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex2Y[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex2Z[$i] = $hold[1];
    //End Vertex2
    //Start Vertex3
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex3X[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex3Y[$i] = $hold[1];
    $data = fread($fp, 4);
    $hold = unpack("f", $data);
    $vertex3Z[$i] = $hold[1];
    //End Vertex3
    //Attribute Byte Count
    $data = fread($fp, 2);
    $hold = unpack("S", $data);
    $abc[$i] = $hold[1];
    
    $x_vals = array($vertex1X[$i], $vertex2X[$i], $vertex3X[$i]);
    $y_vals = array($vertex1Y[$i], $vertex2Y[$i], $vertex3Y[$i]);
    $z_vals = array($vertex1Z[$i], $vertex2Z[$i], $vertex3Z[$i]);

$point[0] = array($vertex1X[$i], $vertex1Y[$i], $vertex1Z[$i]);
$point[1] = array($vertex2X[$i], $vertex2Y[$i], $vertex2Z[$i]);
$point[2] = array($vertex3X[$i], $vertex3Y[$i], $vertex3Z[$i]);

$triangle = array($point[0],$point[1],$point[2]);

$this->triangles[] = $triangle;

}

}

/*
     *
     * @param unknown $number (optional)
     * @param unknown $die    (optional)
     * @return unknown
     */

public function makeWeb() {

$web = "";
$this->thing_report['web'] = $web;


}

    public function readSubject() {

    }


}
