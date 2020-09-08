<?php
namespace Nrwtaylor\StackAgentThing;

class Associate extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->thing_report["info"] = "This takes UUID(s) and associates them.";
        $this->thing_report["help"] =
            "Text ASSOCIATE 20c0241b-3b26-4c9a-8075-885672bbd883 4b48630b-ce14-4f6e-a31e-15357676bf64.";
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
        // Use the UUID agent and extract UUIDs from the provided input.
        $uuid_agent = new Uuid($this->thing, "uuid");
        $uuids = $uuid_agent->extractUuids($this->input);
        //var_dump($uuids);

        // Associate each of the UUIDs with the current Thing.
        // And (if there is a Thing with the provided UUID) associate it with the current Thing.
        foreach ($uuids as $i => $uuid) {
            $this->thing->associate($uuid);
            $this->response .= $this->uuid . " > " . $uuid . " / ";

            $thing = new Thing($uuid);
            $thing->associate($this->uuid);
            $this->response .= $uuid . " > " . $this->uuid . " / ";
        }

        return false;
    }
}
