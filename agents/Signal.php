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
        $this->associate_agent = new Associate($this->thing, $this->subject);

        $this->thing_report['help'] =
            'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. Text SIGNAL DOUBLE YELLOW.';
        $this->thing_report['info'] =
            'DOUBLE YELLOW means keep going. RED means stop.';
    }

    public function lastSignal()
    {
    }

    public function nextSignal()
    {
    }

    public function currentSignal()
    {
    }

    public function respondResponse()
    {
        $this->makeHelp();
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function set()
    {
        if (!isset($this->signal_thing)) {
            //$this->signal_thing = $this->thing;
            // Nothing to set
            //return true;
        }

        if (isset($this->signal_thing->state)) {
            $this->signal['state'] = $this->signal_thing->state;
        }

        $this->signal['id'] = $this->idSignal($this->signal_thing->uuid);

        $this->signal['uuid'] = $this->signal_thing->uuid;

        $this->signal['text'] = "signal check";
        if (isset($this->signal_thing->text)) {
            $this->signal['text'] = $this->signal_thing->text;
        }

        if (isset($this->signal_thing->uuid)) {
            $this->signal_thing->associate($this->signal_thing->uuid);
        }

        if ($this->channel_name == 'web') {
            $this->response .= "Detected web channel. ";
            // Do not effect a state change for web views.
            return;
        }
        /*
        $this->thing->json->writeVariable(
            ["signal", "refreshed_at"],
            $this->current_time
        );

        if (
            isset($this->signal_thing->state) and
            $this->signal_thing->state != null
        ) {
            $this->thing->json->writeVariable(
                ["signal", "state"],
                $this->signal_thing->state
            );
        }

        if (isset($this->signal_thing->text) and $this->signal_thing->text) {
            $this->thing->json->writeVariable(
                ["signal", "text"],
                $this->signal_thing->text
            );
        }
*/
        $this->setSignal();
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
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        if (is_string($this->channel_name)) {
            $this->response .= "Saw channel is " . $this->channel_name . ". ";
        } else {
            $this->response .= "Did not recognize channel name. ";

        }
        $this->getSignal();

        foreach ($this->signals as $i => $signal) {
            if ($signal['uuid'] == $this->signal_thing->uuid) {
                //var_dump($signal);
                $this->signal_thing->state = $signal['state'];
                return;
            }
        }
    }

    public function run()
    {
        // Get this too. Or put it in the run loop.
        $state = "X";
        if (isset($this->signal_thing->state)) {
            $state = $this->signal_thing->state;
        }

        $this->helpSignal($state);
        $this->infoSignal($state);
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
            $help = "Treat this signal as if it is broken. Try SIGNAL RED and see if it changes colour.";
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

    function changeSignal($text)
    {
        if (!isset($this->signal_thing)) {
            return true;
        }
        $state = null;

        if (strtolower($text) == "x") {
            $state = "X";
            $type = "signal x";
        }

        if (strtolower($text) == "red") {
            $state = "red";
            $type = "signal red";
        }
        if (strtolower($text) == "green") {
            $state = "green";
            $type = "signal green";
        }
        if (strtolower($text) == "yellow") {
            $state = "yellow";
            $type = "signal yellow";
        }
        if (strtolower($text) == "double yellow") {
            $state = "double yellow";
            $type = "signal double yellow";
        }

        $this->signal_thing->state = $state;
        $this->signal_thing->text = $type;

        if ($state != null) {
            $this->response = "Selected " . $state . " signal.";
        }
    }

    function setSignal($text = null)
    {
        $this->signal_thing->json->writeVariable(
            ["signal", "state"],
            $this->signal['state']
        );

        $this->signal_thing->json->writeVariable(
            ["signal", "text"],
            $this->signal['text']
        );

        $this->signal_thing->json->writeVariable(
            ["signal", "refreshed_at"],
            $this->current_time
        );

        $this->signal_thing->associate($this->signal_thing->uuid);
    }

    function makeSignal()
    {
        if (!isset($this->signal_thing)) {
            return true;
        }
    }

    function newSignal()
    {
        $this->response .= "Called for a new signal. ";
        $thing = new Thing(null);
        $thing->Create($this->from, 'signal', 'signal');

        $agent = new Signal($thing, "signal");

        $this->signal_thing = $thing;
        $this->signal_thing->state = "X";
        $this->signal_thing->text = "new signal";

        $this->signal_id = $this->idSignal($thing->uuid);
    }

    function getSignalbyUuid($uuid)
    {
        if ($this->channel_name == 'web') {
            $id = $this->idSignal($uuid);
            $this->getSignalbyId($id);
            return;
        }
        $thing = new Thing($uuid);
        if ($thing->thing == false) {
            $this->signal_thing = false;
            $this->signal_id = null;
            return true;
        }

        $signal = $this->thing->json->jsontoArray($thing->thing->variables)[
            'signal'
        ];

        $this->signal_thing = $thing;
        $this->signal_id = $thing->uuid;

        if (isset($signal['state'])) {
            $this->signal_thing->state = $signal['state'];
        }
    }

    function getSignalbyId($id)
    {
        if (!isset($this->signals)) {
            $this->getSignals();
        }
        $matched_uuids = [];
        foreach ($this->signals as $i => $signal) {
            if ($signal['id'] == $id) {
                $matched_uuids[] = $signal['uuid'];
                continue;
            }

            if ($this->idSignal($signal['uuid']) == $id) {
                $matched_uuids[] = $signal['uuid'];
                continue;
            }
        }
        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->signal_thing = new Thing($uuid);

        $signal = $this->thing->json->jsontoArray(
            $this->signal_thing->thing->variables
        )['signal'];

        $this->signal_id = $this->signal_thing->uuid;

        if (isset($signal['state'])) {
            $this->signal_thing->state = $signal['state'];
        }

        /* 
       $this->signal = $this->thing->json->jsontoArray(
            $this->signal_thing->thing->variables
        )['signal'];

        $this->state = $this->signal['state'];
        $this->signal_id = $this->signal_thing->uuid;
        $this->signal['id'] = $this->signal_id;
*/
    }

    // Take in a uuid and convert it to a signal id (id here).
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

    public function textSignal($signal = null)
    {
        if ($signal == null) {
            $signal = $this->signal;
        }
        $id = "X";
        if (isset($signal['id'])) {
            $id = $signal['id'];
        }

        $uuid = "X";
        if (isset($signal['uuid'])) {
            $uuid = $signal['uuid'];
        }

        $state = "X";
        if (isset($signal['state'])) {
            $state = $signal['state'];
        }

        $text = "X";
        if (isset($signal['text'])) {
            $text = $signal['text'];
        }

        $refreshed_at = "X";
        if (isset($signal['refreshed_at'])) {
            $refreshed_at = $signal['refreshed_at'];
        }

        $text =
            $id .
            " " .
            //            $uuid .
            //            " " .
            " " .
            $state .
            " " .
            $text .
            " " .
            $refreshed_at .
            "\n";
        return $text;
    }

    public function getSignal($text = null)
    {
        if ($text != null) {
            $t = $this->getSignalbyId($text);
            return;
        }

        if (
            isset(
                $this->thing->json->jsontoArray($this->thing->thing->variables)[
                    'signal'
                ]
            )
        ) {
            // First is there a signal in this thing.
            $signal = $this->thing->json->jsontoArray(
                $this->thing->thing->variables
            )['signal'];

            $signal_id = "X";
            if (isset($this->signal['id'])) {
                $this->signal_id = $signal['id'];
                $this->response .= "Saw " . $this->signal_id . ". ";

                $this->getSignalbyId($this->signal_id);
                return;
            }

            $signal_id = "X";
            if (isset($signal['uuid'])) {
                $this->signal_id = $this->idSignal($signal['uuid']);
                $this->response .= "Saw " . $this->signal_id . ". ";

                $this->getSignalbyUuid($signal['uuid']);
                return;
            }

            if (isset($this->signal['refreshed_at'])) {
                $this->signal_id = $this->thing->uuid;

                $this->response .= "Saw a signal in the thing. ";

                $this->getSignalbyId($this->signal_id);
                return;
            }

            // Get the most recent signal command.
            //return;
        }
        // Haven't found the signal in the thing.

        if (!isset($this->signals)) {
            $this->getSignals();
        }

        foreach ($this->signals as $i => $signal) {
            //echo $this->textSignal($signal);
            if (isset($signal['uuid'])) {
                $flag = $this->getSignalbyUuid($signal['uuid']);
                return;
            }

            if (isset($signal['id'])) {
                $flag = $this->getSignalbyId($signal['id']);
                return;
            }
        }

        $this->response .= "Did not find a signal. ";

        // Can't find a signal.
        return false;
    }

    function getSignals()
    {
        $this->signalid_list = [];
        $this->signals = [];

        $things = $this->getThings('signal');

        if ($things === null) {
            return;
        }
        if ($things === true) {
            return;
        }
        $count = count($things);
        // See if a headcode record exists.
        //$findagent_thing = new Findagent($this->thing, 'signal');
        //$count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Signal" found ' . $count . " signal Things.");

        if (!$this->is_positive_integer($count)) {
            // No signals found
        } else {
            foreach (array_reverse($things) as $uuid => $thing) {
                $associations = $thing->associations;

                $signal = [];
                $signal["associations"] = $associations;

                $variables = $thing->variables;
                if (isset($variables['signal'])) {
                    if (isset($variables['signal']['refreshed_at'])) {
                        $signal['refreshed_at'] =
                            $variables['signal']['refreshed_at'];
                    }

                    if (isset($variables['signal']['text'])) {
                        $signal['text'] = $variables['signal']['text'];
                    }

                    if (isset($variables['signal']['state'])) {
                        $signal['state'] = $variables['signal']['state'];
                    }

                    $signal["uuid"] = $uuid;
                    $signal["id"] = $this->idSignal($uuid);

                    $this->signals[] = $signal;
                    $this->signalid_list[] = $uuid;
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
        $web = null;
        if (isset($this->signal_thing)) {
            $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
            $web = "";
            $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br><p>';
            $web .= "<p>";
            $web .= '<a href="' . $link . '">';
            //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/sig>
            $web .= $this->html_image;

            $web .= "</a>";
            $web .= "<br>";

            $state_text = "X";
            if (isset($this->signal_thing->state)) {
                $state_text = strtoupper($this->signal_thing->state);
            }

            $web .= "SIGNAL IS " . $state_text;
            $web = "";
            $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br><p>';
            $web .= "<p>";
            $web .= '<a href="' . $link . '">';
            //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/sig>
            $web .= $this->html_image;

            $web .= "</a>";
            $web .= "<br>";

            $state_text = "X";
            if (isset($this->signal_thing->state)) {
                $state_text = strtoupper($this->signal_thing->state);
            }

            $web .= "SIGNAL IS " . $state_text . "<br>";

            $id_text = "X";
            if (isset($this->signal_thing->uuid)) {
                $id_text = strtoupper(
                    $this->idSignal($this->signal_thing->uuid)
                );
            }

            $web .= "SIGNAL ID " . $id_text . "<br>";

            $web .= "<p>";
        }

        if (!isset($this->signals)) {
            $this->getSignals();
        }
        $signal_table = '<div class="Table">
                 <div class="TableRow">
                 <div class="TableHead"><strong>ID</strong></div>
                 <div class="TableHead"><span style="font-weight: bold;">State</span></div>
                 <div class="TableHead"><strong>Text</strong></div></div>';

        //            $id = $signal['id'];
        //            $uuid = $signal['uuid'];
        //            $state = $signal['state'];
        //            $text = $signal['text'];

        if (isset($this->signals) and is_array($this->signals)) {
            $signal_text = "";
            $signals = [];
            foreach ($this->signals as $i => $signal) {
                //  if ($signal['text'] == "signal post") {
                if (isset($signal['uuid']) and !isset($signal['id'])) {
                    $signal['id'] = $this->idSignal($signal['uuid']);
                }

                $signals[] = $signal;
            }

            $refreshed_at = [];
            foreach ($signals as $key => $row) {
                $refreshed_at[$key] = $row['id'];
            }
            array_multisort($refreshed_at, SORT_DESC, $signals);

            foreach ($signals as $i => $signal) {
                $signal_table .= '<div class="TableRow">';

                $signal_table .=
                    '<div class="TableCell">' .
                    strtoupper($signal['id']) .
                    '</div>';

                $state = "X";
                if (isset($signal['state'])) {
                    $state = $signal['state'];
                }
                $signal_table .=
                    '<div class="TableCell">' . strtoupper($state) . '</div>';

                $text = "X";
                if (isset($signal['text'])) {
                    $text = $signal['text'];
                }

                $signal_table .=
                    '<div class="TableCell">' . strtoupper($text) . '</div>';
                $signal_table .= '</div>';

                //               if ($signal['text'] == "signal post") {

                //     $signal_text .= nl2br($this->textSignal($signal));
                //               }
            }
            $signal_text = $signal_table . '</div><p>';

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
        $link = $this->web_prefix;

        if (isset($this->signal_thing->uuid)) {
            $uuid = $this->signal_thing->uuid;
            $link = $this->web_prefix . 'thing/' . $uuid . '/signal';
        }
        $this->link = $link;
        $this->thing_report['link'] = $link;
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
                            strtoupper($this->idSignal($association_uuid)) .
                            " " .
                            $agent_name,
                        "id" => $this->idSignal($association_uuid),
                        "agent_name" => $agent_name,
                    ];
                }
            }
        }
        //$this->associations = $association_array;
        return $association_array;
    }

    function makeHelp()
    {
        if (!isset($this->signal_thing->state)) {
            $this->thing_report['help'] = "No signal thing found.";
            return;
        }
        // Get the latest Help signal.
        if (!isset($this->thing_report['help'])) {
            $this->helpSignal($this->signal_thing->state);
        }
    }

    function makeTXT()
    {
        $signal_id = 'X';
        if (isset($this->signal['id'])) {
            $signal_id = $this->signal['id'];
        }
        $txt = 'This is SIGNAL POLE ' . $signal_id . '. ';

        $state = "X";
        if (isset($this->signal_thing->state)) {
            $state = $this->signal_thing->state;
        }

        $txt .= 'There is a ' . strtoupper($state) . " SIGNAL. ";
        if ($this->verbosity >= 5) {
            $txt .=
                'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }
        $txt .= "\n";
        foreach ($this->signals as $i => $signal) {
            $txt .= $this->textSignal($signal);
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $state = "X";
        if (isset($this->signal_thing->state)) {
            $state = $this->signal_thing->state;
        }

        $signal_id = "X";
        if (isset($this->signal_thing->uuid)) {
            $signal_id = $this->signal_thing->uuid;
            $signal_nuuid = strtoupper(substr($signal_id, 0, 4));
            $signal_id = $this->idSignal($signal_id);
        }

        $state_text = "X";
        if ($state != null) {
            $state_text = strtoupper($state);
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
                if (isset($signal['id'])) {
                    $sms_message .= strtoupper($signal['id']) . " ";
                    $sms_message .= strtoupper($signal['state']) . " / ";
                } elseif (isset($signal['uuid'])) {
                    $sms_message .= $this->idSignal($signal['uuid']) . " ";
                    $sms_message .= strtoupper($signal['state']) . " / ";
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
        $state = "X";
        if (isset($this->signal_thing->state)) {
            $state = $this->signal_thing->state;
        }

        $message =
            'This is a SIGNAL POLE.  The signal is a ' .
            trim(strtoupper($state)) .
            " SIGNAL. ";

        if ($state == 'red') {
            $message .= 'It is a BAD time at the moment. ';
        }

        if ($state == 'green') {
            $message .= 'It is a GOOD time now. ';
        }

        //$test_message .= 'And the signal is ' . strtoupper($this->state) . ".";

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    public function makeImage()
    {
        $state = "X";
        if (isset($this->signal_thing->state)) {
            $state = $this->signal_thing->state;
        }

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
        //echo "merp";
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
                $this->response .= "Got the current signal. ";
                return;
            }

            if ($input == 'signals') {
                $this->response .= "Got active signals. ";
                return;
            }
        }

        if (count($pieces) == 3) {
            if ($input == "signal double yellow") {
                $this->changeSignal('double yellow');
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
            $response = $this->getSignalbyId($t);

            if ($response != true) {
                $this->response .= "Got signal " . $t . ". ";

                /*
foreach($this->signals as $i=>$signal) {

if ($signal['uuid'] == $this->signal_thing->uuid) {
//var_dump($signal);
$this->signal_thing->state = $signal['state'];
return;
}

}
*/

                return;
            }

            $this->response .= "Match not found. ";
        }

        // Lets think about Signals.
        // A signal is connected to another signal.  Directly.

        // So look up the signal in associations.

        // signal - returns the uuid and the state of the current signal

        if ($this->channel_name == 'web') {
            $this->response .= "Detected web channel. ";
            // Do not effect a state change for web views.
            return;
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'red':
                            $this->changeSignal('red');
                            return;
                        case 'green':
                            $this->changeSignal('green');
                            return;

                        case 'yellow':
                            $this->changeSignal('yellow');
                            return;

                        case 'x':
                            $this->changeSignal('X');
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
