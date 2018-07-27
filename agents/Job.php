<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;


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

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];
        $this->entity_name = $thing->container['stack']['entity_name'];


        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->index_type = "index";

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

    function getQuickresponse($text = null)
    {
        if ($text == null) {$text = $this->web_prefix;}
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }

    public function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    public function getIndex()
    {
        $agent = new Index($this->thing, "index");
        $this->index_png = $agent->PNG_embed;
    }


    function setReceipt() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("receipt",
            "refreshed_at"),  $this->thing->json->time()
            );

    }

	public function respond()
    {

		// Thing actions
		$this->thing->flagGreen();


//		$this->thing->choice->Create('receipt', $this->node_list, "receipt managing");
//		$choices = $this->thing->choice->makeLinks('receipt start');
        $choices = false;
//		$html_button_set = $links['button'];

$this->makePNG();


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

$this->makePDF();


		return $this->thing_report;
	}

   public function makeImage()
    {
$text = "job";
        $text = strtoupper($text);

$image_height = 125;
$image_width = 125*1;

        $image = imagecreatetruecolor($image_width, $image_height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);

        //$this->ImageRectangleWithRoundedCorners($image, 0,0, $image_width, $image_height, 12, $black);
        //$this->ImageRectangleWithRoundedCorners($image, 6,6, $image_width-6, $image_height-6, 12-6, $white);


        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';


        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = array(72,36,24,12,6);

        foreach($sizes_allowed as $size) {

            $angle = 0;
            $bbox = imagettfbbox ($size, $angle, $font, $text); 
            $bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
            $bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
            $bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]);
            $bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]);
            extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 

            //check width of the image 
            $width = imagesx($image); 
            $height = imagesy($image);
            if ($bbox['width'] < $image_width - 50) {break;}

        }


        $pad = 0;
        imagettftext($image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $this->subject);
        imagestring($image, 2, $image_width-75, 10, $this->subject, $textcolor);

        $this->image = $image;
    }

    function makeMessage()
    {
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


switch ($this->index_type) {
    case "index":
        $web_message .= '<img src="' . $this->web_prefix . 'thing/' . $this->uuid . '/index.png" alt="look a 4 digit index">';

        break;
    case "nuuid":
        $web_message .= '<img src="' . $this->web_prefix . 'thing/' . $this->uuid . '/nuuid.png" alt="look a 4 character semi-unique id">';
        break;
    default:
        $web_message .= '<img src="' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png" alt="look a freezing snowflake">';

}



        $web_message .= "<br>";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/job.txt';
        $web_message .= '<a href="' . $link . '">job.txt</a>';
        $web_message .= " | ";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/job.pdf';
        $web_message .= '<a href="' . $link . '">job.pdf</a>';
        $web_message .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/job.log';
        $web_message .= '<a href="' . $link . '">job.log</a>';
        //$web_message .= " | ";
        //$link = $this->web_prefix . 'thing/' . $this->uuid . '/'. $this->place_name;
        //$web_message .= '<a href="' . $link . '">'. $this->place_name. '</a>';





//       $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="this snowflake is melting already">';
        $this->web_message = $head. $web_message . $foot;
        $this->thing_report['web'] = $this->web_message;
    }

    public function makePDF()
    {

        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->setSourceFile($this->resource_path . 'snowflake/bubble.pdf');
            $pdf->SetFont('Helvetica', '', 10);

            $tplidx1 = $pdf->importPage(3, '/MediaBox');  
            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s['orientation'], $s);  
//        $pdf->useTemplate($tplidx1,0,0,215);  
            $pdf->useTemplate($tplidx1);  

            //$tplidx1 = $pdf->importPage(1, '/MediaBox');
            //$pdf->addPage();
            //$pdf->useTemplate($tplidx1, 0, 0, 215);

       //     $this->getNuuid();
       //     $pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');

       //     $this->getIndex();
       //     $pdf->Image($this->index_png, 5, 50, 20, 20, 'PNG');

            $pdf->SetFont('Helvetica', '', 12);
            $line_height = 5;
            $pdf->SetXY(16, 243);
            $pdf->MultiCell( 94, $line_height, $this->response, 0);



switch ($this->index_type) {
    case "index":
        $this->getIndex();
        $pdf->Image($this->index_png, 5, 18, 20, 20, 'PNG');

        break;
    case "nuuid":
        $this->getNuuid();
        $pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');
        break;
    default:
        $this->getNuuid();
        $pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');

}


//            $pdf->Image($this->PNG_embed, 5, 5, 20, 20, 'PNG');



            // $pdf->SetTextColor(0,0,0);

            $pdf->SetFont('Helvetica', '', 12);

             $pdf->SetXY(20, 40);
$pdf->MultiCell( 175, 8, $this->txt_message, 0);

            // $t = $this->thing_report['sms'];
            // $pdf->Write(0, $t);

            // Page 2
            $tplidx2 = $pdf->importPage(2);

    //        $pdf->addPage();

            $pdf->addPage($s['orientation'], $s);  
            $pdf->useTemplate($tplidx2, 0, 0);
            // Generate some content for page 2

            $pdf->SetFont('Helvetica', '', 10);
            $this->txt = "".$this->uuid.""; // Pure uuid.
//            $this->getUuid();
//            $pdf->Image($this->uuid_png, 175, 5, 30, 30, 'PNG');

//$reportSubtitle = "blah blah blah foo meep blah bar foo meep";
//$pdf->MultiCell( 100, 40, $reportSubtitle, 1);

        $this->getQuickresponse($this->web_prefix . 'thing\\' . $this->uuid . '\\job');
        $pdf->Image($this->quick_response_png,175,5,30,30,'PNG');

            $pdf->SetTextColor(0, 0, 0);
//        $pdf->SetXY(15, 10);
//        $t = $this->web_prefix . "thing/".$this->uuid;
//        $t = $this->uuid;

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(15, 10);
            $t = $this->thing_report['sms'];

$line_height = 4;

//            $pdf->Write(0, $t);
//$reportSubtitle = "blah blah blah foo meep blah bar foo meep";
            $pdf->MultiCell( 150, $line_height, $t, 0);


            $y = $pdf->GetY() + 1;
            $pdf->SetXY(15, $y);
            $text = "v0.0.1";
            $pdf->MultiCell( 150, $line_height, $this->agent_name . " " . $text, 0);

            $y = $pdf->GetY() + 1;
            $pdf->SetXY(15, $y);

            $text = "Pre-printed text and graphics (c) 2018 " . $this->entity_name;
            $pdf->MultiCell( 150, $line_height, $text, 0);



//            $pdf->SetXY(15, 15);
//            $text = "bravo";
//            $pdf->Write(0, $text);


            //$text = "Pre-printed text and graphics (c) 2018 " . $this->entity_name;
            //$pdf->SetXY(15, 20);
            //$pdf->Write(0, $text);

          $image = $pdf->Output('', 'S');

            $this->thing_report['pdf'] = $image;

        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }


        return $this->thing_report['pdf'];
    }

    public function makePNG()
    {
        $agent = new Png($this->thing, "png"); // long run

        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
        $this->thing_report['png'] = $agent->image_string;
    }

    function makeSMS() {

$this->verbosity = 1;
$this->index = $this->thing->nuuid;
$this->sms_message = "JOB " . strtoupper($this->index);

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

        $this->index = "meep";
        $this->response = "Made a new job sheet.";
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
