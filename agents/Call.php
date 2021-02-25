<?php
/**
 * Call.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Call extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "CALL is a tool for understanding audio-visual conference related text.";
        $this->thing_report["help"] = "Text CALL <text>.";

        $this->node_list = ["call" => ["call", "uuid"]];

        $this->current_time = $this->thing->json->time();

        $this->initCall();
    }

    public function set()
    {
        $this->setCall();
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        //return $this->thing_report;
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report["choices"] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "CALL | ";
        $sms .= $this->message . "\n";
        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */

    /**
     *
     */

    public function setCall()
    {
    }

    /**
     *
     * @return unknown
     */
    public function getCall()
    {
    }

    /**
     *
     */
    public function initCall()
    {
        $this->recognized_services = [
            "zoom",
            "webex",
            "gotomeeting",
            "mailchimp",
        ];
    }

    public function textCall($call = null)
    {
        $password_text = "No password found.";
        if (
            $call["password"] === null or
            $call["password"] === false or
            $call["password"] === "X"
        ) {
        } else {
            $password_text = $call["password"];
        }

        $access_code_text = "No access code/room found.";
        if (
            $call["access_code"] === null or
            $call["access_code"] === false or
            $call["access_code"] === "X"
        ) {
        } else {
            $access_code_text = $call["access_code"];
        }

        $access_url_text = "No access url found.";
        if (
            $call["url"] === null or
            $call["url"] === false or
            $call["url"] === "X"
        ) {
        } else {
            $access_url_text = $call["url"];
        }

        $text_call =
            $password_text .
            " / " .
            $access_code_text .
            " / " .
            $access_url_text .
            "\n";

        return $text_call;
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/call.pdf";
        $this->node_list = ["call" => ["call"]];
        $web = "";
        if (isset($this->html_image)) {
            $web .= '<a href="' . $link . '">';
            $web .= $this->html_image;
            $web .= "</a>";
        }

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "call",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["call", "refreshed_at"],
                $time_string
            );
        }
    }

    // TODO: Test extraction of telephone numbers
    public function readCall($text = null)
    {
        $service = "X";
        $password = "X";
        $access_code = "X";
        $url = "X";
        $urls = [];
        $host_url = "X";
        $telephone_numbers = [];

        $urls = $this->extractUrls($text);
        $telephone_numbers = $this->extractTelephonenumbers($text);

        $services = ["zoom", "webex", "gotomeeting", "mailchimp"];
        foreach ($this->recognized_services as $i => $service) {
            $is_service_flag = $this->{"is" . ucwords($service)}($text);
            $count = 0;
            if ($is_service_flag) {
                // True service matches
                $count += 1;
                if ($count > 1) {
                    continue;
                } // Take first matching service.
                $this->thing->{$service . "_handler"}->{"read" .
                    ucwords($service)}($text);

                $password = $this->thing->{$service . "_handler"}->password;
                $access_code =
                    $this->thing->{$service . "_handler"}->access_code;
                $url = $this->thing->{$service . "_handler"}->url;
                $urls = isset($this->thing->{$service . "_handler"}->urls)
                    ? $this->thing->{$service . "_handler"}->urls
                    : [];
                $host_url = $this->thing->{$service . "_handler"}->host_url;
                $telephone_numbers =
                    $this->thing->{$service . "_handler"}->telephone_numbers;
            }
        }

        // No URL? Try a general search for a
        // paragraph with join a webinar and a url in it.
        if ($url === false) {
            $url = $this->urlWebinar($text);
        }
        $call = [
            "service" => $service,
            "password" => $password,
            "access_code" => $access_code,
            "url" => $url,
            "urls" => $urls,
            "host_url" => $host_url,
            "telephone_numbers" => $telephone_numbers,
        ];

        return $call;
    }

    public function whenCalls($text = null)
    {
        $calls = [];
        foreach (
            $this->thing->when_handler->calendar_agent->calendar->events
            as $event
        ) {
            $haystack =
                $event->summary .
                " " .
                $event->description .
                " " .
                $this->location;

            if (stripos($haystack, "zoom") !== false) {
                //                $zoom_agent = new Zoom($this->thing, "zoom");

                $event->password = $this->thing->zoom_handler->password;
                $event->access_code = $this->thing->zoom_handler->access_code;
                $event->url = $this->thing->zoom_handler->url;
                $event->urls = $this->thing->zoom_handler->urls;
                $event->host_url = $this->thing->zoom_handler->host_url;

                $event->telephone_numbers =
                    $this->thing->zoom_handler->telephone_numbers;

                $calls[] = $event;
                //$this->response .= "Saw a zoom meeting. ";
                continue;
            }

            if (stripos($haystack, "webex") !== false) {
                //                $webex_agent = new Webex($this->thing, "webex");

                $event->password = $this->thing->webex_handler->password;
                $event->access_code = $this->thing->webex_handler->access_code;
                $event->url = $this->thing->webex_handler->url;
                $event->host_url = $this->thing->webex_handler->host_url;

                $event->telephone_numbers =
                    $this->thing->webex_handler->telephone_numbers;

                $calls[] = $event;
                continue;
            }
        }

        return $calls;
    }

    public function makeCall()
    {
        $call_text = "";

        //$when_agent = new When($this->thing, "when");
        if (isset($this->calls)) {
            foreach ($this->calls as $event) {
                $t .= $this->thing->when_handler->textWhen($event) . " ";
                //$t .= $event->summary . " ";
                //$t .=$event->dtstart . " ";

                $t .= $event->password . " ";
                if (isset($event->access_code)) {
                    $t .= $event->access_code . " ";
                }
                if (isset($event->meeting_id)) {
                    $t .= $event->meeting_id . " ";
                }

                $t .= implode(" ", $event->urls) . " ";
                $t .= $event->host_url;

                $t .= implode(" / ", $event->telephone_numbers);
                $call_text .= $t . "\n";

                // Only take the first now.

                break;
            }
        }
        $this->message = $call_text;
    }

    public function nextCall($text = null)
    {
        $this->response .= "Saw a request for the next call. ";
        $response = $this->whenCalls($text);
        return $response;
    }
    /**
     *
     * @return unknown
     */

    public function isCall($text)
    {
        // Contains word call?
        return false;
    }

    public function readSubject()
    {
        //$input = strtolower($this->subject);
        $input = $this->subject;
        if (isset($this->agent_input)) {
            if (strtolower($this->agent_input) == "call") {
                return;
            }
            if (!$this->thing->isEmpty($this->agent_input)) {
                $input = $this->agent_input;
            }
        }

        $this->input = $input;
        $this->readCall($input);

        if (strtolower($input) == "next call") {
            $this->calls = $this->nextCall($input);
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "call") {
                $this->getCall();
                return;
            }
        }

        $this->getCall();

        return;
    }
}
