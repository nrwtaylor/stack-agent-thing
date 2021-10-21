<?php
namespace Nrwtaylor\StackAgentThing;

class Add extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doAdd();
    }

    public function doAdd()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "ADD | " . strtolower($v) . ".";

            $this->add_message = $response; // mewsage?
        } else {
            $this->add_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "add");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an agent for adding things together. Starting with numbers.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
$response = "No response.";
if ($this->response != "") {$response = $this->response;}
        //$this->node_list = array("add" => array("add", "subtract"));
        $this->sms_message = "" . $this->add_message . $this->response;;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
$input = $this->input;
$filtered_input = $this->assert($input);
$numbers = $this->extractNumbers($filtered_input);

if (count($numbers) > 0) {
$add_total = 0;
foreach($numbers as $i=>$number) {

$add_total += $number;

}
$this->response = "Added up to " . $add_total . ". ";
}

        return false;
    }
}
