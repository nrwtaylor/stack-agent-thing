<?php
namespace Nrwtaylor\StackAgentThing;

// tested with google mail > account > privacy > data exporter (with label)
// https://takeout.google.com/settings/takeout

/*

https://www.loc.gov/preservation/digital/formats/fdd/fdd000383.shtml
A message encoded in MBOX format begins with a "From " line,
continues with a series of non-"From " lines, and ends
with a blank line.  A "From " line means any line in the
message or header that  begins with the five characters
'F', 'r', 'o', 'm', and ' ' (space). The "From " line structure
is From sender date moreinfo

Test MBOX file available at
Put into resources/mbox/sample.mbox
https://github.com/qsnake/git/blob/master/t/t5100/sample.mbox

*/

class Mbox extends Agent
{
    // Responds to a query from $from to useragent@stackr.co
    public function init()
    {
        $keywords = [
            "mbox",
            "Berkeley format",
            "email",
            "loademail",
            "load email",
            "emailload",
            "email load",
        ];

        $this->default_resource_path = "/home/nick/txt/";
        $this->default_file_name = "All mail Including Spam and Trash-007.mbox";

        $this->default_resource_path = $this->resource_path . "mbox/";
        $this->default_file_name = "sample.mbox";

        $this->mbox_file_location = $this->settingsAgent([
            "mbox",
            "mbox_file_location",
        ]);

        $this->keywords = [];

        $this->messages = [];
    }

    public function set()
    {
        $this->thing->json->writeVariable(["mbox", "reading"], $this->reading);

        $this->reading = count($this->messages);
        $this->thing->json->writeVariable(["mbox", "reading"], $this->reading);
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "mbox",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["mbox", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable(["mbox", "reading"]);
    }

    public function filterMbox($search_object)
    {
        if (is_string($search_object)) {
            $search_words = $search_object;
        } else {
            return [];
        }

        $filtered_messages = [];
        if ($search_words == "") {
            $this->response .= "No search words provided. ";
        } else {
            $this->response .=
                'Filtered MBOX file by "' . $this->search_words . '". ';
            $filtered_messages = $this->filterArr(
                $this->messages,
                $search_words
            );
        }

        return $filtered_messages;
    }

    public function loadMbox($file_name = null)
    {
        if ($file_name == null) {
            if (file_exists($this->mbox_file_location)) {
                $this->response .= "Used stack mbox file. ";
                $file_name = $this->mbox_file_location;
            } else {
                $this->response .= "Used default agent mbox file. ";
                $file_name =
                    $this->default_resource_path . $this->default_file_name;
            }
        }

        if (!file_exists($file_name)) {
            $this->response .= "Could not see mbox file. ";
            return true;
        }

        $i = 0;
        $data_grams = [];

        $data_gram = null;
        $from = null;
        $to = null;
        $subject = null;
        $date = null;
        $text = "";

        $fd = fopen($file_name, "rb");
        while (($line = fgets($fd)) !== false) {
            if (trim($line) === "") {
                $blank_line = true;
            }

            // Recognize datagram start
            if (substr($line, 0, 5) === "From ") {
                if ($blank_line === true) {
                    $blank_line = false;

                    // Save datagram.

                    $data_gram = [
                        "to" => $to,
                        "from" => $from,
                        "message" => $subject,
                        "time_sent" => $date,
                        "text" => $text,
                    ];

                    $data_grams[] = $data_gram;
                    $i += 1;

                    // Reset datagram

                    $data_gram = null;
                    $from = null;
                    $to = null;
                    $subject = null;
                    $date = null;
                    $lines = [];
                }
            }

            $text .= $line . "\n";

            if (substr($line, 0, 5) === "From:") {
                $from = ltrim(substr($line, 5));
            }

            if (substr($line, 0, 3) === "To:") {
                $to = ltrim(substr($line, 3));
            }

            if (substr($line, 0, 8) === "Subject:") {
                $subject = ltrim(substr($line, 8));
            }

            if (substr($line, 0, 5) === "Date:") {
                $date = ltrim(substr($line, 5));
            }

            // TODO Extract other typical fields
        }
        fclose($fd);

        // $this->response .= "Loaded " . $i . " email(s). ";
        $this->messages = $data_grams;

        $this->message = null;
        if (isset($data_grams[0])) {
            $this->message = $data_grams[0];
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function makeSnippet()
    {
        $web = "<b>MBOX Messages</b";
        $web .= "<p>";
        $web .= $this->snippetArr($this->filtered_messages);
        $this->snippet = $web;
        $this->thing_report["snippet"] = $web;
    }

    public function makeWeb()
    {
        if ($this->search_words !== "") {
            $web = "Search: " . $this->search_words;
            $web .= "<p>";
            $web .= $this->snippet;
        } else {
            $web .= "No filter term provided.";
            $web .= "<p>";
        }
        $this->web = $web;
        $this->thing_report["web"] = $web;
    }

    public function makeSMS()
    {
        $sms = "MBOX | " . $this->message . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeMessage()
    {
        $message = "No messages found.";

        if (isset($this->messages)) {
            if (count($this->messages) > 1) {
                $message = count($this->messages) . " messages loaded.";
            } elseif (count($this->messages) == 1) {
                $message = "1 message loaded.";
            }
        }

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->filterAgent($this->input, $this->keywords);

        // Did we see anything?
        if (mb_strlen($input) !== mb_strlen($filtered_input)) {
            $this->score = 10;
        }

        $this->search_words = $filtered_input;

        $this->loadMbox();

        $this->filtered_messages = [];
        if ($this->search_words !== "") {
            $this->filtered_messages = $this->filterMbox($this->search_words);
        }
    }
}
