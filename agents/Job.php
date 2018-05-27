<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Job {

	function __construct(Thing $thing, $agent_input = null) {

		//if ($agent_input == null) {$agent_input = '';}
		$this->agent_input = $agent_input;

		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.
		$this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->start_time = $this->thing->elapsed_runtime();

		$this->agent_name = 'Job';
        $this->agent_prefix = 'Agent "Job" ';

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];


		// Create some short-cuts.
	        $this->uuid = $thing->uuid;
	        $this->to = $thing->to;
	        $this->from = $thing->from;
	        $this->subject = $thing->subject;
		//$this->sqlresponse = null;



		$this->thing->log('<pre> Agent ' . ucfirst($this->agent_name) . '" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
		$this->node_list = array("receipt management"=>
						array("learning","communicating"=>
							array("more","less"),"channeling"=>
								array("narrowing","broadening")),
							"receipt start"=>
								array("more"=>"receipt management",
									"less"=>"receipt management"));

		$this->aliases = array("learning"=>array("good job"));

$this->thing->log('Agent "Job" constructed a Thing '. $this->uuid . '', "INFORMATION");
$this->thing->log( 'Agent "Job" received this Thing "' . $this->uuid . '"', "INFORMATION");

		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.


		$this->readSubject();

        $this->setReceipt();
        $this->PNG();

        if ($this->agent_input == null) {$this->respond();}


        $this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;


	}

    function setReceipt() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("receipt",
            "refreshed_at"),  $this->thing->json->time()
            );

    }

	public function respond() {



		// Thing actions

//		$this->thing->json->setField("variables");
//		$this->thing->json->writeVariable(array("receipt",
//			"refreshed_at"),  $this->thing->json->time()
//			);


		$this->thing->flagGreen();


		// What receipts states are there.
		// "Hi.  I'm receipt management.  How can I help you."

		// figure out how set do aliases fluidly "good job"="learning".
		// For now.

		
		foreach ($this->aliases as $alias) {

			// Find out if the array test is in the aliase "database"?  But
			// want to avoid database calls.  They clearly have a real world
			// cost.  We are setting the paramenters for what 100 <unit> costs.
			// Because triggering the db connections issue has to have real 
			// world consequences.

		}



//		$this->thing->choice->Create('receipt', $this->node_list, "receipt managing");
//		$choices = $this->thing->choice->makeLinks('receipt start');
        $choices = false;
//		$html_button_set = $links['button'];



		$from = $this->from;
		$to = $this->to;

		//echo "from",$from,"to",$to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

$message = "Thank you $from your message to agent '$to' has been accepted by " . $this->short_name .".  Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
$message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';


$this->makeSMS();

$this->makeTXT();
$this->makeWeb();
$this->makeMessage();
            $this->thing_report['email'] = $this->message;


                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report['info'] = $message_thing->thing_report['info'] ;




		return $this->thing_report;
	}

    function makeMessage() {
        $uuid = $this->uuid;
        $nuuid = $this->thing->nuuid;

        $message = "Thank you " . $this->from . ". The job sent to Agent '" . $this->to. "' has been accepted by " . $this->short_name .".";
        $message .= " ";
        $message .= "Keep on stacking.\n";
        //$message .= $this->web_prefix . "thing\" . $this->uuid . "\job";
        $message .= "\n";


        //$message .= '<img src="' . $this->web_prefix . 'thing/'. $this->uuid .'/job.png" alt="a snowflake ' . $this->thing->nuuid .'" height="92" width="92">';
        $message .= '<img src="' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png" alt="look a freezing snowflake">';


        //$message = htmlspecialchars($message . "\n\n");
        $message = nl2br($message);


        $this->message = $message;
        $this->thing_report['message'] = $this->message;

    }


    function makeTXT()
    {

        $this->verbosity = 1;

        //if ($job_name == null) {
            $job_name = "<two or three words>";
        //}

        //if ($job_commitment == null) {
            $job_commitment = "Provide <sometime> hours during <promised block(s) of time>.";
        //}

        //if ($job_mandate == null) {
            $job_mandate = "Provide the results of <some work> doing some <thing> for us.";
        //}

        //if ($job_proof == null) {
            $job_proof = "I will need this <thing> from you to prove you have done it.";
        //}

        //if ($job_first == null) {
            $job_first = "<thing> is the first job.";
        //}

        //if ($job_manager == null) {
            $job_manager = "<messagable person identity>";
        //}

        //if ($job_address == null) {
            $job_address = "<mailable address>";
        //}

        //if ($job_payment == null) {
            $job_payment = "<some monies>";
        //}
        $job_work = "<some work>";

        //if ($job_summary == null) {
            $job_summary = "Basically " . $job_payment . " for " . $job_work. ".";
        //}

        //if ($job_insurance == null) {
            $job_insurance = "With <some insurance requirements>.";
        //}


        $this->txt_message = "JOB DESCRIPTION\n\n";

        $this->txt_message .= 'Here is the "' . $job_name . '" job description.';
        $this->txt_message .= " ";
        $this->txt_message .= $job_commitment . " ";  
        $this->txt_message .= $job_mandate . " ";
        $this->txt_message .= $job_proof. " ";

        $this->txt_message .= "\n\n";
        //$this->txt_message .= "\n";

$this->txt_message .= $job_summary;
$this->txt_message .="\n\n";
$this->txt_message .= $job_first;
$this->txt_message .= "\n\n";

$this->txt_message .= $job_insurance;
$this->txt_message .= "\n\n";


$this->txt_message .= $this->web_prefix . "thing/" . $this->uuid . "/start";
$this->txt_message .= "\n";
$this->txt_message .= "\n";
$this->txt_message .= $job_manager;
$this->txt_message .= "\n";
$this->txt_message .= $job_address;

if ($this->verbosity > 5) {

//$this->sms_message = "RECEIPT";
$this->txt_message .= "\n\n";
$this->txt_message .= $this->sms_message;
}

if ($this->verbosity >=1) {

$this->txt_message .= "\n";
$this->txt_message .= "-\n\n";
$this->txt_message .= "thing to do " . $this->thing->nuuid . " made up at " . $this->thing->thing->created_at. "\n";
$this->txt_message .= "This job is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $this->web_prefix . "privacy";

}

//$this->sms_message .= ' | TEXT ?';


$this->thing_report['txt'] = $this->txt_message;

        return $this->txt_message;

    }


    function makeWeb() {

        $head = '<p class="description">';
        $foot = '</p>';

        $web_message = htmlspecialchars($this->txt_message . "\n\n");
        $web_message = nl2br($web_message);
        $web_message .= '<img src="' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png" alt="look a freezing snowflake">';




//       $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="this snowflake is melting already">';
        $this->web_message = $head. $web_message . $foot;
        $this->thing_report['web'] = $this->web_message;
    }

    function makeSMS() {

$this->verbosity = 1;

$this->sms_message = "JOB " . strtoupper($this->thing->nuuid);

if ($this->verbosity > 5) {

//$this->sms_message = "RECEIPT";
$this->sms_message .= " | thing " . $this->uuid ."";
$this->sms_message .= " created " . $this->thing->thing->created_at;
$this->sms_message .= " by " . strtoupper($this->from);
}

if ($this->verbosity >=1) {

$this->sms_message .= " | thing " . $this->web_prefix . "web/" . $this->uuid ."/job" . " made up " . $this->thing->thing->created_at. ".";


}

//$this->sms_message .= ' | TEXT ?';


$this->thing_report['sms'] = $this->sms_message;

        return $this->sms_message;

    }

	public function readSubject() {


		$status = true;
	return $status;		
	}

        public function PNG() {
// Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

//I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and testing, including re-installing apache on my computer a couple of times, I traced the problem to an included file.
//No the problem was not a whitespace, but the UTF BOM encoding character at the begining of one of my inluded files...
//So beware of your included files!
//Make sure they are not encoded in UTF or otherwise in UTF without BOM.
//Hope it save someone's time.

//http://php.net/manual/en/function.imagepng.php

//header('Content-Type: text/html');
//echo "Hello World";
//exit();

//header('Content-Type: image/png');
//QRcode::png('PHP QR Code :)');
//exit();
                // here DB request or some processing

    $snowflake_agent = new Snowflake($this->thing, "snowflake");

    $snowflake_agent->makePNG();
    $this->PNG = $snowflake_agent->PNG;

    //$this->thing_report['png'] = $this->PNG;
//return;
/*
        ob_start();
        imagepng($this->PNG);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->PNG = $imagedata;
*/
        $this->thing_report['png'] = $this->PNG;

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
        $response = '<img src="data:image/png;base64,'.base64_encode($this->PNG).'"alt="this snowflake is melting already">';

//        $this->thing_report['png'] = $image;

        //imagedestroy($imagedata);

        return $response;



                }




}









?>
