<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Translink {

	public $var = 'hello';


    function __construct(Thing $thing) {
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



		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log('Agent "Translink" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log('Agent "Translink" received this Thing "' . $this->subject .  '".');

//		$this->readSubject(); // No need to read subject 'translink' is pretty clear.
        //$this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');

        $this->readSubject();
//$this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
  		$this->thing_report = $this->respond();
//$this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
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

        function translinkHelp() {

                        $this->sms_message = "TRANSIT";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
                        $this->sms_message .= " | ";
                        $this->sms_message .= 'Text the five-digit stop number for live Translink stop inforation. | For example, "51380". | ';
                        $this->sms_message .= "TEXT <5-digit stop number>";
                return;


        }

    function translinkSyntax() {

        $this->sms_message = "TRANSIT";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Syntax: "51380". | ';
        $this->sms_message .= "TEXT HELP";

        return;
    }


	public function stopTranslink($stop) {
        $split_time = $this->thing->elapsed_runtime();
        //$this->thing->log('Agent "Translink". Start Translink API call. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');


		$this->stop = $stop;
		try {

			$file = 'http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60';

			$web_input = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60');


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $file);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$xmldata = curl_exec($ch);
			curl_close($ch);

			$web_input = $xmldata;

			$this->error = "";

		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->error = $e;
			$web_input = false;
            $this->sms_message = "Request not understood: " . $this->error;

			return "Request not understood";
		}

		//echo $web_input;


                $xml = simplexml_load_string($web_input);  
                $t = $xml->NextBus;

                //var_dump($xml);
                $json_data = json_encode($t,true);
                //echo $json_data;

                $response = null;

                foreach($t as $item) {
  $response .= '<li>' . $item->Schedules->Schedule->ExpectedLeaveTime . ' ' . $item->RouteNo . ' ' . $item->RouteName . ' ' . '> ' . $item->Schedules->Schedule->Destination . '</li>';
                }

                $message = "Thank you for your request for stop " . $stop .".  The next buses are: <p><ul>" . ucwords(strtolower($response)) . '</ul>';
		$message .= "";
		$message .= "Source: Translink real-time data feed.";


// Hacky here to be refactored.
// Generate a special short SMS message

$this->sms_message = "";
$response ="";

                foreach($t as $item) {
 // $response .=  $item->Schedules->Schedule->ExpectedLeaveTime . ' ' . $item->RouteNo . '> ' . $item->Schedules->Schedule->Destination . ' | ';

  $response .=  $item->RouteNo . ' ' . $item->Schedules->Schedule->ExpectedLeaveTime . ' > ' . $item->Schedules->Schedule->Destination . ' | ';

                }



                	$this->sms_message = "NEXT BUS";
			if (count($t) > 1) {$this->sms_message .= "ES";}

			$this->sms_message .= " | ";


			// Sometimes Translink return 
			// a date in the time string.  Remove it.

			$input = $response;
			//$input = "Current from 2014-10-10 to 2015/05/23 and 2001.02.10";
			$output = preg_replace('/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/', '', $input);

			//echo $output;

			if (count($t) == 0) {
				$this->sms_message .= "No information returned for stop " . $this->stop . ' | ';
			} else {
				$this->sms_message .= ucwords(strtolower($output))  ;
			}

            $this->sms_message .= "Source: Translink | ";

			$this->sms_message .= "TEXT ?";

        $this->thing->log('Agent "Translink". Translink API call took ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.');


		return $message;
	}



        public function busTranslink($bus_id) {

                try {

                        $file = 'http://api.translink.ca/rttiapi/v1/buses/' . $bus_id . '?apikey=' . $this->api_key;

//http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60';

                        $web_input = file_get_contents($file);


                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $file);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $xmldata = curl_exec($ch);
                        curl_close($ch);

                        $web_input = $xmldata;

                        $this->error = "";

                } catch (Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                        $this->error = $e;
                        $web_input = false;
			return "Bus information not yet supported";
                }

		$message = "Here is some xml information" . $web_input;
		$this->sms_message = "TRANSIT | Bus number service not implemented.";
		$this->message = "A bus number was provided, but the agent cannot yet respond to this.";
                //echo $web_input;
                return $message;
        }





// -----------------------

	private function respond()
    {
        //$this->thing->log('Agent "Translink". Start Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');

		// Thing actions
		$this->thing->flagGreen();

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';




  //              $this->thing_report['email'] = array('to'=>$this->from,
  //                              'from'=>'transit',
  //                              'subject' => $this->subject,
  //                              'message' => $message, 
  //                              'choices' => false);




		// Generate email response.

		$to = $this->thing->from;
		$from = "transit";

		//$message = $this->readSubject();

		//$message = "Thank you for your request.<p><ul>" . ucwords(strtolower($response)) . '</ul>' . $this->error . " <br>";

// This is running at 20s...
//		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
//		$choices = $this->thing->choice->makeLinks('start');
//		$this->thing_report['choices'] = $choices;


		// Need to refactor email to create a preview of the sent email in the $thing_report['email']
		// For now this attempts to send both an email and text.

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


// And then at this point if Mordok is on?
// Run an hour train.
//$thing = new Mordok($this->thing);
//If Mordok is on.  Then allow starting of a train automatically.
//        if (strtolower($thing->state) == "on") {

//            $thing = new Transit($this->thing, "transit " . $this->stop);
//        }

//	$this->thing_report['info'] = 'This is the translink agent responding to a request.';
	    $this->thing_report['help'] = 'Connector to Translink API.';

        //$this->thing->log('Agent "Translink". End Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');


		return $this->thing_report;
	}

	private function nextWord($phrase)
    {


	}

	public function readSubject()
    {

		$this->response = null;

		$keywords = array('stop', 'bus', 'route');

		$input = strtolower($this->subject);

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
