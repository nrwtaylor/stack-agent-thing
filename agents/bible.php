
<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/html/stackr.ca/agents/message.php';
ini_set("allow_url_fopen", 1);

class Bible {

	public $var = 'hello';


    function __construct(Thing $thing) {

		$this->thing = $thing;
		$this->agent_name = 'bible';
        $this->agent_prefix = 'Agent "Bible" ';

        $this->thing_report = array('thing' => $this->thing->thing);


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['biblesearch'];

		$this->retain_for = 1; // Retain for at least 1 hour.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log( $this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.' );
		$this->thing->log( $this->agent_prefix . 'received this Thing "' . $this->subject . '".');
		$this->readSubject(); // No need to read subject 'translink' is pretty clear.

		$this->thing_report = $this->respond();

		$this->thing->log( ' Agent "Bible" completed.');

		return;

		}

    public function Get($words = null) {

        if (!isset($this->keywords)) {
            $this->keywords = $words;
        }

        $words = str_replace(' ', '+', $words);

        $xml = $this->getText($words);


//$this->verse = (string) $xml->search->result->verses->verse->id;

//var_dump($this->verse);
//exit();

        $verses = $xml->search->result->verses->verse;

        if ($verses == null) {
            $sms_message = "BIBLE";
            $sms_message .= " | No matching verse found for " . $words . ".";
            $sms_message .= " | MESSAGE 'BIBLE words'";
            $this->sms_message = $sms_message;
            return;
        }
        $arr[] = array();
        $sms_messages = array();
        foreach ($verses as $key=>$verse) {
            $id = (string) $verse->id;
            $id = strip_tags($id);

            $text = (string) $verse->text;
            $text = strip_tags($text);

            $copyright = (string) $verse->copyright;

            $text = preg_replace('#^\d+#', '', $text);

            $text = preg_replace('/^[a-zA-Z]+$/', '', $text);

            // Remove line breaks
            $text = preg_replace( "/\r|\n/", " ", $text );

            $message = $id . " | " . $text;

            $sms_message = "BIBLE";
            $sms_message .= " | " .$message;
            $sms_message .= " | MESSAGE 'BIBLE words'";

            $arr[] = array("id"=>$id , "verse"=>$text, "message"=>$message);
            $sms_messages[] = $sms_message;
        }


        $k = array_rand($sms_messages);
        $this->sms_message = $sms_messages[$k];
//        $this->sms_message = "testtest";

        $k = array_rand($arr);

        $this->sms_message = "BIBLE";
        $this->sms_message .= " | " . $arr[$k]['message'];

        $this->sms_message .= " | google " . $this->getLink($arr[$k]['id']);

        $this->sms_message .= " | text source bibles.org datafeed";

//var_dump($this->sms_message);
//exit();
//$this->verse = $arr[$k]['id'];
//exit();

        return;
    }

    public function Parse ($url) 
    {

        $fileContents= file_get_contents($url);

        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);

        $fileContents = trim(str_replace('"', "'", $fileContents));

        $simpleXml = simplexml_load_string($fileContents);

        $json = json_encode($simpleXml);

        return $json;

    }

        public function nullAction()
        {

                        $this->thing->json->setField("variables");
                        $names = $this->thing->json->writeVariable( array("character", "action"), 'null' );


                $this->message = "BIBLE | Request not understood. | TEXT SYNTAX";
                $this->sms_message = "BIBLE | Request not understood. | TEXT SYNTAX";
                $this->response = true;
                return $this->message;
        }


        function bibleInfo() {


                        $this->sms_message = "BIBLE";

                        $this->sms_message .= " | ";

                        $this->sms_message .= 'Live data feed provided through the bibles.org API. | https://developer.translink.ca/ | ';


                        $this->sms_message .= "TEXT HELP";

                return;


        }

        function bibleHelp() {

                        $this->sms_message = "BIBLE";

                        $this->sms_message .= " | ";

                        $this->sms_message .= 'Text one or more words. | For example, "Bible peace". | ';

                        $this->sms_message .= "TEXT BIBLE <word(s)>";

                return;


        }

        function bibleSyntax() {


                        $this->sms_message = "BIBLE";

                        $this->sms_message .= " | ";

                        $this->sms_message .= 'Syntax: "<keyword>". | ';


                        $this->sms_message .= "TEXT HELP";

                return;


        }

    public function getVerse()
    {


        //$token = '#{API Token}';
        $token = $this->api_key;
        $url = 'https://bibles.org/v2/verses/eng-GNTD:Acts.8.34.xml';

        // Set up cURL
        $ch = curl_init();
// Set the URL
curl_setopt($ch, CURLOPT_URL, $url);
// don't verify SSL certificate
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
// Return the contents of the response as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// Follow redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
// Set up authentication
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "$token:X");

// Do the request
$response = curl_exec($ch);
curl_close($ch);

//print($response);

$test = simplexml_load_string($response);
//print_r($test);


    }

    public function getText($keywords = null)
    {
        if ($keywords == null) {
            $options = array('peace', 'love', 'help', 'protect', 'care', 'support', 'aid');

            $k = array_rand($options);
            $keywords = $options[$k];
        }

        $url = 'https://bibles.org/v2/verses.xml?keyword='.$keywords;
        $xml = $this->getXML($url);

        return $xml;
    }

    public function getXML($url)
    {
        $token = $this->api_key;

        // Set up cURL
        $ch = curl_init();
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't verify SSL certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Return the contents of the response as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Set up authentication
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$token:X");

        // Do the request
        $response = curl_exec($ch);
        curl_close($ch);

        //$xml = simplexml_load_string($response);
        $xml = new SimpleXMLElement($response);

        return $xml;
    }

    function getLink($ref) {
        // Give it the message returned from the API service
        // Extract the verse reference.

        // Urgh.

        // First option.  Return a usable google link

        // google.ca/search?source=eng-CEV:1John.5.2 - Bork

//https://www.google.ca/search?r=1&biw=840&bih=630&ei=3m08WoytK46-jwOV65eoCg&q=%22eng-CEV%3A1John.5.2%22&oq=%22eng-CEV%3A1John.5.2%22

//http://biblehub.com/hebrews/12-7.htm

//https://www.bible.com/bible/100/ROM.16.nasb?parallel=69

//https://www.biblegateway.com/passage/?search=Romans+16

//https://www.biblesociety.org.uk/explore-the-bible/read/eng/GNB/Rom/16/

// https://www.google.ca/search?q=eng-NASB:Rom.15.33
// Should work.

//Get the reference.

        $this->link = "https://www.google.ca/search?q=" . $ref; 
        return $this->link;

    }

    public function findText($input)
    {

        $url = 'https://bibles.org/v2/search.xml?query='.$input;

    }

// -----------------------

	private function respond() {

		//$this->thing_report = array('thing' => $this->thing->thing);

		// Thing actions
		$this->thing->flagGreen();

		//$this->readSubject();

                        $this->thing_report['sms'] = $this->sms_message;
                        $this->thing_report['choices'] = false;
                        $this->thing_report['info'] = 'SMS sent';




		// Generate email response.

		$to = $this->thing->from;


		$from = "bible";


		$message = $this->readSubject();


//		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
//		$choices = $this->thing->choice->makeLinks('start');
//		$this->thing_report['choices'] = $choices;




//		$this->thing->email->sendGeneric($to,$from,$this->subject, $message, $choices);
//		echo '<pre> Agent "Translink" email sent to '; echo $to; echo ' </pre>';


  //              $this->thing_report['email'] = array('to'=>$this->from,
  //                              'from'=>'transit',
  //                              'subject' => $this->subject,
  //                              'message' => $message, 
  //                              'choices' => false);


		// Need to refactor email to create a preview of the sent email in the $thing_report['email']
		// For now this attempts to send both an email and text.

                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report['info'] = $message_thing->thing_report['info'] ;



//exit();

//	$this->thing_report['info'] = 'This is the translink agent responding to a request.';
	$this->thing_report['help'] = 'Connector to bibles.org API.';

		return $this->thing_report;


	}

	private function nextWord($phrase) {


	}

	public function readSubject() 
    {
        $emoji_thing = new Emoji($this->thing, "emoji");
        $thing_report = $emoji_thing->thing_report;

        if (isset($emoji_thing->emojis)) {
            $input = ltrim(strtolower($emoji_thing->translated_input));

        }

		$this->response = null;

		$keywords = array('bible');

		//$input = strtolower($this->subject);

		$prior_uuid = null;

		$pieces = explode(" ", strtolower($input));


        if (count($pieces) == 1) {

            $input = $this->subject;

            if (strtolower($input) == 'bible') {
                $this->Get();
                return;
            }
		}


		foreach ($pieces as $key=>$piece) {
			foreach ($keywords as $command) {
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
						case 'bible':	

                            $prefix = 'bible';
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            $this->Get($words);
                            return;


						default:

							//echo 'default';

					}

				}
			}

        }




        $this->nullAction();
		return "Message not understood";
	}



}




?>



