<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';

ini_set("allow_url_fopen", 1);

class Clerk {
	

	public $var = 'hello';


    function __construct(Thing $thing) {
	//function __construct($arguments) {

		//echo $arguments;
		//var_dump($arguments);
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];




		// create container and configure it
		$settings = require '../src/settings.php';
		$this->container = new \Slim\Container($settings);
		// create app instance
		$app = new \Slim\App($this->container);
		$this->container = $app->getContainer();
		$this->test= "Development code";


		$this->container['api'] = function ($c) {
			$db = $c['settings']['api'];
			return $db;
			};

		$this->api_key = $this->container['api']['translink'];

		//echo $this->api_key;
// Get
//http://api.translink.ca/rttiapi/v1/stops/60980/estimates?apikey=L400dNFwJW5Cfm6DpIcT&count=3&timeframe=120

		//$web_input = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/60980/estimates?apikey='. $this->api_key . '&count=3&timeframe=60');




		//var_dump($web_input);


		//var_dump($json);

//		$p = xml_parser_create();
//		xml_parse_into_struct($p, $web_input, $vals, $index);



		$thingy = $thing->thing;
		$this->thing = $thing;

		//echo "subject",$thing->subject;
		//echo "to",$thing->to;
		//echo "from",$thing->from;


		//var_dump($thingy);


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

		echo '<pre> Agent "Account" running on Thing ';echo $this->uuid;echo'</pre>';

		echo '<pre> Agent "Account" received this Thing "';echo $this->subject;echo'"</pre>';

		//echo "construct email responser";

		// Read the subject as passed to this class.
		$this->readSubject();
		$this->respond();





		// Which means at this point, we have a UUID
		// whether or not the record exists is another question.

		// But we don't need to find, it because the UUID is randomly created.	
		// Chance of collision super-super-small.

		// So just return the contents of thing.  false if it doesn't exist.
		
		//return $this->getThing();

		echo '<pre> Agent "Account" completed</pre>';

		return;

		}







// -----------------------

	private function respond() {




		// Thing actions

		// Check if 


		$this->thing->json->setField("settings");

		$old_number = $this->thing->json->readVariable(array("account","number"));

		if (is_numeric($this->number)) {
				$new_number = $old_number + $this->number;
			} else {
				throw new Exception('Non numeric value in account');	
			}


		$this->thing->Write(array("account","number"),  $new_number);


		$this->thing->flagGreen();


		// Generate email response.

		$to = $this->thing->from;
		$from = "clerk";






		$this->message = "Thank you for your request.  The following accounting was done: " .  $old_number ." + ". $this->number . " = " . $new_number;

//		$this->thing->email->sendGeneric($to,$from,$this->subject, $this->message);
		echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}

	private function nextWord($phrase) {


	}

	public function readSubject() {

		$this->response = null;
		//echo "readSubject()";
		//echo "I am reading the subject";

		//echo $this->subject;
	

		$keywords = array('credit', 'debit');

		$input = strtolower($this->subject);
		//echo $input;

		//$prior_uuid = $this->uuid;
		$prior_uuid = null;
		//$command = strtolower($this->subject);
		//echo $command;



		$pieces = explode(" ", strtolower($input));


		foreach ($pieces as $key=>$piece) {
			
			foreach ($keywords as $command) {

				
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
						case 'credit':	


							if ($key + 1 > count($pieces)) {
								//echo "last word is stop";
								$this->number = false;
							} else {
								//echo "next word is:";
								//var_dump($pieces[$index+1]);
								$this->number = $pieces[$key+1];
							}
							break;
						case 'debit':
							if ($key + 1 > count($pieces)) {
								//echo "last word is stop";
								$this->number = false;
							} else {
								//echo "next word is:";
								//var_dump($pieces[$index+1]);
								$this->number = -1 * $pieces[$key+1];
							}

							break;
						default:
							//echo 'default';
						}

				}
			}

		

	}
		return $this->response;

	
	}






}




return;

if ( $url ) {
	$rss = fetch_rss( $url );
	echo "Channel: " . $rss->channel['title'] . "<p>";
	echo "<ul>";
	foreach ($rss->items as $item) {
		$href = $item['link'];
		$title = $item['title'];	
		echo "<li><a href=$href>$title</a></li>";
	}
	echo "</ul>";
}
?>

<?php

$textbody = "Channel: " . $rss->channel['title'] ;

$n = 0;

foreach ($rss->items as $item) {
		$n = $n + 1;				
		$href = $item['link'];
		$title = $item['title'];	
		$textbody = $n." ".$textbody."*".$title."\n";
	}

$textbody .= "\n";

foreach ($rss->items as $item) {
		$href = $item['link'];
		$title = $item['title'];	
		$textbody .= "*".$href."\n";
	}

?>

<?php

$body = "Channel: " . $rss->channel['title'] . "<p>";
$body .= "<p>"."[<a href=".$url.">".$url."</a>]";
$body .= "<ul>";


foreach ($rss->items as $item) {
		$href = $item['link'];
		$title = $item['title'];	
		$body = $body. "<li><a href=$href>$title</a></li>";
	}
	$body = $body. "</ul>";


?>


<?php
//define the receiver of the email
$to = 'redpanda.stack@gmail.com';

//define the subject of the email
$subject = 'Craigslist - Freestuff v2';

//create a boundary string. It must be unique
//so we use the MD5 algorithm to generate a random hash
$random_hash = md5(date('r', time()));





//define the headers we want passed. Note that they are separated with \r\n
$headers = "From: redpanda.stack@gmail.com\r\nReply-To: redpanda.stack@gmail.com";

//add boundary string and mime type specification
$headers .= "\r\nMime-Version: 1.0";
$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"\n";


//define the body of the message.
ob_start(); //Turn on output buffering
?>

--PHP-alt-<?php echo $random_hash; ?>
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit



<?php echo $textbody; ?> 


--PHP-alt-<?php echo $random_hash; ?>

Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit


<font size="2" face="arial,helvetica,sans-serif">
<?php echo $body; ?>
</font>


--PHP-alt-<?php echo $random_hash; ?>--

<?
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();
//send the email
$mail_sent = @mail( $to, $subject, $message, $headers );
//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
echo $mail_sent ? "Mail sent" : "Mail failed";
?>

