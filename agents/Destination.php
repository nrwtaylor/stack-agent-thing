<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Destination {

	public $var = 'hello';

    // Not finsihdd.  Or really started.
    // This will look up a destination by cross street
    // Find the trips servicng it.


    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
	//function __construct($arguments) {

		//echo $arguments;
		//var_dump($arguments);
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];




		$this->thing = $thing;
		$this->agent_name = 'translink';

                $this->thing_report = array('thing' => $this->thing->thing);

        $this->start_time = $this->thing->elapsed_runtime();


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

		$this->retain_for = 2; // Retain for at least 2 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];



		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log('Agent "Translink" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log('Agent "Translink" received this Thing "' . $this->subject .  '".');

//		$this->readSubject(); // No need to read subject 'translink' is pretty clear.
        $this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');

        $this->readSubject();
$this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
  		$this->thing_report = $this->respond();
$this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
		$this->thing->log('Agent "Translink" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

		return;

		}

        public function nullAction() {

                        $this->thing->json->setField("variables");
                        $names = $this->thing->json->writeVariable( array("character", "action"), 'null' );


                $this->message = "TRANSIT | Request not understood. | TEXT SYNTAX";
                $this->sms_message = "TRANSIT | Request not understood. | TEXT SYNTAX";
                $this->response = true;
                return $this->message;
        }


        function translinkInfo() {


                        $this->sms_message = "TRANSIT";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
                        $this->sms_message .= " | ";
                        $this->sms_message .= 'Live data feed provided through the TransLink Open API. | https://developer.translink.ca/ | ';
                        $this->sms_message .= "TEXT HELP";

                return;


        }

        function destinationHelp() {

                        $this->sms_message = "DESTINATION";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
                        $this->sms_message .= " | ";
                        $this->sms_message .= 'Text a desired transit place.  | For example "Metrotown". Or "Commercial-Broadway".';
                return;


        }


    public function makeDestination($text)
    {
        $columns = explode(",",$text);

        $arr = array("a"=>$columns[0],
                        "stop_number"=>$columns[1],
                        "stop_name"=>$columns[2],
                        "stop_description"=>$columns[3],
                        "stop_latitude"=>$columns[4],
                        "stop_longitude"=>$columns[5],
                        "stop_zone"=>$columns[6] );


        return $arr;

    }

    public function getDestinations()
    {
//            $stop_name = $destination["stop_name"];
//            $stop_number = $destination["stop_number"];


        $this->destination_list = array();
        $places = $this->gtfs->places;
//var_dump($places);
//exit();

        foreach ($places as $stop_desc=>$stops) {
            foreach($stops as $stop) {
            //$station_id = $this->gtfs->idStation($stop_code);
            //$stop = $this->gtfs->getStop($station_id);

            $this->destination_list[] = array("stop_desc"=>$stop['stop_desc'],
                                                "stop_code"=>$stop['stop_code']);

            }

        }

       // var_dump($this->destination_list);
//exit();
    }

    public function getDestination() {

        if (!isset($this->destination_list)) {$this->getDestinations();}

        $this->destination = false;
        if ((is_array($this->destination_list)) and count($this->destination_list) == 1) {$this->destination = $this->destination_list[0];}

    }




// -----------------------

	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();

        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';


		// Generate email response.

		$to = $this->thing->from;
		$from = "destination";

		//$message = $this->readSubject();

		//$message = "Thank you for your request.<p><ul>" . ucwords(strtolower($response)) . '</ul>' . $this->error . " <br>";

// This is running at 20s...
//		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
//		$choices = $this->thing->choice->makeLinks('start');
//		$this->thing_report['choices'] = $choices;


		// Need to refactor email to create a preview of the sent email in the $thing_report['email']
		// For now this attempts to send both an email and text.

        $this->makeSMS();

//$this->sms_message = "hello";
//$this->thing_report['sms'] = "heelo";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

	    $this->thing_report['help'] = 'Connector to Translink API.';

        $this->thing->log('Agent "Translink". End Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');


		return $this->thing_report;


	}

    public function makeSMS()
    {

       if (!isset($this->destination_list)) {$this->getDestinations();}

 //       if ($this->destination == false) {
 //           $message = "DESTINATION | " . $this->destination_count . " matches | " . $this->web_prefix . "thing/" . $this->uuid . "/destination";
 //       } else {

//var_dump($this->destination["stop_name"]);
 //           $stop_name = $this->destination["stop_name"];
 //           $stop_number = $this->destination["stop_number"];

            $message = "DESTINATION";
 //           $message .= " | " . $stop_number;
 //           $message .= " | " . $stop_name;
            $message .= " > " . $this->route_list_text;
//        }

        $this->sms_message = $message;
        $this->thing_report['sms'] = $message;

    }

    public function makeWeb()
    {
        if (!isset($this->destination_list)) {$this->getDestinations();}

//var_dump($this->destination["stop_name"]);
//$stop_name = $this->destination["stop_name"];
//$stop_number = $this->destination["stop_number"];

        $message = "DESTINATION<br>";

        foreach($this->destination_list as $key=> $destination) {
            $stop_name = $destination["stop_desc"];
            $stop_number = $destination["stop_code"];


            $message .= $stop_number . " | " . $stop_name . "<br>";
        }
        $this->web_message = $message;
        $this->thing_report['web'] = $message;

    }



	private function nextWord($phrase)
    {
	}

    function assertDestination($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "destination is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("destination is")); 
        } elseif (($pos = strpos(strtolower($input), "destination")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("destination")); 
        }

        //$filtered_input = ltrim(strtolower($whatIWant), " ");
        //$destination = $this->getDestination($filtered_input);
        //if ($place) {
        //    //true so make a place
        //    $this->makeDestination(null, $filtered_input);
        //}

        $this->destination_input = $whatIWant;
    }

	public function readSubject()
    {

		$this->response = null;

		$keywords = array('destination');

		$input = strtolower($this->subject);

        $input = str_replace("destination " , "", $input);

        $this->gtfs = new Gtfs($this->thing, $input);

//var_dump($this->gtfs->response);
var_dump($this->gtfs->thing_report['sms']);


foreach ($this->gtfs->stations as $station) {

    $station_id =  $station['station_id'];
    $this->gtfs->getRoutes($station_id);

    //$route_text = "";
    foreach ($this->gtfs->routes[$station_id] as $route_id=>$route) {
        $route_list[$route['route_short_name']] = true;
        //$route_text .= $route['route_short_name'] . " ";
    }
    //echo $route_text;
}

$route_text = "";
foreach($route_list as $route_number=>$value) {
    $route_text .= $route_number . " ";
}

$this->route_list_text = $route_text;
$this->response = "Got routes serving " . $input;
return;



		$prior_uuid = null;

		$pieces = explode(" ", strtolower($input));


        if (count($pieces) == 1) {

            $input = $this->subject;

            if (ctype_alpha($this->subject[0]) == true) {
                // Strip out first letter and process remaning 4 or 5 digit number
                $input = substr($input, 1);
	            if (is_numeric($input) and strlen($input) == 4 ) {
                    return $this->busTranslink($input);
                    //return $this->response;
               	}

                                if (is_numeric($input) and strlen($input) == 5 ) {
                                        return $this->busTranslink($input);
                                        //return $this->response;
                                }


                                if (is_numeric($input) and strlen($input) == 6 ) {
                                        return $this->busTranslink($input);
                                        //return $this->response;
                                }



			}

                        if (is_numeric($this->subject) and strlen($input) == 5 ) {
                                return $this->stopTranslink($input);
                                //return $this->response;
                        }

                        if (is_numeric($this->subject) and strlen($input) == 4 ) {
                                return $this->busTranslink($input);
                                //return $this->response;
                        }



//                        return "Request not understood";

        	}


		foreach ($pieces as $key=>$piece) {
			foreach ($keywords as $command) {
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
                        case 'destination':
                            $prefix = 'destination';
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);
                            $this->search_words = $words;

                            $this->getDestinations();

                            //$this->extractWords($words);
//var_dump($words);
                            //$t = $this->findWord('list', $words);
//echo "test";
//var_dump($this->words);
//exit();
            //$this->words = implode(" ", $t);

                            return;




						case 'stop':	

							if ($key + 1 > count($pieces)) {
								//echo "last word is stop";
								$this->stop = false;
								return "Request not understood";
							} else {
								//echo "next word is:";
								//var_dump($pieces[$index+1]);
								$this->stop = $pieces[$key+1];
								$this->response = $this->stopTranslink($this->stop);
								return $this->response;
							}
							break;

						case 'bus':

							//echo 'bus';
							break;

						case 'translink':
							$this->translinkInfo();
							return;

                                                case 'info':
                                                        $this->translinkInfo();
                                                        return;

                                                case 'information':
                                                        $this->translinkInfo();
                                                        return;

                                                case 'help':
                                                        $this->translinkHelp();
                                                        return;

                                                case 'syntax':
                                                        $this->translinkSyntax();
                                                        return;


						default:

							//echo 'default';

					}

				}
			}

		}
		$this->nullAction();
		return "Message not understood";
	}



}




?>
