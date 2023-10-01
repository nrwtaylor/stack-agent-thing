<?php
namespace Nrwtaylor\StackAgentThing;

/*
tests

agent claws
agent claws when
agent claws --channel=txt "/var/www/stackr.test/resources/call/call-test-CapiTalized.txt"
agent claws --channel=txt "/var/www/stackr.test/resources/call/call-test-CapiTalized.txt" "/var/www/stackr.test/resources/call/call-test.txt"

1009  1013  858

*/

class Claws extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doClaws();
    }

    function claws()
    {
    }

    function test()
    {
        if ($this->claws_test_flag != "on") {
            return;
        }

        $this->response .= "No test performed. ";
    }

    public function doClaws()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "CLAWS | " . strtolower($v) . ".";

            $this->claws_message = $response; // mewsage?
        } else {
            $this->claws_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->makeClaws();
        $this->makeUrl();
        $this->makeZoommtg();

        $this->emacsorgClaws();

        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a tool for making decisions about your MH email messages.";
        $this->thing_report["help"] = "Try CLAWS <file name>.";

        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeChoices()
    {
        //        $this->thing->choice->Create('channel', $this->node_list, "claws");
        //        $choices = $this->thing->choice->makeLinks('claws');
        //        $this->thing_report['choices'] = $choices;
    }

    /*
        Load file provided by Claws.
        Assume it is MH.
        dev - test filetype and respond appropriately.
    */
    public function loadClaws($text = null)
    {
        if ($text == null) {
            return true;
        }
        $filename = trim($text, '"');

        if (!file_exists($filename)) {
            return true;
        }

        if (is_string($filename)) {
            $mh_contents = file_get_contents($filename);
            $contents = $this->textMH($mh_contents);
            return $contents;
        }
        return true;
    }

    public function readClaws($text = null)
    {
    }

    /*

Code to update when (lightandmatter / ben crowell) calendar.
Start by appending file and relying on user to
manually duplicates.

dev - Detect duplicates.

*/
    public function whenClaws()
    {
        if ($this->claws_when_flag != "on") {
            return;
        }

        // Code to write When calendar line item goes here.

        // Build entry for when calendar
        $line = "test item";

        $this->updateWhen($line);
        $this->response .= "Wrote item to When calendar file. ";
    }

    public function emacsorgClaws()
    {
        // Add emacsorg to agent command string to trigger writing of emacs buffer file

        if (
            isset($this->claws_emacsorg_flag) and
            $this->claws_emacsorg_flag != "on"
        ) {
            return;
        }
        // Code to write an org item goes here.

        /*
** Strategy Meeting
   SCHEDULED: <2021-03-31 Wed 11:00>
   https://meeting.example.com/place/z.phz?MTID=a1a1a1a1a1
*/

        // Build entry for emacs org mode
        // and append to appropriate emacs buffer

        //$text = "";
        $count = 0;
        foreach ($this->claws_items as $i => $claws_item) {
            $text = $this->textEmacs($claws_item);
            $this->updateEmacs($text);
            $count += 1;
        }
        $this->response .= "Wrote " . $count . " items to Emacs buffer file. ";
    }

    // for testing.
    // Might be better as Link but try Url first.
    public function makeUrl()
    {
        // Only use the first claws item.
        // Error if given more than one?
        if (!isset($this->claws_items[0])) {
            return true;
        }
        $claws_item = $this->claws_items[0];

        $url = "No URL.";

        if (isset($claws_item["call"]["url"])) {
            $url = $claws_item["call"]["url"];
        }

        $this->thing_report["url"] = $url;
        $this->url_message = $url;
    }

    // agent --channel=zoommtg --meta=off claws "/home/jsae/Mail/Vector/39654" | xargs xdg-open

    public function makeZoommtg()
    {
        // Only use the first claws item.
        // Error if given more than one?
        if (!isset($this->claws_items[0])) {
            return true;
        }
        $claws_item = $this->claws_items[0];

        $url = "No URL.";
        if (isset($claws_item["call"]["url"])) {
            $url = $claws_item["call"]["url"];
        }

        if ($claws_item["call"]["service"] === "zoom") {
            $text = $claws_item["call"]["url"];

            $text = str_replace("/j/", "/join?action=join&confno=", $text);
            $text = str_replace("?pwd=", "&pwd=", $text);
            $text = str_replace("https://", "zoommtg://", $text);

            $url = $text;
        }

        $this->thing_report["zoommtg"] = $url;
        $this->zoommtg_message = $url;
    }

    public function makeClaws()
    {
        $this->thing_report["claws"] = "Custom report for Claws. Test.";
    }

    public function makeSMS()
    {
        $count = count($this->claws_items);

        $sms = "CLAWS | " . "Read " . $count . " items. See TXT response.";
        $sms .= " ";
        $sms .= $this->response;

        $this->thing_report["sms"] = $sms;
        $this->sms_message = $sms;
    }

    public function emacsorgtextClaws()
    {
        $count = 0;
        $text = "";
        foreach ($this->claws_items as $i => $claws_item) {
            $text .= $this->textEmacs($claws_item);
            $count += 1;
        }
        return $text;
    }

    public function makeTXT()
    {
        if (
            isset($this->claws_emacsorg_flag) and
            $this->claws_emacsorg_flag == "on"
        ) {
            $txt = $this->emacsorgtextClaws();

            $this->thing_report["txt"] = $txt;
            $this->txt = $txt;
            return;
        }

        $txt = "CLAWS\n";
        foreach ($this->claws_items as $i => $claws_item) {
            $text_claws = $this->textCall($claws_item["call"]);
            $call = $claws_item["call"];

            // URL is available like this.
            // $text_claws .= "xx".$claws_item["call"]['url']."xx"."\n";

            $text_claws .= $claws_item["subject"] . "\n";

            $line_text = "No line found.";
            if (
                $claws_item["dateline"] !== null and
                isset($claws_item["dateline"]["line"])
            ) {
                $line_text = $claws_item["dateline"]["line"];

                $text_claws .= $line_text . "\n";
                $text_claws .=
                    $this->timestampDateline($claws_item["dateline"]) . "\n";
            }

            $call = $claws_item["call"];

            if (
                $this->isThing($call["password"]) and
                $this->isThing($call["access_code"]) and
                $this->isUrl($call["url"])
            ) {
                $txt .= "FOUND MEETING DETAILS\n";
            }

            if ($this->isUrl($call["url"])) {
                $txt .= "FOUND URL\n";
            }

            $txt .= $this->textHtml($text_claws) . "\n";

            // Tidy up text display.
            //            $txt = html_entity_decode($text_claws). "\n";
            //           $txt .= $text_claws . "\n";
        }
        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    // For testing
    // is it not null, true, false, X, Z, ""
    public function isThing($text)
    {
        if ($text == null) {
            return false;
        }
        if ($text === true) {
            return false;
        }
        if ($text == "") {
            return false;
        }
        if (strtoupper($text) == "Z") {
            return false;
        }
        if (strtoupper($text) == "X") {
            return false;
        }
        return true;
    }

    public function scoreAt($dateline)
    {
        // Multiple dimensions.
        // Here we care about do we have enough to know when a meeting is.

        $context = "meeting";

        $score = 10 - $this->falsesCount($dateline);

        return $score;
    }

    function datelinesCall($text = null)
    {
        if ($text == null) {
            return;
        }
        $paragraph_agent = new Paragraph($this->thing, $text);
        $paragraphs = $paragraph_agent->paragraphs;

        $datelines = [];
        // Read every line for a date.

        $count = 0;
        foreach ($paragraphs as $i => $paragraph) {
            // Don't waste time on empty paragraphs.
            if (trim($paragraph) == "") {
                continue;
            }

            // Ignore code (ie css);
            if ($this->isCode($paragraph) === true) {
                continue;
            }
            if ($this->isStreet($paragraph) === true) {
                continue;
            }

            // Check for things like #00363a
            if ($this->isColour($paragraph) === true) {
                continue;
            }

            $count += 1;

            $containsDigit = preg_match("/\d/", $paragraph);
            if ($containsDigit == false) {
                continue;
            } // No digit. So no date. Reasonable?

            $dateline = $this->extractDateline($paragraph);
            if ($dateline == false) {
                continue;
            }

            $dateline["score"] = $this->scoreAt($dateline, "meeting");
            $datelines[] = $dateline;
        }

        // TODO extract dates over multiple paragraphs

        foreach ($datelines as $i => $dateline) {
        }

        // Sort by best to worst match.
        // Subject to how defined the date is.
        // Expect it to be missing stuff. Like year.
        // And to perform poorly if all we get is "Details for the call Thursday night".

        usort($datelines, function ($a, $b) {
            return $b["score"] - $a["score"];  // a < b. If a < b, true.  b-a = true
        });

        return $datelines;
    }

    public function readSubject()
    {
        $input = $this->input;

        // Note for dev.
        // Try this as $this->assert($input, false).

        //        $filtered_input = $this->assert($input);
        //        $this->filenameClaws($filtered_input);

        // Recognize if the instruction has "when" in it.
        // Set a flag so that we can later create a calendar item if needed.
        $indicators = [
            "emacsorg" => ["emacsorg"],
            "when" => ["when"],
            "test" => ["test"],
        ];
        $this->flagAgent($indicators, strtolower($input));

        $string = $input;
        $str_pattern = "claws";
        $str_replacement = "";
        $filtered_input = $input;
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

        // See note above to re-factor above.

        $this->claws_items = [];
        $tokens = explode(" ", $filtered_input);

        foreach ($tokens as $i => $token) {
            $filename = trim($token);

            // Delegating contents to agents for processing

            if (!file_exists($filename)) {
                continue;
            }
            $contents = $this->loadClaws($filename);

            // Pass contents through MH routine to remove trailing =
            //$subject = $this->subjectMH($contents);
            //$body = $this->bodyMH($contents);

            // Pass contents to call to extract conference details.
            // Tested on Webex.
            // Needs further service development.
            // Prioritize Zoom dev test.

            //            $isEmail = $this->isEmail($contents);

            //if ($isEmail === true) {
            $meta = $this->metaEmail($contents);
            $parts = $this->attachmentsEmail($contents);
            $events = [];

            foreach ($parts as $i => $part) {
                if ($part["content_type"] === "text/calendar") {
                    $event = $this->eventCalendar($part);

                    $uid = $event->uid;
                    if ($event->uid === null) {
                        $uid = $this->thing->getUuid();
                    }
                    $events[$uid] = $event;
                }
            }
            $calendar_events_count = count($events);

            if ($calendar_events_count == 1) {
                // Found exactly one calendar event.
                $timezone = $event->calendar_timezone;

                $subject = $event->summary;

                // TODO - Consider datelineCall.
                // dev

                $datelines = $this->datelinesCall($event->start_at);

                $dateline = $datelines[0];
                $dateline["line"] = $event->summary;

                $call = $this->readCall($event->description);
            } else {
                $subject = $this->subjectMH($contents);

                $body = $this->bodyMH($contents);
                $call = $this->readCall($body);

                // Try to figure out date from body text.

                // This does poorly with large chunks of text.
                /*
                $dateline = $this->extractAt($body);
                $subject_at_score = 0;
                $subject_at_score = $this->scoreAt($dateline, "meeting");
*/

                // TODO - Check if the subject has a well qualified date time.
                // dev start with a simple score of missing information.
                // dev assess whether date time is "adequate"

                //                if ($subject_at_score <= 4) {
                // Otherwise ... see if there is a better date time in the combined contents.
                $datelines = $this->datelinesCall($subject . "\n" . $body);

                // dev
                // TODO Assess datelines for validity.

                // Pick best dateline.
                if (isset($datelines[0])) {
                    $dateline = $datelines[0];
                }
                //                }
            }

            $this->claws_items[] = [
                "subject" => $subject,
                "call" => $call,
                "dateline" => $dateline,
                "meta" => $meta,
            ];
            //}
        }
        // get an MH reader to clean up the format - done
        // See what we get from Call.
        //$call_agent = new Call($this->thing, "call");

        // desired actions - priority and focuses
        // 1. insert with conference link into when calendar

        //        foreach ($this->claws_items as $i=>$claws_item) {
        //           $timestamp = $this->timestampDateline($claws_item['dateline']);

        //           $this->whenClaws($timestamp . " , " . $subject . " [" . implode(" ", $claws_item['call']) . "] ");
        //        }

        // 2. take conference link to forward it in an email (?)
        // 3. clickable action to connect to conference link (?)
        // 4. include subject of original email
        return false;
    }
}
