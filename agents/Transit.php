<?php
/**
 * Transit.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Transit extends Agent  {

    // This is the transit manager.

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        $this->test = "Development code"; // Always

        $this->num_hits = 0;

        $this->agent_prefix = 'Agent "Transit" ';

        $this->node_list = array(
            "start" => array("stop 1" => array("stop 2", "stop 1"), "stop 3"),
            "stop 3"
        );
        $this->loadChoice('train');

        $this->keywords = array(
            'run',
            'change',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'run',
            'red',
            'green'
        );

        //                'block' => array('default run_time'=>'105',
        //                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->time();

        //$this->default_run_time = $this->thing->container['api']['train']['default run_time'];
        //$this->negative_time = $this->thing->container['api']['train']['negative_time'];
        $this->default_run_time = $this->current_time;
        $this->negative_time = true;

        $this->default_agency = "translink";
        if (isset($this->thing->container['api']['transit']['agency'])) {
           $this->default_agency = $this->thing->container['api']['transit']['agency'];

        }

        $this->stop = "X";

        $default_train_name = "transit";

        //        $this->variables_agent = new Variables($this->thing, "variables " . $default_train_name . " " . $this->from);



        $this->current_time = $this->thing->time();

        $this->thing_report['help'] = 'This is a bus with people on it.';

        $this->state = 'X';
        $this->requested_state = 'X';


        $this->thing->log(
            'running on Thing ' .
            $this->thing->nuuid .
            '.'
        );
        $this->thing->log(
            'received this Thing "' .
            $this->subject .
            '".'
        );

        $this->thing->log(
            $this->agent_prefix .
            'ran for ' .
            number_format($this->thing->elapsed_runtime()) .
            'ms.'
        );

        $this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;
    }


    /**
     *
     */
    function set() {
        // A block has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->train_thing)) {
            $this->train_thing = $this->thing;
        }

//        if ((!isset($requested_state)) or ($requested_state == null)) {
//            $requested_state = $this->requested_state;
//        }

        // Update calculated variables.

        //$this->variables_agent->setVariable("state", $this->state);
        //$this->variables_agent->setVariable("stop_id", $this->transit_id);
        //$this->variables_agent->setVariable("agency", $this->agency);

        //$this->variables_agent->setVariable("refreshed_at", $this->refreshed_at);

        $this->identity = $this->from;

        $this->thing->db->setFrom($this->identity);
        $this->thing->Write(
            array("transit", "state"),
            $this->state
        );
        $this->thing->Write(
            array("transit", "transit_id"),
            $this->transit_id
        );
        $this->thing->Write(
            array("transit", "agency"),
            $this->agency
        );

        $this->refreshed_at = $this->current_time;
        $this->thing->Write(
            array("transit", "refreshed_at"),
            $this->refreshed_at
        );


    }


    /**
     *
     * @param unknown $train_time (optional)
     */
    public function get($train_time = null) {

       $this->getFlag();

    }

    /**
     *
     * @param unknown $variable_name (optional)
     * @param unknown $variable      (optional)
     * @return unknown
     */
/*
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
        if (isset($this->{"default_" . $variable_name})) {
            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }
*/

    /**
     *
     * @return unknown
     */
    function getFlag() {
        $this->flag_thing = new Flag($this->thing, 'flag');
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }


    /**
     *
     * @param unknown $colour
     * @return unknown
     */
    function setFlag($colour) {
        $this->flag_thing = new Flag($this->thing, 'flag ' . $colour);
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }


    /**
     *
     */
    public function respond() {
        // Thing actions
//        $this->thing->flagGreen();
        // Generate email response.

//        $this->state = 'green';
//        $this->requested_state = 'green';

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

    }

    public function makeEmail() {
        if (!isset($this->sms_message)) {$this->makeSMS();}
        $this->thing_report['email'] = $this->sms_message;
    }

    public function makeMessage() {
        if (!isset($this->sms_message)) {$this->makeSMS();}
        $this->thing_report['message'] = $this->sms_message;

    }

    public function makeSnippet() {

        if (!isset($this->choices)) {$this->makeChoices();}

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $this->choices['link'] .
            '].';

        $state_text = "X";
        if (isset($this->state)) {$state_text = $this->state;}
        $test_message .= '<br>Train state: ' . $state_text . '<br>';

        $test_message .= '<br>' . $this->sms_message;

//        $test_message .=
//            '<br>Current node: ' . $this->thing->choice->current_node;
        $this->thing_report['snippet'] = $test_message;

    }

    public function makeChoices() {

        $this->node_list = array("transit"=>null);


        $this->createChoice($this->agent_name, $this->node_list, "transit");
        $this->choices = $this->linksChoice('transit');

        $this->thing_report['choices'] = $this->choices;

    }

    public function makeSMS() {

        $sms_message = "TRANSIT ";
        $sms_message .= " | flag " . $this->flag;

        $sms_message .= " | " . $this->stop;

        $sms_message .= " | nuuid " . substr($this->thing->uuid, 0, 4);
        $sms_message .=
            " | rtime " . number_format($this->thing->elapsed_runtime()) . 'ms';

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractStops($input) {
        if (!isset($this->stops)) {
            $this->stops = array();
        }

        $pattern = "|\d{5}$|";
        $pattern = '/\b(\d{5})\b/';
        preg_match_all($pattern, $input, $m);
        $this->stops = $m[0];
        return $this->stops;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractStop($input) {
        $stops = $this->extractStops($input);

        if (count($stops) == 1) {
            $this->stop = $stops[0];
            $this->thing->log(
                'Agent "Transit" found a stop (' .
                $this->stop .
                ') in the text.'
            );
            return $this->stop;
        }

        if (count($stops) == 0) {
            return false;
        }
        if (count($stops) > 1) {
            return true;
        }

        return true;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractAgency($input) {
        $this->agency = $this->default_agency;

        if (
            substr($input, 0, 4) == "1778" or
            mb_substr($input, 0, 4) == "1778"
        ) {
            $this->agency = "translink";
            return $this->agency;
        }

        return $this->agency;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        $this->response = null;

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

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $this->extractStop($haystack); // Can X be improved on?

        $this->extractAgency($input);

        if (isset($this->stop)) {
            $this->thing->log(
                'Agent "Transit" found a stop (' .
                $this->stop .
                ') for agency ' .
                $this->agency .
                '.'
            );
            $this->transit_id = $this->stop;
        }

        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {
            return;
        }

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'transit') {
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                    case 'red':
                        $this->setFlag('red');
                        break;

                    case 'green':
                        $this->setFlag('green');
                        break;

                    case 'on':
                        //$this->setFlag('green');
                        //break;

                    default:
                    }
                }
            }
        }

        return "Message not understood";
    }


    /**
     *
     * @return unknown
     */
/*
    function kill() {
        // No messing about.
        return $this->thing->Forget();
    }
*/

}
