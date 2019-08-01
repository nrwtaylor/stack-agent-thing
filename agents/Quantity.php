<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Quantity
{

    // This is a place.

    //

    // This is an agent of a place.  They can probaby do a lot for somebody.
    // With the right questions.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
//echo "meep";
//exit();
//        $this->start_time = microtime(true);

        //if ($agent_input == null) {$agent_input = "";}
        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing;

        $this->agent_prefix = 'Agent "Quantity" ';

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.',"INFORMATION");

        // I'm not sure quite what the node_list means yet
        // in the context of headcodes.
        // At the moment it seems to be the headcode routing.
        // Which is leading to me to question whether "is"
        // or "Place" is the next Agent to code up.  I think
        // it will be "Is" because you have to define what 
        // a "Place [is]".
 //       $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
 //       $this->thing->choice->load('headcode');

        $this->keywords = array('quantity','next', 'last', 'nearest', 'accept', 'clear', 'drop','add','new','here','there');

//        $this->default_quantity = $this->thing->container['api']['quantity']['default_quantity'];
        $this->default_quantity = 0;
        //$this->default_place_code = $this->thing->container['api']['place']['default_place_code'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';


        $this->default_alias = "Thing";
        $this->current_time = $this->thing->time();

		$this->test= "Development code"; // Always iterative.

        // Non-nominal
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;

        // Potentially nominal
        $this->subject = $thing->subject;

        // Treat as nominal
        $this->from = $thing->from;

        // Agent variables
        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

        $this->state = null; // to avoid error messages
        $this->lastQuantity();
        // Read the subject to determine intent.

		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.

        $this->Respond();

        if ($this->agent_input != "extract") {
            $this->set();
        }

        $this->thing->log( $this->agent_prefix .' loaded quantity ' . $this->quantity . "." );

        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

        $this->thing_report['response'] = $this->response;

		return;
    }

    function set()
    {

        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}
        //$string_coordinate = $this->stringCoordinate($this->coordinate);
        $quantity = $this->quantity;
        if (($this->quantity == true) and (!is_numeric($this->quantity))) {return;}

        //$this->refreshed_at = $this->current_time;
        $quantity_variable = new Variables($this->thing, "variables quantity " . $this->from);

        $quantity_variable->setVariable("quantity", $quantity);
        $quantity_variable->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log( $this->agent_prefix .' set ' . $this->quantity . ".", "INFORMATION" );

        //$coordinate_variable = new Variables($this->thing, "variables " . $string_coordinate . " " . $this->from);
        //$coordinate_variable->setVariable("refreshed_at", $this->refreshed_at);

        return;
    }
/*
    function isCoordinate()
    {
        $place_zone = "05";
        //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);

        foreach (range(1,9999) as $n) {
            foreach($this->coordinates as $coordinate) {

                $place_code = $place_zone . str_pad($n, 4, "0", STR_PAD_LEFT);
                if ($this->getCoordinate($coordinate)) {
                    // Code doesn't exist
                    break;
                }
            }
            if ($n >= 9999) {
                $this->thing->log("No Place code available in zone " . $place_zone .".", "WARNING");
                return;
            }
        }
    }
*/
/*
    function nextCode()
    {
        $place_code_candidate = null;

        foreach ($this->coordinates as $place) {
            $place_code = strtolower($place['code']);
            if (($place_code == $place_code_candidate) or ($place_code_candidate == null)) {
                $place_code_candidate = str_pad(rand(100,9999) , 8, " ", STR_PAD_LEFT);
            }
        }

//        $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);
        return $place_code;
    }
*/
/*
    function nextCoordinate() {

        $this->thing->log("next coordinate");
        // Pull up the current headcode
        $this->get();

        // Find the end time of the headcode
        // which is $this->end_at

        // One minute into next headcode
        $quantity = 1;
        $next_time = $this->thing->json->time(strtotime($this->end_at . " " . $quantity . " minutes"));

        $this->get($next_time);

        // So this should create a headcode in the next quantity unit.

        return $this->available;

    }
*/
    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

    function getQuantity($selector = null)
    {
if (!isset($this->quantities)) {$this->getQuantities();}
//var_dump ($this->coordinates);
        foreach ($this->quantities as $quantity) {
            // Match the first matching place

            if (($selector == null) or ($selector == "")) {

                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.

if (is_array($this->last_quantity)) {echo "lastquant"; exit();}
                $this->quantity = $this->last_quantity;

                $this->quantity_variable = new Variables($this->thing, "variables " . $this->quantity . " " . $this->from);
                return $this->quantity;

            }

            // Get closest
/*
            if (($place['code'] == $selector) or ($place['name'] == $selector)) {

                $this->refreshed_at = $coordinate['refreshed_at'];
                //$this-> = $coordinate['coordinate'];

                $this->coordinate_variable = new Variables($this->thing, "variables " . $this->coordinate . " " . $this->from);

                return array($this->coordinate);
            }
*/
       }

       return true;
    }

    //function makeCoordinate($v)
    //{

    //    var_dump($v);

    //}

    function getQuantities()
    {
        $this->quantity_list = array();
        $this->quantities = array();

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, 'quantity');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Quantity" found ' . count($findagent_thing->thing_report['things']) ." quantity Things." );


//        if ($findagent_thing->thing_reports['things'] == false) {
//                $place_code = $this->default_place_code;
//                $place_name = $this->default_place_name;
//            return array($this->placecode_list, $this->placename_list, $this->places);
//        }

        if ( ($findagent_thing->thing_report['things'] == true)) {}

        //var_dump(count($findagent_thing->thing_report['things'])); 
        //var_dump($findagent_thing->thing_report['things'] == true);

        if (!$this->is_positive_integer($count))
        {
//echo $count;
//echo "meep";
            // No places found
        } else {



            foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object)
            {
                $uuid = $thing_object['uuid'];

                $variables_json= $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables['quantity'])) {

                    $quantity = $this->default_quantity;
                    $refreshed_at = "meep getPlaces";

                    if(isset($variables['quantity']['quantity'])) {$quantity = $variables['quantity']['quantity'];}
                    if(isset($variables['quantity']['refreshed_at'])) {$refreshed_at = $variables['quantity']['refreshed_at'];}
/*
                    // Check if the place is already in the the list (this->places)
                    $found = false;
                    foreach(array_reverse($this->places) as $key=>$place) {

                        if ($place["name"] == $place_name) {
                            // Found place in list.  Don't add again.
                            $found = true;
                            break;
                        }
                    }

$found = false;

*/
//                    if ($found == false) {
// If it isn't an array try and convert text to array
//if (!is_array($coordinate)) {$coordinate = $this->arrayCoordinate($coordinate);}

                        $this->quantities[] = array("quantity"=>$quantity, "refreshed_at"=>$refreshed_at);
                        $this->quantity_list[] = $quantity;
  //                  }
                }
            }
        }
//exit();
        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_quantities = array();
        foreach(array_reverse($this->quantities) as $key=>$quantity) {

            $quantity = $quantity['quantity'];

            if (!isset($quantity['refreshed_at'])) {continue;}

            $refreshed_at = $quantity['refreshed_at'];

            if (isset($filtered_quantities[$quantity]['refreshed_at'])) {
                if (strtotime($refreshed_at) > strtotime($filtered_quantities[$quantity]['refreshed_at'])) {
                    $filtered_quantities[$quantity] = array("quantity"=>$quantity, 'refreshed_at' => $refreshed_at);
                }
                continue;
            } 

            $filtered_quantities[$quantity] = array("quantity"=>$quantity, 'refreshed_at' => $refreshed_at);


        }

$refreshed_at = array();
foreach ($this->quantities as $key => $row)
{
    $refreshed_at[$key] = $row['refreshed_at'];
}
array_multisort($refreshed_at, SORT_DESC, $this->quantities);

/*
// Get latest per place
$this->places = array();
foreach($filtered_places as $key=>$filtered_place) {
//var_dump($filtered_place);

        $this->places[] = $filtered_place;
}
*/
$this->old_quantities = $this->quantities;
$this->quantities = array();
foreach ($this->old_quantities as $key =>$row)
{

//var_dump( strtotime($row['refreshed_at']) );
    if ( strtotime($row['refreshed_at']) != false) { 
      $this->quantities[] = $row;
    }
}

//exit();
    //exit();
       // Indexing not implemented
        $this->max_index = 0;

        return array($this->quantity_list, $this->quantities);

    }

    function is_positive_integer($str)
    {
        return (is_numeric($str) && $str > 0 && $str == round($str));
    }

    private function get($quantity = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($place_code == null) {$place_code = $this->place_code;}

        //$this->quantity_variable = new Variables($this->thing, "variables " . $quantity . " " . $this->from);
        $this->quantity_variable = new Variables($this->thing, "variables " . "quantity" . " " . $this->from);


        $quantity = $this->variables_quantity->getVariable("quantity");

//        $this->quantity = $this->arrayQuantity($quantity);
        $this->quantity = $quantity;

        $this->refreshed_at = $this->variables_quantity->getVariable("refreshed_at");

        return $this->quantity;
    }

    function dropQuantity() {
        $this->thing->log($this->agent_prefix . "was asked to drop a Quantity.");


        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->quantity)) {
            $this->quantity->Forget();
            $this->quantity = null;
        }

        $this->get();
 
    }


    function makeQuantity($quantity = null)
    {
        if ($quantity == null) {return true;}
if (!is_numeric($quantity)) {return true;}
//var_dump($coordinate);


/*
        if (!isset($this->coordinates)) {$this->getCoordinates();}

        // See if the coordination is already tagged
        foreach ($this->coordinates as $coordinate_object) {
            if ($coordinate == $coordinate_object['coordinate']) {return true;}
        }
*/
        //if ($coordinate == null) {$coordinate = $this->nextCode();}
        $this->thing->log('Agent "Quantity" will make a Quantity for ' . $this->stringQuantity($quantity) . ".");

        $this->current_quantity = $quantity;
        $this->quantity = $quantity;
        $this->refreshed_at = $this->current_time;
  
        // This will write the refreshed at.
        $this->set();

      //      $this->getCoordinate();
      //      $this->getPlace($this->place_code);

      //      $this->place_thing = $this->thing;

        $this->thing->log('Agent "Quantity" found a Quantity and pointed to it.');
    }

    function quantityTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $quantity_time = "x";
            return $quantity_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $quantity_time = $this->hour . $this->minute;

        if ($input == null) {$this->quantity_time = $quantity_time;}

        return $quantity_time;
    }

    // Currently just tuple extraction.
    // With negative numbers.

    public function extractQuantities($input = null)
    {

        $number = new Number($this->thing, "number");
//var_dump ($number->numbers);
//exit();

        $numbers = $number->numbers;

        //$this->coordinates = array_unique($this->coordinates);
        return $numbers;
    }

    public function extractQuantity($input)
    {
        $this->quantity = null;

if (is_array($input)) {$this->quantity = true; return;}

        $quantities = $this->extractQuantities($input);


        if ( (is_array($quantities)) and (count($quantities) == 1)) {
        //if ( ( count($coordinates) ) == 1) {
            if (isset($quantities[0])) {$this->quantity = $quantities[0];}

            $this->thing->log( $this->agent_prefix  . 'found a quantity ' . $this->quantity . ' in the text.');
            return $this->quantity;
        }


        //if (count($place_codes == 0)) {return false;}
        //if (count($place_codes > 1)) {return true;}

        // And then extract place names.
        // Take out word 'place' at the start.
//        $filtered_input = ltrim(strtolower($input), "place");

        if ( (is_array($quantities)) and (count($quantities) == 1)) {
        //if (count($coordinates) == 1) {
            $this->quantity = $this->quantities[0];
        }
        return $this->quantity;
    }

    // Assert that the string has a coordinate.
    function assertQuantity($input)
    {

        if (($pos = strpos(strtolower($input), "quantity is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("quantity is")); 
        } elseif (($pos = strpos(strtolower($input), "quantity")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("quantity")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

//echo "assert coordinate<br>";
//var_dump($filtered_input);

        $quantity = $this->extractQuantity($filtered_input);
//var_dump($coordinate);
        if ($quantity) {
            //true so make a place
            $this->makeQuantity($this->quantity);
        }

//        $this->coordinate = $coordinate;

    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        //return $this->available;
    }



    function addQuantity() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    public function makeWeb()
    {
        $test_message = "<b>QUANTITY " . $this->quantity . "</b>" . '<br>';

        if (!isset($this->refreshed_at)) {
            $test_message .= "<br>Thing just happened.";
        } else {
            $refreshed_at = $this->refreshed_at;

            $test_message .= "<p>";
            $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
            $test_message .= "<br>Thing happened about ". $ago . " ago.";
        }
        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report['web'] = $test_message;


    }


    function makeTXT()
    {
        if (!isset($this->quantities)) {$this->getQuantities();}

        if (!isset($this->quantity)) {$txt = "Not here";} else {

            $txt = 'These are QUANTITYs for RAILWAY ' . $this->last_quantity_variable->nuuid . '. ';
        }
        $txt .= "\n";
        $txt .= "\n";


        $txt .= " " . str_pad("QUANTITY", 19, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("REFRESHED AT", 25, " ", STR_PAD_RIGHT);

        $txt .= "\n";
        $txt .= "\n";

        // Places must have both a name and a code.  Otherwise it's not a place.
        foreach ($this->quantities as $key=>$quantity) {

            if (isset($quantity['refreshed_at'])) {

                $t = $quantity['quantity'];
                $txt .= " " . "  " .str_pad($t, 15, " ", STR_PAD_LEFT);

                $txt .= " " . "  " .str_pad(strtoupper($quantity['refreshed_at']), 25, " ", STR_PAD_RIGHT);
            }
            $txt .= "\n";

        }

        $txt .= "\n";
        $txt .= "Last place " . $this->last_quantity . "\n";
        $txt .= "Now at " . $this->quantity;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    // String to array
    function arrayQuantity($input) 
    {

        //if (is_array($input)) {echo "meep";exit();}

        $quantities = $this->extractQuantities($input);

        $quantity_array = true;

//prod
//var_dump($coordinates);
//        if (is_array($coordinates)) {

//        if (count($coordinates) == 1) {$coordinate_array = $coordinates[0];}
//        }

        if ( (is_array($quantities)) and (count($quantities) == 1)) {
            $quantity_array = $quantities[0];
        }


//exit();

        return $quantity_array;

    }

    function stringQuantity($quantity = null)
    {

       if ($quantity == null) {$quantity = $this->quantity;}

//        if (!is_array($quantity)) {$this->quantity_string = true; return $this->quantity_string;}
        if (is_array($quantity)) {$this->quantity_string = true; return $this->quantity_string;}



      $this->quantity_string = "" . $quantity . " units ";
        return $this->quantity_string;
    }

    private function makeSMS()
    {
        $this->inject = null;
        $s = $this->inject;
//echo "makesms";
//echo implode(" ", $this->coordinate);
//echo "\n";
$string_quantity = $this->stringQuantity($this->quantity);
//echo $string_coordinate;


        $sms = "QUANTITY " . $string_quantity;

        if ((!empty($this->inject))) {
            $sms .= " | " . $s;
        } 

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }

	private function Respond()
    {
		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "quantity";


		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;



// Get available for place.  This would be an available agent.
//$available = $this->thing->human_time($this->available);

        // Allow for indexing.
        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;//
        }

        $this->makeSMS();
        $this->makeWeb();

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>headcode state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;

    	$this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

        $this->thing_report['help'] = 'This is a Quantity.';

		return;
	}

    function isData($variable) {
        if (
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {
            return true;

        } else {
            return false;
        }
    }

    function lastQuantity()
    {
        $this->last_quantity_variable = new Variables($this->thing, "variables quantity " . $this->from);
       // $this->last_coordinate = $this->last_coordinate_variable->getVariable('coordinate');

        // Get a textual representation of a coordinate
        $quantity = $this->last_quantity_variable->getVariable("quantity");

        // Turn the text into an array
//        $this->last_quantity = $this->arrayQuantity($quantity);
        $this->last_quantity = $quantity;  
        // This doesn't work
        $this->last_refreshed_at = $this->last_quantity_variable->getVariable('refreshed_at');

        return;

        // So do it the hard way

        if (!isset($this->quantities)) {$this->getQuantities();}
$last_quantity = $this->quantities[0];

$this->last_quantity = $last_quantity['quantity'];
$this->last_refreshed_at = $last_coordinate['refreshed_at'];
var_dump($last_quantity['quantity']);
//        foreach(array_reverse($this->coordinates) as $key=>$coordinate) {

//            if ($coordinate['coordinate'] == $this->last_coordinate) {
//                $this->last_refreshed_at = $coordinate['refreshed_at'];
//                break;
//            }


//        }
//exit();


    }

    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;

        switch (true) {
            case ($this->agent_input == "extract"):
                //$input = strtolower($this->from . " " . $this->subject);
                $input = strtolower($this->subject);

                break;
            case ($this->agent_input != null):
                $input = strtolower($this->agent_input);
                break;
            case (true):
                // What is going to be found in from?
                // Allows place name extraction from email address.
                // Allows place code extraction from "from"
                // Internet of Things.  Sometimes all you have is the Thing's address.
                // And that's a Place.
                //$input = strtolower($this->from . " " . $this->subject);
                $input = strtolower($this->subject);

        }

        // Would normally just use a haystack.
        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
		// $haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractQuantity($input);


        if ($this->agent_input == "extract") {$this->response = "Extracted quantity(s).";return;}
        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {
            if ($input == 'quantity') {

                $this->lastQuantity();

                $this->quantity = $this->last_quantity;
                $this->refreshed_at = $this->last_refreshed_at;

                $this->response = "Last quantity retrieved.";
                return;
            }
        }

        foreach ($pieces as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'next':
                            $this->thing->log("read subject next quantity");
                            $this->nextQuantity();
                            break;

                        case 'drop':
                            //$this->thing->log("read subject nextheadcode");
                            $this->dropQuantity();
                            break;
                        case 'make':
                        case 'new':
                        case 'quantity':
                        case 'create':
                        case 'add':


//                            if (is_array($this->quantity)) {
//                                $this->response = 'Asserted quantity and found ' . $this->stringQuantity($this->quantity) .".";
//                                return;
//                            }

                            if (is_numeric($this->quantity)) {
                                $this->response = 'Asserted quantity and found ' . $this->stringQuantity($this->quantity) .".";
                                return;
                            }


                            if ($this->quantity) { // Coordinate not provided in string
                                $this->lastQuantity();
                                $this->quantity = $this->last_quantity;
                                $this->refreshed_at = $this->last_refreshed_at;
                                $this->response = 'Asserted quantity and found last ' . $this->stringQuantity($this->quantity) .".";
                                return;
                            }

                            $this->assertQuantity(strtolower($input));

                            if (empty($this->quantity)) {$this->quantity = "X";}

                            $this->response = 'Asserted Quantity and found ' . $this->stringQuantity($this->quantity) .".";

                            return;
                            break;

                        default:

                    }
                }
            }

        }


        // If at this point we get false/false, then the default Place has not been created.
//        if ( ($this->place_code == false) and ($this->place_name == false) ) {
//            $this->makePlace($this->default_place_code, $this->default_place_name);
//        }


        if ($this->quantity != null) {
            $this->getQuantity($this->quantity);
            $this->thing->log($this->agent_prefix . 'using extracted quantity ' . $this->stringQuantity() . ".","INFORMATION");
            $this->response = $this->stringQuantity() . " used to retrieve a Quantity.";

            return;
        }

        if ($this->last_quantity != null) {
            $this->getQuantity($this->last_quantity);
            $this->thing->log($this->agent_prefix . 'using extracted last_quantity ' . $this->last_quantity . ".","INFORMATION");
            $this->response = "Last quantity " . $this->last_quantity . " used to retrieve a Quantity.";

            return;
        }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

        $quantity = strtolower($this->subject);

        if ( !$this->getQuantity(strtolower($quantity)) ){
            // Place was found
            // And loaded
            $this->response = $quantity . " used to retrieve a Quantity.";

            return;
        }

        //    function makePlace($place_code = null, $place_name = null) {
        $this->makeQuantity($quantity);
//        $this->thing->log($this->agent_prefix . 'using default_quantity ' . implode(" ",$this->default_quantity) . ".","INFORMATION");

        $this->response = "Made a Quantity called " . $quantity . ".";
        return;


        if (($this->isData($this->quantity)) or ($this->isData($this->quantity)) ) {
            $this->set();
            return;
        }

		return false;

	}
/*
	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}
*/
}
