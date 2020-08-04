<?php
namespace Nrwtaylor\StackAgentThing;
//require '/var/www/stackr.test/vendor/autoload.php';

//$GLOBALS['stack'] = '/var/www/stackr.test/';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Watchdog extends Agent
{
    public function init()
    {
        $this->state = "red"; // running

        $this->url = false;
        if (isset($this->thing->container['api']['watchdog']['url'])) {
            $this->url = $this->thing->container['api']['watchdog']['url'];
        }
        $this->thing_report['help'] = "Watches out for barks.";
    }

    public function run()
    {
        $this->doWatchdog();
    }

    public function makeChoice()
    {
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        //        $this->thing_report['sms'] = $this->sms_message;
    }

    function set()
    {
        $this->thing->json->setField("variables");

        $this->thing->json->writeVariable(
            ["watchdog", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    public function get()
    {
    }

    function makeSMS()
    {
        $message = "WATCHDOG";

        $message .= " | " . $this->response;

        $this->sms_message = $message;
        $this->thing_report['sms'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/watchdog';

        //$web = '<a href="' . $link . '">';
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';

        //$web .= "</a>";
        //$web .= "<br>";
        $web = "";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->sms_message;

        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
        $this->response .= "Heard. ";
        $input = $this->input;
        if ($input == 'watchdog') {
            return;
        }

        $read_agent = new Read($this->thing, "read");
        $this->response .= $read_agent->response;
    }

    function doWatchdog()
    {
        $this->tickWatchdog();
        $this->webWatchdog();
    }

    function tickWatchdog($depth = null)
    {
// This watchdog watches for cron ticks.
        $things = $this->getThings('cron');

        if ($things == null) {
            $this->response .= "No ticks found. ";
            return true;
        }

        $refreshed_at = [];
        foreach ($things as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $things);

        $age = 1e99;

        if (isset($things[0]['refreshed_at'])) {
            //            $age = 1e99;
            $refreshed_at = $things[0]['refreshed_at'];
            $age = strtotime($this->current_time) - strtotime($refreshed_at);
        }

        $tick_limit_seconds = 60;
        if ($age > $tick_limit_seconds) {
            //echo 'merp';
            $thing = new Thing(null);
// Document as $thing->Create('to', 'from', 'message text')
            $thing->Create('human', 'watchdog', 'Watchdog barked. It has been quiet since '. $age);
            $thing->thing_report['sms'] = "merp";
            $web = "";
            $web .= "This number was made about ago.";

            $web .= "<br>";

            $thing->thing_report['email'] = $web;

            $message_thing = new Message($thing, $thing->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];

            $this->response .=
                "Did not see a tick within the last " .
                $tick_limit_seconds .
                ". ";
        }
    }

    function webWatchdog($depth = null)
    {
        if ($this->url === false) {
            return true;
        }

        file_get_contents($this->url);
    }
}
