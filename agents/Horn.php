<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Horn extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->keyword = "horn";

        $this->test = "Development code"; // Always

        // Set up default horn settings
        $this->verbosity = 1;
        $this->requested_state = null;
        $this->default_state = "green";
        $this->node_list = ["green" => ["red" => ["green"]]];

        // Get some stuff from the stack which will be helpful.

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/horn';

        //$this->refreshed_at = null;

        //$this->current_time = $this->thing->time();
    }

    function set($requested_state = null)
    {
        if ($requested_state == null) {
            if (!isset($this->requested_state)) {
                $this->requested_state = "green"; // If not sure, show green.

                if (isset($this->state)) {
                    $this->requested_state = $this->state;
                }
            }

            $requested_state = $this->requested_state;
        }

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        $this->horn->setVariable("state", $this->state);
        $this->horn->setVariable("refreshed_at", $this->current_time);

    }

    function isHorn($horn = null)
    {
        // Validates whether the Horn is on or off.
        // Nothing else is allowed.

        if ($horn == null) {
            if (!isset($this->state)) {
                $this->state = "on";
            }

            $horn = $this->state;
        }

        if (
            $horn == "on" or
            $horn == "off"
        ) {
            return false;
        }

        return true;
    }

    public function get()
    {

        // Train variable so show headcode.
        $this->thing->json->setField("variables");
        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);

        // $horn_variable_name = "_" . $this->head_code;
        $horn_variable_name = "";

        // Get the current Identities horn
        $this->horn = new Variables(
            $this->thing,
            "variables horn" . $horn_variable_name . " " . $this->from
        );

        // get gets the state of the Horn the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->horn->getVariable("state");
        $this->refreshed_at = $this->horn->getVariable("refreshed_at");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isHorn($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            if (!isset($this->state)) {
                $this->response .= "Did not find an existing horn. ";
                $this->state = $this->default_state;
            }
            $choice = $this->state;
        }

        if (!isset($this->state)) {
            $this->state = "X";
        }
        $this->previous_state = $this->state;
        $this->state = $choice;

        $this->response .= "Selected horn " . $this->state . ". ";

        return $this->state;
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->state
        );

        $choices = $this->horn->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<b>' . ucwords($this->agent_name) . ' Agent</b><br><p>';
        $web .= "<p>";
        $web .= '<a href="' . $link . '">';

        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= $this->sms_message;
        $this->thing_report['web'] = $web;
    }

    public function readHorn()
    {
        $state_text = "X";
        if ((isset($this->state)) and ($this->state !== false))  {
            $state_text = $this->state;
        }

        $this->response .= "Saw horn is " . $state_text . ". ";
    }

    public function respondResponse()
    {
        // At this point state is set
        //        $this->set($this->state);

        // Thing actions
        if ($this->state == "inside nest") {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $this->thing_report['email'] = $this->message;

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeHelp()
    {
        if ($this->state == "on") {
            $this->thing_report['help'] =
                'This Horn is either ON or OFF. ON means it can be heard.';
        }

        if ($this->state == "off") {
            $this->thing_report['help'] =
                'This Horn is either OFF or ON. OFF means it can not be heard.';
        }
    }

    function makeTXT()
    {
        $txt = 'This is HORN ' . $this->horn->nuuid . ' for '. $this->head_code .  '. ';
        $txt .= 'Horn is ' . strtoupper($this->state) . ". ";
        if ($this->verbosity >= 5) {
            $txt .=
                'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $horn_state = "X";
        if ($this->state != false) {
            $horn_state = $this->state;
        }

        $headcode_text = strtoupper($this->head_code);
        //$headcode_text = "XXXX";

        $sms_message =
            "HORN " . $headcode_text . " IS " . strtoupper($horn_state);
        if ($this->verbosity > 6) {
            $sms_message .=
                " | previous state " . strtoupper($this->previous_state);
            $sms_message .= " state " . strtoupper($this->state);
            $sms_message .=
                " requested state " . strtoupper($this->requested_state);
            $sms_message .=
                " current node " .
                strtoupper($this->base_thing->choice->current_node);
        }
        if ($this->verbosity > 2) {
            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        }
        if ($this->verbosity >= 9) {
            $sms_message .=
                " | base nuuid " . strtoupper($this->horn->thing->nuuid);
        }

        if ($this->verbosity > 0) {
            $sms_message .= " | nuuid " . $this->horn->nuuid;
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }

            if ($this->state == "green") {
                $sms_message .= ' | MESSAGE Red';
            }
        }

        $sms_message .= " " . $this->response;

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    function makeMessage()
    {
        $message =
            'This is a HORN.  The horn is ' .
            trim(strtoupper($this->state)) .
            ". ";

        if ($this->state == 'off') {
            $message .= 'No horn sounding. ';
        }

        if ($this->state == 'on') {
            $message .= 'Horn is sounding. ';
        }

        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
    }

    public function makeImage()
    {
        $this->image = imagecreatetruecolor(200, 125);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->horn_on = imagecolorallocate($this->image, 231, 0, 0); // red = sounding
        $this->horn_off = imagecolorallocate($this->image, 0, 129, 31); // green = quiet

        // Draw a white rectangle
        if (!isset($this->state) or $this->state == false) {
            $color = $this->grey;
        } else {
            if (isset($this->{$this->state})) {
                $color = $this->{$this->state};
            } elseif (isset($this->{'horn_' . $this->state})) {
                $color = $this->{'horn_' . $this->state};
            }
        }

        imagefilledrectangle($this->image, 0, 0, 200, 125, $color);

        $light_text_list = ["on"];
        if (in_array($this->state, $light_text_list)) {
            $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        } else {
            $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        }

        // Write the string at the top left
        imagestring($this->image, 2, 150, 100, $this->horn->nuuid, $textcolor);
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }
        $agent = new Png($this->thing, "png");

        //$this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
    }

    public function readSubject()
    {
        $keywords = [
            'horn',
            'on',
            'off',
            'sound',
            'quiet',
        ];

        if (isset($this->agent_input)) {
            $input = $this->agent_input;
        } else {
            $input = strtolower($this->subject);
        }

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                //$this->get();
                $this->response .= "Got the horn. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'sound':
                        case 'on':
                            $this->thing->log(
                                $this->agent_prefix .
                                    'received request for HORN ON.',
                                "INFORMATION"
                            );
                            $this->selectChoice('on');
                            return;
                        case 'quiet':
                        case 'off':
                            $this->selectChoice('off');
                            return;

                        case 'next':

                        default:
                    }
                }
            }
        }

        $this->readHorn();

    }
}
