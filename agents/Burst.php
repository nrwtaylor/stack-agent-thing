<?php
/**
 * Burst.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Burst extends Agent
{
    public $var = 'hello';

    /**
     */
    function init()
    {
        $this->keyword = "burst";

        $this->verbosity = 1;
        if (isset($this->settings['verbosity'])) {
            $this->verbosity = $this->settings['verbosity'];
        }

//        $this->start_time = $this->thing->elapsed_runtime();

        $this->test = "Development code"; // Always

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    'received this Thing, "' .
                    $this->subject .
                    '".'
            );
        }

        // Set up default flag settings
        //$this->verbosity = 1;
        $this->requested_flag = null;
        $this->default_flag = "green";

        $this->requested_thing_name = 'thing';
        $this->horizon = 4; // 15 sounded about right.  But lets try 4.

        $this->burst_horizon = 60; //s
        $this->burst_time = null;

        // Allows for manual overloading of input.  Gets boring.
        // Allows a 15 day horizong (14+1) which is a good default
        // for repeating daily scheduling patterns.

        $this->node_list = ["green" => ["red" => ["green"]]];

//        $this->current_time = $this->thing->json->time();


//        $this->thing_report['log'] = $this->thing->log;

    }

    /**
     *
     * @return unknown
     */
    public function read($text = null)
    {
        $this->readInstruction();

        $this->readSubject();
    }

    /**
     *
     */
    function run()
    {
        $this->getBurst();
    }

    /**
     *
     * @param unknown $requested_flag (optional)
     */
    function set($requested_flag = null)
    {
        if ($requested_flag == null) {
            if (!isset($this->requested_flag)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_flag = "green"; // If not sure, show green.
            }
            $requested_flag = $this->requested_flag;
        }

        $this->flag = $requested_flag;
        $this->refreshed_at = $this->current_time;

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(["burst", "flag"], $this->flag);
        $this->thing->json->writeVariable(
            ["burst", "refreshed_at"],
            $this->current_time
        );
        $this->thing->json->writeVariable(["burst", "burst"], $this->burst);
        $this->thing->json->writeVariable(
            ["burst", "burstiness"],
            $this->burstiness
        );
        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix . 'set Flag to ' . $this->flag
            );
        }
        return;
    }

    /**
     *
     * @param unknown $flag (optional)
     * @return unknown
     */
    function isFlag($flag = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($flag == null) {
            if (!isset($this->flag)) {
                $this->flag = "X";
            }
            $flag = $this->flag;
        }

        if ($flag == "red" or $flag == "green") {
            return false;
        }

        return true;
    }

    /**
     *
     */
    function getBurst()
    {
        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    ' start getBurst. Timestamp ' .
                    number_format($this->thing->elapsed_runtime()) .
                    'ms.',
                "OPTIMIZE"
            );
        }
        // Get recent train tags.
        // This will include simple 'train'
        // requests too.
        // Think about that.

        $findagent = 'prod';
        // prod 5,626 5,897 5,147
        // dev 3,575 5,082 7,746 7,038 6,690
        if ($findagent == 'prod') {
            $findagent_thing = new Findagent(
                $this->thing,
                $this->requested_thing_name . ' ' . $this->horizon
            );
            $t = $findagent_thing->thing_report;
        } else {
            $this->thing->db->setUser($this->from);
            $t = $this->thing->db->variableSearch(null, null, $this->horizon);
        }

        if (!isset($t)) {
            return null;
        }

        if ($this->verbosity >= 2) {
            $count = 0;
            if ((isset($t['things'])) and (is_array($t['things']))) {
                $count = count($t['things']);
            }

            $this->thing->log(
                $this->agent_prefix .
                    'found ' .
                    $count .
                    " " .
                    ucwords($this->requested_thing_name) .
                    " Agent Things.",
                "DEBUG"
            );
        }

        $this->max_index = 0;
        $this->burst = 0;
        $this->burstiness = 0;
        $this->flag = "green";

if (!isset($t['things'][0]['created_at'])) {return true;}

        $created_at = $t['things'][0]['created_at'];

        $this->max_index = 0;
        $this->previous_trains = [];
        $this->similarness = 0;
        $this->similarity = 0;
        $this->flag = "green";

        $this->matches = [];
        if (
            isset($findagent_thing->thing_report['things']) and
            $findagent_thing->thing_report['things'] != true and
            count($findagent_thing->thing_report['things']) > 1
        ) {
            foreach ($t['things'] as $thing) {
                $previous_created_at = $created_at;
                $created_at = $thing['created_at'];

                $age = strtotime($this->current_time) - strtotime($created_at);

                $inter_arrival_time =
                    strtotime($previous_created_at) - strtotime($created_at);

                if ($inter_arrival_time < $this->burst_horizon) {
                    $this->burst += 1;
                    // Set the age to the head of the series
                    $this->burst_time = $t['things'][0]['created_at'];
                }

                $this->burstiness += $inter_arrival_time;
                if ($this->verbosity >= 2) {
                    $this->thing->log(
                        $this->agent_prefix .
                            ' td ' .
                            $inter_arrival_time .
                            '.',
                        "DEBUG"
                    );
                }
            }
        }

        if ($this->burst >= 1) {
            $this->flag = "red";
        }
        if ($this->verbosity >= 2) {
            if (!isset($age)) {
                $age = "X";
            }
            $this->thing->log(
                $this->agent_prefix . 'calculated age =  ' . $age . '.',
                "DEBUG"
            );
            $this->thing->log(
                $this->agent_prefix .
                    'calculated burst =  ' .
                    $this->burst .
                    '.',
                "DEBUG"
            );
            $this->thing->log(
                $this->agent_prefix .
                    'calculated burstiness =  ' .
                    $this->burstiness .
                    '.',
                "DEBUG"
            );
        }
    }

    /**
     *
     */
    function get()
    {
        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_flag = $this->thing->getVariable("burst", "flag");
        $this->refreshed_at = $this->thing->getVariable(
            "burst",
            "refreshed_at"
        );

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix . 'got from db ' . $this->previous_flag,
                "DEBUG"
            );
        }

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isFlag($this->previous_flag)) {
            $this->flag = $this->previous_flag;
        } else {
            $this->flag = $this->default_flag;
        }


        $this->thing->log(
            $this->agent_prefix . 'got a ' . strtoupper($this->flag) . ' FLAG.'
        );

        return;
    }

    /**
     *
     * @param unknown $choice (optional)
     * @return unknown
     */
    function selectChoice($choice = null)
    {
        if ($choice == null) {
            if (!isset($this->flag)) {
                $this->flag = $this->default_flag;
            }
            $choice = $this->flag;
        }

        if (!isset($this->flag)) {
            $this->previous_flag = false;
        } else {
            $this->previous_flag = $this->flag;
        }

        $this->flag = $choice;

        $this->thing->log(
            'Agent "' .
                ucwords($this->keyword) .
                '" chose "' .
                $this->flag .
                '".'
        );

        return $this->flag;
    }

    /**
     *
     */
    function makeChoices()
    {
        //        $this->thing->choice->Choose($this->state);
        //        $this->thing->choice->save($this->keyword, $this->state);

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->flag
        );

        $choices = $this->flag->thing->choice->makeLinks($this->flag);
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    public function respond()
    {
        // At this point state is set
        $this->set($this->flag);

        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = $this->keyword;

        $this->makeSMS();
        $this->makeMessage();

        $this->thing_report['email'] = $this->message;

        //        $this->makePNG();
        //        $this->makeChoices(); // Turn off because it is too slow.

        //        $this->makeTXT();

        //$respond = "all";
        //if (($this->flag == "red") or ($respond == "all")) {
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        //$this->thing_report['help'] = 'This Flag is either RED or GREEN. RED means busy.';
        $this->makeHelp();
    }

    /**
     *
     */
    function makeHelp()
    {
        if ($this->flag == "green") {
            $this->thing_report['help'] =
                'FLAG GREEN. No recent burst has been seen.';
        }

        if ($this->flag == "red") {
            $this->thing_report['help'] =
                'FLAG RED. Recent burst(s) have been seen.';
        }
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = 'This is BURST ' . $this->uuid . '. ';
        $txt .= 'There is a ' . strtoupper($this->flag) . " FLAG. ";
        if ($this->verbosity >= 5) {
            $txt .=
                'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms_message = "BURST ";

        if ($this->verbosity >= 7) {
            $sms_message .= " | burst " . strtoupper($this->burst);
        }

        if ($this->verbosity >= 7) {
            $sms_message .= " | burstiness " . strtoupper($this->burstiness);
        }

        if ($this->verbosity >= 6) {
            $sms_message .=
                " | previous flag " . strtoupper($this->previous_flag);
            //$sms_message .= " requested flag " . strtoupper($this->requested_flag);
            //$sms_message .= " current node " . strtoupper($this->base_thing->choice->current_node);
        }

        if ($this->verbosity >= 1) {
            $sms_message .= " | flag " . strtoupper($this->flag);
        }

        if ($this->verbosity >= 5) {
            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        }

        if ($this->verbosity >= 2) {
            if ($this->flag == "red") {
                $sms_message .= " | MESSAGE Latency";
            }

            if ($this->flag == "green") {
                //                $sms_message .= ' | MESSAGE &ltinput&gt';
                $sms_message .= ' | MESSAGE Input';
            }
        }

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = 'This is a BURSTINESS detector. ';

        if ($this->flag == 'red') {
            $message .=
                'MORDOK has seen burstiness in the previous ' .
                $this->horizon .
                ' messages. ';
        }

        if ($this->flag == 'green') {
            $message .= 'MORDOK is likely operating with low similarity. ';
        }

        $message .= 'The flag is a  ' . strtoupper($this->flag) . " FLAG. ";

        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
    }

    /**
     *
     */
    public function readInstruction()
    {
        if ($this->agent_input == null) {
            //$this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->agent_input));

        if (isset($pieces[0])) {
            $this->requested_thing_name = $pieces[0];
        }
        if (isset($pieces[1])) {
            $this->horizon = $pieces[1];
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        if ($this->agent_input == "burst") {
            return null;
        }

        if ($this->agent_input != null) {
            $this->requested_thing_name = $this->agent_input;
        }

        $this->response = null;

        $keywords = ['flag', 'red', 'green'];

        $input = strtolower($this->subject);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->get();
                return;
            }
            //return "Request not understood";
            // Drop through to piece scanner
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'red':
                            $this->thing->log(
                                $this->agent_prefix .
                                    'received request for RED FLAG.'
                            );
                            $this->selectChoice('red');
                            return;
                        case 'green':
                            $this->selectChoice('green');
                            return;
                        case 'next':

                        default:
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        $this->requested_flag = $this->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_flag) {
            case 'green':
                $this->selectChoice('green');
                return;
            case 'red':
                $this->selectChoice('red');
                return;
        }

        $this->get();

        return "Message not understood";

        return false;
    }

    /**
     *
     * @param unknown $input
     * @param unknown $discriminators (optional)
     * @return unknown
     */
    function discriminateInput($input, $discriminators = null)
    {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ['red', 'green'];
        }

        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        $aliases = [];

        $aliases['red'] = ['r', 'red', 'on'];
        $aliases['green'] = ['g', 'grn', 'gren', 'green', 'gem', 'off'];
        //$aliases['reset'] = array('rst','reset','rest');
        //$aliases['lap'] = array('lap','laps','lp');

        $words = explode(" ", $input);

        $count = [];

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
                }

                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                    }
                }
            }
        }

        $this->thing->log(
            'Agent "Flag" has a total count of ' . $total_count . '.'
        );
        // Set total sum of all values to 1.

        $normalized = [];
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key => $value) {
            if (isset($max)) {
                $delta = $max - $value;
                break;
            }
            if (!isset($max)) {
                $max = $value;
                $selected_discriminator = $key;
            }
        }

        if ($delta >= $minimum_discrimination) {
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }
}
