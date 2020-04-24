<?php
namespace Nrwtaylor\StackAgentThing;

class Associate extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doAssociate();
    }

    public function doAssociate()
    {
    }


    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->sms_message = "ASSOCIATE | " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
$uuid_agent = new Uuid($this->thing, "uuid");
$uuids = $uuid_agent->extractUuids($this->input);
var_dump($uuids);

foreach($uuids as $i=>$uuid) {

$this->thing->associate($uuid);
$this->response .= $this->uuid ." > ".$uuid ." / ";

$thing = new Thing($uuid);
$thing->associate($this->uuid);
$this->response .= $uuid ." > " . $this->uuid ." / ";

}




        return false;
    }
}
