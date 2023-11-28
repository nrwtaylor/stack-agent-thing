<?php
namespace Nrwtaylor\StackAgentThing;

class Bells extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doBells();
    }

    public function doBells()
    {
        if ($this->agent_input == null) {
$clocktime_handler = new Time($this->thing, "clocktime");
$clocktime = $clocktime_handler->text;
$parts = explode(":",$clocktime);
$hour = $parts[0];
$minute = $parts[1];

$bells = [
'00'=>['bells'=>'8','text'=>'middle'],
'01'=>['bells'=>'1','text'=>''],
'02'=>['bells'=>'2','text'=>''],
'03'=>['bells'=>'3','text'=>''],
'04'=>['bells'=>'4','text'=>'morning'],
'05'=>['bells'=>'5','text'=>''],
'06'=>['bells'=>'6','text'=>''],
'07'=>['bells'=>'7','text'=>''],
'08'=>['bells'=>'8','text'=>'forenoon'],
'09'=>['bells'=>'1','text'=>''],
'10'=>['bells'=>'2','text'=>''],
'11'=>['bells'=>'3','text'=>''],
'12'=>['bells'=>'4','text'=>'afternoon'],
'13'=>['bells'=>'5','text'=>''],
'14'=>['bells'=>'6','text'=>''],
'15'=>['bells'=>'7','text'=>''],
'16'=>['bells'=>'8','text'=>'first dog'],
'17'=>['bells'=>'1','text'=>''],
'18'=>['bells'=>'2','text'=>'last dog'],
'19'=>['bells'=>'3','text'=>''],
'20'=>['bells'=>'4','text'=>'first'],
'21'=>['bells'=>'5','text'=>''],
'22'=>['bells'=>'6','text'=>''],
'23'=>['bells'=>'7','text'=>''],
'24'=>['bells'=>'8','text'=>'middle'],
];

$v = ucwords($bells[$hour]['text']) . " watch";
$this->bell = $bells[$hour];
$this->bells = $bells;
            $response = $bells[$hour]['bells'] . " BELLS | " . ($v) . ".";

            $this->bells_message = $response; // mewsage?
        } else {
            $this->bells_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "bells");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    function makeSMS()
    {
        $this->node_list = array("bells" => array("bels"));
        $this->sms_message = "" . $this->bells_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "bells");
        $choices = $this->thing->choice->makeLinks('bells');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
