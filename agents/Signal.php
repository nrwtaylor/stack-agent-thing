<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Signal extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->default_state = "X";

        $this->keyword = "signal";

        $this->test = "Development code"; // Always

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        // Get the current identities uuid.
        $default_signal_id = new Identity($this->thing, "identity");
        $this->default_signal_id = $default_signal_id->uuid;

        // Set up default signal settings
        $this->verbosity = 1;
        $this->requested_state = null;
        $this->default_state = "green";
        $this->node_list = ["green" => ["red" => ["green"]]];

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/signal';
        $this->refreshed_at = null;

        $this->current_time = $this->thing->time();

        // devstack
        $this->associations = new Associations($this->thing, $this->subject);

        $this->thing_report['help'] =
            'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. Text SIGNAL DOUBLE YELLOW.';
        $this->thing_report['info'] =
            'DOUBLE YELLOW means keep going. RED means stop.';
    }

    public function respondResponse()
    {
        $this->makeHelp();
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        //return $this->thing_report;
    }

    function set()
    {
        if (isset($this->signal_thing->uuid)) {
            $this->associations->setAssociation($this->signal_thing->uuid);
        }

        if ($this->channel_name == 'web') {
            $this->response .= "Detected web channel. ";
            // Do not effect a state change for web views.
            return;
        }

        $this->thing->json->writeVariable(
            ["signal", "state"],
            $this->signal['state']
        );
        $this->thing->json->writeVariable(
            ["signal", "refreshed_at"],
            $this->current_time
        );
        $this->thing->json->writeVariable(["signal", "text"], "X");

        $this->setSignal();

        $this->thing->log(
            $this->agent_prefix . 'set Signal to ' . $this->state,
            "INFORMATION"
        );
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    function isSignal($signal = null)
    {
        // Validates whether the Signal is green or red.
        // Nothing else is allowed.

        if ($signal == null) {
            if (!isset($this->state)) {
                $this->state = "red";
            }

            $signal = $this->state;
        }

        if (
            $signal == "red" or
            $signal == "green" or
            $signal == "yellow" or
            $signal == "double yellow"
        ) {
            return false;
        }

        return true;
    }

    public function get()
    {
        //        if (!isset($this->signal_thing)) {
        //            $this->signal_thing = $this->thing;
        //        }

        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        $this->response .= "Saw channel is " . $this->channel_name . ". ";
        $this->getSignal();

        $this->thing->log(
            $this->agent_prefix .
                'got a ' .
                strtoupper($this->signal['state']) .
                ' SIGNAL.',
            "INFORMATION"
        );
    }

    public function run()
    {
        // Get this too. Or put it in the run loop.
        $this->helpSignal($this->signal['state']);
        $this->infoSignal($this->signal['state']);
    }

    function helpSignal($text = null)
    {
        $a = $this->associationsSignal();

        $next_signal_id = "X";
        if (isset($a[0]['id'])) {
            $next_signal_id = $a[0]['id'];
        }
        $next_signal_text = "Next SIGNAL " . $next_signal_id . ".";

        if (strtolower($text) == "x") {
            $state = "X";
            $help = "Treat this signal as if it is broken. Text CONTROL.";
        }

        if (strtolower($text) == "red") {
            $state = "red";
            $help = "This Signal is RED. Text SIGNAL. Wait for it to change.";
        }

        if (strtolower($text) == "green") {
            $state = "green";
            $help =
                "This Signal is GREEN. Keep going. Don't expect to stop. Text SIGNAL.";
        }

        if (strtolower($text) == "yellow") {
            $state = "yellow";
            $help =
                "This Signal is YELLOW. Expect to stop at the next one. " .
                $next_signal_text;
        }

        if (strtolower($text) == "double yellow") {
            $state = "double yellow";
            $help =
                "This Signal is DOUBLE YELLOW. Expect to stop after the next one. " .
                $next_signal_text;
        }

        $this->thing_report['help'] = $help;

        return $help;
    }

    function infoSignal($text = null)
    {
        if (strtolower($text) == "x") {
            $state = "X";
            $info = "Signal is OFF. Or broken.";
        }

        if (strtolower($text) == "red") {
            $state = "red";
            $info = "This Signal is RED.";
        }
        if (strtolower($text) == "green") {
            $state = "green";
            $info = "This Signal is GREEN.";
        }
        if (strtolower($text) == "yellow") {
            $state = "yellow";
            $info = "This Signal is YELLOW.";
        }
        if (strtolower($text) == "double yellow") {
            $state = "double yellow";
            $info = "This Signal is DOUBLE YELLOW.";
        }
        $this->thing_report['info'] = $info;

        return $info;
    }

    function makeState($text)
    {
        $state = null;

        if (strtolower($text) == "x") {
            $state = "X";
            $this->text = "signal change";
        }

        if (strtolower($text) == "red") {
            $state = "red";
            $this->text = "signal change";
        }
        if (strtolower($text) == "green") {
            $state = "green";
            $this->text = "signal change";
        }
        if (strtolower($text) == "yellow") {
            $state = "yellow";
            $this->text = "signal change";
        }
        if (strtolower($text) == "double yellow") {
            $state = "double yellow";
            $this->text = "signal change";
        }
        $this->signal['state'] = $state;
        $this->state = $state;
        if ($state != null) {
            $this->response = "Selected " . $this->state . " signal.";
        }
    }

    function setSignal($text = null)
    {
        $this->signal_thing->json->writeVariable(
            ["signal", "state"],
            $this->signal['state']
        );
        $this->signal_thing->json->writeVariable(
            ["signal", "refreshed_at"],
            $this->current_time
        );
        $this->signal_thing->json->writeVariable(
            ["signal", "text"],
            "signal post"
        );

        $this->associations->setAssociation($this->signal_thing->uuid);
    }

    function makeSignal()
    {
        if (isset($this->state) and $this->state != "X") {
            $this->signal['state'] = $this->state;
        }
    }

    function newSignal()
    {
        $this->signal_thing = $this->thing;
        $this->signal_thing->state = "X";
        $this->signal_thing->text = "signal post";

        $this->signal['state'] = "X";
        $this->signal['id'] = $this->signal_thing->uuid;
        $this->signal['text'] = "signal post";
    }

    function getSignalbyUuid($uuid)
    {
        if ($this->channel_name == 'web') {
            $id = $this->idSignal($uuid);
            $this->getSignalbyId($id);
            return;
        }

        $this->signal_thing = new Thing($uuid);
        $this->signal = $this->thing->json->jsontoArray(
            $this->signal_thing->thing->variables
        )['signal'];

        $this->state = $this->signal['state'];
        $this->signal_id = $this->signal_thing->uuid;
        $this->signal['id'] = $this->signal_id;
    }

    function getSignalbyNuuid($nuuid)
    {
        if (!isset($this->signals)) {
            $this->getSignals();
        }
        $matched_uuids = [];
        foreach ($this->signals as $i => $signal) {
            if ($signal['text'] != "signal post") {
                continue;
            }

            if (substr($signal['id'], 0, 4) == $nuuid) {
                $matched_uuids[] = $signal['id'];
            }
        }

        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->response .=
            "Found signal match " . $this->idSignal($uuid) . ". ";

        $this->signal_thing = new Thing($uuid);
        $this->signal = $this->thing->json->jsontoArray(
            $this->signal_thing->thing->variables
        )['signal'];

        $this->state = $this->signal['state'];
        $this->signal_id = $this->signal_thing->uuid;
        $this->signal['id'] = $this->signal_id;
    }

    function getSignalbyId($id)
    {
        if (!isset($this->signals)) {
            $this->getSignals();
        }
        $matched_uuids = [];
        foreach ($this->signals as $i => $signal) {
            if ($signal['text'] != "signal post") {
                continue;
            }

            if (substr($this->idSignal($signal['id']), 0, 4) == $id) {
                $matched_uuids[] = $signal['id'];
            }
        }

        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->response .=
            "Found signal match " . $this->idSignal($uuid) . ". ";

        $this->signal_thing = new Thing($uuid);
        $this->signal = $this->thing->json->jsontoArray(
            $this->signal_thing->thing->variables
        )['signal'];

        $this->state = $this->signal['state'];
        $this->signal_id = $this->signal_thing->uuid;
        $this->signal['id'] = $this->signal_id;
    }

    function idSignal($text = null)
    {
        $signal_id = $text;
        if ($text == null) {
            if (isset($this->signal_thing->uuid)) {
                $signal_id = $this->signal_thing->uuid;
            }
        }

        $t = hash('sha256', $signal_id);
        $t = substr($t, 0, 4);
        return $t;
    }

    public function getSignal()
    {
        if (
            isset(
                $this->thing->json->jsontoArray($this->thing->thing->variables)[
                    'signal'
                ]
            )
        ) {
            // First is there a signal in this thing.
            $this->signal = $this->thing->json->jsontoArray(
                $this->thing->thing->variables
            )['signal'];

            $signal_id = "X";
            if (isset($this->signal['id'])) {
                $this->signal_id = $this->signal['id'];
            }

            //return;
        }

        if (!isset($this->signals)) {
            $this->getSignals();
        }

        foreach ($this->signals as $i => $signal) {
            if ($signal['text'] == "signal post") {
                // Apply the latest known signal state.

                $this->signal = $signal;

                $this->signal_id = $signal['id'];
                //       $this->signal_thing = new Thing($this->signal_id);

                $this->getSignalbyUuid($this->signal_id);

                return $this->signal_thing;
            }
        }

        // No signal post found.
        $this->signal = [
            "id" => $this->uuid,
            "state" => "X",
            "text" => "signal post",
        ];
        $this->state = "X";
        $this->text = "signal post";
        $this->signal_id = $this->uuid;
        $this->signal_thing = $this->thing;

        // This signal is the latest signal we have.
        //$this->signal = $this->signals[0];

        return $this->signal_thing;
    }

    function getSignals()
    {
        $this->signalid_list = [];
        $this->signals = [];

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, 'signal');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log(
            'Agent "Signal" found ' .
                count($findagent_thing->thing_report['things']) .
                " signal Things."
        );

        if (!$this->is_positive_integer($count)) {
            // No signals found
        } else {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];

                $associations_json = $thing_object['associations'];
                $associations = $this->thing->json->jsontoArray(
                    $associations_json
                );

                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables['signal'])) {
                    $refreshed_at = "X";
                    $signal_id = $uuid;

                    if (isset($variables['signal']['refreshed_at'])) {
                        $refreshed_at = $variables['signal']['refreshed_at'];
                    }

                    $text = "X";
                    if (isset($variables['signal']['text'])) {
                        $text = $variables['signal']['text'];
                    }

                    $state = "X";
                    if (isset($variables['signal']['state'])) {
                        $state = $variables['signal']['state'];
                    }

                    $this->signals[] = [
                        "id" => $signal_id,
                        "state" => $state,
                        "associations" => $associations,
                        "text" => $text,
                        "refreshed_at" => $refreshed_at,
                    ];
                    $this->signalid_list[] = $signal_id;
                }
            }
        }

        $refreshed_at = [];
        foreach ($this->signals as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->signals);

        return [$this->signalid_list, $this->signals];
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    function makeWeb()
    {
        if (!isset($this->signals)) {
            $this->getSignals();
        }

        if (isset($this->signal_thing->uuid)) {
            $flag = false;
            if (isset($this->signals) and is_array($this->signals)) {
                $signal_text = "";
                foreach ($this->signals as $i => $signal) {
                    if ($signal['text'] != "signal post") {
                        continue;
                    }

                    if ($this->signal_thing->uuid == $signal['id']) {
                        $flag = true;
                    }
                }
            }

            if ($flag === false) {
                //$web = "Merp.";
                $web = null;
                $this->thing_report['web'] = $web;
                return;
            }
        }

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
        $web = "";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br><p>';
        $web .= "<p>";
        $web .= '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/signal.png">';
        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";

        $state_text = "X";
        if ($this->signal['state'] != null) {
            $state_text = strtoupper($this->signal['state']);
        }

        $web .= "SIGNAL IS " . $state_text . "<br>";

        $web .= "SIGNAL ID " . strtoupper($this->idSignal()) . "<br>";

        $web .= "<p>";
        //$web .= "Refreshed at " . strtoupper($this->signal['refreshed_at']) . "<br>";

        if (!isset($this->signals)) {
            $this->getSignals();
        }

        if (isset($this->signals) and is_array($this->signals)) {
            $signal_text = "";

            foreach ($this->signals as $i => $signal) {
                if ($signal['text'] == "signal post") {
                    $signal['hash'] = $this->idSignal($signal['id']);
                    $signals[] = $signal;

                    //$signal_text .= strtoupper($this->idSignal($signal['id'])) . " " . strtoupper($signal['state']) . "<br>";
                }
            }

            //$signals = $this->signals;

            $refreshed_at = [];
            foreach ($signals as $key => $row) {
                $refreshed_at[$key] = $row['hash'];
            }
            array_multisort($refreshed_at, SORT_DESC, $signals);

            foreach ($signals as $i => $signal) {
                if ($signal['text'] == "signal post") {
                    //$signal['hash'] = $this->idSignal($signal['id']);
                    //$this->signals[$i] = $signal;

                    $signal_text .=
                        strtoupper($signal['hash']) .
                        " " .
                        strtoupper($signal['state']) .
                        "<br>";
                }
            }

            $web .= "<b>SIGNALS FOUND</b><br><p>" . $signal_text;
        }

        if (isset($this->associations->thing->thing->associations)) {
            $web .= "<p>";
            $association_text = "";

            $associations_array = json_decode(
                $this->associations->thing->thing->associations,
                true
            );
            foreach ($associations_array as $agent_name => $associations) {
                foreach ($associations as $i => $association_uuid) {
                    $association_text .=
                        strtoupper(
                            $this->idSignal($this->idSignal($association_uuid))
                        ) .
                        " " .
                        $agent_name .
                        "<br>";
                }
            }

            $web .= "<b>ASSOCIATIONS FOUND</b><br><p>" . $association_text;
        }

        $this->thing_report['web'] = $web;
    }

    function makeLink()
    {
        $this->link =
            $this->web_prefix . 'thing/' . $this->signal['id'] . '/signal';

        $this->thing_report['link'] = $this->link;
    }

    public function associationsSignal()
    {
        $association_array = [];
        if (isset($this->associations->thing->thing->associations)) {
            //$web .= "<p>";
            //$association_text = "";

            $associations_array = json_decode(
                $this->associations->thing->thing->associations,
                true
            );
            foreach ($associations_array as $agent_name => $associations) {
                foreach ($associations as $i => $association_uuid) {
                    $association_array[] = [
                        'text' =>
                            strtoupper(
                                $this->idSignal(
                                    $this->idSignal($association_uuid)
                                )
                            ) .
                            " " .
                            $agent_name,
                        "id" => $this->idSignal(
                            $this->idSignal($association_uuid)
                        ),
                        "agent_name" => $agent_name,
                    ];
                }
            }
        }
        $this->associations = $association_array;
        return $association_array;
    }

    function makeHelp()
    {
        // Get the latest Help signal.
        if (!isset($this->thing_report['help'])) {
            $this->helpSignal($this->signal['state']);
        }

        return;
        if ($this->signal['state'] == "X") {
            $this->thing_report['help'] = 'This Signal is not set.';
        }

        if ($this->signal['state'] == "green") {
            $this->thing_report['help'] =
                'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. GREEN means available.';
        }

        if ($this->signal['state'] == "red") {
            $this->thing_report['help'] =
                'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. RED means stop.';
        }

        if ($this->signal['state'] == "red") {
            $this->thing_report['help'] =
                'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. YELLOW means plan to stop.';
        }

        if ($this->signal['state'] == "red") {
            $this->thing_report['help'] =
                'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. DOUBLE YELLOW means keep going.';
        }
    }

    function makeTXT()
    {
        $signal_id = 'X';
        if (isset($this->signal['id'])) {
            $signal_id = $this->signal['id'];
        }
        $txt = 'This is SIGNAL POLE ' . $signal_id . '. ';
        $txt .=
            'There is a ' . strtoupper($this->signal['state']) . " SIGNAL. ";
        if ($this->verbosity >= 5) {
            $txt .=
                'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $signal_id = "X";
        if (isset($this->signal_thing->uuid)) {
            $signal_id = $this->signal_thing->uuid;
            $signal_nuuid = strtoupper(substr($signal_id, 0, 4));
            $signal_id = $this->idSignal($signal_id);
        }

        $state_text = "X";
        if ($this->signal['state'] != null) {
            $state_text = strtoupper($this->signal['state']);
        }

        $sms_message =
            "SIGNAL " . strtoupper($signal_id) . " IS " . $state_text;

        $sms_message .= " | ";

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

        if ($this->verbosity > 0) {
            //    $sms_message .= " | signal id " . strtoupper($this->signal['id']);
        }

        if (strtolower($this->input) == "signals") {
            $sms_message .= " Active signals: ";
            foreach ($this->signals as $i => $signal) {
                if ($signal['text'] == "signal post") {
                    $sms_message .= $this->idSignal($signal['id']) . " ";
                }
            }
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }

            if ($this->state == "green") {
                $sms_message .= ' | MESSAGE Red';
            }
        }
        $sms_message .= "" . trim($this->response);

        /*
        if ($this->verbosity > 0) {
            $sms_message .= " | " . $this->link . "";
            $sms_message .= " Text HELP";
        }
*/

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    function makeMessage()
    {
        $message =
            'This is a SIGNAL POLE.  The signal is a ' .
            trim(strtoupper($this->signal['state'])) .
            " SIGNAL. ";

        if ($this->signal['state'] == 'red') {
            $message .= 'It is a BAD time at the moment. ';
        }

        if ($this->signal['state'] == 'green') {
            $message .= 'It is a GOOD time now. ';
        }

        //$test_message .= 'And the signal is ' . strtoupper($this->state) . ".";

        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
    }

    public function makeImage()
    {
        $state = $this->signal['state'];

        // Create a 55x30 image

        $this->image = imagecreatetruecolor(60, 125);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->dark_grey = imagecolorallocate($this->image, 64, 64, 64);

        $this->red = imagecolorallocate($this->image, 231, 0, 0);

        $this->yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->green = imagecolorallocate($this->image, 0, 129, 31);

        $this->color_palette = [$this->red, $this->yellow, $this->green];

        // Draw a white rectangle
        if (!isset($state) or $state == false) {
            $color = $this->grey;
        } else {
            if (isset($this->{$state})) {
                $color = $this->{$state};
            } elseif (isset($this->{'signal_' . $state})) {
                $color = $this->{'signal_' . $state};
            }
        }

        // Bevel top of signal image

        $points = [0, 0, 6, 0, 0, 6];
        imagefilledpolygon($this->image, $points, 3, $this->white);

        $points = [60, 0, 60 - 6, 0, 60, 6];
        imagefilledpolygon($this->image, $points, 3, $this->white);

        $green_x = 30;
        $green_y = 50;

        $red_x = 30;
        $red_y = 100;

        $yellow_x = 30;
        $yellow_y = 75;

        $double_yellow_x = 30;
        $double_yellow_y = 25;

        imagefilledellipse(
            $this->image,
            $green_x,
            $green_y,
            20,
            20,
            $this->dark_grey
        );

        imagefilledellipse(
            $this->image,
            $red_x,
            $red_y,
            20,
            20,
            $this->dark_grey
        );

        imagefilledellipse(
            $this->image,
            $yellow_x,
            $yellow_y,
            20,
            20,
            $this->dark_grey
        );
        imagefilledellipse(
            $this->image,
            $double_yellow_x,
            $double_yellow_y,
            20,
            20,
            $this->dark_grey
        );

        if ($state == "green") {
            imagefilledellipse(
                $this->image,
                $green_x,
                $green_y,
                20,
                20,
                $this->green
            );
        }

        if ($state == "red") {
            imagefilledellipse(
                $this->image,
                $red_x,
                $red_y,
                20,
                20,
                $this->red
            );
        }

        if ($state == "yellow") {
            imagefilledellipse(
                $this->image,
                $yellow_x,
                $yellow_y,
                20,
                20,
                $this->yellow
            );
        }

        if ($state == "double yellow") {
            imagefilledellipse(
                $this->image,
                $yellow_x,
                $yellow_y,
                20,
                20,
                $this->yellow
            );
            imagefilledellipse(
                $this->image,
                $double_yellow_x,
                $double_yellow_y,
                20,
                20,
                $this->yellow
            );
        }
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

    public function readSignal()
    {
    }

    public function readSubject()
    {
        $keywords = [
            'signal',
            'x',
            'X',
            'red',
            'double yellow',
            'green',
            'yellow',
            'list',
            'new',
            'make',
            'last',
            'next',
        ];

        $input = $this->input;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->response .= "Got the signal. ";
                return;
            }
        }

        if (count($pieces) == 3) {
            if ($input == "signal double yellow") {
                $this->makeState('double yellow');
                return;
            }
        }

        $uuid_agent = new Uuid($this->thing, "uuid");
        $t = $uuid_agent->extractUuid($input);
        if (is_string($t)) {
            $this->getSignalbyUuid($t);
            return;
        }

        // Okay maybe not a full UUID. Perhaps a NUUID.
        $nuuid_agent = new Nuuid($this->thing, "nuuid");
        $t = $nuuid_agent->extractNuuid($input);

        if (is_string($t)) {
            $response = $this->getSignalbyNuuid($t);

            if ($response != true) {
                $this->response .= "Got signal " . $t . ". ";
                return;
            }

            $response = $this->getSignalbyId($t);

            if ($response != true) {
                $this->response .= "Got signal " . $t . ". ";
                return;
            }

            $this->response .= "Match not found. ";
        }

        // Lets think about Signals.
        // A signal is connected to another signal.  Directly.

        // So look up the signal in associations.

        // signal - returns the uuid and the state of the current signal

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'red':
                            $this->thing->log(
                                $this->agent_prefix .
                                    'received request for RED SIGNAL.',
                                "INFORMATION"
                            );
                            $this->makeState('red');
                            return;

                        case 'green':
                            $this->makeState('green');
                            return;

                        case 'yellow':
                            $this->makeState('yellow');
                            return;

                        case 'x':
                            $this->makeState('X');
                            return;

                        case 'list':
                            $this->getSignals();
                            return;

                        case 'make':
                        case 'new':
                            $this->newSignal();
                            return;

                        case 'back':

                        case 'next':

                        default:
                    }
                }
            }
        }

        $this->readSignal();
        $this->response .= "Did not see a command. ";

        // devstack
        return "Message not understood";
        return false;
    }
}
