<?php
namespace Nrwtaylor\StackAgentThing;

// This code has nothing to do with the company of the same name.
// This is about Turing Oracles.

class Oracle extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doOracle();
    }

    /**
     *
     * @return unknown
     */
    public function oracle()
    {

        if ($this->address === false) {
            return;
        }
        if ($this->address === true) {
            return;
        }

        $this->sms_message =
            'ORACLE The datagram was forwarded to a mapped address.';
        $this->message = $this->word . ' forwarded to a mapped address.';

        $message = $this->subject;

        $thing = new Thing(null);

        $to = $this->address;

        $thing->Create($to, $thing->uuid, 'oracle ' . $this->subject);
        $thing->flagGreen();

        $thing_report['thing'] = $thing;
        $thing_report['message'] = $message;
        $thing_report['sms'] = $message;
        $thing_report['email'] = $message;

        $message_thing = new Message($thing, $thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->response .= "Sent message to the oracle."; 


    }


    public function doOracle()
    {
        if ($this->agent_input == null) {

            $this->oracle_message = ""; // what does the oracle say?
        } else {
            $this->oracle_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is an oracle.";
        $this->thing_report["help"] = "This is about providing helpful and/or useful when others cannot.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        //return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("oracle" => array("oracle"));
        $sms = "ORACLE | " . $this->oracle_message . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {

        $input = $this->assert($this->input);

        $address_agent = new Address($this->thing, 'address');

        $address = $address_agent->isAddress("oracle", "address");

        $this->address = $address;

        $this->oracle();
    }
}
