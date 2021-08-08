<?php
namespace Nrwtaylor\StackAgentThing;

class Googlecalendar extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doGooglecalendar();
    }

    public function doGooglecalendar()
    {
        if ($this->agent_input == null) {
            $this->calendar_message = $this->calendar_text; // mewsage?
        } else {
            $this->calendar_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This agent communicates with Google Calendars.";
        $this->thing_report["help"] = "This is about seeings Events.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeWeb()
    {
        $web = "";

        $this->web = $web;
        $this->thing_report['web'] = $this->web;
    }

    function makeSMS()
    {
        $this->node_list = ["google calendar" => ["google calendar", "dog"]];
        $this->sms_message =
            "GOOGLE CALENDAR | " .
            $this->calendar_message .
            " " .
            $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            'channel',
            $this->node_list,
            "google calendar"
        );
        $choices = $this->thing->choice->makeLinks('google calendar');
        $this->thing_report['choices'] = $choices;
    }

    public function icsGooglecalendar($text = null)
    {
        if ($text == null) {
            if (!isset($this->file)) {
                return true;
            }

            $text = $this->file;
        }

        $tokens = explode("@", $text);

        if (count($tokens) != 2) {
            $tokens = explode("%40", $text);
            if (count($tokens) != 2) {
                return true;
            }
        } // Invalid input.

        // TODO Test
        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $email_address = urlencode($text);
            $ics_url =
                "https://calendar.google.com/calendar/ical/" .
                $email_address .
                "/public/basic.ics";
            $this->addresses[] = $ics_url;
            return $ics_url;
        }

        $text_test = mb_ereg_replace('%40', '@', $text);
        // TODO Test
        if (filter_var($text_test, FILTER_VALIDATE_EMAIL)) {
            // Is already url encoded... probably.
            $email_address = $text;
            $ics_url =
                "https://calendar.google.com/calendar/ical/" .
                $email_address .
                "/public/basic.ics";
            $this->addresses[] = $ics_url;
            return $ics_url;
        }

        return false;
    }

    public function addressesGooglecalendar($text = null)
    {
        if (stripos($text, "https://calendar.google.com/calendar/") !== false) {
            $text = str_replace(['?cid=', '&cid='], '&cid=', $text);
            $addresses = explode('&cid=', $text);

            array_shift($addresses);
            if (count($addresses) == 0) {
                return true;
            }
            $this->addresses = array_merge($this->addresses, $addresses);
            return $addresses;
        }
        return false;
    }

    public function readSubject()
    {
        $input = $this->subject;
        if (isset($this->agent_input) and $this->agent_input != "") {
            $input = $this->agent_input;
        }

        // https://stackoverflow.com/questions/9598665/php-replace-first-occurrence-of-string->
        $string = $input;
        $str_pattern = 'googlecalendar';
        $str_replacement = '';

        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }

        $filtered_input = trim($filtered_input);

        if ($filtered_input == "") {
            return;
        }

        $this->file = $filtered_input;

        $this->calendar_text = "empty";
        $response = $this->icsGooglecalendar($this->file);

        if (is_string($response)) {
            $this->response .= "Saw file " . $this->file . ". ";
        }

        $this->addressesGooglecalendar($this->file);

        $calendar_count = count($this->addresses);
        $this->calendar_text = "Saw " . $calendar_count . " address(es).";

        return false;
    }
}
