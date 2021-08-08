<?php
/**
 * Pick.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Pick extends Agent
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

        $this->node_list = ["pick" => ["pick"]];

        $this->thing_report["info"] = "This picks a token.";
        $this->thing_report['help'] = 'Try PICK ROBERT DAVID MARK.';
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
            "pick",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["pick", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->last_result = $this->thing->json->readVariable([
            "pick",
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

        $this->thing_report["info"] = "This picks a token.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] = 'Try PICK.';
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
            "pick"
        );

        $choices = $this->thing->choice->makeLinks('pick');
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    function makeWeb()
    {
        $web = "";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = ["pick" => ["heads", "tails"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');

        $text = $this->textPick($this->result);

        if ($text === true) {
            $web = "Did not pick.<br>";
        } else {
            $web .= $text . "<br>";
        }

        $web .= "<br>";

        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "Picked about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        $temp_sms_message = "";

        $text = $this->textPick($this->result);
        $sms = "PICKED " . ucwords($this->textPick($this->result)) . ".";

        if ($text === true) {
            $sms = "PICK | Did not pick a token.";
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function textPick($pick = null)
    {
        if ($pick == null) {
            return true;
        }

        $this->token = $this->tokens[$pick - 1];

        $text = $this->token;
        return $text;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = "Picked the following for you.<br>";

        $this->thing_report['message'] = $message;
    }

    /**
     *
     */
    function set()
    {
        if ($this->last_result == false) {
            $this->thing->json->writeVariable(
                ["pick", "result"],
                $this->result
            );

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
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
            if ($this->agent_input != null) {
                $input = strtolower($this->agent_input);
            } else {
                $input = strtolower($this->subject);

                $temp_thing = new Emoji($this->thing, "emoji");
                $input = $temp_thing->translated_input;
            }

            $this->result = rand(1, 2);
        } else {
            $input = strtolower($this->input);
            $this->result = $this->last_result;
        }

        $filtered_input = $this->assert($input);

// devstack
// Brilltagger


        $tokens = explode(" ", $filtered_input);

        $this->tokens = $tokens;

        $count = count($tokens);
        $this->result = rand(1, $count);

    }
}
