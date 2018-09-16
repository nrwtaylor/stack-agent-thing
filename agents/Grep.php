<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);


class Grep
{
	function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->thing_report['thing'] = $thing;


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        $this->uuid = $thing->uuid; 
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		$this->node_list = array("start","grep"=>array("grep"));

		$this->thing->log( 'running on Thing ' .  $this->uuid .  ' ' );

        $this->readSubject();

        // Move this out

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("grep", "refreshed_at") );

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }

        $this->getGreps();

		$this->Respond();

		return;
	}

    function isGrep($string)
    {
        if (strpos(strtolower($string), 'grep') !== false) {
            return true;
        }
        return false;
    }

    function getGreps()
    {
        $text = $this->grep_phrase;

        // Search how?

        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch($text, 3);
        $agent_things = $thing_report['things'];

        // Searches
        //$this->thing->db->setUser($this->from);
        $thing_report = $this->thing->db->userSearch($text);
        $user_things = $thing_report['thing']; // Fix this discrepancy thing vs things


        // Or this.
        $thing_report = $this->thing->db->variableSearch(null, $text);
        $variable_things = $thing_report['things'];

        $this->things = array_merge($agent_things, $user_things, $variable_things);

        $this->sms_message = "";
        $reset = false;

        $this->greps = array();
        foreach($this->things as $thing) {
            $task = $thing['task'];
            $created_at = $thing['created_at'];
            $thing_string= $created_at . ' "'. $task .'"';

            if ($this->isGrep($task)) {continue;}
            // echo $thing_string . "\n";
            $this->greps[] = $thing;
        }

    }

    public function makeSms()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/events';
        $count = count($this->greps);

        if (!isset($this->greps[0])) {
            $sms = "GREP | ". $this->response;
        } else {
                $sms = "GREP " . $count;
                $sms .= " | " . $this->greps[0]['task'];
                $sms .= " | " . $this->response;
            }
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function grepString($thing)
    {
        $string = $thing['created_at'] ." " .$thing['task'];
        return $string;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/grep';

        $html = "<b>GREP</b>";

        $html .= "<br>Grep says , '";
        $html .= $this->sms_message. "'";

        $html .= "<p>";
        foreach($this->greps as $grep) {
            $html .= "<p>". $this->grepString($grep);
        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

    function makeTxt()
    {
        $txt = "grep for " . $this->grep_phrase . "\n";
        foreach($this->things as $thing) {
            $txt .= "created " . $thing['created_at']  . "";
            //$txt .= '"' . $thing['task'] .'". ';
            $txt .= " " . $thing['task']  . "\n";
        }

        $this->thing_report['txt'] = $txt;
    }

	public function Respond()
    {
		// Develop the various messages for each channel.
		$this->thing->flagGreen(); 

        $this->makeSms();

		$this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
        }

        $this->makeWeb();
        $this->makeTxt();

		return $this->thing_report;
	}

	public function readSubject()
    {
        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "grep is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("grep is")); 
        } elseif (($pos = strpos(strtolower($input), "grep")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("grep")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->grep_phrase = $this->search_words;

            $this->response = 'Grepped "' . $this->search_words . '"';
            return false;
        }

        $this->grep_phrase = $this->search_words;
	}

}

?>
