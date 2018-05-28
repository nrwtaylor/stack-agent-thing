<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Button
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $start_time = $thing->elapsed_runtime();
        $this->start_time = microtime(true);

        $this->agent_name = "button";

        if ($agent_input == null) {$agent_input = "";}
        $this->agent_input = $agent_input;

        $this->keyword = "button";

        $this->agent_prefix = 'Agent "Button" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


        $this->node_list = array("off"=>array("on"=>array("off")));

        $this->current_time = $this->thing->json->time();

        $this->variables_thing = new Variables($this->thing, "variables button " . $this->from);

        $this->get(); // Updates $this->elapsed_time;

		$this->thing->log('Agent "Button" running on Thing ' . $this->thing->nuuid . ".");
		$this->thing->log('Agent "Button" received this Thing, "' . $this->subject .  '".') ;

		$this->readSubject();
        if ($this->agent_input == null) {
		    $this->respond();
        }

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );
        $this->thing_report['log'] = $this->thing->log;

		return;

    }


    function set($requested_state = null)
    {
        // Refactor

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_thing->setVariable("state", $requested_state);
        $this->variables_thing->setVariable("refreshed_at", $this->current_time);

        $this->thing->choice->Choose($requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }


    function get()
    {
        $this->previous_state = $this->variables_thing->getVariable("state")  ;
        $this->refreshed_at = $this->variables_thing->getVariables("refreshed_at");

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->previous_state);

        if (isset($this->requested_state)) {
            $this->thing->choice->Choose($this->requested_state);
            $this->state = $this->thing->choice->current_node;
        } else {
            $this->state = $this->previous_state;
        }

        return;
    }

    function extractButtons($input = null)
    {
        
        $this->buttons = array();
        if ($input == null) {$input = $this->subject;}
        $input = strtolower($input);
        $breaks = count(explode("|",$input)) - 1;

        $words = count(explode(" ",$input));

        if ($words != 1) {
            $input = trim($input);
            $input = str_replace("button", "", $input);
            $input = trim($input);
            $input = str_replace("is","",$input);
            $input = trim($input);
        }
        switch (true) {
            case ( ($words > 1) and ($breaks == 0)):

                $buttons = explode(" ",strtolower($input));
                break;
            case($breaks >= 1):
                $buttons = explode("|",strtolower($input));
                break;
            case (count($words) == 1):
                $buttons = array($input);
                break;
            default:
                $buttons = explode(" ",$input);
        }

        foreach ($buttons as $key=>$button) {

          //  $words = explode(" ",$button);
          //  if ($words[0] == "button") {
          //      $button = str_replace("button", "", $button);
          //  }
            $button=trim($button);
            $this->buttons[] = $button;
            //echo $button . "<br>";

        }

    }

    function read()
    {
// Test corpus
//        $this->subject = "button yes | no";
//        $this->subject = "yes | no";
//        $this->subject = "button is yes";
//        $this->subject = "button is yes no";
//        $this->subject = "button is yes | no";
//        $this->subject = "orange brown";
//        $this->subject = "button";

//        $this->getButtons();
        $this->extractButtons();
        return $this->state;
    }

    function getButtons()
    {
        $this->words = $this->choices['words'];
        $this->links = $this->choices['links'];
        $this->url = $this->choices['url']; // nl version of links array
        $this->link = $this->choices['link'];
        $this->buttons = $this->choices['button'];
    }

    function makeWeb()
    {
        if (!isset($this->words)) {$this->getButtons();}
        $w = "<b>Button Agent</b>";
        $w .= "<br><br>";
        $w .= implode(" ",$this->words);
        $w .= "<br><br>";

        //foreach($this->links as $key=>$link) {
        //    $w .= '<a href = "' . $link . '">' . $link . '</a><br>';
        //}

        $w .= $this->link;

        $w .= "<br><br>Copy-and-paste buttons below into your email.<br>";
        //$w .= htmlentities($this->buttons);
        

        //$w .= nl2br($this->url);

        $this->thing_report['web'] = $w;

    }

    function makeSMS()
    {

        if (!isset($this->words)) {$this->getButtons();}

//        if ($this->state == "inside nest") {
//            $t = "NOT SET";
//} else {
//       $t = $this->state;
//}
        $s = "BUTTONS ARE " . implode(" | ",$this->words);
//        $sms_message .= " | Previous " . strtoupper($this->previous_state);
//        $sms_message .= " | Now " . strtoupper($this->state);
//        $sms_message .= " | Requested " . strtoupper($this->requested_state);
//        $sms_message .= " | Current " . strtoupper($this->base_thing->choice->current_node);
//        $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
//        $sms_message .= " | base nuuid " . strtoupper($this->variables_thing->thing->nuuid);

//        $sms_message .= " | another nuuid " . substr($this->variables_thing->uuid,0,4); 
        $s .= " | nuuid " . substr($this->variables_thing->variables_thing->uuid,0,4); 
        $s .= " | " . $this->web_prefix . "thing/" . $this->uuid . "/button"; 



        //if ($this->state == "off") {
        //    $sms_message .= " | TEXT BUTTON ON";
        //} else {
        //    $sms_message .= " | TEXT ?"; 
        //}
        $this->thing_report['sms'] = $s;
    }

    function makeChoices()
    {
        $this->thing->log( $this->agent_prefix .'started makeChoices. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->node_list = array("button"=>$this->buttons);
        $this->thing->choice->Create($this->agent_name, $this->node_list, "button");

        $this->choices = $this->thing->choice->makeLinks('button');

        $this->thing->log( $this->agent_prefix .'completed makeLinks. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing_report['choices'] = $this->choices;

        $this->thing->log( $this->agent_prefix .'completed create choice. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
    }

    function selectChoice($choice = null)
    {

        if ($choice == null) {
            return $this->state;
        }

        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".');

        $this->set($choice);

        return $this->state;
    }


	private function respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "button";


        $this->makeChoices();
        $this->makeSMS();

		$test_message = 'Here are some buttons made from "' . $this->subject . '".  Your next choices are [ ' . $this->link . '].';
		//$test_message .= '<br>Shift state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->thing_report['sms'];

		//$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;
		//$test_message .= '<br>Requested state: ' . $this->requested_state;

		$this->thing_report['email'] = $test_message;
		$this->thing_report['message'] = $test_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;

        //$this->makeChoices();
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


        $this->makeWeb();

        $this->thing_report['help'] = 'This is a set of buttons.  Buttons tells a stack what you have decided.';

		return;
	}


    public function readSubject() 
    {

        if ($this->agent_input != null) {
            return;
        }

        $input = strtolower($this->subject);
        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == $this->keyword) {
                $this->read();
                return;
            }
            // return "Request not understood";
        }


        $this->read();

        return "Message not understood";
	}

}

?>

