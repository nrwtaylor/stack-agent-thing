<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Oxforddictionaries 
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();

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

        $this->keywords = array('oxford','dictionary','dictionaries','synonyms', 'antonyms','english','spanish','german');

        $this->current_time = $this->thing->json->time();


        $this->application_id = $this->thing->container['api']['oxford_dictionaries']['application_id'];
        $this->application_key = $this->thing->container['api']['oxford_dictionaries']['application_key']; 

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "oxford_dictionaties" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi("dictionary");

		$this->respond();

        $this->thing_report['log'] = $this->thing->log;

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['response'] = $this->response;

		return;

	}

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

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

    function getApi($type = "dictionary")
    {
        if ($type == null) {$type = "dictionary";}

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: application/json\r\n" .
                    "app_id: " . $this->application_id . "\r\n" .  // check function.stream-context-create on php.net
                    "app_key: " . $this->application_key . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );

        $context = stream_context_create($options);

        $data_source = "https://od-api.oxforddictionaries.com:443/api/v1/entries/en/". $keywords;

//get /entries/{source_lang}/{word_id}/synonyms

        $data = @file_get_contents($data_source, false, $context);
        if ($data == false) {
            $this->response = "Could not ask Oxford Dictionaries.";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        $definitions = $json_data['results'][0]['lexicalEntries'][0]['entries'][0]['senses'];

        $count = 0;
        foreach ($definitions as $id=>$definition) {
            if (!isset($definition['definitions'][0])) {continue;}
            $this->definitions[] = $definition['definitions'][0];
            $count += 1;
        }

        $this->definitions_count = $count;

        return false;
    }


    function getSynonyms()
    {
        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept: application/json\r\n" .
                    "app_id: " . $this->application_id . "\r\n" .  // check function.stream-contex$
                    "app_key: " . $this->application_key . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );

        $context = stream_context_create($options);
        $data_source = "https://od-api.oxforddictionaries.com:443/api/v1/entries/en/". $keywords . "/synonyms";

        $data = file_get_contents($data_source, false, $context);
        if ($data == false) {
            $this->response = "Could not ask Oxford Dictionaries about synonyms.";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);
$items = $json_data['results'][0]['lexicalEntries'][0]['entries'][0]['senses'];
$synonyms_list = $items[0]['synonyms'];

$count = 0;
foreach($synonyms_list as $key=>$item) {
    //var_dump($item['text']);
    $this->synonyms[] = $item['text'];
            $count += 1;

}

        $this->synonyms_count = $count;

        return false;
    }

    function getAntonyms()
    {
        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept: application/json\r\n" .
                    "app_id: " . $this->application_id . "\r\n" .  // check function.stream-contex$
                    "app_key: " . $this->application_key . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );

        $context = stream_context_create($options);
        $data_source = "https://od-api.oxforddictionaries.com:443/api/v1/entries/en/". $keywords . "/antonyms";

        $data = file_get_contents($data_source, false, $context);
        if ($data == false) {
            $this->response = "Could not ask Oxford Dictionaries about antonyms.";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);
$items = $json_data['results'][0]['lexicalEntries'][0]['entries'][0]['senses'];
//var_dump($items);
//exit();
$antonyms_list = $items[0]['antonyms'];

$count = 0;
foreach($antonyms_list as $key=>$item) {
    //var_dump($item['text']);
    $this->antonyms[] = $item['text'];
            $count += 1;

}

        $this->antonyms_count = $count;

        return false;
    }


    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.oxforddictionaries.com";
        return $this->link;
    }

	private function respond()
    {
		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "oxforddictionaries";

        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->flag = "green";

        $this->makeSms();
        $this->makeMessage();

        $this->makeWeb();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->thingreportOxforddictionaries();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This triggers provides currency prices using the 1forge API.';

		return;
	}

    public function makeWeb()
    {
        $html = "<b>OXFORD DICTIONARIES</b>";
        $html .= "<p><b>Oxford Dictionaries definitions</b>";

        if (!isset($this->events)) {$html .= "<br>No definitions found on Oxford Dictionaries.";} else {
        
            foreach ($this->events as $id=>$event) {

                $event_html = $this->eventString($event);

                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                //        $web .= $this->html_image;
                $html_link .= "oxford dictionaries";
                $html_link .= "</a>";

                $html .= "<br>" . $event_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }

    public function makeSms()
    {
        //$sms = "OXFORD DICTIONARIES";
        $sms = strtoupper($this->search_words);

    if ($this->search_type == "dictionary") {

        switch ($this->definitions_count) {
            case 0:
                $sms .= " | No definitions found.";
                break;
            case 1:
                $sms .= " | " .$this->definitions[0];




                break;
            default:
                foreach($this->definitions as $definition) {
                    $sms .= " / " . $definition;
                }
        }
    }

    if ($this->search_type == "synonyms") {
        switch ($this->synonyms_count) {
            case 0:
                $sms .= " | No synonyms found.";
                break;
            case 1:
                $sms .= " | " .$this->synonyms[0];




                break;
            default:
                foreach($this->synonyms as $synonym) {
                    $sms .= " / " . $synonym;
                }
        }
    }

    if ($this->search_type == "antonyms") {
        switch ($this->antonyms_count) {
            case 0:
                $sms .= " | No antonyms found.";
                break;
            case 1:
                $sms .= " | " .$this->antonyms[0];




                break;
            default:
                foreach($this->antonyms as $antonym) {
                    $sms .= " / " . $antonym;
                }
        }
    }


        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;

    }

    public function makeMessage()
    {
        $message = "Oxford Dictionaries";

        switch ($this->definitions_count) {
            case 0:
                $message .= " did not find any definitions.";
                break;
            case 1:
                $message .= ' found, "' .$this->definitions[0] . '"';
                break;
            default:
                foreach($this->definitions as $definition) {
                    $message .= " / " . $definition;
                }

        }

        $this->message = $message;

    }

    private function thingreportOxforddictionaries()
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

        $this->search_type = "dictionary";

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

            if ($input == 'oxforddictionaries') {
                //$this->search_words = null;
                $this->response = "Asked Oxford Dicionaries about nothing.";
                return;
            }

        }

    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {

   case 'synonyms':

//        $input = $whatIWant;
        if (($pos = strpos(strtolower($input), "synonyms is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("synonyms is")); 
        } elseif (($pos = strpos(strtolower($input), "synonyms")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("synonyms")); 
        }


        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {

            $this->search_words = $filtered_input;
            $this->search_type = "synonyms";
            $this->getSynonyms();

            $this->response = "Asked Oxford Dictionaries about the word " . $this->search_words . ".";
            return false;
        }



        return;
        break;

   case 'antonyms':

//        $input = $whatIWant;
        if (($pos = strpos(strtolower($input), "antonyms is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("antonyms is")); 
        } elseif (($pos = strpos(strtolower($input), "antonyms")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("antonyms")); 
        }


        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {

            $this->search_words = $filtered_input;
            $this->search_type = "antonyms";
            $this->getAntonyms();

            $this->response = "Asked Oxford Dictionaries about the word " . $this->search_words . ".";
            return false;
        }



        return;
        break;


    default:
                                        }

                                }
                        }

                }


        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "oxforddictionaries is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("oxforddictionaries is")); 
        } elseif (($pos = strpos(strtolower($input), "oxforddictionaries")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("oxforddictionaries")); 
        }


        $input = $whatIWant;
        if (($pos = strpos(strtolower($input), "synonyms is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("synonyms is")); 
        } elseif (($pos = strpos(strtolower($input), "synonyms")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("synonyms")); 
        }


        $filtered_input = ltrim(strtolower($whatIWant), " ");

    if ($filtered_input != "") {
        $this->search_words = $filtered_input;
        $this->response = "Asked Oxford Dictionaries about the word " . $this->search_words . ".";
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

