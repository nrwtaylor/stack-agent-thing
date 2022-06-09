<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Notstack extends Agent
{
    function init()
    {
        // devstack

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->aliases = ['default', 'ex-stack'];
        $this->node_list = ["not stack" => ["new user", "watson"]];

        $this->previous_state = $this->thing->getState('usermanager');
    }

    public function set()
    {
        $this->setNotstack();
    }

    function setNotstack()
    {
        $this->thing->Write(
            ["notstack", "refreshed_at"],
            $this->thing->time()
        );
    }



    public function respondResponse()
    {
        // Thing actions

 //       $this->thing->json->setField("settings");
 //       $this->thing->json->writeVariable(
//            ["notstack", "receipt", "received_at"],
//            date("Y-m-d H:i:s")
//        );

        $this->thing->flagGreen();

        // Get the current user-state.

        switch ($this->previous_state) {
            case 'opt-out':
                $newuser_thing = new Newuser($this->thing);
                $thing_report = $newuser_thing->thing_report;

                break;
            case 'opt-in':
                // Opted-in user has sent a message to stack.
                // Call Watson.

                $watson_thing = new Watson($this->thing);
                $thing_report = $watson_thing->thing_report;

                break;
            case 'new user':
                // Ignore repeated attempts.

                break;
            case null:
                // See if an existing Opt-in Thing for this user exists.

                $newuser_thing = new Newuser($this->thing);
                $thing_report = $newuser_thing->thing_report;
                break;
            default:
                $watson_thing = new Newuser($this->thing);

                $thing_report = $watson_thing->thing_report;
        }

        $this->thing->flagGreen();

        return $thing_report;
    }

    public function makeSMS() {

       $sms = "NOT STACK";
       $this->sms_message = $sms;
       $this->thing_report['sms'] = $sms;

    }


    public function readSubject()
    {
    }
}
