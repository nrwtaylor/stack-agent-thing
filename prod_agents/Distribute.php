<?php
/**
 * Distribute.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Distribute extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->width = 125;
        $this->height = $this->width;

        $this->node_list = ["distribute" => ["distribute"]];

        $this->thing_report["info"] = "This distributes tokens.";
        $this->thing_report['help'] = 'Try DISTRIBUTE 4 d1 Kitsilano 10 Strathcona 9 Marpole 20';
.';
    }

    /**
     *
     */
    public function get()
    {
        $this->current_time = $this->thing->time();

        $time_string = $this->thing->Read([
            "distribute",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["distribute", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->last_result = $this->thing->Read([
            "distribute",
            "result",
        ]);
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This distributes a token.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] = 'Try DISTRIBUTE.';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "distribute"
        );

        $choices = $this->thing->choice->makeLinks('distribute');
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    function makeWeb()
    {
        $web = "";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = ["distribute" => ["heads", "tails"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');

        //$text = $this->textDistribute($this->result);
$text = "merp";
        if ($text === true) {
            $web = "Did not distribute.<br>";
        } else {
            $web .= $text . "<br>";
        }

        $web .= "<br>";

        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "Distributed about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        //$temp_sms_message = "";

        //$text = $this->textDistribute($this->result);

        //$sms = "DISTRIBUTED " . ucwords($this->textDistribute($this->result)) . ".";

        $sms = "DISTRIBUTED | ";

        //if ($text === true) {
        //    $sms = "DISTRIBUTED | Did not distribute a token.";
        //}

        $sms .= $this->distribute_message;

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function textDistribute($distribution = null)
    {
        if ($distribution == null) {
            return true;
        }
        $text = "";
        foreach($distribution as $key=>$value) {
            $text .= $key ." " . $value ." ";
        }
        $text = trim($text);
        return $text;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = "Distributed the following for you.<br>";

        $this->thing_report['message'] = $message;
    }

    /**
     *
     */
    function set()
    {
        if ($this->last_result == false) {
//            $this->thing->Write(
//                ["distribute", "result"],
//                $this->result
//            );

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
            );
        }
    }

    public function randomDistribute($distribution, $pairs, $d)
    {
        // Take the amount. Flip a coin (or d).
        // Loop through all the sites until amount is exhausted.
        $remaining_distribution = $distribution;
        $previous_remaining_distribution = $distribution;

        $i = 0;
        $remaining = $pairs;
        $flag_end = false;
        $logic_text = "";
        while ($flag_end !== true) {

            // Pick a random key value pair.

            $key = array_rand($pairs);
            $value = $pairs[$key];

            // Use the provided dice to generate a hit on the selected pair.
                $distribution = rand(1, $d);

                if ($remaining[$key] === 0) {continue;}

                if ($distribution > $remaining[$key]) {
                    $distribution = $remaining[$key];
                }

                if ($remaining_distribution < $distribution) {
                    $distribution = $remaining_distribution;
                }

                $remaining[$key] = $remaining[$key] - $distribution;
                $remaining_distribution =
                    $remaining_distribution - $distribution;

                $logic_text .= "Took " . $distribution . " from " . $key . ". ";
                $logic_text .= $remaining_distribution . " left. ";


// Repeat until not able to distribute more.
                // No distribution made. End.
                if (
                    $previous_remaining_distribution ===
                        $remaining_distribution or
                    $remaining_distribution === 0
                ) {
                    $logic_text .= "Cannot distribute more. ";
                    $logic_text .= $remaining_distribution . " undistributed. ";
                    $flag_end = true;
                    break;
                }

                $previous_remaining_distribution = $remaining_distribution;
        }

        $this->logic_text = $logic_text;
        return $remaining;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $filtered_input = $this->assert($this->input, false);

        // devstack
        // Brilltagger

        $tokens = explode(" ", $filtered_input);

        $this->d = 2;
        foreach ($tokens as $i => $token) {
            if (substr($token, 0, 1) === 'd') {
                $num = ltrim($token, 'd');
                if (is_numeric($num)) {
                    $this->d = $num;
                    unset($tokens[$i]);
                    break;
                }
            }
        }

        if (!is_numeric($tokens[0])) {
            $this->response .= "No amount to distribute provided. ";
        } else {
            $this->amount = $tokens[0];
            unset($tokens[0]);
        }

        // Reindex tokens
        $tokens = array_values($tokens);

        $pairs = [];
        $n = 0;
	$sum = 0;
        foreach ($tokens as $i => $token) {
            if ($i / 2 === intval($i / 2)) {
                $key = $token;
            } else {
                $value = $token;
		$sum = $sum + $value;
                $pairs[$key] = $value;
                $n += 1;
            }
        }
	$this->available = $sum;

        $remaining = $this->randomDistribute($this->amount, $pairs, $this->d);

        $text = "";
	$remaining_tokens = 0;
        foreach ($remaining as $key => $value) {
	    $remaining_tokens += $value;
        }

        $this->distribute_message = $this->available . " tokens seen. " .
            "Asked to distribute " . $this->amount . " tokens. " . $remaining_tokens. " tokens undistributed. Remaining amounts are " . $this->textDistribute($remaining). ". ";

//        $this->tokens = $tokens;

//        $count = count($tokens);
//        $this->result = rand(1, $count);

    }
}
