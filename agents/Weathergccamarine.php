<?php
namespace Nrwtaylor\StackAgentThing;

// Weathergccamarine
// https://weather.gc.ca/marine/weatherConditions-lightstation_e.html?mapID=02&siteID=16200

class Weathergccamarine extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->weathergccamarine_terse_flag = "on";

    }

    function run()
    {
        //$this->doWeathergccamarine();
    }

    // So this is where the work is.
    public function initWeathergccamarine()
    {
        //https://weather.gc.ca/marine/forecast_e.html?mapID=02&siteID=07010

        $this->allowed_lightstations_resource =
            "weathergccamarine/weathergccamarine.php";
        $lightstations_endpoints = [];
        if (
            file_exists(
                $this->resource_path . $this->allowed_lightstations_resource
            )
        ) {
            $allowed_lightstations = require $this->resource_path .
                $this->allowed_lightstations_resource;
        }
    }

    public function set()
    {
        $this->thing->Write(
            ["weathergccamarine", "refreshed_at"],
            $this->current_time
        );
    }

    public function get()
    {
        $this->getWeathergccamarine();
    }

    public function getWeathergccamarine()
    {
        //$url =
        //    "https://weather.gc.ca/marine/marine_bulletins_e.html?Bulletin=fqcn10.cwvr";

        $url =
            "https://weather.gc.ca/marine/weatherConditions-lightstation_e.html?mapID=02&siteID=16200";

        // Same report from.
        // https://weather.gc.ca/marine/weatherConditions-lightstation_e.html?mapID=02&siteID=16200

        $read_handler = new Read($this->thing, "read");
        $read_handler->urlRead($url, 1);
        $this->response .= "[" . trim($read_handler->response) . "] "; // Happy memories of Dave VE7CNV's.
        // http://www.luther.ca/~dave7cnv/cdnspelling/cdnspelling.html

        $dom = new \DOMDocument();
        $dom->loadHTML($read_handler->contents);

        $result = "";

        $blocks = $dom->getElementsByTagName("pre");
        foreach ($blocks as $i => $block) {
            $result = trim($block->nodeValue);
            // $result = $dom->getElementsByTagName("pre")[0]->nodeValue;
            $messages = [];
            $message = [];
            $lines = explode("\n", $result);

            foreach ($lines as $i => $line) {
                // Read each lightstation report.
                $line = trim($line);
                if ($line == "") {
                    continue;
                }
                //$message[] = $line;
                $lightstation = trim(substr($line, 0, 13));
                $text = trim(str_replace($lightstation, "", $line));

                $message = ["lightstation" => $lightstation, "text" => $text];

                if ($lightstation === "SUPPLEMENTARY") {
                    continue;
                }

                $messages[] = $message;
                //  $message = []; // reset the message.
            }
        }

        if (count($messages) == 0 or $messages == null) {
            return false;
        }

        $this->lightstations = $messages;

        /*
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response =
                "ENVIRONMENT CANADA MARINE WEATHER | " . strtolower($v) . ".";

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
*/
    }
/*
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }
*/
    function makeMessage()
    {
        $message = "";
        // Try a different pattern. input is universally available.
        $tokens = explode("-", $this->getSlug($this->input));
        $match = false;
        foreach ($tokens as $j => $token) {
            foreach ($this->lightstations as $i => $lightstation) {
                if (
                    strtolower($lightstation["lightstation"]) ==
                    strtolower($token)
                ) {
                    $message .=
                        $lightstation["lightstation"] .
                        " " .
                        $lightstation["text"] .
                        ". ";
                    $match = true;
                }
            }
        }

        $this->message = $message;
    }

    function makeSMS()
    {
        $sms =
            strtoupper($this->agent_name) . " | " . "" . $this->message . " ";

        if (
            isset($this->weathergccamarine_terse_flag) and
            $this->weathergccamarine_terse_flag == "on"
        ) {
        } else {
            $sms .= $this->response;
        }
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
        $tokens = explode("-", $this->getSlug($this->input));
        $match = false;
        foreach ($tokens as $j => $token) {
            foreach ($this->lightstations as $i => $lightstation) {
                if (
                    strtolower($lightstation["lightstation"]) ==
                    strtolower($token)
                ) {
                    //$this->response .= $lightstation['lightstation'] . " " . $lightstation['text'] .". ";
                    $match = true;
                }
            }
        }

        if ($match === false) {
            $this->response .= "No matching lightstations found. ";
            return;
        }

        $this->response .= "Got lightstation. ";

        return false;
    }
}
