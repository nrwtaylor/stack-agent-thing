<?php
/**
 * Flip.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Flip extends Agent
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

        $this->node_list = ["flip" => ["coin", "flip", "card"]];

        $this->thing_report["info"] = "This flips a coin.";
        $this->thing_report['help'] = 'Try FLIP HEADS. Or FLIP TAILS.';
    }

    /**
     *
     */
    public function get()
    {
        $this->current_time = $this->thing->time();

        // Borrow this from iching
        $time_string = $this->thing->Read([
            "flip",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["flip", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->last_result = $this->thing->Read([
            "flip",
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

        $this->thing_report["info"] = "This flips a coin.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] = 'Try FLIP HEADS. Or FLIP TAILS.';
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
            "flip"
        );

        $choices = $this->thing->choice->makeLinks('flip');
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    function makeWeb()
    {
        $web = "";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = ["flip" => ["heads", "tails"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');

        $text = $this->textFlip($this->result);

        if ($text === true) {
            $web = "Did not flip a coin.<br>";
        } else {
            $web .= $text . "<br>";
        }

        $web .= "<br>";

        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "Flipped about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        $temp_sms_message = "";

        $text = $this->textFlip($this->result);
        $sms = "FLIPPED " . ucwords($this->textFlip($this->result)) . ".";

        if ($text === true) {
            $sms = "FLIP | Did not flip a coin.";
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function textFlip($flip = null)
    {
        if ($flip == null) {
            return true;
        }
        $text = true;
        if ($this->result == 1) {
            $text = "heads";
            return $text;
        } elseif ($this->result == 2) {
            $text = "tails";
        }

        return $text;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = "Flipped the following for you.<br>";

        $this->thing_report['message'] = $message;
    }

    /**
     *
     */
    function set()
    {
        if ($this->last_result == false) {
            $this->thing->Write(
                ["flip", "result"],
                $this->result
            );
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        if ($this->last_result == false) {
            $this->result = rand(1, 2);
        } else {
            $this->result = $this->last_result;
        }

        $input = strtolower($this->input);
        $filtered_input = $this->assert($input);

        if ($filtered_input == 'heads' or $filtered_input == 'tails') {
            if ($this->textFlip($this->result) == $filtered_input) {
                $this->response .= "You win the toss.";
            } else {
                $this->response .= "You lose the toss.";
            }
            return;
        }
    }
}
