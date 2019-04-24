<?php
namespace Nrwtaylor\StackAgentThing;

class SNMP extends Agent
{
	public $var = 'hello';

    public function init()
    {
        // So I could call
        $this->test = false;
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        $this->node_list = array("snmp"=>array("snmp"));
    }

    public function run()
    {
        $this->getSNMP();
    }

	public function respond()
    {
		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.
		$to = $this->thing->from;
		$from = "snmp";

        $this->makeSms();
        $this->makeMessage();
		$this->thing_report['email'] = $this->sms_message;

		//$this->thing_report['choices'] = false; 

        if ($this->agent_input == null) {
                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['keyword'] = 'snmp';
        $this->thing_report['help'] = 'Useful for checking infrastructure.';

		return $this->thing_report;

	}

    public function makeSms()
    {
        $this->sms_message = "SNMP | Requested SNMP data.";
        $this->sms_message .= " | " . $this->snmp_text . ".";

        $this->sms_message .= " | TEXT WATSON";
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function getSNMP()
    {
        $received_at = strtotime($this->thing->thing->created_at);
        $this->snmp_time = time() - $received_at;

        // Need to see if it is possible to pull MAC addresses
        // Otherwise do an arp scan.

        // For now proof upload and download volume.

        //$syscontact = snmp2_get("192.168.1.1", "public", "system.SysContact.0");
        $outgoing = snmp2_get("192.168.1.1", "public", "1.3.6.1.2.1.2.2.1.16.1");
        $incoming = snmp2_get("192.168.1.1", "public", "1.3.6.1.2.1.2.2.1.10.1");

        $incoming = trim(explode(":", $incoming)[1]);
        $outgoing = trim(explode(":", $outgoing)[1]);

        $this->snmp_text = "SNMP server polled. ";
        $this->snmp_text .= "incoming " . $incoming;
        $this->snmp_text .= " outgoing " . $outgoing;
    }

    public function makeMessage()
    {
        $message = "A message from this Identity snmped us.";
        $message .= " Received " . $this->snmp_text . " ago.";

        $this->sms_message = $message;
        $this->thing_report['message'] = $message;
    }

	public function readSubject()
    {
        $this->response = "Responded to a snmp request.";
	}
}
