<?php
namespace Nrwtaylor\StackAgentThing;


class Nomnom extends Agent {

function init() 
    {



        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nomnom", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nomnom", "refreshed_at"), $time_string );
        }

	}

    public function nomnom($text = null)
    {

$nonnom_agent = new Nonnom($this->thing, "nonnom");
$t = $nonnom_agent->nonnomify($text);
$this->response .= $t . " ";
    }


	public function respondResponse() {

		$this->cost = 100;

		// Thing stuff


		$this->thing->flagGreen();


//		$this->thing_report['sms'] = $this->sms_message;

        // Make message
		$this->thing_report['message'] = $this->sms_message;

        // Make email
//        $this->makeEmail(); 

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

 //       $this->reading = count($this->nominals);
 //       $this->thing->json->writeVariable(array("nomnom", "reading"), $this->reading);

 //       return $this->thing_report;
	}


    function makeSMS()
    {
/*
        if (isset($this->words)) {

        if (count($this->words) == 0) {
            $this->sms_message = "NOMNOM | no words found";
            return;
        }


        if ($this->words[0] == false) {
            $this->sms_message = "NOMNOM | no words found";
            return;
        }

        if (count($this->words) > 1) {
            $this->sms_message = "NOMINALS ARE ";
        } elseif (count($this->nominals) == 1) {
            $this->sms_message = "NOMINAL IS ";
        }
        $this->sms_message .= implode(" ",$this->nominal);
        return;
    }
*/
        $sms = "NOMNOM | " . $this->response;
$this->sms_message = $sms;
$this->thing_report['sms'] = $sms;
    }


    function makeEmail()
    {
        $this->email_message = "NOMNOM | ";
    }



	public function readSubject()
    {
        $input = strtolower($this->input);

        $keywords = array('nomnom');
        $pieces = explode(" ", strtolower($input));

$text = $this->nomnom($input);
var_dump($text);
//        $this->extractNominals($input);


		$status = true;

	}


}
