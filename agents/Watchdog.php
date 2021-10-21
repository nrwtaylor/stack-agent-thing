<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Watchdog extends Agent
{
    public function init()
    {
        $this->state = "red"; // running

        $this->url = false;
        if (isset($this->thing->container["api"]["watchdog"]["url"])) {
            $this->url = $this->thing->container["api"]["watchdog"]["url"];
        }
        $this->thing_report["help"] = "Watches out for barks.";
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
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        //        $this->thing_report['sms'] = $this->sms_message;
    }

    function set()
    {
        $this->thing->Write(
            ["watchdog", "refreshed_at"],
            $this->thing->time()
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
        $this->thing_report["sms"] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/watchdog";

        //$web = '<a href="' . $link . '">';
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';

        //$web .= "</a>";
        //$web .= "<br>";
        $web = "";
        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";
        $web .= $this->sms_message;

        $this->thing_report["web"] = $web;
    }

    public function queryWatchdog($text = null, $query = null)
    {
        if (stripos($text, $query) !== false) {
            // Saw query in source.
            return true;
        }
        // Did not see query in source.
        return false;
    }

    public function readSubject()
    {
        $input = $this->assert($this->input, "watchdog", false);

        $tokens = explode(" ", $input);
        if (count($tokens) >= 2) {
            $file = $tokens[0];

            $path = $this->resource_path;
            //$file = 'filename';

            $location = $path . "read/" . $file;

            if (file_exists($location)) {
                $contents = file_get_contents($location);
                $this->metaRead($contents);
                $text = $this->textHtml($contents);
            } else {
                $this->response .= "Source not found. ";
                return;
            }

            unset($tokens[0]);

            $query = implode(" ", $tokens);

            if ($this->queryWatchdog($text, $query) === true) {
                $this->response .= "Query seen in source. ";
            } else {
                $this->response .= "Query not seen in source. ";
            }

            return;
        }

        if ($input == "") {
            $this->response .= "Saw watchdog. Did nothing. ";
            return;
        }

        $this->response .= "Did not recognize query. ";
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
        $things = $this->getThings("cron");

        if ($things == null) {
            $this->response .= "No ticks found. ";
            return true;
        }

        //            // devstack.
        //            $test = $thing->variables['test'];
        //            //$test = $thing['variables']['test'];

        $refreshed_at = [];
        foreach ($things as $uuid => $thing) {
            $refreshed_at[$uuid] = $thing->variables["cron"]["refreshed_at"];
        }
        array_multisort($refreshed_at, SORT_DESC, $things);

        $age = 1e99;

        if (isset($things[0]->variables["cron"]["refreshed_at"])) {
            //            $age = 1e99;
            $refreshed_at = $things[0]->variables["cron"]["refreshed_at"];
            $age = strtotime($this->current_time) - strtotime($refreshed_at);
        }

        $tick_limit_seconds = 60;
        if ($age > $tick_limit_seconds) {
            $thing = new Thing(null);
            // Document as $thing->Create('to', 'from', 'message text')
            $thing->Create(
                "human",
                "watchdog",
                "Watchdog barked. It has been quiet since " . $age
            );
            $thing->thing_report["sms"] = "merp";
            $web = "";
            $web .= "This number was made about ago.";

            $web .= "<br>";

            $thing->thing_report["email"] = $web;

            $message_thing = new Message($thing, $thing->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];

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
