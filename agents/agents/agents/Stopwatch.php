<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Stopwatch times trains.
// The elapsed time of the stopwatch reflects how long the train has been running.

class Stopwatch extends Agent
{
    public $var = 'hello';

    function init()
    {
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
        $this->stopwatch_thing->json->setField("variables");
        $this->stopwatch_thing->json->readVariable(
            ["stopwatch", "elapsed"],
            $this->elapsed_time
        );
        $this->stopwatch_thing->json->readVariable(
            ["stopwatch", "refreshed_at"],
            $this->current_time
        );
        $this->stopwatch_thing->choice->save('stopwatch', $this->state);
    }

    function get()
    {
        // Read the elapsed time ie 'look at stopwatch'.

        // See if a stopwatch record exists.
        //require_once '/var/www/html/stackr.ca/agents/findagent.php';
        //$findagent_thing = new FindAgent($this->thing, 'stopwatch');

        $things = $this->getThings('stopwatch');

        foreach (
            // array_reverse($findagent_thing->thing_report['things'])
            array_reverse($things)
            as $uuid => $thing
        ) {
            //            $uuid = $thing['uuid'];
            //     $variables_json = $thing['variables'];
            //   $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables['stopwatch'])) {
                continue;
            }
            if (!isset($variables['stopwatch']['elapsed'])) {
                continue;
            }

            $thing->refreshed_at = $variables['stopwatch']['refreshed_at'];
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
        //var_dump($thing->uuid);
        //var_dump($thing->refreshed_at); // currently null
        //var_dump($thing->created_at);
        //var_dump($thing->elapsed_time); // currently null
        // See where we stand.
        //var_dump($thing->flagGet());

        if (!isset($thing->refreshed_at) or !isset($thing->elapsed_time)) {
            // Nothing found.

            // Make a stopwatch. Thing.

            $this->stopwatch_thing = $this->thing;

            $this->thing->json->writeVariable(
                ["stopwatch", "refreshed_at"],
                $this->current_time
            );
            $this->elapsed_time = 0;
            $this->refreshed_at = $this->current_time;
            $this->state = 'stop';
            $this->previous_state = 'start';
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

            // devstack here

            $this->previous_state =
                $this->stopwatch_thing->choice->current_node;

            $this->state = $this->previous_state;

            //$this->state = $thing->flagGet();
            //$this->previous_state = $this->state;
        }
    }

    function readStopwatch($variable = null)
    {
        $this->response .= "Looked at stopwatch. ";

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

        if ($this->stopwatch_thing->choice->current_node == 'stopped') {
            // Do nothing.
            $this->response .= "Clock is stopped. ";
        }

        if ($this->stopwatch_thing->choice->current_node == 'running') {
            $this->stopwatch_thing->choice->Choose('stopped');

            $t =
                strtotime($this->current_time) - strtotime($this->refreshed_at);

            $this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->stopwatch_thing->elapsed_time = $this->elapsed_time;

            $this->response .= "Stopped the clock. ";
        }

        $this->stopwatch_thing->flagSet('green');
        $this->set();

        //                $this->elapsed_time = time() - strtotime($time_string);
        return $this->elapsed_time;
    }

    function start()
    {
        $this->thing->log("start");

        $this->get();

        if ($this->stopwatch_thing->choice->current_node == 'stopped') {
            $this->stopwatch_thing->choice->Choose('running');

            $this->stopwatch_thing->flagSet('red');

            //$this->state = 'running';
            $this->set();

            $this->response .= "Started the clock. ";

            return;
        }

        if ($this->stopwatch_thing->choice->current_node == 'running') {
            //echo $this->current_time;
            //ech
            $t =
                strtotime($this->current_time) - strtotime($this->refreshed_at);

            $this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->set();
            $this->stopwatch_thing->elapsed_time = $this->elapsed_time;
            $this->response .= "Saw it already running. ";
            return;
        }

        throw 'not running and stopped.';
    }

    public function respondResponse()
    {
        //        $this->thing->flagGreen();

        $choices = $this->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = 'This is a stopwatch.';
    }

    public function makeSMS()
    {
        $sms = "STOPWATCH | " . $this->elapsed_time . "s " . $this->state;

        $sms .= ' ' . $this->stopwatch_thing->flagGet();
        $sms .= ' ' . $this->stopwatch_thing->choice->current_node;
        $sms .= ' ' . $this->response;

        //$sms = "MERP";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
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

            // return "Request not understood";
        }

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
