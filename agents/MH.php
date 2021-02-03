<?php
namespace Nrwtaylor\StackAgentThing;

class MH extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doMH();
    }

    public function doMH()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MH | " . strtolower($v) . ".";

            $this->mh_message = $response; // mewsage?
        } else {
            $this->mh_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an agent to handle the MH email format.";
        $this->thing_report["help"] = "This mostly deals with equal signs at the end of lines.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    public function metaMH($text = null) {
       if ($text == null) {return;}

       // Test and dev.
       // Extract subject line
       $this->subject = $this->subjectMH($text);
       $this->meta = ["subject"=>$this->subject];
    }

    public function subjectMH($text = null) {
       if ($text == null) {return;}

       $this->datagram = $this->readEmail($text);

       if (isset($this->datagram['subject'])) {return $this->datagram['subject'];}


       // Test and dev.
       // Extract subject line
       $subject = "TODO Extract subject line - see MH.php";
       return $subject;
    }

    public function bodyMH($text = null) {

$datagram = $this->readEmail($text);

       $this->datagram = $this->readEmail($text);
       if (isset($this->datagram['text'])) {return $this->datagram['text'];}

    }

// Move email.
// refactor 
    public function readEmail($text = null) {

$email = $text;

// Test this code from Emailhandler.php
// refactor

//Parse "subject"
$subject1 = explode ("\nSubject: ", $email);

if (!isset($subject1[1])) {return true;}

$subject2 = explode ("\n", $subject1[1]);
$subject = $subject2[0];

//Parse "to"
$to1 = explode ("\nTo: ", $email);
$to2 = explode ("\n", $to1[1]);
$to = str_replace ('>', '', str_replace('<', '', $to2[0]));

$message1 = explode ("\n\n", $email);

$start = count ($message1) - 3;

if ($start < 1)
{
    $start = 1;
}

//Parse "message"
$message2 = explode ("\n\n", $message1[$start]);
$message = $message2[0];

//Parse "from"
$from1 = explode ("\nFrom: ", $email);
$from2 = explode ("\n", $from1[1]);


if(strpos ($from2[0], '<') !== false)
{
    $from3 = explode ('<', $from2[0]);
    $from4 = explode ('>', $from3[1]);
    $from = $from4[0];
}
else
{
    $from = $from2[0];
}

$datagram=['to'=>$to, 'from'=>$from, 'subject'=>$subject, 'text'=>$message];
return $datagram;

    }

    public function textMH($text = null) {

       if ($text == null) {return;}

       // Test and dev.

       $lines = preg_split("/\r\n|\n|\r/", $text);

       $new_lines = [];
       foreach($lines as $i=>$line) {
           $new_line = rtrim($line," =");
           $new_lines[] = $new_line;

       }

       $contents = implode("\n", $new_lines);

       return $contents;
    }

    public function readMH($text = null) {
      if ($text == null) {return;}

      $this->datagram = $this->readEmail($text);

      $this->meta = $this->metaMH($text);
      $this->contents = $this->textMH($text);

    }

    function makeSMS()
    {
        $this->node_list = array("mh" => array("mh", "dog"));
        $this->sms_message = "" . $this->mh_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "mh");
        $choices = $this->thing->choice->makeLinks('mh');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;
        return false;
    }
}
