<?php
namespace Nrwtaylor\StackAgentThing;

class Stackrinteractive extends Agent
{

    function init()
    {
        $this->agent_prefix = 'Agent "Stackr Interactive" ';
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/stackrinteractive/';
        $this->keywords = array();
	}

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("stackrinteractive", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("stackrinteractive", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("stackrinteractive", "reading") );
    }

    public function set()
    {
        $this->thing->json->writeVariable( array("interactive", "reading"), $this->reading );
    }

    function getWord() {
        if (!isset($this->words)) {
            $this->extractWords($this->subject);
        }
        if (count($this->words) == 0) {$this->word = false;return false;}
        $this->word = $this->words[0];
        return $this->word;
    }

    function getStackrinteractive()
    {

        $file = $this->resource_path . 'stackrinteractive.txt';
        $contents = file_get_contents($file);

        $nuggets = explode("s/ ", $contents);

        $agents = new Agents($this->thing, "agents");

        $this->content = array();

        foreach($nuggets as $nugget) {
            foreach($agents->agencies as $id=>$agent) {
                $agent = strtolower($agent);
                $first_word = substr($nugget, 0, mb_strlen($agent) );

                if (strtolower($agent) == strtolower($first_word)) {
                    $this->content[$agent][] = $nugget;
                    continue;
                }

                if (strtolower($agent) == strtolower("make".$first_word)) {
                    $this->content[$agent][] = $nugget;
                    continue;
                }
            }
        }
        return;
    }

	public function Respond()
    {

		$this->cost = 100;

		// Thing stuff
		$this->thing->flagGreen();

        // Make SMS
        $this->makeSMS();
		$this->thing_report['sms'] = $this->sms_message;

        // Make message
		$this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail(); 
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}


    function makeSMS()
    {
        $sms_content = ($this->content['sms'][0]);
        $litany = array();
        $lines = explode("\n", $sms_content);

        foreach ($lines as $line) {
            if ($lines == "sms") {continue;}
            preg_match("/^d[1-9]{0,1}[0-9]{0,15}. (.*)$/m",$line,$m);

            if (isset($m[0])) {
                $roll_text = explode(". ", $m[0])[0];
                $litany[$roll_text][] = $m[1];
            }
        }

        foreach ($litany as $roll_code=>$text) {
            if ($roll_code == "d") {$roll_code = "d1";}

            if (!isset($roll_description)) {$roll_description = $roll_code;} else {
                $roll_description = $roll_description ."+" .$roll_code;
            }
        }

        $roll = new Roll($this->thing, "roll " .$roll_description);

        foreach($roll->result as $index=>$roll) {

            reset($roll);
            $first_key = key($roll);

            if ($roll[$first_key] == 1) {
                if ($first_key == "d1") {$first_key = "d";}
                $sms =  $litany[$first_key][0];
                break;
            }
        }

        $sms = "STACKR INTERACTIVE | " . $sms;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
        return;

    }

    function makeEmail()
    {
        $this->email_message = "STACKR INTERACTIVE";
    }

	public function readSubject()
    {

        if ($this->agent_input == null) {
        $input = strtolower($this->subject);
        } else {
            $input = strtolower($this->agent_input);
        }

        $keywords = array('stackr', 'interactive');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'stackr':

                            $prefix = 'stackr';
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);
                            $this->search_words = $words;

                            $this->getStackrinteractive();

                            // if ($this->word != null) {return;}
                            // return;

                        default:
                    }

                }
            }

        }

        $this->getStackrinteractive();

		$status = true;
    	return $status;
	}

}
