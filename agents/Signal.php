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
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        //return $this->thing_report;
    }

    function set()
    {
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

        if (!isset($this->signal_id)) {
            $this->signal_id = $this->uuid;
        }

        $this->associations->setAssociation($this->signal_id);

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

    function get()
    {
        $this->getSignal();

        $this->thing->log(
            $this->agent_prefix .
                'got a ' .
                strtoupper($this->signal['state']) .
                ' SIGNAL.',
            "INFORMATION"
        );
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
        if (!isset($this->signal_thing)) {
            $this->signal_thing = $this->thing;
        }

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
        $this->signal_thing = new Thing($uuid);
        $this->signal = $this->thing->json->jsontoArray(
            $this->signal_thing->thing->variables
        )['signal'];

        $this->state = $this->signal['state'];
        $this->signal_id = $this->signal_thing->uuid;
        $this->signal['id'] = $this->signal_id;
    }

    function getSignal()
    {
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
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/signal.png">';
        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        //$web .= $this->sms_message;

        $web .= "SIGNAL IS " . strtoupper($this->signal['state']) . "<br>";
        $web .= "SIGNAL ID " . strtoupper($this->signal['id']) . "<br>";
        //$web .= "Refreshed at " . strtoupper($this->signal['refreshed_at']) . "<br>";

        $this->thing_report['web'] = $web;
    }

    function makeLink()
    {
        $this->link =
            $this->web_prefix . 'thing/' . $this->signal['id'] . '/signal';

        $this->thing_report['link'] = $this->link;
    }
    /*
    function make() {
        $this->makeSignal();

        $this->makeHelp();
$this->makeLink();
        $this->makeSMS();
        $this->makeMessage();

        $this->thing_report['email'] = $this->message;

        $this->makePNG();

        $this->makeTXT();

        $this->makeChoices();
        $this->makeWeb();

        // $this->makeHelp();
    }
*/
    function makeHelp()
    {
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
        $txt = 'This is SIGNAL POLE ' . $this->signal['id'] . '. ';
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
        $sms_message = "SIGNAL IS " . strtoupper($this->signal['state']);

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

        if ($this->verbosity > 0) {
            $sms_message .= " | " . $this->link . "";
            $sms_message .= " Text HELP";
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }

            if ($this->state == "green") {
                $sms_message .= ' | MESSAGE Red';
            }
        }
        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    function makeMessage()
    {
        $message =
            'This is a SIGNAL POLE.  The signal is a ' .
            trim(strtoupper($this->state)) .
            " SIGNAL. ";

        if ($this->state == 'red') {
            $message .= 'It is a BAD time at the moment. ';
        }

        if ($this->state == 'green') {
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

        return;
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
                //    $this->selectChoice('double yellow');
                $this->makeState('double yellow');

                $this->response .= "Selected a double yellow signal. ";
                return;
            }
        }

        $uuid_agent = new Uuid($this->thing, "uuid");
        $t = $uuid_agent->extractUuid($input);
        if (is_string($t)) {
            $this->getSignalbyUuid($t);
            return;
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

        // If all else fails try the discriminator.
        $this->requested_state = $this->discriminateInput($this->input); // Run the discriminator.
        switch ($this->requested_state) {
            case 'green':
                $this->makeState('green');
                $this->response = "Asserted a Green Signal.";
                return;
            case 'red':
                $this->makeState('red');
                $this->response = "Asserted a Red Signal.";
                return;
        }

        $this->readSignal();
        $this->response = "Looked at the Signal.";

        // devstack
        return "Message not understood";
        return false;
    }

    function discriminateInput($input, $discriminators = null)
    {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ['red', 'green'];
        }

        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        $aliases = [];

        $aliases['red'] = ['r', 'red', 'on'];
        $aliases['green'] = ['g', 'grn', 'gren', 'green', 'gem', 'off'];
        //$aliases['reset'] = array('rst','reset','rest');
        //$aliases['lap'] = array('lap','laps','lp');

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
                    //echo "sum";
                }
                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                        //echo "sum";
                    }
                }
            }
        }

        $this->thing->log(
            'Agent "Signal" matched ' . $total_count . ' discriminators.',
            "DEBUG"
        );
        // Set total sum of all values to 1.

        $normalized = [];
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key => $value) {
            //echo $key, $value;

            if (isset($max)) {
                $delta = $max - $value;
                break;
            }
            if (!isset($max)) {
                $max = $value;
                $selected_discriminator = $key;
            }
        }

        //echo '<pre> Agent "Usermanager" normalized discrimators "';print_r($normalized);echo'"</pre>';

        if ($delta >= $minimum_discrimination) {
            //echo "discriminator" . $discriminator;
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }
}
