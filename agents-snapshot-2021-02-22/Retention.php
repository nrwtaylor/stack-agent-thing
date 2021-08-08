<?php
namespace Nrwtaylor\StackAgentThing;

// Bounty
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Retention extends Agent
{
    function init()
    {
        $this->stack_state = $this->thing->container['stack']['state'];

        $this->retain_for = $this->thing->container['stack']['retain_for'];

        // Before doing anything else
        $this->thing->json->setField("variables");
        $this->remember_status = $this->thing->json->readVariable([
            "remember",
            "status",
        ]);

        if ($this->remember_status == true) {
            $this->thing->log(
                '<pre> Agent "Retention" found a record flagged for Remember </pre>'
            );
            //$this->setRemember();
        } else {

$this->retain_to = true; // True - not possible to retain record.
$this->age = false;
// Check if the thing has creation meta.
// If not, can not persist.

if ($this->thing->thing !== false) {
            $this->created_at = strtotime($this->thing->thing->created_at);

            $dteStart = $this->created_at;
            $dteEnd = time();

            $dteDiff = $dteEnd - $dteStart; // in seconds

            $this->age = $dteDiff;

            // Provide for translation to stack time unit
            if ($this->retain_for['unit'] == 'hours') {
                $age = $dteDiff / (60 * 60);
                $retain_for = $this->retain_for['amount'] * (60 * 60);
            }

            $time_string =
                $this->retain_for['amount'] . " " . $this->retain_for['unit'];

            $this->retain_to = $this->created_at + $retain_for;

            if ($age > $this->retain_for['amount']) {
                $persistence_thing = new Persistence($this->thing, 'quiet');
                $this->persist_to = $this->thing->json->readVariable([
                    "persistence",
                    "persist_to",
                ]);

                // See if the record should persist.

                if (strtotime($this->persist_to) < time()) {
                    $this->thing->log(
                        '<pre> Agent "Retention" forgot Thing ' .
                            $this->nuuid .
                            '</pre>'
                    );
                    $this->thing->Forget();
                    //echo $age . " forgot";
                } else {
                    $this->thing->log(
                        '<pre> Agent "Retention" the Thing persisted. </pre>'
                    );
                    //echo "persisted";
                }
            } else {
                $this->persist_to = "X";
                //echo $age . " / " . $this->retain_for['amount'] . $this->retain_for['unit'];
            }
}
        }

        $this->thing->log(
            '<pre> Agent "Retention" started running on Thing ' .
                date("Y-m-d H:i:s") .
                '</pre>'
        );
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->aliases = ["destroy" => ["delete"]];
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        // Thing actions

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(
            ["retention", "received_at"],
            $this->thing->json->time()
        );

        $this->thing->flagGreen();

        $from = $this->from;
        $to = $this->to;

        //echo "from",$from,"to",$to;

        $subject = $this->subject;

        // Now passed by Thing object
        $uuid = $this->uuid;
        $sqlresponse = "yes";

        $message = "Thank you $from this was RETENTION";

        //      $this->makeSMS();

        //$this->makeWeb();
        $this->thing_report['email'] = [
            'to' => $from,
            'from' => 'uuid',
            'subject' => $subject,
            'message' => $message,
            'choices' => false,
        ];

        $this->thing_report['thing'] = $this->thing->thing;
    }
    public function makeSMS()
    {
        //echo $age . " / " . $this->retain_for['amount'] . $this->retain_for['unit'];

        $this->sms_message = "RETENTION | ";
        $this->sms_message .=
            "Retain for setting is " .
            $this->retain_for['amount'] .
            " " .
            $this->retain_for['unit'];
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeWeb()
    {
        //echo $age . " / " . $this->retain_for['amount'] . $this->retain_for['unit'$

        $w = '<b>Retention Agent</b><br>';
        $w .= "This agent sets the minimum time a Thing is on the stack.<br>";
        $w .=
            "retain for " .
            $this->retain_for['amount'] .
            " " .
            $this->retain_for['unit'] .
            "<br>";
        $w .=
            "created at " .
            strtoupper(date('Y M d D H:i', $this->created_at)) .
            "<br>";

        $w .=
            "retain to " .
            strtoupper(date('Y M d D H:i', $this->retain_to)) .
            "<br>";
        $w .= "age is " . $this->thing->human_time($this->age) . "<br>";
        //$w.= "<br>" . $this->age;

        //            $w .= $this->retain_to;
        //            $w .= ' | TEXT ?';

        $this->web_message = $w;
        $this->thing_report['web'] = $w;
    }

    public function readSubject()
    {
        $status = true;
        return $status;
    }

    public function PNG()
    {
        // Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

        //I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and testing, including re-installing apache on my computer a couple of times, I traced the problem to an included file.
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
        $codeText = "thing:" . $this->uuid;

        ob_clean();

        ob_start();

        QRcode::png($codeText, false, QR_ECLEVEL_Q, 4);

        $image = ob_get_contents();

        //header('Content-Type: image/png');
        //echo $image;
        //exit();

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
