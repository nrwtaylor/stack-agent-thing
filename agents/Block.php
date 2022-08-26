<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Block extends Agent
{
    // This is a resource block.  It is a train which be run by the block scheduler.
    // It will respond to trains with a signal.
    // Red - Not available
    // Green - Slot allocated
    // Yellow - Next signal Red.
    // Double Yellow - Next signal Yellow

    // The block keeps track of the uuids of associated resources.
    // And checks to see what the block signal should be.  And pass and collect tokens.

    // This is the block manager.  They are an ex-British Rail signalperson.

    public $var = 'hello';

    function init()
    {
        $this->node_list = [
            "start" => ["stop 1" => ["stop 2", "stop 1"], "stop 3"],
            "stop 3",
        ];
        $this->loadChoice('block');

        $this->state = "off";

        $this->default_run_time =
            $this->thing->container['api']['block']['default run_time'];
        $this->negative_time =
            $this->thing->container['api']['block']['negative_time'];

        $this->current_time = $this->thing->time();

        $this->shift_state = "off";
        $this->max_index = 0;
        $this->test = "Development code"; // Always
    }

    function set()
    {
        // A block has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->block_thing)) {
            $this->block_thing = $this->thing;
        }

        $this->block_thing->Write(
            ["block", "index"],
            $this->index
        );

        $this->block_thing->Write(
            ["block", "start_at"],
            $this->start_at
        );
        $this->block_thing->Write(
            ["block", "quantity"],
            $this->quantity
        );

        $this->getAvailable();
        $this->block_thing->Write(
            ["block", "available"],
            $this->available
        );
        $this->block_thing->Write(
            ["block", "refreshed_at"],
            $this->current_time
        );

        $this->saveChoice('block', $this->state);
    }

    function nextBlock()
    {
        $this->thing->log("next block");
        // Pull up the current block
        $this->get();

        // Find the end time of the block
        // which is $this->end_at

        // One minute into next block
        $quantity = 1;
        $next_time = $this->thing->time(
            strtotime($this->end_at . " " . $quantity . " minutes")
        );

        $this->get($next_time);

        // So this should create a block in the next minute.

        return $this->available;
    }

    function get($block_time = null)
    {
        // Loads current block into $this->block_thing

        $match = false;

        if ($block_time == null) {
            $block_time = $this->current_time;
        }

        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'block');

$things = $findagent_thing->thing_report['things'];

if ($things === true) {return true;}

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->thing->log(
            'found ' .
                count($things) .
                " Block Things."
        );

        $this->max_index = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            $thing = new Thing($block_thing['uuid']);

            $thing->index = $thing->Read(["block", "index"]);
            if ($thing->index > $this->max_index) {
                $this->max_index = $thing->index;
            }

            $thing->start_at = $thing->Read([
                "block",
                "start_at",
            ]);
            $thing->quantity = $thing->Read([
                "block",
                "quantity",
            ]);
            $thing->available = $thing->Read([
                "block",
                "available",
            ]);
            $thing->refreshed_at = $thing->Read([
                "block",
                "refreshed_at",
            ]);

            if ($thing->quantity > 0) {
                $thing->end_at = $this->thing->time(
                    strtotime(
                        $thing->start_at . " " . $thing->quantity . " minutes"
                    )
                );
            } else {
                $thing->end_at = null;
            }

            if (
                strtotime($block_time) >= strtotime($thing->start_at) and
                strtotime($block_time) <= strtotime($thing->end_at)
            ) {
                $this->thing->log(
                    'found ' .
                        $this->blockTime($block_time) .
                        ' in existing block #' .
                        $thing->index .
                        ' (' .
                        $this->blockTime($thing->start_at) .
                        " " .
                        $thing->quantity .
                        ').'
                );
                //$this->block_thing->flagRed();
                $match = true;
                break; //Take first matching block.
            } else {
                $this->thing->log(
                    'Block #' .
                        $thing->index .
                        ' (' .
                        $this->blockTime($thing->start_at) .
                        " - " .
                        $this->blockTime($thing->end_at) .
                        " )"
                );
            }
        }

        // Set-up empty block variables.
        $this->flagposts = [];
        $this->trains = [];
        $this->bells = [];

        // If it drops through as Green, then no blocks matched the current time.
        if ($match == false) {
            // No valid block found, so make a block record in current Thing
            // and set flag to Green ie accepting trains.

            $this->block_thing = $this->thing;

            $this->index = 0;
            $this->start_at = $this->current_time;
            $this->quantity = 22;
            $this->available = 22;

            $this->thing->log(
                'did not find a valid block at blocktime ' .
                    $this->blockTime($block_time) .
                    "."
            );

            //$this->makeBlock($this->current_time, "x");
        } else {
            $this->thing->log("found a valid block.");

            // Red Block Thing - There is a current operating block on the stack.
            // Load the block details into this Thing.

            $this->block_thing = $thing;

            $this->index = $thing->index;
            $this->start_at = $thing->start_at;
            $this->quantity = $thing->quantity;
            $this->available = $thing->quantity;
        }

        $this->getAvailable();
        $this->getEndat();

        //$this->block_thing->json->setField("associations");
        $this->associations = $this->block_thing->Read(["agent"], 'associations');

        if ($this->associations != false) {
            foreach ($this->associations as $association_uuid) {
                $association_thing = new Thing($association_uuid);

                $this->flagposts[] = $association_thing->Read([
                    "flagpost",
                ]);

                $this->trains[] = $association_thing->Read([
                    "train",
                ]);

                $this->bells[] = $association_thing->Read([
                    "bell",
                ]);
            }
        }
    }

    function dropBlock()
    {
        $this->thing->log("was asked to drop a block.");

        // If it comes back false we will pick that up with an unset block thing.

        if (isset($this->block_thing)) {
            $this->block_thing->Forget();
            $this->block_thing = null;
        }

        $this->get();
    }

    function makeBlock($run_at = null, $quantity = null, $available = null)
    {
        $shift_state = $this->shift_state;

	$quantity_default = 105;
        if (($quantity === null) and (isset($this->quantity))) {
          $quantity_default = $this->quantity;
        }
        if (!$this->thing->isData($quantity)) {$quantity = $quantity_default;}

        $available_default = 105;
        if (($available === null) and (isset($this->available))) {
          $available_default = $this->available;
        }
        if (!$this->thing->isData($available)) {$available = $available_default;}

/*
        if (isset($this->quantity) and $quantity == null and $this->quantity == null) {
            $quantity = 105;
        }

        if (isset($this->quantity) and $quantity != null and $this->quantity != null) {
            //$quantity = $this->quantity;
        }

        if (isset($this->available) and $available == null and $this->available == null) {
            $available = 100;
        } elseif (isset($this->quantity) and ($this->available != null)) {
            $available = $this->available;
        }
*/

        $run_at_default = $this->current_time;
        if (($run_at === null) and (isset($this->run_at))) {
          $run_at_default = $this->run_at;

        }
        if (!$this->thing->isData($run_at)) {$run_at = $run_at_default;}

/*
        if ($run_at == null and $this->run_at == null) {
            $run_at = $this->current_time;
        }

        if ($this->run_at != null and $run_at != null) {
            // Let run_at stand.
        }

        if ($run_at == null) {
            $run_at = $this->current_time;
        }
*/
        $this->thing->log(
            'will make a Block with ' .
                $this->blockTime($run_at) .
                " " .
                $quantity .
                " " .
                $available .
                "."
        );

        $shift_override = true;

        if (
            $shift_state == "off" or
            $shift_state == "null" or
            $shift_state == "" or
            $shift_override
        ) {
            // Only if the shift state is off can we
            // create blocks on the fly.

            // Otherwise we needs to make trains to run in the block.

            $this->thing->log("found that this is the Off shift.");

            // So we can create this block either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->start_at = $run_at;
            $this->quantity = $quantity;
            $this->getEndat();
            $this->getAvailable();

            $this->block_thing = $this->thing;
        } else {
            $this->thing->log("checked the shift state: " . $shift_state . ".");
            // ... and decided there was already a shift running ...
            $this->start_at = "meep"; // We could probably find when the shift started running.
            $this->quantity = 0;
            $this->available = 0;
            $this->end_at = "meep";
        }

        $this->set();

        $this->thing->log('found a run_at and a quantity and made a Block.');
    }

    function blockTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $block_time = "x";
            return $block_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $block_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->block_time = $block_time;
        }

        return $block_time;
    }

    function getEndat()
    {
        if ($this->start_at != "x" and $this->quantity != "x") {
            $this->end_at = $this->thing->time(
                strtotime($this->start_at . " " . $this->quantity . " minutes")
            );
        } else {
            $this->end_at = "x";
        }

        return $this->end_at;
    }

    function getAvailable()
    {
        // This proto-typical block manages (available) time.

        // From start_at and current_time we can calculate elapsed_time.

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        //if ($this->current_time  < $this->start_at) {
        if (strtotime($this->current_time) < strtotime($this->start_at)) {
            $this->available =
                strtotime($this->end_at) - strtotime($this->start_at);
            // $this->available = $this->quantity;
        } else {
            $this->available =
                strtotime($this->end_at) - strtotime($this->current_time);
        }

        $this->thing->log(
            'identified ' . $this->available . ' resource units available.'
        );
    }

    function extractHeadcodes($input)
    {
        if (!isset($this->headcodes)) {
            $this->head_codes = [];
        }

        $pattern = "|\d[A-Za-z]{1}\d{2}|";

        preg_match_all($pattern, $input, $m);
        $arr = $m[0];

        return $arr;
    }

    function extractUuids($input)
    {
        if (!isset($this->uuids)) {
            $this->uuids = [];
        }

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);

        return $arr;
    }

    function readBlock()
    {
        $this->thing->log("read");

        //        $this->get();
//        return $this->available;
    }

    function addBlock()
    {
        $this->makeBlock();
        $this->get();
    }

    function setState($input)
    {
        switch ($input) {
            case "red":
                if (
                    $this->state == "green" or
                    $this->state == "yellow" or
                    $this->state == "yellow yellow" or
                    $this->state == "X"
                ) {
                    $this->state = "red";
                }
                break;

            case "green":
                if ($this->state == "red" or $this->state == "X") {
                    $this->state = "green";
                }

                break;
        }
    }

    function reset()
    {
        $this->thing->log("reset");

        $this->get();
        // Set elapsed time as 0 and state as stopped.
        $this->elapsed_time = 0;
        $this->createChoice('block', $this->node_list, 'red');
        /*
        $this->thing->Write( array("stopwatch", "refreshed_at"), $this->current_time);
        $this->thing->Write( array("stopwatch", "elapsed"), $this->elapsed_time);
*/
        $this->chooseChoice('start');

        $this->set();

        return $this->quantity_available;
    }

    function stop()
    {
        $this->thing->log("stop");
        $this->get();
        $this->chooseChoice('red');
        $this->set();

        return $this->quantity_available;
    }

    function start()
    {
        $this->thing->log("start");

        $this->get();

        if ($this->previous_state == 'stop') {
            $this->chooseChoice('start');
            $this->state = 'start';
            $this->set();
            return;
        }

        if ($this->previous_state == 'start') {
            $t =
                strtotime($this->current_time) - strtotime($this->refreshed_at);

            $this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->set();
            return;
        }

        $this->chooseChoice('start');
        $this->state = 'start';
        $this->set();
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->makeChoices();

        $available = $this->thing->human_time($this->available);

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
        $this->thing_report['help'] = 'This is a block.';
    }

    public function makeSMS()
    {
        $s = "RED";
        $sms_message =
            "BLOCK " .
            $this->index .
            " | " .
            round($this->available / 60, 0) .
            " minutes | " .
            $s;

        $sms_message .=
            " | from " .
            $this->blockTime($this->start_at) .
            " to " .
            $this->blockTime($this->end_at);
        $sms_message .= " | now " . $this->blockTime();
        $sms_message .= " | nuuid " . strtoupper($this->block_thing->nuuid);

        switch ($this->index) {
            case null:
                //          $sms_message =  "BLOCK | No block scheduled. | TEXT ADD BLOCK";
                $sms_message =
                    "BLOCK | No active block found. | TEXT BLOCK <four digit clock> <1-3 digit runtime>";
                break;

            case '1':
                $sms_message .=
                    " | TEXT BLOCK <four digit clock> <1-3 digit runtime>";
                //$sms_message .=  " | TEXT ADD BLOCK";
                break;
            case '2':
                $sms_message .= " | TEXT DROP BLOCK";
                //$sms_message .=  " | TEXT BLOCK";
                break;
            case '3':
                $sms_message .= " | TEXT BLOCK";
                break;
            case '4':
                $sms_message .= " | TEXT BLOCK";
                break;
            default:
                $sms_message .= " | TEXT ?";
                break;
        }

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    public function makeChoices()
    {
        if (!isset($this->state)) {
            $state = "block";
        } else {
            $state = $this->state;
        }

        $this->createChoice('channel', $this->node_list, $state);
        $this->choices = $this->linksChoice($state);
        $this->thing_report['choices'] = $this->choices;
    }

    public function makeWeb()
    {
        $web = "<b>Block Agent</b><br>";

        switch ($this->index) {
            case null:
                //          $sms_message =  "BLOCK | No block scheduled. | TEXT ADD BLOCK";
                $web .= "No active block found.";
                break;

            default:
                $web .= "from " . $this->blockTime($this->start_at) . " ";
                $web .= "to " . $this->blockTime($this->end_at) . "<br>";

                $web .= "quantity available: " . $this->quantity . "<br>";
                $web .= "available resource: " . $this->available . "<br>";
        }
        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
        $this->num_hits = 0;
        // Extract uuids into
        //        $uuids_in_input

        //        $headcodes_in_input

        $keywords = ['next', 'accept', 'clear', 'drop', 'add'];
        /*
        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }
*/
        $input = $this->input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $head_codes = $this->extractHeadcodes($input);

        if (count($head_codes) == 1) {
            $this->head_code = $head_codes[0];
            $this->thing->log('found a headcode ' . $this->head_code . '.');
        }

        $uuids = $this->extractUuids($input);

        $this->thing->log("counted " . count($uuids) . " uuids.");

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'block') {
                $this->readBlock();
                return;
            }
        }

        // Extract runat signal
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if (strlen($piece) == 4 and is_numeric($piece)) {
                $run_at = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            $this->run_at = $run_at;
            $this->num_hits += 1;
            $this->thing->log(
                'found a "run at" time of "' . $this->run_at . '".'
            );
        }

        // Extract runtime signal
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if ($piece == 'x' or $piece == 'z') {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }

            if (
                $piece == '5' or
                $piece == '10' or
                $piece == '15' or
                $piece == '20' or
                $piece == '25' or
                $piece == '30' or
                $piece == '45' or
                $piece == '55' or
                $piece == '60' or
                $piece == '75' or
                $piece == '90'
            ) {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 3 and is_numeric($piece)) {
                $this->quantity = $piece; //3 digits is a good indicator of a runtime in minutes
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 2 and is_numeric($piece)) {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 1 and is_numeric($piece)) {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }
        }

        if ($matches == 1) {
            $this->quantity = $piece;
            $this->num_hits += 1;
            //$this->thing->log('Agent "Block" found a "run time" of ' . $this->quantity .'.');
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'accept':
                            $this->acceptThing();
                            break;

                        case 'clear':
                            $this->clearThing();
                            break;

                        case 'start':
                            $this->start();
                            break;
                        case 'stop':
                            $this->stop();
                            break;
                        case 'reset':
                            $this->reset();
                            break;
                        case 'split':
                            $this->split();
                            break;

                        case 'next':
                            $this->thing->log("read subject nextblock");
                            $this->nextBlock();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextblock");
                            $this->dropBlock();
                            break;

                        case 'add':
                            //     //$this->thing->log("read subject nextblock");
                            $this->makeBlock();
                            break;

                        default:
                    }
                }
            }
        }

        // Check whether Block saw a run_at and/or run_time
        // Intent at this point is less clear.  But Block
        // might have extracted information in these variables.

        // $uuids, $head_codes, $this->run_at, $this->run_time

        if (
            count($uuids) == 1 and
            count($head_codes) == 1 and
            isset($this->run_at) and
            isset($this->quantity)
        ) {
            // Likely matching a head_code to a uuid.
        }

        if (isset($this->run_at) and isset($this->quantity)) {
            //$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
            // Likely matching a head_code to a uuid.
            $this->makeBlock($this->run_at, $this->quantity);
            return;
        }

        //    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // block to be created, or to override existing block.
        //        $this->thing->log('Agent "Block" found a run time.');

        //        $this->nextBlock();
        //        return;
        //    }

        // If all else fails try the discriminator.

        $input_agent = new Input($this->thing, "input");

        $discriminators = ['accept', 'clear'];

        $input_agent->aliases['accept'] = ['accept', 'add', '+'];
        $input_agent->aliases['clear'] = ['clear', 'drop', 'clr', '-'];

        $this->requested_state = $input_agent->discriminateInput(
            $haystack,
            $discriminators
        );

        switch ($this->requested_state) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'reset':
                $this->reset();
                break;
            case 'split':
                $this->split();
                break;
        }

        $this->readBlock();

        //return "Message not understood";

        return false;
    }
}
