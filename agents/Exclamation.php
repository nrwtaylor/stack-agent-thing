<?php
namespace Nrwtaylor\StackAgentThing;

class Exclamation extends Agent
{
    public function init()
    {
        $this->keywords = [];
    }

    public function initExclamation()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "exclamation",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["exclamation", "refreshed_at"],
                $time_string
            );
        }
    }

    public function get()
    {
        $this->thing->json->setField("variables");

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable([
            "exclamation",
            "reading",
        ]);
    }

    public function set()
    {
        $this->thing->json->setField("variables");

        $this->thing->json->writeVariable(
            ["exclamation", "reading"],
            $this->reading
        );
        //        $this->thing->json->writeVariable(array("exclamation", "reading"), $this->reading);
    }

    public function stripExclamation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace(
            '/[\!]/i',
            $replace_with,
            $input
        );
        return $unpunctuated;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

    }

    public function makeSMS()
    {
        if (is_numeric($this->reading)) {
            $text = "Saw " . $this->reading . ' exclamation marks.';

            if ($this->reading == 1) {
                $text = "Saw " . $this->reading . ' exclamation mark.';
            }
        }
        $sms = 'EXCLAMATION | ' . $text . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function countExclamation($text = null)
    {
        if ($text == null) {
            return true;
        }

        $count = substr_count($text, "!");

        return $count;
    }

    public function readExclamation($text = null)
    {
        $this->reading = $this->countExclamation($text);
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $this->readExclamation($input);

        $keywords = ['!', 'interrobang', 'exclamation'];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case '!':
                            $prefix = '!';
                        case 'interrobang':
                            $prefix = 'interrobang';
                        case 'exclamation':
                            if (!isset($prefix)) {
                                $prefix = 'exclamation';
                            }

                            return;

                        default:
                    }
                }
            }
        }

    }
}
