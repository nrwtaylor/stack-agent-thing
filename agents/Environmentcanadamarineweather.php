<?php
namespace Nrwtaylor\StackAgentThing;

class EnvironmentCanadaMarineWeather extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->environmentcanadamarineweather_terse_flag = "on";
    }

    function run()
    {
        $this->doEnvironmentCanadaMarineWeather();
    }

    public function set()
    {
        $this->thing->Write(
            ["environmentcanadamarineweather", "refreshed_at"],
            $this->current_time
        );
    }

    public function doEnvironmentCanadaMarineWeather()
    {
        $url =
            "https://weather.gc.ca/marine/marine_bulletins_e.html?Bulletin=fqcn10.cwvr";

        $read_handler = new Read($this->thing, "read");
        $read_handler->urlRead($url, 1);
        $this->response .= $read_handler->response;

        $dom = new \DOMDocument();
        $dom->loadHTML($read_handler->contents);

        $result = "";

        $result = $dom->getElementsByTagName("pre")[0]->nodeValue;
        $messages = [];
        $message = [];
        $lines = explode("\n", $result);

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($line == "") {
                continue;
            }
            $message[] = $line;

            if (substr(strtolower($line), 0, 3) === strtolower("END")) {
                $messages[] = $message;
                $message = [];
            }
        }
        foreach ($messages as $j => $message) {
            $header = array_shift($message);
            $footer = array_pop($message);
            $message = implode(" ", $message);
            $messages[$j] = [
                "text" => $message,
                "header" => $header,
                "footer" => $footer,
            ];
        }

        $this->message = $this->textEnvironmentcanadamarineweather(
            $messages[0]["text"]
        );

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

    public function textEnvironmentcanadamarineweather($text)
    {
        $parts = explode("Systems position.", $text);
        return trim($parts[1]);
    }

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

    function makeSMS()
    {
        $sms =
            strtoupper($this->agent_name) . " | " . "" . $this->message . " ";

        if (
            isset($this->environmentcanadamarineweather_terse_flag) and
            $this->environmentcanadamarineweather_terse_flag == "on"
        ) {
        } else {
            $sms .= $this->response;
        }
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
        return false;
    }
}
