<?php
namespace Nrwtaylor\StackAgentThing;

class Is {

	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "Is" ';

        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
	    $this->subject = $thing->subject;
		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

//        $this->thing->json->setField("variables");
//        $time_string = $this->thing->json->readVariable( array("word", "refreshed_at") );

//        if ($time_string == false) {
//            //$this->thing->json->setField("variables");
//            $time_string = $this->thing->json->time();
//            $this->thing->json->writeVariable( array("word", "refreshed_at"), $time_string );
//        }

        // If it has already been processed ...
//        $this->reading = $this->thing->json->readVariable( array("word", "reading") );

            $this->readSubject();

//            $this->thing->json->writeVariable( array("word", "reading"), $this->reading );

            if ($this->agent_input == null) {$this->Respond();}

  //      if (count($this->words) != 0) {
//
//		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->word . '.');


  //      } else {
    //                $this->thing->log($this->agent_prefix . 'did not find words.');
      //  }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


	}







	public function Respond()
    {
		$this->cost = 0;

		// Thing stuff
		$this->thing->flagGreen();

		// Compose email

        // Make SMS
        $this->makeSMS();
		$this->thing_report['sms'] = $this->sms_message;

        // Make message
		$this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail(); 

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

    function makeSMS() {

    if ($this->alias_thing->alias_id != null) {

        $this->sms_message = "IS | alias_id = " . $this->alias_thing->alias_id;
        return;
    } else {
        $this->sms_message = "IS | Message not used by Agent 'Is'.";
        return;
    }

        // Why did we get here?
        return true;
    }


    function makeEmail() {

        $this->email_message = "IS | ";

    }



	public function readSubject()
    {

//        $this->translated_input = $this->wordsEmoji($this->subject);

        $input = strtolower($this->subject);

//        $keywords = array('is');
        $pieces = explode(" is ", strtolower($input));


//        $this->max_ngram = 10;
        if (count($pieces) == 2) {
            // A left and a right pairing and nothing else.
            // So we can substitute the word and pass it to Alias.
            $this->thing->log($this->agent_prefix . 'passed to Alias "' . $this->subject .  '".', "INFORMATION");
             $this->alias_thing = new Alias($this->thing);

//            }

            $this->thing->json->writeVariable(array("is", "alias_id"), $this->alias_thing->alias_id);
            return;
        }

            $this->thing->json->writeVariable(array("is", "alias_id"), true);

	return true;
	}



}



?>
