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

        $this->thing_report["info"] = "This distributes a token.";
        $this->thing_report['help'] = 'Try DISTRIBUTE ROBERT DAVID MARK.';
    }

    /**
     *
     */
    public function get()
    {
        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "distribute",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["distribute", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->last_result = $this->thing->json->readVariable([
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

        $text = $this->textDistribute($this->result);

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

    public function textDistribute($distribute = null)
    {
        if ($distribute == null) {
            return true;
        }

        $this->token = $this->tokens[$distribute - 1];

        $text = $this->token;
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
            $this->thing->json->writeVariable(
                ["distribute", "result"],
                $this->result
            );

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
        while ($flag_end !== true) {
            foreach ($pairs as $key => $value) {
                //if (!isset($remaining[$key])) {$remaining[$key] = $pairs[$key];}
                $distribution = rand(1, $d);

                if ($distribution > $remaining[$key]) {
                    $distribution = $remaining[$key];
                    $remaining[$key] = 0;
                } else {
                    $remaining[$key] = $remaining[$key] - $distribution;
                }

                if ($remaining_distribution < $distribution) {
                    $distribution = $remaining_distribution;
                    $remaining_distribution = 0;
                } else {
                    $remaining_distribution =
                        $remaining_distribution - $distribution;
                }

                // No distribution made. End.
                if (
                    $previous_remaining_distribution ===
                        $remaining_distribution or
                    $remaining_distribution === 0
                ) {
                    $flag_end = true;
                    break;
                }

                $previous_remaining_distribution = $remaining_distribution;
            }
        }

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
        foreach ($tokens as $i => $token) {
            if ($i / 2 === intval($i / 2)) {
                $key = $token;
            } else {
                $value = $token;
                $pairs[$key] = $value;
                $n += 1;
            }
        }

        $remaining = $this->randomDistribute($this->amount, $pairs, $this->d);

        $text = "";
        foreach ($remaining as $key => $value) {
            $text .= $remaining[$key] . "/" . $pairs[$key] . " " . $key . " ";
        }
        $this->distribute_message =
            "Distributed " . $this->amount . " tokens. Remaining amounts are " . $text . ". ";

        $this->tokens = $tokens;

        $count = count($tokens);
        $this->result = rand(1, $count);
    }
}
