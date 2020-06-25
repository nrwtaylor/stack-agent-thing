<?php
namespace Nrwtaylor\StackAgentThing;

use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Say extends Agent
{
    public $var = 'hello';

    function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        //$this->pain_score = null;

        $this->sqlresponse = null;

        $this->thing_report['info'] =
            'This is the pain manager responding to a request.';
        $this->thing_report['help'] =
            'This is the pain manager.  PAIN <1 to 10>.  PAIN <text>.';

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["forget"]];

//        $this->thing->log('running on Thing ' . $this->uuid . '');
//        $this->thing->log('received this Thing "' . $this->subject . '"');
//        $this->thing->log('completed');
    }

    public function get()
    {
        // Read the group agent variable
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "say",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->group_id = $this->thing->json->readVariable(["say", "group_id"]);

        if ($this->group_id == false) {
            // No group_id found on this Thing either.
            $this->findGroup();
        }
    }

    public function findGroup()
    {
        $group_thing = new Group($this->thing, "find");

        $this->group_id = "open";
        $this->group_id = $group_thing->thingreport['groups'][0];

        //$this->response .= "Found group " . $this->group_id . ". ";
    }

    public function notePain($text = null)
    {
        if ($text == null) {
            return true;
        }
        //    if ($this->pain_score != null) {
        // Then this Thing has no group information
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["say", "refreshed_at"],
            $time_string
        );
        $this->thing->json->writeVariable(
            ["say", "group_id"],
            strtoupper($this->group_id)
        );
        //    }

        // Generate a Thing

        $say_thing = new Thing(null);
        // remove "say" at start

        $group_message = substr(strstr($this->subject, " "), 1);

        if (
            $group_message == "" or
            $group_message == null or
            $group_message == " "
        ) {
            $this->response .= "No message provided. ";
            //sent to the current group " .
        } else {
            //$this->response .= "Created a SAY thing. ";
            $say_thing->Create(
                'null@stackr.ca',
                $to = "say:" . strtoupper($this->group_id),
                $group_message
            );
            $say_thing->flagGreen(); // to be sure
            $this->response .=
                'Said "' . $group_message . '" to group ' . strtoupper($this->group_id) . ". ";
        }
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report['num_hits'] = $this->num_hits;

        // Generate email response.

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['message'] = $this->thing_report['sms'];

        return $this->thing_report;
    }

    public function makeSMS()
    {
        $sms = "SAY | " . trim($this->response) . " | TEXT GROUP";
        $this->thing_report['sms'] = $sms;
    }

    public function makeWeb()
    {
        $web = '<b>Say Agent</b><br>';
        $web .= "<p>";
        $web .= $this->response;
        $this->thing_report['web'] = $web;
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks('start');

        $this->thing_report['choices'] = $choices;
    }
    /*
    private function nextWord($phrase)
    {
    }
*/
    public function readSubject()
    {
        //$this->response .= "Read subject. ";
        //$this->response = null;

        $keywords = ['say', 's/say', 'say', 'sey'];

        $input = strtolower($this->subject);

        $prior_uuid = null;

        // Split into 1-grams.
        $pieces = explode(" ", strtolower($input));

        // Keywording first
        if (count($pieces) == 1) {
            foreach ($keywords as $keyword) {
                if ($keyword == $input) {
                    // So this should find the 'closest' group.
                    $this->findGroup();
                    $this->num_hits += 1;
                }
                return $this->notePain($input);
            }

            if (is_numeric($this->subject) and strlen($input) == 5) {
                //return $this->response;
            }

            if (is_numeric($this->subject) and strlen($input) == 4) {
                //return $this->response;
            }

            // And if there is no matching keyword.
            // Hmmm.  I feel your pain buddy.
            // $this->notePain($input);
        }

        $filtered_input = $this->assert($input);
        $this->notePain($filtered_input);
        return;

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case '?':
                            $this->thing->log('noted the question');

                            if ($key + 1 > count($pieces)) {
                                //echo "last word is pain";
                                break;
                                //$this->pain_score = null;
                                // I feel your pain buddy?
                                $this->notePain($input);
                                return;
                            } else {
                                $value = $pieces[$key + 1];
                                if (is_numeric($value)) {
                                    $this->sayWhatis();
                                    return;
                                } elseif (
                                    $value == 'score' and
                                    is_numeric($pieces[$key + 2])
                                ) {
                                    return;
                                } else {
                                    break;
                                }
                            }

                            break;

                        case 'start':
                            // Not responding to start for some reason 29 June 2017
                            //			$this->sms_message = "s/devstack start";
                            //            $this->message = "s/devstack start";
                            return;

                        default:
                            return; // Capture
                    }
                }
            }
        }
    }

    public function PNG()
    {
        // Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

        //I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and tes$
        //No the problem was not a whitespace, but the UTF BOM encoding character at the begining of one of my inluded files...
        //So beware of your included files!
        //Make sure they are not encoded in UTF or otherwise in UTF without BOM.
        //Hope it save someone's time.

        //http://php.net/manual/en/function.imagepng.php

        //header('Content-Type: text/html');
        //echo "Hello World";
        //exit();

        //header('Content-Type: image/png');
        //QRcode::png('PHP QR Code :)');
        //exit();
        // here DB request or some processing

        if ($this->group_id == null) {
            $this->startGroup();
        }

        $codeText = "group:" . $this->group_id;

        ob_clean();
        ob_start();

        // choose a color for the ellipse
        //$ellipseColor = imagecolorallocate($image, 0, 0, 255);

        // draw the blue ellipse
        //imagefilledellipse($image, 100, 100, 10, 10, $ellipseColor);

        QR_Code::png($codeText, false, QR_ECLEVEL_Q, 4);
        //QRcode::png($codeText,false,QR_ECLEVEL_Q,4);
        $image = ob_get_contents();

        ob_clean();

        // Can't get this text editor working yet 10 June 2017

        //$textcolor = imagecolorallocate($image, 0, 0, 255);
        // Write the string at the top left
        //imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

        $this->thing_report['png'] = $image;
        //echo $this->thing_report['png']; // for testing.  Want function to be silent.

        return $this->thing_report['png'];
    }
}
