<?php
namespace Nrwtaylor\StackAgentThing;

/*
TODO Develop event reading when events are available.
*/

class Geekenders extends Agent
{
    // https://www.facebook.com/Geekenders/

    public $var = "hello";

    public function init()
    {
        $this->email = $this->thing->container["stack"]["email"];
        $this->link = "https://www.geekenders.net";

        $this->geekenders_read_flag = false; // False do not read.
    }

    function set()
    {
        // UK Commonwealth spelling
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["geekenders", "refreshed_at"],
            $time_string
        );
    }

    function get()
    {
        $time_string = $this->thing->Read([
            "geekenders",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["geekenders", "refreshed_at"],
                $time_string
            );
        }
    }

    public function getClocktime()
    {
        $this->clocktime = new Clocktime($this->thing, "clocktime");
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function geekendersDo()
    {
        // What does this do?
    }

    public function doGeekenders()
    {
        $this->earliest_event_string = "No event read.";
        if ($this->geekenders_read_flag === false) {
            return false;
        }

        $read_handler = new Read($this->thing, $this->link);

        $read_handler->urlRead();
        $sentences = $read_handler->sentences;
        $datelines = [];
        foreach ($sentences as $i => $sentence) {
            if (trim($sentence) == "") {
                continue;
            }
            $dateline = $this->extractDateline($sentence);
            if ($dateline == false) {
                continue;
            }
            $datelines[] = $dateline;
        }

        return;

        // Deprecate use of Eventful to get Geekenders events.
        $this->earliest_event_string =
            "Didn't find anything. Which is weird, because there has to be a geekenders show. Check https://www.facebook.com/Geekenders/";

        // What is a Geekenders.
        $this->eventful = new Eventful($this->thing, "eventful geekenders");

        foreach ($this->eventful->events as $eventful_id => $event) {
            $event_name = $event["event"];
            $event_time = $event["runat"];
            $event_place = $event["place"]; // Doesn't presume the Rio

            $time_to_event =
                strtotime($event_time) - strtotime($this->current_time);
            if (!isset($time_to_earliest_event)) {
                $time_to_earliest_event = $time_to_event;
                $event_string = $this->eventful->eventString($event);
                $this->earliest_event_string =
                    $this->thing->human_time($time_to_earliest_event) .
                    " until " .
                    $event_string .
                    ".";
            } else {
                $this->response = "Got the current Geekenders.";
                if ($time_to_earliest_event > $time_to_event) {
                    $time_to_earliest_event = $time_to_event;
                    $event_string = $this->eventful->eventString($event);

                    $this->earliest_event_string =
                        $this->thing->human_time($time_to_earliest_event) .
                        " until " .
                        $event_string .
                        ".";

                    if ($time_to_event < 0) {
                        $this->earliest_event_string =
                            "About to happen. Happening. Or just happened. " .
                            $event_string .
                            ".";
                    }

                    $this->response = "Got the next Geekenders.";

                    $this->runat = new Runat(
                        $this->thing,
                        "runat " . $event_time
                    );

                    if ($this->runat->isToday($event_time)) {
                        $this->response = "Got today's Geekenders.";
                    }
                }
            }
        }
    }

    public function readSubject()
    {
        $this->response .= "Heard Geekenders. ";
        $this->keyword = "geekenders";

        $this->doGeekenders();

        return $this->response;
    }

    public function makeMessage()
    {
        //        $message = $this->eventful->message;

        $message = $this->earliest_event_string;
        $this->message = $message; //. ".";
        $this->thing_report["message"] = $message;
    }

    public function makeSMS()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/geekenders";

        $sms = "GEEKENDERS ";
        $sms .= " | " . $this->earliest_event_string;
        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/geekenders";

        $html = "<b>GEEKENDERS WATCHER</b>";

        $html .= '<br>Geekenders watcher says , "';
        $html .= $this->sms_message . '"';

        /*
// Deprecate eventful for events.
        $html .= "<p>";
        foreach($this->eventful->events as $id=>$event) {
            $e = $this->eventful->eventString($event);
            $html .= "<br>" . ($e);
        }
*/

        $this->web_message = $html;
        $this->thing_report["web"] = $html;
    }
}
