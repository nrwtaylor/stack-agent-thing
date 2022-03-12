<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Shift extends Agent
{
    public $var = 'hello';
    public function init()
    {
        $this->keyword = "shift";

        $this->requested_state = "X";

        $this->node_list = ["off" => ["on" => ["off"]]];

        $this->block_patterns = [];
        // Off
        $this->block_patterns['off'] = [
            "0O01" => [
                "alias" => "am",
                "run_at" => "0000",
                "run_time" => "660",
            ],
            "0O02" => [
                "alias" => "afternoon",
                "run_at" => "1445",
                "run_time" => "360",
            ],
            "0O03" => [
                "alias" => "evening",
                "run_at" => "1730",
                "run_time" => "285",
            ],
            "0O04" => [
                "alias" => "pm",
                "run_at" => "2215",
                "run_time" => "225",
            ],
        ];

        $this->block_patterns['on'] = [
            "0O01" => [
                "alias" => "am",
                "run_at" => "0000",
                "run_time" => "510",
            ],
            "0O02" => [
                "alias" => "morning school run",
                "run_at" => "0830",
                "run_time" => "60",
            ],
            "0O03" => [
                "alias" => "day",
                "run_at" => "0930",
                "run_time" => "315",
            ],
            "0O04" => [
                "alias" => "pm school run",
                "run_at" => "1445",
                "run_time" => "45",
            ],
            "0O05" => [
                "alias" => "afterschool",
                "run_at" => "1530",
                "run_time" => "90",
            ],
            "0O06" => [
                "alias" => "evening",
                "run_at" => "1700",
                "run_time" => "270",
            ],
            "0O07" => [
                "alias" => "pm",
                "run_at" => "2130",
                "run_time" => "150",
            ],
        ];

        $this->block_patterns['X'] = [
            "0O01" => [
                "alias" => "all day",
                "run_at" => "0000",
                "run_time" => "1440",
            ],
        ];

        // This isn't going to help because we don't know if this
        // is the base.

        $this->current_time = $this->thing->time();

        $this->test = "Development code"; // Always
    }

    // This is a commonality between shift and block
    function blockTime($input_time = null)
    {
        if ($input_time == null) {
            $input_time = $this->current_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $this->block_time = $this->hour . $this->minute;

        return $this->block_time;

    }

    function set($requested_state = null)
    {
        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->thing->Write(
            [$this->keyword, "state"],
            $requested_state
        );
        $this->thing->Write(
            [$this->keyword, "refreshed_at"],
            $this->current_time
        );

        $this->thing->choice->Choose($requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }

    function getBlocks()
    {
        $this->block_list = null;

        $block_pattern = $this->block_patterns[$this->state];
        $block_time = $this->blockTime(); // Current 4 digit block time

        foreach ($block_pattern as $block => $train) {

            $run_time = $train['run_time'];
            $run_at = $train['run_at'];

            $end_at = $this->thing->time(
                strtotime($train['run_at'] . " " . $run_time . " minutes")
            );

            if ($train['run_at'] >= $block_time or $end_at > $block_time) {
                $this->thing->log(
                    $this->blockTime($run_at) .
                        ' ' .
                        $run_time .
                        ' ' .
                        $this->blockTime($end_at)
                );

                $this->block_list .= $block . " ";

                //                $this->thing->log('Agent "Shift" block is ' . $block . ' ' . $this->blockTime($run_at) . ' '. $run_time .'.');

                // Then it is a valid block.
                // So create a block thing.

                // This is the latest request so create all needed blocks.
                // When being reviewed the stack will pick the latest most contextually
                // appropriate block

                //                $agent_instruction = $block . " " . $train['run_at'] . " " . $train['run_time'];
                //                $block_thing = new Block($this->thing, $agent_instruction);

                //$t = $block_thing->thing_report['info'];

                //$this->thing->log('<pre>Agent "Shift" created a Block Thing with ' . $agent_instruction . ' ' . $t .'.</pre>');

                break;
            }
        }
    }

    function get()
    {
        $findagent_thing = new Findagent($this->thing, $this->keyword);

        foreach (
            array_reverse($findagent_thing->thing_report['things'])
            as $thing_obj
        ) {
            $thing = new Thing($thing_obj['uuid']);

            $thing->previous_state = $thing->Read([
                $this->keyword,
                "state",
            ]);
            $thing->refreshed_at = $thing->Read([
                $this->keyword,
                "refreshed_at",
            ]);

            if ($thing->refreshed_at == false) {
                // Things is list sorted by date.  So this is the oldest Thing.
                // with a 'keyword' record.
                continue;
            } else {
                break;
            }
        }

        // See where we stand.

        if (!isset($this->requested_state)) {
            $this->requested_state = 'X'; // Default request for signal (X)
        }

        // Redundant, but probably helpful when wanting confirmation.  And
        // redundancy.  And a record of the state change
        // request.
        $this->set($this->requested_state);

        if ($thing->refreshed_at == false) {
            // No $this->keyword agent found.  So
            // the current Thing also becomes the base thing.

            $this->base_thing = $this->thing;
        } else {
            // The Thing already exists
            $this->base_thing = $thing;
        }


        $this->previous_state = $this->base_thing->Read([
            $this->keyword,
            "state",
        ]);
        $this->refreshed_at = $this->base_thing->Read([
            $this->keyword,
            "refreshed_at",
        ]);

        $this->previous_state = $this->base_thing->choice->load($this->keyword);

        $this->base_thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->requested_state
        );
        $this->base_thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;

        return;
    }

    function readShift()
    {
        $this->getBlocks();

        return $this->state;
    }

    function setShifts()
    {
        $block_pattern = $this->block_patterns[$this->state];
        $block_time = $this->blockTime(); // Current 4 digit block time

        foreach ($block_pattern as $block => $train) {

            $run_time = $train['run_time'];
            $run_at = $train['run_at'];

            $end_at = $this->thing->time(
                strtotime($train['run_at'] . " " . $run_time . " minutes")
            );

            if ($train['run_at'] >= $block_time or $end_at > $block_time) {
                $this->thing->log(
                    $this->blockTime($run_at) .
                        ' ' .
                        $run_time .
                        ' ' .
                        $this->blockTime($end_at)
                );

                $this->block_list .= $block . " ";

                $this->thing->log(
                    'Agent "Shift" block is ' .
                        $block .
                        ' ' .
                        $this->blockTime($run_at) .
                        ' ' .
                        $run_time .
                        '.'
                );

                // Then it is a valid block.
                // So create a block thing.

                // This is the latest request so create all needed blocks.
                // When being reviewed the stack will pick the latest most contextually
                // appropriate block

                $agent_instruction =
                    $block . " " . $train['run_at'] . " " . $train['run_time'];
                $block_thing = new Block($this->thing, $agent_instruction);

                $t = $block_thing->thing_report['info'];

                $this->thing->log(
                    '<pre>Agent "Shift" created a Block Thing with ' .
                        $agent_instruction .
                        ' ' .
                        $t .
                        '.</pre>'
                );

                break;
            }
        }
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            $choice = 'off'; // Fail off.
        }

        $this->thing->log(
            'Agent "' .
                ucfirst($this->keyword) .
                '" chose "' .
                strtoupper($choice) .
                '".'
        );

        $this->set($choice);

        $this->setShifts();

        return $this->state;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = $this->keyword;

        $choices = $this->base_thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;

        $sms_message = "SHIFT = " . strtoupper($this->state);
        $sms_message .=
            " | Last shift was " . strtoupper($this->previous_state);

        $sms_message .= " | base nuuid " . strtoupper($this->base_thing->nuuid);

        if (isset($this->block_list)) {
            $sms_message .= " | Blocks to go " . strtoupper($this->block_list);
        }

        $sms_message .= " | TEXT ?";

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';
        $test_message .= '<br>Shift state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $sms_message;

        $test_message .=
            '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>Requested state: ' . $this->requested_state;

        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $sms_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            'This is a shift manager.  Currently three shifts are available: ON, OFF and X';

        return;
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ['on', 'off', 'next'];

        $input = strtolower($this->subject);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->readShift();
                return;
            }

            return "Request not understood";
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'on':
                            $this->selectChoice('on');
                            return;
                        case 'off':
                            $this->selectChoice('off');
                            return;
                        case 'next':
                            $choices = $this->base_thing->choice->makeLinks(
                                $this->state
                            );

                            $next = strtolower(array_pop($choices['words']));

                            $this->selectChoice($next);
                            return;

                        case 'mon':
                            $this->selectChoice('mon');
                            return;
                        case 'sat':
                            $this->selectChoice('sat');
                            return;

                        default:
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_state) {
            case 'on':
                $this->selectChoice('on');
                return;
            case 'off':
                $this->selectChoice('off');
                return;
        }

        $this->readShift();

        return "Message not understood";

        return false;
    }

    function discriminateInput($input, $discriminators = null)
    {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ['on', 'off'];
        }

        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        $aliases = [];

        $aliases['on'] = ['red', 'on'];
        $aliases['off'] = ['green', 'off'];

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

        $this->thing->log("total count " . $total_count);
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
