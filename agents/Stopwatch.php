<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';
//require_once '/var/www/html/stackr.ca/agents/message.php';
//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
// factored to
// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Stopwatch extends Agent
{
    public $var = 'hello';

    function init()
    {
        // function __construct(Thing $thing, $agent_input = null) {

        $this->node_list = [
            "stop" => ["start" => ["split", "stop"], "reset"],
            "reset",
        ];
        $this->thing->choice->load('stopwatch');

        $this->test = "Development code"; // Always
    }

    function set()
    {
        // Read the elapsed time ie 'look at stopwatch'.
        $this->thing->json->setField("variables");
        $this->thing->json->readVariable(
            ["stopwatch", "elapsed"],
            $this->elapsed_time
        );
        $this->thing->json->readVariable(
            ["stopwatch", "refreshed_at"],
            $this->current_time
        );
        $this->thing->choice->save('stopwatch', $this->state);
    }

    function get()
    {
        // Read the elapsed time ie 'look at stopwatch'.

        // See if a stopwatch record exists.
        //require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'stopwatch');

        foreach (
            array_reverse($findagent_thing->thing_report['things'])
            as $thing
        ) {
            $uuid = $thing['uuid'];
            $variables_json = $thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables['stopwatch'])) {
                continue;
            }
            if (!isset($variables['stopwatch']['elapsed'])) {
                continue;
            }

            $thing->elapsed_time = $variables['stopwatch']['elapsed'];

            if (
                $thing->refreshed_at == false or
                $thing->elapsed_time == false
            ) {
                continue;
            } else {
                break;
            }
        }

        // See where we stand.

        if ($thing->refreshed_at == false or $thing->elapsed_time == false) {
            // Nothing found.

            $this->stopwatch_thing = $this->thing;

            $this->thing->json->writeVariable(
                ["stopwatch", "refreshed_at"],
                $this->current_time
            );
            $this->elapsed_time = 0;
            $this->refreshed_at = $this->current_time;
            $this->state = 'stop';
        } else {
            $this->stopwatch_thing = $thing;

            $this->stopwatch_thing->json->setField("variables");
            $this->elapsed_time = $this->stopwatch_thing->json->readVariable([
                "stopwatch",
                "elapsed",
            ]);

            $this->refreshed_at = $this->stopwatch_thing->json->readVariable([
                "stopwatch",
                "refreshed_at",
            ]);
            $this->previous_state = $this->stopwatch_thing->choice->name;

            $this->state = $this->previous_state;
        }

        return;
    }

    function readStopwatch($variable = null)
    {
        return;
        $this->thing->log("read");

        $this->get();
        return $this->elapsed_time;
    }

    function reset()
    {
        $this->thing->log("reset");

        $this->get();
        // Set elapsed time as 0 and state as stopped.
        $this->elapsed_time = 0;
        $this->thing->choice->Create('stopwatch', $this->node_list, 'stop');

        $this->thing->choice->Choose('stop');

        $this->set();

        return $this->elapsed_time;
    }

    function stop()
    {
        $this->thing->log("stop");
        $this->get();
        $this->thing->choice->Choose('stop');
        $this->set();
        //                $this->elapsed_time = time() - strtotime($time_string);
        return $this->elapsed_time;
    }

    function start()
    {
        $this->thing->log("start");

        $this->get();

        if ($this->previous_state == 'stop') {
            $this->thing->choice->Choose('start');
            $this->state = 'start';
            $this->set();
            return;
        }

        if ($this->previous_state == 'start') {
            //echo $this->current_time;
            //ech
            $t =
                strtotime($this->current_time) - strtotime($this->refreshed_at);

            $this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->set();
            return;
        }

        $this->thing->choice->Choose('start');
        $this->state = 'start';
        $this->set();
        return;

        return null;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $choices = $this->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;

        $sms_message =
            "STOPWATCH | " .
            $this->elapsed_time .
            " | " .
            $this->state .
            " | TEXT ?";

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';
        $test_message .= '<br>Stopwatch state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $sms_message;

        $test_message .=
            '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>Requested state: ' . $this->requested_state;

        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = 'This is a stopwatch.';
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ['stop', 'start', 'lap', 'reset'];

        $input = strtolower($this->subject);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        //		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'stopwatch') {
                $this->readStopwatch();
                return;
            }

            return "Request not understood";
        }

        echo "meepmeep";

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
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

                        default:
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        //    $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $input_agent = new Input($this->thing, "input");
        //$input_agent->discriminateInput($discriminators);

        $discriminators = ['start', 'stop', 'reset', 'lap'];
        $input_agent->aliases['start'] = [
            'start',
            'sttr',
            'stat',
            'st',
            'strt',
        ];
        $input_agent->aliases['stop'] = ['stop', 'stp'];
        $input_agent->aliases['reset'] = ['rst', 'reset', 'rest'];
        $input_agent->aliases['lap'] = ['lap', 'laps', 'lp'];

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

        $this->readStopwatch();

        return "Message not understood";

        return false;
    }
}
