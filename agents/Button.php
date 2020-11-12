<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Button extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test_slack_button = null;
        if (file_exists($this->resource_path . 'button/test-button.php')) {
            $this->test_slack_button = require $this->resource_path .
                'button/test-button.php';
        }
        $this->agent_name = "button";
        $this->keyword = "button";
        $this->test = "Development code"; // Always
        $this->node_list = ["off" => ["on" => ["off"]]];
    }

    function run()
    {
        $this->makeSMS();
    }

    function getBody()
    {
        $t = $this->test_slack_button;

        $bodies = json_decode($this->thing->thing->message0, true);
        $this->body = $bodies['slack'];

        $this->body = json_decode($t);
    }

    function set($requested_state = null)
    {
        // Refactor

        if ($requested_state == null) {
            $requested_state = $this->state;
        }

        $this->variables_thing->setVariable("state", $requested_state);
        $this->variables_thing->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->choice->Choose($requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }

    function get()
    {
        if (!isset($this->variables_thing)) {
            $this->variables_thing = new Variables(
                $this->thing,
                "variables button " . $this->from
            );
        }

        $this->previous_state = $this->variables_thing->getVariable("state");
        $this->refreshed_at = $this->variables_thing->getVariables(
            "refreshed_at"
        );

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );

        if (isset($this->requested_state)) {
            $this->thing->choice->Choose($this->requested_state);
            $this->state = $this->thing->choice->current_node;
        } else {
            $this->state = $this->previous_state;
            //            $this->requested_state = $this->state;
        }
    }

    function extractButtons($input = null)
    {
        $this->buttons = [];
        if ($input == null) {
            $input = $this->subject;
        }

        $input = strtolower($input);
        $breaks = count(explode("|", $input)) - 1;

        $words = count(explode(" ", $input));

        if ($words != 1) {
            $input = trim($input);
            $input = str_replace("button", "", $input);
            $input = trim($input);
            $input = str_replace("is", "", $input);
            $input = trim($input);
        }
        switch (true) {
            case $words > 1 and $breaks == 0:
                $buttons = explode(" ", strtolower($input));
                break;
            case $breaks >= 1:
                $buttons = explode("|", strtolower($input));
                break;
            case $words == 1:
                $buttons = [$input];
                break;
            default:
                $buttons = explode(" ", $input);
        }

        foreach ($buttons as $key => $button) {
            //  $words = explode(" ",$button);
            //  if ($words[0] == "button") {
            //      $button = str_replace("button", "", $button);
            //  }
            $button = trim($button);
            $this->buttons[] = $button;
        }
    }

    function test()
    {
        // Test corpus
        //        $this->subject = "button yes | no";
        //        $this->subject = "yes | no";
        //        $this->subject = "button is yes";
        $this->subject = "button is yes no";
        //        $this->subject = "button is yes | no";
        //        $this->subject = "orange brown";
        //        $this->subject = "button";
        return true;
        //        $this->getButtons();
        //        $this->extractButtons();
        //        return $this->state;
    }

    // Make buttons from a choice
    function getButtons()
    {
        if (!isset($this->choices) or $this->choices == null) {
            $this->makeChoices();
        }

        $this->words = $this->choices['words'];
        $this->links = $this->choices['links'];
        $this->url = $this->choices['url']; // nl version of links array
        $this->link = $this->choices['link'];

        $this->buttons = $this->choices['button'];
    }

    public function makeWeb()
    {
        if (!isset($this->words)) {
            $this->getButtons();
        }
        $w = "<b>Button Agent</b>";
        $w .= "<br><br>";
        $w .= "Made text and web buttons.";
        //        $w .= implode(" ",$this->words);
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

    public function makeSnippet()
    {
        if (!isset($this->words)) {
            $this->getButtons();
        }
        $w = "<div>";
        $w .= "Click this button to get the token.";
        $w .= "<br>";

        //$w .= $this->link;
        $w .= $this->buttons;
        $w .= "</div>";
        //        $w .= "<br><br>Copy-and-paste buttons below into your email.<br>";

        $this->snippet = $w;
        $this->thing_report['snippet'] = $w;
    }

    function makeSMS()
    {
        $s = "BUTTONS ARE " . implode(" | ", $this->buttons);
        //        $s .= " | nuuid " . substr($this->variables_thing->variables_thing->uuid,0,4);
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
        $this->node_list = ["button" => $this->buttons];
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "button"
        );
        $this->choices = $this->thing->choice->makeLinks('button');
        $this->thing_report['choices'] = $this->choices;
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            return $this->state;
        }

        $this->set($choice);

        return $this->state;
    }

    public function readSubject()
    {
        $input = $this->agent_input;

        if ($this->agent_input == "button") {
            $input = $this->subject;
        }

        $this->extractButtons($input);

        if ($this->agent_input != null) {
            //            $this->response = "Saw an agent instruction and didn't read further.";
            return;
        }

        //var_dump($this->input);
        //        $this->extractButtons($this->subject);
        return;

        $input = $this->input;
        //        $input = strtolower($this->subject);
        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                return;
            }
        }

        return "Message not understood";
    }
}
