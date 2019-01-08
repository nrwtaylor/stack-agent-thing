<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Carpool
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;
        $this->agent_name = "carpool";
        $this->keyword = "carpool";

        $this->agent_prefix = 'Agent "Carpool" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->node_list = array("carpool"=>array("on"=>array("off")));

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/carpool';



// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

        $this->variables_thing = new Variables($this->thing, "variables carpool " . $this->from);

        $this->get(); // Updates $this->elapsed_time;

		$this->thing->log('Agent "Carpool" running on Thing ' . $this->thing->nuuid . ".");
		$this->thing->log('Agent "Carpool" received this Thing, "' . $this->subject .  '".') ;

		$this->readSubject();
		$this->respond();

		//$this->thing->log( '<pre> Agent "Mordok" completed and is showing a ' . $this->state . ' flag.</pre>');


        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . $milliseconds . 'ms.' );


        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;

		return;

		}


    function set($requested_state = null)
    {
 
        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

//        $this->thing->json->setField("variables");
//        $this->thing->json->writeVariable( array($this->keyword, "state"), $requested_state );
//        $this->thing->json->writeVariable( array($this->keyword, "refreshed_at"), $this->current_time );

        $this->variables_thing->setVariable("state", $requested_state);
        $this->variables_thing->setVariable("refreshed_at", $this->current_time);

      

        $this->thing->choice->Choose($requested_state);


        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;


//$this->thing->log("Result of choice->load() ". $this->thing->choice->load($this->keyword));


        return;
    }


    function get()
    {
        //$this->variables_thing->getVariables();

        if (!isset($this->requested_state)) {
            if (!isset($this->state)) {
                $this->requested_state = "X";
            } else {
                $this->requested_state = $this->state;
            }
        }


        $this->previous_state = $this->variables_thing->getVariable("state")  ;
        $this->refreshed_at = $this->variables_thing->getVariables("refreshed_at");

        //var_dump($this->variables_thing);
        //exit();
        //$this->previous_state = $this->variables_thing->choice->load($this->keyword);
        //exit();
        //$this->previous_state = $this->thing->choice->current_node;

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->previous_state);
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;

        // Bring in stuff
        $this->getPlace();
        $this->getFlag();
        $this->getHeadcode();



        return;
    }

    function read()
    {
        return $this->state;
    }

    function selectChoice($choice = null)
    {

        if ($choice == null) {
            return $this->state;

    //        $choice = 'off'; // Fail off.
        }


        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".');

        $this->set($choice);


        //$this->thing->log('Agent "' . ucwords($this->keyword) . '" choice selected was "' . $choice . '".');

        return $this->state;
    }


	private function respond()
    {
        $this->makeCarpool();
		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = $this->keyword;

		//$choices = $this->variables_thing->thing->choice->makeLinks($this->state);
		//$this->thing_report['choices'] = $choices;

        $this->makeChoices();

        $this->makeSMS();

		$this->thing_report['email'] = $this->sms_message;

        $this->makeMessage();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }
        $this->makeWeb();

        $this->thing_report['help'] = 'This is your Carpool. Try CARPOOL ON. CARPOOL OFF. PLACE IS GILMORE. FLAG IS RAINBOW.';

		return;


	}

    function makeChoices ()
    {
        $this->node_list = array("carpool"=>array("privacy","warranty","github"));


       $this->thing->choice->Create($this->agent_name, $this->node_list, "carpool");

       $this->choices = $this->thing->choice->makeLinks('carpool');

        $this->thing_report['choices'] = $this->choices;


    }

    function makeMessage()
    {

        switch($this->state) 
        {
            case 'off':
                $m = "The carpool is not running";
                break;
            case 'on':
                if (!isset($this->place->place_name)) {$place = "NOT SET";} else {$place =  strtoupper($this->place->place_name);}

                $m = "The carpool is at " . strtoupper($this->place->place_name);
                $m .= " showing a " . strtoupper($this->flag->state) . " flag.";
                $m .= " Carpool " . strtoupper($this->headcode->head_code) . " is running.";
                break;
            default:
                $m = "The carpool is not running.";
        }

        $this->message = $m;
        $this->thing_report['message'] = $m;

    }

    function getPlace()
    {
        $this->place = new Place($this->thing,"place");

        if ((!isset($this->place->place_name)) or ($this->place->place_name == false)) {$this->place->place_name = "X";}

//        echo $this->place->place_name ."\n";

    }

    function getHeadcode()
    {
        $this->headcode = new Headcode($this->thing, "headcode");
        if (!isset($this->headcode->head_code)) {$this->headcode->head_code = "X";}
    }

    function getFlag()
    {
        $this->flag = new Flag($this->thing, "flag");

        if ((!isset($this->flag->state)) or ($this->flag->state == false)) {$this->flag->state = "X";}

        $this->thing->log( $this->agent_prefix .' got a flag ' . $this->flag->state .  '.' );

    }

    function getIdentity()
    {
        $this->identity = new Identity($this->thing, "identity");
//var_dump($this->identity->state);
//exit();
        if ((!isset($this->identity->state)) or ($this->identity->state == false)) {$this->identity->state = "off";}

        $this->thing->log( $this->agent_prefix .' got identity state ' . $this->identity->state .  '.' );

    }



    function makeSMS()
    {


        if ($this->state == false) {
            $text = "X";
        } else {
            $text = $this->state;
        }
        $sms_message = "CARPOOL IS " . strtoupper($text);

        switch($this->state) 
        {
            case 'off':
                $sms_message .= " | The carpool is not running.";
                break;
            case 'on':

                if ($this->flag->state == false) {$flag_state = "X";} else {$flag_state = $this->flag->state;}
                $sms_message .= " | flag " . strtoupper($flag_state);
                $sms_message .= " | headcode " . strtoupper($this->headcode->head_code);

                if ($this->place->place_name == false) {$place_name = "X";} else {$place_name = $this->place->place_name;}
                $sms_message .= " | place " . strtoupper($place_name);

                $sms_message .= " | link " . $this->link;


                break;
            default:
                $sms_message .= " | The carpool is not running.";
        }

/*
        if ($this->state == "on") {

            if ($this->flag->state == false) {$flag_state = "X";} else {$flag_state = $this->flag->state;}
            $sms_message .= " | flag " . strtoupper($flag_state);
            $sms_message .= " | headcode " . strtoupper($this->headcode->head_code);

            if ($this->place->place_name == false) {$place_name = "X";} else {$place_name = $this->place->place_name;}
            $sms_message .= " | place " . strtoupper($place_name);

        }
*/

        $sms_message .= " | nuuid " . substr($this->variables_thing->variables_thing->uuid,0,4); 

//var_dump($this->state);
/*
        if (($text == "off") or ($text =="X")) {
            $sms_message .= " | TEXT HELP";
        } else {
            $sms_message .= $this->link . " | TEXT HELP"; 
        }
*/
            $sms_message .= " | TEXT HELP"; 




        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

    function makeWeb()
    {

        $head = '<p class="description">';
        $foot = '</p>';


        if ($this->state == "on") {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';


        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/carpool.txt';

        $web = "This is an experimental carpool tool hosted by Stackr Interactive - a small AI and Games start-up in Burnaby, British Columbia. ";

        $web .= "<br><br>";

        if ($this->identity->state == "on") {
            $web .= "You can talk directly to the carpool with ". $this->channel->channel_name . " ".  $this->from . ".<br><br>";
        } else {
            $web .= "The carpool has not shared contact details.<br><br>";
        }


        $web .= $this->place->html_image;
        //$web .= "</a>";
        $web .= "<br>";

        $web .= "<br>";

        $web .= "The carpool is going to be arriving at ".  strtoupper($this->place->place_name) . " ";

        $web .= " at ".  str_pad($this->clocktime->hour,2,"0",STR_PAD_LEFT). ":" . str_pad($this->clocktime->minute,2,"0",STR_PAD_LEFT) . ".<br>";


        $web .= "The driver says there are ".  $this->quantity->quantity . " seats available.<br>";

        $web .= "The driver is showing a ". $this->flag->state . " flag.<br><br>";

        $web .= "The common destination is " . $this->destination->destination_name . ".";


 //       if ($this->identity->state == "on") {
 //           $web .= "The carpool driver has shared their " . $this->channel->channel_name . " contact as ". $this->from . ".<br><br>";
 //       } else {
 //           $web .= "The carpool driver has not shared an " . $this->channel->channel_name . " contact.<br><br>";
 //       }

//        $web .= '<a href="' . $this->flag->link . '">';
        $web .= $this->flag->html_image;
//        $web .= "</a>";

        $web .= "<br><br>";

        $web .= "The link you have been shared tells you about this carpool. ";

        $web .= "Shared with you by the car pool driver and/or guests. ";

        $web .= "Users of this service are guests of the individual carpool driver.";

        $web .= "<br><br>";

        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';


$refreshed_at = max(strtotime($this->flag->refreshed_at), strtotime($this->place->refreshed_at)); 

$refreshed_at = max(strtotime($this->flag->refreshed_at), strtotime($this->place->refreshed_at), 
    strtotime($this->quantity->refreshed_at));
//    strtotime($this->clocktime->refreshed_at)); 

$refreshed_at = max(strtotime($this->flag->refreshed_at), strtotime($this->place->refreshed_at), 
    strtotime($this->quantity->refreshed_at),
    strtotime($this->clocktime->refreshed_at)); 


        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - $refreshed_at );
  //      if ($refreshed_at == null) {
  //          $web .= "Place and/or Flag have not yet been asserted.";
  //      } else {
            $web .= "Last heard from this carpool about ". $ago . " ago.";
  //      }

        $web .= "<br><br>";

        $web .= '<b>' . ucwords($this->agent_name) . ' Information</b><br>';

        //$this->warranty = new Warranty($this->thing, "warranty");
        //$web .= $this->warranty->message. "<br>";
        //$web .= "<br>";


    //    $web .= "<br><br>";

        $web .= "The link you have been shared gives you the carpool's state. ";

        $web .= "Shared with you by the car pool driver. ";

        $web .= "Users of this service are guests of the individual carpool driver.";

        $web .= "<br><br>";

        $web .= "The Province of British Columbia says that traveling together in a single vehicle can save you both money, and is also better for the environment.";
        $web .= '<a href="https://www2.gov.bc.ca/gov/content/family-social-supports/seniors/transportation/carpooling-and-car-sharing">';
   
         $web .= "https://www2.gov.bc.ca/gov/content/family-social-supports/seniors/transportation/carpooling-and-car-sharing";

        $web .= "</a>";

        $web .= "<br><br>";

       $web .= "A car pool vehicle is exempt from the provisions of the Passenger Transportation Act. ";
       $web .= "Section 2 (below) of the Transportation Act describes a car pool vehicle.<br> ";

        $web .= '<a href="http://www.bclaws.ca/Recon/document/ID/freeside/266_2004">';
   
         $web .= "http://www.bclaws.ca/Recon/document/ID/freeside/266_2004";

        $web .= "</a>";

        $web .= "<br><br>";

        $web .= "
(2) A motor vehicle that can accommodate a driver and not more than 11 passengers is a car pool vehicle on any day if<br>
(a) on that day, the motor vehicle is used for no purpose other than to transport passengers on one return trip between<br>
(i) the residences of any or all of the driver and the passengers, and<br>
(ii) the respective places of employment of the driver and passengers, or a common destination, and<br>
(b) neither the driver nor the operator receives any compensation for that transportation other than contributions for
operating costs, which contributions do not, in the aggregate, exceed the operating costs that are attributable to the return trip referred to in paragraph (a).";



        }

        if ($this->state != "on") {
            $web = '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';

            //if (($this->state == null) or ($this->state == false)) {$t = "NOT SET";} else {$t = strtoupper($this->state);}

            //$web .= "Beacon is " .  $t;
            //$web .= "<br>";

            $web .= $this->message;
            $web .= "<br>";
        }

$this->thing_report['web'] = $head . $web.  $foot;
return;


$web .= $this->sms_message;
        $web .= "<br>";


/*        
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/place.txt';
        $web .= '<a href="' . $link . '">place.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/place.log';
        $web .= '<a href="' . $link . '">place.log</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/'. $this->place_name;
        $web .= '<a href="' . $link . '">'. $this->place_name. '</a>';
*/


        $web .= "<br>";



        $web .= "<br>";



        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->refreshed_at) );
        $web .= "Last asserted about ". $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    function makeCarpool()
    {

        //$this->identity = new Identity($this->thing, "identity");

        //$this->flag = new Flag($this->thing, "flag");
        $this->flag->makePNG();

        //$this->headcode = new Headcode($this->thing, "headcode");
        $this->headcode->makePNG();

        //$this->place = new Place($this->thing, "place");
        $this->place->makePNG();

    }

    function assertCarpool($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "carpool is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("carpool is")); 
        } elseif (($pos = strpos(strtolower($input), "carpool")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("carpool")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $place = $this->getPlace($filtered_input);
        if ($place) {
            //true so make a place
            $this->makePlace(null, $filtered_input);
        }
    }


    public function readSubject() 
    {
        $this->response = null;

        $keywords = array('off', 'on');

        $input = strtolower($this->subject);

        // Because the identity is likely to be in the from address
		$haystack = $this->agent_input . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;


//
$this->quantity = new Quantity($this->thing, "quantity");

$this->place = new Place($this->thing, "place");

$this->destination = new Destination($this->thing, "destination");

$this->headcode = new Headcode($this->thing, "headcode");
//var_dump($this->headcode->head_code);

$this->getIdentity();

$this->clocktime = new Clocktime($this->thing, "clocktime");
//var_dump($this->clocktime->hour);
//var_dump($this->clocktime->minute);

$this->channel = new Channel($this->thing, "channel");


$this->context = new Context($this->thing, "group");


        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == $this->keyword) {
                $this->read();
                return;
            }
            //return "Request not understood";
            // Drop through to piece scanner
        }


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) 
                    {
                        case 'off':
                            $this->thing->log('switch off');
                            $this->selectChoice('off');
                            return;
                        case 'on':
                            $this->selectChoice('on');
                            return;
                        case 'next':
                        default:
                    }

                }
            }

        }




    if ($this->carpool_code != null) {
        $this->getPlace($this->place->place_code);
        $this->thing->log($this->agent_prefix . 'using extracted place_code ' . $this->place->place_code . ".","INFORMATION");
        $this->response = $this->place->place_code . " used to retrieve a Place.";

        return;
    }

    if ($this->carpool_name != null) {

        $this->getCarpool($this->carpool_name);

        $this->thing->log($this->agent_prefix . 'using extracted place_name ' . $this->place->place_name . ".","INFORMATION");
        $this->response = strtoupper($this->place->place_name) . " retrieved.";
$this->assertCarpool($this->place->place_name);
        return;
    }





    if ($this->last_place_code != null) {
        $this->getPlace($this->last_place_code);
        $this->thing->log($this->agent_prefix . 'using extracted last_place_code ' . $this->last_place_code . ".","INFORMATION");
        $this->response = "Last place " . $this->last_place_code . " used to retrieve a Place.";

        return;
    }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

$place = strtolower($this->subject);

if ( !$this->getPlace(strtolower($place)) ){
    // Place was found
    // And loaded
    $this->response = $place . " used to retrieve a Place.";

    return;
}


        $this->makePlace(null, $place);
        $this->thing->log($this->agent_prefix . 'using default_place_code ' . $this->default_place_code . ".","INFORMATION");

        $this->response = "Made a Place called " . $place . ".";
        return;

        if (($this->isData($this->place_name)) or ($this->isData($this->place_code)) ) {
            $this->set();
            return;
        }




        // If all else fails try the discriminator.

        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch($this->requested_state)
        {
            case 'on':
                $this->selectChoice('on');
                return;
            case 'off':
                $this->selectChoice('off');
                return;
        }

        $this->read();

        return "Message not understood";

	}

    function discriminateInput($input, $discriminators = null)
    {


                //$input = "optout opt-out opt-out";

                if ($discriminators == null) {
                        $discriminators = array('on', 'off');
                }       



                $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

                if (count($discriminators) > 4) {
                        $minimum_discrimination = $default_discriminator_thresholds[4];
                } else {
                        $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
                }



                $aliases = array();

                $aliases['on'] = array('red','on');
                $aliases['off'] = array('green', 'off');
                //$aliases['reset'] = array('rst','reset','rest');
                //$aliases['lap'] = array('lap','laps','lp');



                $words = explode(" ", $input);

                $count = array();

                $total_count = 0;
                // Set counts to 1.  Bayes thing...     
                foreach ($discriminators as $discriminator) {
                        $count[$discriminator] = 1;

                       $total_count = $total_count + 1;
                }
                // ...and the total count.



                foreach ($words as $word) {

                        foreach ($discriminators as $discriminator) {

                                if ($word == $discriminator) {
                                        $count[$discriminator] = $count[$discriminator] + 1;
                                        $total_count = $total_count + 1;
                                                //echo "sum";
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                                //echo "sum";
                                        }
                                }
                        }

                }

                $this->thing->log('Agent "Flag" has a total count of ' . $total_count . '.');
                // Set total sum of all values to 1.

                $normalized = array();
                foreach ($discriminators as $discriminator) {
                        $normalized[$discriminator] = $count[$discriminator] / $total_count;            
                }


                // Is there good discrimination
                arsort($normalized);


                // Now see what the delta is between position 0 and 1

                foreach ($normalized as $key=>$value) {
                        //echo $key, $value;

          if ( isset($max) ) {$delta = $max-$value; break;}
                        if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
                }


//                        echo '<pre> Agent "Usermanager" normalized discrimators "';print_r($normalized);echo'"</pre>';


                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}

?>
