<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Wikipedia 
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = microtime(true);

        $this->agent_input = $agent_input;

        $this->keyword = "mordok";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->agent_prefix = 'Agent "Oxford Dictionaries" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('oxford','dictionary','dictionaries','english','spanish','german');

        $this->current_time = $this->thing->json->time();


        $this->application_id = $this->thing->container['api']['oxford_dictionaries']['application_id'];
        $this->application_key = $this->thing->container['api']['oxford_dictionaries']['application_key']; 

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "oxford_dictionaties" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

//        if ($this->verbosity == false) {$this->verbosity = 2;}


		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi();
//        if ($this->available_events_count > 10) {$this->getApi('date');}


		$this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( 'ran for ' . $milliseconds . 'ms.' );

		$this->thing->log( 'completed.');

        $this->thing_report['log'] = $this->thing->log;

		return;

	}



    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('usermanager', $this->state);

        return;
    }


    function get()
    {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;

        return;
    }





    function getApi($sort_order = null)
    {

        if ($sort_order == null) {$sort_order = "popularity";}
        // http://api.eventful.com/docs/events/search

        //count_only boolean
        //    If count_only is set, an abbreviated version of the output will be returned. Only total_items and search_time elements are included in the result. (optional) 

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        //$keywords = str_replace(" ", "%20%", $keywords);
        $keywords = urlencode($keywords);
//$keywords = "vancouver";
/*
$options = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: application/json\r\n" .
              "" // i.e. An iPad 
  )
);

$context = stream_context_create($options);
*/

      //  $data_source = "https://en.wikipedia.org/api/rest_v1/page/summary/bridge";

        //$titles = "&titles=New_York_Yankees";
        $titles = "&titles=". $keywords;

        $format = "&format=json";

        $rvprop = "&rvprop=timestamp|user|comment|content";
        $rvprop = "";

        $prop ="&prop=revisions";
        $prop = "&prop=extracts";

//if we just want the intro, we can use exintro. Otherwise it shows all sections
        $exintro = "&exintro=1";
        $list = "&list=search";

        //$srsearch = "&srsearch=皮皮果";
        $srsearch = "&srsearch=". $keywords;

       // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $prop . $exintro . $format . $prop . $titles . $rvprop;

//$data_source = "http://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=皮皮果&utf8=&format=json";

// Gets a list of matches
$data_source = "http://en.wikipedia.org/w/api.php?action=query" . $list . $srsearch . "&utf8=&format=json";

//$data_source = "http://en.wikipedia.org/w/api.php?action=query" . $srsearch . $prop . $exintro . $format . $rvprop;


//var_dump($data_source);

        $data = file_get_contents($data_source);

//        $data = file_get_contents($data_source, false, $context);

        if ($data == false) {
            $this->response = "Could not ask Wikipedia.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

//var_dump($json_data);
//exit();

//if (!isset($json_data['query']['pages']['extract'])) {echo "no extract found";$pages = false;$text = "Nothing found.";} else {

if (!isset($json_data['query']['search'][0]['snippet'])) {

    $this->text = "Wikipedia did not find anything.";
    return true;

}

$snippet = strip_tags($json_data['query']['search'][0]['snippet']);
$this->text = $snippet;
return false;


    $pages = $json_data['query']['pages'];

    foreach($pages as $id=>$page) {
        $text = strip_tags($page['extract']);
        break;
    }
//}
$filtered_text = trim($text);
        $this->text = $filtered_text;
        return false;

    }


    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref; 
        return $this->link;
    }


    // Read and figure out what it is.

    public function makeEvent()
    {



    // Need to check whether the events exists...
    // This can be post response.   


    // Load as new event things onto stack
    $thing = new Thing(null);
//    $thing->Create("eventful@stackr.ca","events", "event is eventful #" . $eventful_id);
    $thing->Create("wikipedia@stackr.ca","events", "s/ fact wikipedia " . $eventful_id);

    // make sure the right fields are directly given

    new Event($thing, "event is ". $eventful_title);
    new Runat($thing, "runat is ". $run_at);
    new Place($thing, "place is ". $venue_name);
    new Link($thing, "link is " . $link);






    }


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



    function getFlag() 
    {
        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag');
        $this->flag = $this->flag_thing->state; 

        return $this->flag;
    }

    function setFlag($colour) 
    {
        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag '.$colour);
        $this->flag = $this->flag_thing->state; 

        return $this->flag;
    }



	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "eventful";

		//echo "<br>";

		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;



        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');
        //$available = $this->thing->human_time($this->available);


        $this->flag = "green";

        $this->makeSms();
        $this->makeMessage();

        $this->makeWeb();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->thingreportEventful();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This triggers provides currency prices using the 1forge API.';

//        $this->thingreportEventful();

		return;
	}

    public function eventString($event) 
    {
        $event_date = date_parse($event['runat']);

        $month_number = $event_date['month'];
        $month_name = date('F', mktime(0, 0, 0, $month_number, 10)); // March

        $simple_date_text = $month_name . " " . $event_date['day'];
        $event_string = ""  . $simple_date_text;
        $event_string .= " "  . $event['event'];

        $runat = new Runat($this->thing, $event['runat']);

        $event_string .= " "  . $runat->day;
        $event_string .= " "  . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
        $event_string .= ":"  . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);

//var_dump($event['runtime']);

$run_time = new Runtime($this->thing, $event['runtime']);
//var_dump($run_time->minutes);

if ($event['runtime'] != "X") {
    $event_string .= " " . $this->thing->human_time($run_time->minutes);
}
//var_dump($event_string);
//        $event_string .= " "  . $endat->day;
//        $event_string .= " "  . $endat->hour;
//        $event_string .= ":"  . $endat->minute;

                $event_string .= " "  . $event['place'];


        return $event_string;

    }

    public function makeWeb()
    {
        $html = "<b>EVENTFUL</b>";
        $html .= "<p><b>Eventful Events</b>";

        if (!isset($this->events)) {$html .= "<br>No events found on Eventful.";} else {
        
        foreach ($this->events as $id=>$event) {
            //var_dump($event['event']);
            //var_dump($event['runat']);
            //var_dump($event['place']);
            //var_dump($event['link']);
//            $event_html = $event['event'] . " " . $event['runat'] . " " . $event['place'];

            $event_html = $this->eventString($event);

//        $link = $this->web_prefix . 'thing/' . $this->uuid . '/splosh';
        $link = $event['link'];
        $html_link = '<a href="' . $link . '">';
//        $web .= $this->html_image;
        $html_link .= "eventful";
        $html_link .= "</a>";

            $html .= "<br>" . $event_html . " " . $html_link;
            //exit();
        }
        }

        $this->html_message = $html;
    }


//function truncate($string,$length=100,$append="&hellip;") {
function truncate($string,$length=100,$append="[...]") {


  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
} 


    public function makeSms()
    {
        $sms = "WIKIPEDIA | ";

//var_dump($this->truncate($this->text,130));
//exit();

        if ($this->text == "") {$text = "Nothing found.";} else {$text = $this->truncate($this->text,100);}
        $sms .= $text;
       // $sms .= $this->truncate($this->text,130);
        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;

    }

    public function makeMessage()
    {
        if ($this->text == "") {$text = "Nothing found.";} else {$text = $this->truncate($this->text,100);}
        $message = 'Wikipedia said, "';
        $message .= $text;
        $message .= '"';
       // $message .= " | " . $this->response;

        // Really need to refactor this double :/

        $this->message = $message;

    }


    private function thingreportEventful()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function extractNumber($input = null)
    {
        if ($input == null) {$input = $this->subject;}

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key=>$piece) {

            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }

        return $this->number;
    }

    public function readSubject()
    {
//        $this->response = "Asked Eventful about events.";
        $this->response = null;


        $this->num_hits = 0;
        // Extract uuids into

        //$this->number = extractNumber();

        $keywords = $this->keywords;

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {

            $input = strtolower($this->subject);

        }

        $this->input = $input;

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'wikipedia') {
                //$this->search_words = null;
                $this->response = "Asked Wikipedia about everything.";
                return;
            }

        }

    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {

   case 'run':
   //     //$this->thing->log("read subject nextblock");
        $this->runTrain();
        break;

    default:
                                        }

                                }
                        }

                }


        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "wikipedia is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("wikipedia is")); 
        } elseif (($pos = strpos(strtolower($input), "wikipedia")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("wikipedia")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

    if ($filtered_input != "") {
        $this->search_words = $filtered_input;
        $this->response = "Asked Wikipedia about " . $this->search_words . ".";
        return false;
    }



        $this->response = "Message not understood";
		return true;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

       function discriminateInput($input, $discriminators = null) {


                //$input = "optout opt-out opt-out";

                if ($discriminators == null) {
                        $discriminators = array('accept', 'clear');
                }       



                $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

                if (count($discriminators) > 4) {
                        $minimum_discrimination = $default_discriminator_thresholds[4];
                } else {
                        $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
                }



                $aliases = array();

                $aliases['accept'] = array('accept','add','+');
                $aliases['clear'] = array('clear','drop', 'clr', '-');



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

                //echo "total count"; $total_count;
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


                        //echo '<pre> Agent "Train" normalized discrimators "';print_r($normalized);echo'"</pre>';


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

