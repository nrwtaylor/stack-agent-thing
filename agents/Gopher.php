<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// https://tools.ietf.org/html/rfc1436

class Gopher extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->retain_for = 1; // Retain for at least 1 hour.
        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];
    }

    public function getGopher($words = null)
    {
        if (!isset($this->keywords)) {
            $this->keywords = $words;
        }

        $words = str_replace(" ", "+", $words);

        $xml = $this->getText($words);

        $verses = $xml->search->result->verses->verse;

        if ($verses == null) {
            $sms_message = "GOPHER";
            $sms_message .= " | No matching verse found for " . $words . ".";
            $sms_message .= " | MESSAGE 'GOPHER words'";
            $this->sms_message = $sms_message;
            return;
        }
        $arr[] = [];
        $sms_messages = [];
        foreach ($verses as $key => $verse) {
            $id = (string) $verse->id;
            $id = strip_tags($id);

            $text = (string) $verse->text;
            $text = strip_tags($text);

            $copyright = (string) $verse->copyright;

            $text = preg_replace("#^\d+#", "", $text);

            $text = preg_replace('/^[a-zA-Z]+$/', "", $text);

            // Remove line breaks
            $text = preg_replace("/\r|\n/", " ", $text);

            $message = $id . " | " . $text;

            $sms_message = "GOPHER";
            $sms_message .= " | " . $message;
            $sms_message .= " | MESSAGE 'GOPHER words'";

            $arr[] = ["id" => $id, "verse" => $text, "message" => $message];
            $sms_messages[] = $sms_message;
        }

        $k = array_rand($sms_messages);
        $this->sms_message = $sms_messages[$k];
        //        $this->sms_message = "testtest";

        $k = array_rand($arr);

        $this->sms_message = "GOPHER";
        $this->sms_message .= " | " . $arr[$k]["message"];

        $this->sms_message .= " | text source Gopher datafeed";
    }

    public function typeGopher($item_type)
    {
        $item_types = [
            "0" => "Item is a file",
            "1" => "Item is a directory",
            "2" => "Item is a CSO phone-book server",
            "3" => "Error",
            "4" => "Item is a BinHexed Macintosh file.",
            "5" =>
                "Item is DOS binary archive of some sort. Client must read until the TCP connection closes.  Beware.",
            "6" => "Item is a UNIX uuencoded file.",
            "7" => "Item is an Index-Search server.",
            "8" => "Item points to a text-based telnet session.",
            "9" =>
                "Item is a binary file! , Client must read until the TCP connection closes.  Beware.",
            "+" => "Item is a redundant server",
            "T" => "Item points to a text-based tn3270 session.",
            "g" => "Item is a GIF format graphics file.",
            "I" =>
                "Item is some kind of image file.  Client decides how to display.",
        ];

        if (isset($item_types[$item_type])) {
            return $item_types[$item_type];
        }
        return true;
    }

    public function nullGopher()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["character", "action"],
            "null"
        );

        $this->message = "GOPHER | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "GOPHER | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    function infoGopher()
    {
        $this->sms_message = "GOPHER";

        $this->sms_message .= " | ";

        $this->sms_message .=
            "Gopher protocol handler. | https://evertpot.com/100/ | ";

        $this->sms_message .= "TEXT HELP";
    }

    function helpGopher()
    {
        $this->sms_message = "GOPHER";

        $this->sms_message .= " | ";

        $this->sms_message .=
            'Text one or more words. | For example, "burrow deep". | ';

        $this->sms_message .= "TEXT GOPHER <word(s)>";

        return;
    }

    function syntaxGopher()
    {
        $this->sms_message = "GOPHER";

        $this->sms_message .= " | ";

        $this->sms_message .= 'Syntax: "<keyword>". | ';

        $this->sms_message .= "TEXT HELP";

        return;
    }

    public function getVerse($url)
    {
        // Set up cURL
        $ch = curl_init();
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't verify SSL certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Return the contents of the response as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Set up authentication
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // Do the request
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function getText($keywords = null)
    {
        if ($keywords == null) {
        }

        // devstack. endpoint has changed.
        $url = "gopher://gopher.quux.org/";

        $response = $this->getVerse($url);
        $burrows = $this->readGopher($response);
        return $burrows;
    }

    public function readGopher($text)
    {
        if (!$this->thing->isData($text)) {
            return;
        }
        $burrows = [];
        $lines = explode("\n", $text);
        foreach ($lines as $i => $line) {
            $burrow = $this->parseGopher($line);
            $burrows[] = $burrow;
        }
        $this->burrows = $burrows;
        return $burrows;
    }

    public function parseGopher($text)
    {
        $type = substr($text, 0, 1);
        $str1 = substr($text, 1);
        $comp = preg_split("/[\t]/", $str1);

        $burrow["type"] = $type;
        $burrow["type_description"] = $this->typeGopher($type);
        $burrow = array_merge($burrow, $comp);
        return $burrow;
    }

    public function makeTXT()
    {
        $txt = "";
        foreach ($this->burrows as $i => $burrow) {
            $txt .= $burrow["type_description"] . "\n";
            $element_count = 0;
            $flag = true;
            while ($flag) {
                if (isset($burrow[$element_count])) {
                    $txt .= " " . $burrow[$element_count];
                    $element_count += 1;
                    continue;
                }
                $flag = false;
            }
            $txt .= "\n";
        }

        $this->thing_report["txt"] = $txt;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["choices"] = false;
        $this->thing_report["info"] = "SMS sent";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] = "Connector to Gopher protocol.";
    }

    private function nextWord($phrase)
    {
    }

    public function readSubject()
    {
        // For the stack's gopher service.
        // TODO dev.
        $emoji_thing = new Emoji($this->thing, "emoji");
        $thing_report = $emoji_thing->thing_report;

        if (isset($emoji_thing->emojis)) {
            $input = ltrim(strtolower($emoji_thing->translated_input));
        }

        $keywords = ["gopher"];

        //$input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            $input = $this->subject;

            if (strtolower($input) == "gopher") {
                $this->getGopher();
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "gopher":
                            $prefix = "gopher";
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);
                            $this->getGopher($words);
                            return;

                        default:

                        //echo 'default';
                    }
                }
            }
        }

        $this->nullGopher();
        return "Message not understood";
    }
}
