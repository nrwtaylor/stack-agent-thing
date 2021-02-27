<?php
namespace Nrwtaylor\StackAgentThing;

// dev not tested

class Html extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doHtml();
    }

    public function doHtml()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "HTML | " . strtolower($v) . ".";

            $this->html_message = $response; // mewsage?
        } else {
            $this->html_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This agent handles HTML.";
        $this->thing_report["help"] = "This is about recognizing and processing HTML.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["html" => ["html", "dog"]];
        $this->sms_message = "" . $this->html_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "html");
        $choices = $this->thing->choice->makeLinks("html");
        $this->thing_report["choices"] = $choices;
    }

    function textHtml($html)
    {
        $detagged = $this->stripHtml($html);
        $text = html_entity_decode($detagged);

        $breaks = [
            "<p>",
            "</p>",
            "<br />",
            "<br>",
            "<br/>",
            "<br />",
            "&lt;br /&gt;",
            "&lt;br/&gt;",
            "&lt;br&gt;",
        ];
        $text = str_ireplace($breaks, "\r\n", $text);

        $text = preg_replace("/\s+/", " ", $text);

        // Deal with &nbsp etc
        //$text = htmlspecialchars_decode($text)
        return $text;
    }
    // test
    function stripHtml($html)
    {
        $urls = $this->extractUrls($html);
        // Strip html tags.
        if (is_array($urls)) {
            foreach ($urls as $u => $url) {
                $html = str_replace("<" . $url . ">", " " . $url . " ", $html);
            }
        }

        $stripped_html = strip_tags(str_replace("<", " <", $html));

        return $stripped_html;

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $result = "";
        foreach ($dom->getElementsByTagName("p") as $node) {
            if (strstr($node->nodeValue, "Legal Disclaimer:")) {
                break;
            }
            $result .= $node->nodeValue;
        }
        echo $result;
    }

    public function readSubject()
    {
        return false;
    }
}
