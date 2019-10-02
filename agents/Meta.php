<?php
namespace Nrwtaylor\StackAgentThing;


class Meta
{
	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);

        if ($agent_input == null) {}

        $this->agent_input = $agent_input;

		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_name = 'meta';
        $this->agent_prefix = 'Agent "Nonnom" ';

        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nonnom", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nonnom", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("nonnom", "reading") );

        $this->readSubject();

        $this->thing->json->writeVariable( array("nonnom", "reading"), $this->reading );

        if ($this->agent_input == null) {$this->Respond();}

        $this->reading = null;
        $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->reading . '.');

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;
	}


    function getMeta($thing=null)
    {
        if ($thing == null) {
            if (!isset($this->thing)) {
                $thing->to = null;
                $thing->from = null;
                $thing->subject = null;
            } else {
                $thing = $this->thing;
            }
        }



        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
        if (!isset($thing->subject)) {$this->subject = null;} else {$this->subject = $thing->subject;}



        $data_gram = array("from"=>$this->from,
                                "to"=>$this->to,
                                "message"=>$this->subject);

        $this->meta = $data_gram;
        $this->meta_string = implode(" ", $data_gram);

    }


    function extractMeta($input=null)
    {

        if (($input == null) ) {
            if($this->agent_input == null) {$input = $this->agent_input;} else {$input = $this->subject;}
        }

        if ($input == "") {        $data_gram = array("from"=>null,
                                "to"=>null,
                                "message"=>null);

        $this->meta = $data_gram;
return;}


        if (!isset($this->words)) {
            $this->getWords($input);
        }

        $sections = array("from", "to", "message");

        $parse_section = null;
        $message = "";
        $to = "";
        $from = "";

        foreach ($this->words as $temp=>$word)
        {

            foreach($sections as $temp=>$section)
            {
                if ($word == $section) {
                    $parse_section = $word;
                }

            }

            switch ($parse_section) {
                case "message":
                    if (!isset($message_count)) {
                        $message_count = 1;
                    } else {
                        $message_count += 1;
                        $message .= " " . $word;
                    }
                    continue;
                case "to":
                    if (!isset($to_count)) {
                        $to_count = 1;
                    } else {
                        $to_count += 1;
                        $to .= " " . $word;
                    }
                    continue;
                case "from":
                    if (!isset($from_count)) {
                        $from_count = 1;
                    } else {
                        $from_count += 1;
                        $from .= " " . $word;
                    }
                    continue;
            }
        }

        $this->subject = ltrim($message);
        $this->to = ltrim($to);
        $this->from = ltrim($from);

    }

    function getWords($message=null)
    {
        if ($message == null) {$message = $this->subject;}

        $agent = new Word($this->thing, $message);
        $this->words = $agent->words;
    }

	public function Respond() {

		$this->cost = 100;

		// Thing stuff
		$this->thing->flagGreen();

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

        if (isset($this->meta)) {$this->thing->json->writeVariable(array($this->agent_name, "meta"), $this->meta);}

        return $this->thing_report;
	}


    function makeSMS()
    {
        if (isset($this->meta)) {

            switch ($this->meta) {
            case true:
                $this->sms_message = "META | no thing metadata found";
                break;
            case false:
                $this->sms_message = "META | no thing metadata";
                break;
            case null:
                $this->sms_message = "META | no thing metadata";
                break;
            default:
                $this->sms_message = "META | " . $this->meta_string;
                break;
            }
        } else {
            $this->sms_message = "META | no metadata set";
        }
        return;
    }


    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }



	public function readSubject()
    {
        $input = strtolower($this->subject);


        $keywords = array('meta','metadata');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'metadata':   
                        case 'meta':   
                            $prefix = $piece;
                            if (!isset($prefix)) {$prefix = 'meta';}
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            $this->extractMeta($words);


                            return;

                        default:


                    }

                }
            }

        }

        $this->extractMeta();
		$status = true;

	    return $status;
	}






    function contextWord ()
    {

        $this->word_context = '';
        return $this->word_context;
    }
}
