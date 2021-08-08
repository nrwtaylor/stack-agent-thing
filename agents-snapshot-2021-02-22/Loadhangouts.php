<?php
namespace Nrwtaylor\Stackr;


class Loadhangouts {

	// Responds to a query from $from to useragent@stackr.co

	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_name = "loadhangouts";
        $this->agent_prefix = 'Agent "Load Hangouts" ';

//        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        $this->default_resource_path = '/home/nick/txt/';
        $this->default_file_name = "Hangouts.json";

        //$agent = new \Nrwtaylor\Stackr\Meta($this->thing, "meta");
        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("load_hangouts", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("load_hangouts", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("load_hangouts", "reading") );

            $this->readSubject();

            $this->thing->json->writeVariable( array("load_hangouts", "reading"), $this->reading );

            if ($this->agent_input == null) {$this->Respond();}

        if (count($this->messages) != 0) {
            //$this-> = $this->ngrams[0];
		    $this->thing->log($this->agent_prefix . 'completed with ' . count($this->messages) . ' messages loaded.');


        } else {
            $this->messages = null;
                    $this->thing->log($this->agent_prefix . 'did not find messages.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


	}



    function loadHangouts($file_name = null)
    {
        if ($file_name == null) {$file_name = $this->default_resource_path . $this->default_file_name;}
        // Get Hangouts

        $jsonFile = file_get_contents($file_name);

        $jsondata = json_decode($jsonFile);

        $i=0;
        foreach ($jsondata->conversation_state as $conversation_key=>$conversation) {
            $participants = [];
            foreach ($conversation->conversation_state->conversation->participant_data as $participant) {
                if (!isset($participant->fallback_name)) {
                    $p = null;
                } else {
                    $p = $participant->fallback_name;
                }
                $participants[$participant->id->gaia_id] = $p;
            }

            $events = array_reverse($conversation->conversation_state->event);
            foreach ($events as $event)
            {
                $time_stamp = date('Y-m-d H:i:s', $event->timestamp  / 1000000);

                if (!isset($participants[$event->sender_id->gaia_id])) {
                    $participant = null;
                } else {
                    $participant = $participants[$event->sender_id->gaia_id];
                }

                if (!isset($event->chat_message->message_content->segment)) {continue;}
                foreach ($event->chat_message->message_content->segment as $seg) {
                    if (!isset($seg->text)) {continue;}
                    $text = $seg->text;

                    $data_gram = array("time_sent"=>$time_stamp, "from"=>$participant, "id"=>$participant, "time_b"=>null, "message"=>$text);
                    $this->data[] = $data_gram;
                }
            }

        }

        $this->messages = $this->data;
        $this->message = $this->data[0];
        echo "Hangouts load completed\n";

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

        $this->reading = count($this->words);
        $this->thing->json->writeVariable(array("load_hangouts", "reading"), $this->reading);

        return $this->thing_report;
	}


    function makeSMS()
    {
        if (isset($this->messages)) {

           if (count($this->messages) == 0) {
                $this->sms_message = "LOAD HANGOUTS | no messages found";
                return;
            }

            if ($this->messages[0] == false) {
                $this->sms_message = "LOAD HANGOUTS | no messages found";
                return;
            }

            if (count($this->messages) > 1) {
                $this->sms_message = "LOAD HANDOUTS | " . count($this->messages) . " loaded.";
            } elseif (count($this->messages) == 1) {
                $this->sms_message = "LOAD HANGOUTS | 1 message loaded.";
            }
            $this->sms_message .= "LOAD HANGOUTS | undefined response";
            return;
        }

        $this->sms_message = "LOAD HANGOUTS | no messages found";
        return;
    }

    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }

	public function readSubject()
    {
        $input = strtolower($this->subject);


        $keywords = array('loadhangouts','load hangouts', 'hangoutsload', 'hangouts load');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'loadhangouts':
                        case 'load hangouts':
                        case 'hangoutsload':
                        case 'hangouts load':
                            $prefix = $piece;
                            if (!isset($prefix)) {$prefix = 'loadhangouts';}
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            //$this->search_words = $words;

                            $this->loadHangouts();

                            return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->loadHangouts();


		$status = true;

	return $status;
    }

}



?>
