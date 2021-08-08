<?php
namespace Nrwtaylor\Stackr;


class LoadSMS {

	// Responds to a query from $from to useragent@stackr.co

	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_name = "loadsms";
        $this->agent_prefix = 'Agent "Load SMS" ';

//        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        $this->default_resource_path = '/home/nick/txt/';
        $this->default_file_name = "data_combined.csv";

        //$agent = new \Nrwtaylor\Stackr\Meta($this->thing, "meta");
        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("load_sms", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("load_sms", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("load_sms", "reading") );

            $this->readSubject();

            $this->thing->json->writeVariable( array("load_sms", "reading"), $this->reading );

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



    function loadSMS($file_name = null)
    {
        if ($file_name == null) {$file_name = $this->default_resource_path . $this->default_file_name;}
        // Get Hangouts

        $csvFile = file($file_name);
        $this->data = [];
        foreach ($csvFile as $csv_line) {

            $line = str_getcsv($csv_line);
//var_dump($line);
            if (!isset($line[1])) {continue;}

            // Incomplete line...drop
            if (!isset($line[4])) {continue;var_dump($line);}
            if (!isset($line[5])) {continue;var_dump($line);}

            if (isset($line[6])) {$to = $line[4]; $from = null;}
            if (isset($line[5])) {$from = $line[4]; $to = null;}


            if (!isset($line[6])) {$text = $line[5];} else {$text = $line[5] ." " .$line[6];}
            $data_gram = array("time_sent"=>$line[1], "time_b"=>$line[3], 
                "to"=>$to,
                "from"=>$from,
                "id"=>$line[4], "message"=>$text);
            $this->data[]  = $data_gram;
        }


        $this->messages = $this->data;
        $this->message = $this->data[0];
        echo "SMS load completed\n";

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

        $this->reading = count($this->messages);
        $this->thing->json->writeVariable(array("load_sms", "reading"), $this->reading);

        return $this->thing_report;
	}


    function makeSMS()
    {
        if (isset($this->messages)) {

           if (count($this->messages) == 0) {
                $this->sms_message = "LOAD SMS | no messages found";
                return;
            }

            if ($this->messages[0] == false) {
                $this->sms_message = "LOAD SMS | no messages found";
                return;
            }

            if (count($this->messages) > 1) {
                $this->sms_message = "LOAD SMS | " . count($this->messages) . " loaded.";
            } elseif (count($this->messages) == 1) {
                $this->sms_message = "LOAD SMS | 1 message loaded.";
            }
            $this->sms_message .= "LOAD SMS | undefined response";
            return;
        }

        $this->sms_message = "LOAD SMS | no messages found";
        return;
    }

    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }

	public function readSubject()
    {
        $input = strtolower($this->subject);


        $keywords = array('loadsms','load sms', 'smsload', 'sms load');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'loadsms':
                        case 'load sms':
                        case 'smsload':
                        case 'sms load':
                            $prefix = $piece;
                            if (!isset($prefix)) {$prefix = 'loadsms';}
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            //$this->search_words = $words;

                            $this->loadSMS();

                            return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->loadSMS();


		$status = true;

	return $status;
    }

}



?>
