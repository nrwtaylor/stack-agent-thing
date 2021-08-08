<?php
/**
 * Input.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class More extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */


    /**
     * function __construct(Thing $thing, $text = null) {
     */
    function init() {

//$container = $app->getContainer();
//$settings = $container->get('settings');


        $this->test= "Development code";
$this->input_agent = null;
//        $this->current_time = $this->thing->json->time();

    }


    // -----------------------

    function assertIs($input)
    {
$this->input_agent = null;
$agent_name = "input";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $agent_name. " is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen($agent_name. " is")); 
        } elseif (($pos = strpos(strtolower($input), $agent_name)) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen($agent_name)); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $this->input_agent = $filtered_input;

}

    /**
     *
     * @param unknown $text (optional)
     */
    function doMore($text = null) {

        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index=>$ngram) {

            switch ($ngram) {

            case "more":

$web = new Web($this->thing, "web");
var_dump($web->prior_agent);

if ( (strtolower($web->prior_agent)) == (strtolower("more")) ) {
$this->response .= "More more? ";
return;}

//var_dump($this->input_agent->input_agent);
//$input_agent = $this->input_agent;
//$this->input_agent = null;
//$this->assertIs($this->input);
//$input_agent_text = $input_agent . " is expecting input. ";

//if ($input_agent == false) {$input_agent_text = "No input expected. ";}
//$this->input_agent = $input_agent;

$this->getMore($web->prior_agent);
//var_dump($this->agent->thing_report['sms']);
$this->response .= $this->agent->thing_report['sms'];
//                $this->response .= "Hello more. ";
                return;

            default:
            }

        }



$this->assertIs($this->input);
$this->response .= "Said that input response is expected to the current agent. ";

                //                if (($pos = strpos(strtolower($filtered_text), "uuid")) !== FALSE) {

                //                   $this->response .= "uuid " . $this->basket_thing->uuid .". ";



    }



    // Get the basket
    //$this->getBasket($basket_uuid);

    //        $this->setBasket();

    //        $this->inventoryBasket();

//}


/**
 *
 */
public function get() {

$this->input_agent = new Input($this->thing, "input");


//    $this->variables_agent = new Variables($this->thing, "variables " . "input " . $this->from);


    //        $input = new Variables($this->thing, "variables basket " . $this->from);

//    $this->input_agent = $this->variables_agent->getVariable("agent");
//    $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

    //        if ($this->input_flag != false) {$basket_code = $this->input_flag;}
}


/**
 *
 * @param unknown $input_flag (optional)
 */
function set($input_agent = null) {
//$this->respond();
//    if ($input_agent == null) {$input_agent = $this->input_agent;}
//    if (!isset($this->variables_agent)) {$this->get();}
    //$this->variables_agent->setVariable("value_destroyed", $this->value_destroyed);

    //$this->variables_agent->setVariable("things_destroyed", $this->things_destroyed);

    //$this->thing->setVariable("damage_cost", $this->damage_cost);

//    $this->variables_agent->setVariable("agent", $input_agent);
//    $this->variables_agent->setVariable("refreshed_at", $this->current_time);
}

/**
 *
 */
function makeSMS() {
//    if ($this->state == true) {
//        $sms = "INPUT | ?";
//    }

//    if ($this->state == false) {
//        $sms = "INPUT | " . $this->subject;

//    }

$sms = "MORE " . $this->response;
    $this->sms_message = $sms;
    $this->thing_report['sms'] = $sms;

}


/**
 *
 * @return unknown
 */
public function respond() {


    $this->thing->flagGreen();

    $to = $this->thing->from;
    $from = "more";


    $this->makeSMS();

    $choices = false;

    $this->thing_report[ "choices" ] = $choices;
    $this->thing_report["info"] = "This makes an input thing.";
    $this->thing_report["help"] = "This is about input variables.";

    //$this->thing_report['sms'] = $this->sms_message;
    $this->thing_report['message'] = $this->sms_message;
    $this->thing_report['txt'] = $this->sms_message;

    $message_thing = new Message($this->thing, $this->thing_report);
    $this->thing_report['info'] = $message_thing->thing_report['info'] ;

    return $this->thing_report;


}




/**
 *
 * @return unknown
 */
public function readSubject() {
    $this->doMore($this->input);
    //$input = strtolower($this->subject);
    //$this->getInput();

    return false;
}


/**
 *
 * @return unknown
 */
//public function readInstruction() {


    //$input = strtolower($this->subject);
//    $this->getInput();

//    return false;
//}



/**
 *
 * @return unknown
 */
function getMore($agent_class_name) {

        try {

            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

            $this->thing->log( 'trying Agent "' . $agent_class_name . '".', "INFORMATION" );
            $agent = new $agent_namespace_name($this->thing, strtolower($agent_class_name));

            // If the agent returns true it states it's response is not to be used.
            if ((isset($agent->response)) and ($agent->response === true)) {
                throw new Exception("Flagged true.");
            }

            $this->thing_report = $agent->thing_report;

            $this->agent = $agent;


        } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.
            $this->thing->log( 'could not load "' . $agent_class_name . '".' , "WARNING" );
            // echo $ex;
            $message = $ex->getMessage();
            // $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . '  ' . $file . ' line:' . $line;
            $this->thing->log($input , "WARNING" );

            // This is an error in the Place, so Bork and move onto the next context.
            // $bork_agent = new Bork($this->thing, $input);
            //continue;
            return false;
        }
        //if (!isset($this->thing_report['sms'])) {return false;}
        return true;


}


}
