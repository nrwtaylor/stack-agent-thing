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
    public $var = 'hello';

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

        //        $this->input = 'claws "/var/www/stackr.test/resources/call/call-test-CapiTalized.txt" "/var/www/stackr.test/resources/call/call-test.txt"';
        //        $this->readSubject();

        $this->response .= "No test performed. ";
    }

    public function doClaws()
    {
        if ($this->agent_input == null) {
            $array = ['miao', 'miaou', 'hiss', 'prrr', 'grrr'];
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
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a claws keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        $this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
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
        var_dump("Claws readClaws");
        var_dump($text);
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

    // for testing.
    // Might be better as Link but try Url first.
    public function makeUrl()
    {
        // Only use the first claws item.
        // Error if given more than one?
        if (!isset($this->claws_items[0])) {return true;}
        $claws_item = $this->claws_items[0];

        $url = "No URL.";
        if (isset($claws_item['call']['url'])) {$url = $claws_item['call']['url'];}
//var_dump($claws_item);
        if ($claws_item['call']['service'] === 'zoom') {
            $password = $claws_item['call']['password'];
            $access_code = $claws_item['call']['access_code'];
            $access_code = str_replace(" ","", $access_code);
            $url = "zoommtg://ca01web.zoom.us/join?action=join&confno=". $access_code . "&pwd=" . $password;
        }

        $this->thing_report['url'] = $url;
        $this->url_message = $url;
    }


    public function makeClaws()
    {
        $this->thing_report['claws'] = "Custom report for Claws. Test.";
    }

    public function makeSMS()
    {
        $count = count($this->claws_items);

        $sms = "CLAWS | " . "Read " . $count . " items. See TXT response.";
        $sms .= " ";
        $sms .= $this->response;

        $this->thing_report['sms'] = $sms;
        $this->sms_message = $sms;
    }

    public function makeTXT()
    {

        $txt = "CLAWS\n";
        foreach ($this->claws_items as $i => $claws_item) {

            $text_claws = $this->textCall($claws_item['call']);

            $text_claws .= $claws_item['subject'] . "\n";
            $text_claws .= $claws_item['dateline']['line'] . "\n";
            $text_claws .=
                $this->timestampDateline($claws_item['dateline']) . "\n";

            $txt .= $text_claws . "\n";
        }
        $txt .= "\n";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function scoreAt($dateline)
    {
        // Multiple dimensions.
        // Here we care about do we have enough to know when a meeting is.

        $context = 'meeting';

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

            // Segmentation if this continues past count 2.
            // 3f55 29 January 2021
            $count += 1;
            //if ($count > 3) {break;}

            $containsDigit = preg_match('/\d/', $paragraph);
            if ($containsDigit == false) {
                continue;
            } // No digit. So no date. Reasonable?

            $dateline = $this->extractDateline($paragraph);
            if ($dateline == false) {
                continue;
            }

            $dateline['score'] = $this->scoreAt($dateline, "meeting");
            $datelines[] = $dateline;
        }

        // TODO extract dates over multiple paragraphs

        foreach($datelines as $i=>$dateline) {
        //    var_dump($dateline);
        }

        // Sort by best to worst match.
        // Subject to how defined the date is.
        // Expect it to be missing stuff. Like year.
        // And to perform poorly if all we get is "Details for the call Thursday night".

        usort($datelines, function ($a, $b) {
            return $a['score'] < $b['score'];
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
            'when' => ['when'],
            'test' => ['test'],
        ];
        $this->flagAgent($indicators, strtolower($input));

        //        $filtered_input = $this->assert($input, false);
        //var_dump($filtered_input);
        //exit();

        $string = $input;
        $str_pattern = 'claws';
        $str_replacement = '';
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
            $contents = $this->loadClaws($filename);

            // Pass contents through MH routine to remove trailing =

            //$meta = $mh_agent->metaMH($contents);
            $subject = $this->subjectMH($contents);

            $body = $this->bodyMH($contents);

            // TODO dev readEmail to properly extract text body.
            //var_dump($body);

            //var_dump("Claws metaMH response");
            //var_dump($subject);

            // Pass contents to call to extract conference details.
            // Tested on Webex.
            // Needs further service development.
            // Prioritize Zoom dev test.

            $call = $this->readCall($body);
            //var_dump("Claws readCall response");
            //var_dump($call);

            $dateline = $this->extractAt($body);
            //var_dump("Claws readSubject");
            //var_dump("TODO - Read at in subject. See Claws");
            //var_dump($at);

            $subject_at_score = 0;
            if ($dateline != null) {
                $subject_at_score = $this->scoreAt($dateline, "meeting");
            }

            // TODO - Check if the subject has a well qualified date time.
            // dev start with a simple score of missing information.
            // dev assess whether date time is "adequate"
            if ($subject_at_score <= 4) {
                // Otherwise ... see if there is a better date time in the combined contents.
                $datelines = $this->datelinesCall($subject . "\n" . $body);
                // Pick best dateline.

                $dateline = $datelines[0];
            }

            $this->claws_items[] = [
                'subject' => $subject,
                'call' => $call,
                'dateline' => $dateline,
            ];
        }

        // get an MH reader to clean up the format
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
