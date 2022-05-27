<?php
namespace Nrwtaylor\Stackr;

class LoadGoogleMail {

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
        $this->default_file_name = "All mail Including Spam and Trash-007.mbox";

        //$agent = new \Nrwtaylor\Stackr\Meta($this->thing, "meta");
        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

        $time_string = $this->thing->Read( array("load_googlemail", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->Write( array("load_googlemail", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->Read( array("load_googlemail", "reading") );

            $this->readSubject();

            $this->thing->Write( array("load_googlemail", "reading"), $this->reading );

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



    function loadGoogleMail($file_name = null)
    {
        if ($file_name == null) {$file_name = $this->default_resource_path . $this->default_file_name;}
        // Get Hangouts

        // tested with google mail > account > privacy > data exporter (with label)
        // https://takeout.google.com/settings/takeout
        echo "About to load file\n";
        //$data = array();

        $i=0;

        $from = null;
        $to = null;
        $subject = null;
        $date = null;

        $fd = fopen($file_name, 'rb');
        while( ($line = fgets($fd)) !== false ) {
//echo $line;
//echo "\n\n";

            if (substr( $line, 0, 5 ) === "From:") {
                $from = ltrim(substr($line,5));
            }

            if (substr( $line, 0, 3 ) === "To:") {
                $to = ltrim(substr($line,3));
            }

            if (substr( $line, 0, 8 ) === "Subject:") {
                $subject = ltrim(substr($line,8));
            }

            if (substr( $line, 0, 5 ) === "Date:") {
                $date = ltrim(substr($line,5));
            }

            if (($from != null) and ($to != null) and ($subject != null) and ($date != null)) {
    //echo $from . " " . $to . " " . $subject . " " . $date . "\n";
                $data_gram = array("to"=>$to,"from"=>$from,"message"=>$subject,"time_sent"=>$date);
                $from = null;
                $to = null;
                $subject = null;
                $date = null;

                $this->data[] = $data_gram;
    //var_dump($data_gram);
                $i += 1;
            }
    // do something with $line
        }
        fclose($fd);
        
        echo "Loaded " . $i . " email(s).";

        $this->messages = $this->data;
        $this->message = $this->data[0];


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
        $this->thing->Write(array("load_googlemail", "reading"), $this->reading);

        return $this->thing_report;
	}


    function makeSMS()
    {
        if (isset($this->messages)) {

           if (count($this->messages) == 0) {
                $this->sms_message = "LOAD GOOGLEMAIL | no messages found";
                return;
            }

            if ($this->messages[0] == false) {
                $this->sms_message = "LOAD GOOGLEMAIL | no messages found";
                return;
            }

            if (count($this->messages) > 1) {
                $this->sms_message = "LOAD GOOGLEMAIL | " . count($this->messages) . " loaded.";
            } elseif (count($this->messages) == 1) {
                $this->sms_message = "LOAD GOOGLEMAIL | 1 message loaded.";
            }
            $this->sms_message .= "LOAD GOOGLEMAIL | undefined response";
            return;
        }

        $this->sms_message = "LOAD GOOGLEMAIL | no messages found";
        return;
    }

    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }

	public function readSubject()
    {
        $input = strtolower($this->subject);


        $keywords = array('loademail','load email', 'emailload', 'email load');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'loademail':
                        case 'load email':
                        case 'emailload':
                        case 'email load':
                            $prefix = $piece;
                            if (!isset($prefix)) {$prefix = 'loadgooglemail';}
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            //$this->search_words = $words;

                            $this->loadGoogleMail();

                            return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->loadGoogleMail();


		$status = true;

	return $status;
    }

}



?>
