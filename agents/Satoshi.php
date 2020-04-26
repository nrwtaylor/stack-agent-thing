<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);

use QR_Code\QR_Code;
use setasign\Fpdi;

//require_once('/var/www/html/stackr.ca/lib/fpdf.php');
//require_once('/var/www/html/stackr.ca/lib/fpdi.php');
//require_once('/var/www/html/stackr.ca/agents/message.php');


//include_once('/var/www/html/stackr.ca/src/pdf.php'); 


class Satoshi {

	function __construct(Thing $thing) {

        $this->start_time = microtime(true);

        // Setup Thing
        $this->thing = $thing;
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        // Setup logging
        $this->thing_report['thing'] = $this->thing->thing;




//		$this->thing = $thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

//        $this->uuid = $thing->uuid;
//        $this->to = $thing->to;
//        $this->from = $thing->from;
//        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("new user"=>array("opt-in"=>
							array("opt-out"=>array("opt-in","delete"))));

//		echo '<pre> Agent "Satoshi" running on Thing ';echo $this->uuid;echo'</pre>';

		
		$this->getSubject();
		$this->setSignals();


        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( 'Agent "Satoshi" ran for ' . $milliseconds . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;

//ob_clean();
//echo $this->thing_report['pdf'];
//exit();


        return;
//		echo '<pre> Agent "Satoshi" completed</pre>';

	}

	public function setSignals() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 


require_once '/var/www/html/stackr.ca/public/PHPCoinAddress.php';


// CoinAddress::set_debug(true);      // optional - show debugging messages
// CoinAddress::set_reuse_keys(true); // optional - use same key for all addresses

$coin = CoinAddress::bitcoin();  

//print 'public (base58): ' . $coin['public'] . "<br>";
//print 'public (Hex)   : ' . $coin['public_hex'] . "<br>";
//print 'private (WIF)  : ' . $coin['private'] . "<br>";
//print 'private (Hex)  : ' . $coin['private_hex'] . "<br>"; 

//exit();

		// This code should return the pdf when called.

                        $this->thing->json->setField("variables");


                       $public =  $this->thing->json->readVariable(array("satoshi",
                        "public")
                        );

                       $secret =  $this->thing->json->readVariable(array("satoshi",
                        "secret") 
                        );

		if ( ($public == false) and ($secret ==false) ) {

			$public_key = $coin['public'];
			$this->public_key = $public_key;

			$secret_key = $coin['private'];
			$this->secret_key = $secret_key;


                        $this->thing->json->setField("variables");
                         $this->thing->json->writeVariable(array("satoshi",
                        "public"),  $this->public_key
                        );

                        $this->thing->json->setField("variables");
                         $this->thing->json->writeVariable(array("satoshi",
                        "secret"),  $this->secret_key
                        );


		} else {
			$this->public_key = $public;
			$this->secret_key = $secret;


		}



		$this->PNG();
		$this->PDF();


$this->sms_message = "SATOSHI | ";
$this->sms_message .= "public key " .$this->public_key . ' ';
$this->sms_message .= "secret key " .$this->secret_key;
$this->sms_message .= ' | TEXT ?';





  //              if ( is_numeric($this->from) ) {
    //                    require_once '/var/www/html/stackr.ca/agents/sms.php';

  //                      $this->readSubject();

    //                    $sms_thing = new Sms($this->thing, $this->sms_message);
  //                      $this->thing_report['info'] = 'SMS sent';
                //return $thing_report//;
//                }

//$this->thing_report['thing'] = $this->thing->thing;
//$this->thing_report['created_at'] = $this->created_at;
//$this->thing_report['sms_message'] = $this->sms_message;


    
    //  $this->thing_report['thing'] = $this->thing->thing;
   //     $this->thing_report['sms'] = $this->sms_message;


        // While we work on this
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];


//$this->PNG();

//$image = $this->PNG();
//ob_clean();
//header('Content-Type: image/png');
//echo $image;
//echo $this->thing_report['png'];
//exit();



//$this->PDF();



		return;
	}






	public function getSubject() {
	}


	public function PDF() {

               // This code should return the pdf when called.
//		ob_clean();
//		header('text/html');

                $public_image = 'filename.png';
                //secret_image = 'filename.png';

                // initiate FPDI
                //$pdf = new FPDI('P','mm','Letter');

                $pdf = new Fpdi\Fpdi();


                $pdf->setSourceFile('/var/www/stackr.test/templates/satoshi.pdf');

                $tplidx1 = $pdf->importPage(1, '/MediaBox');  
                $pdf->addPage();  
                $pdf->useTemplate($tplidx1,0,0,215);  


                // Generate secret key first
                QRcode::png($this->secret_key,"/var/www/html/stackr.ca/temp/file.png", QR_ECLEVEL_L, 4,1);               
		$pdf->Image("/var/www/html/stackr.ca/temp/file.png",168,125,40);

                // Overwrite secret key
                // Create public code and publish as available Thing png image
                QRcode::png($this->public_key,"/var/www/html/stackr.ca/temp/file2.png", QR_ECLEVEL_L, 4,1);
                $pdf->Image("/var/www/html/stackr.ca/temp/file2.png",115,125,40);
		//$pdf->MemImage($image, 115,125,40);


		

//                $pdf->SetFont('Helvetica','',6);
//                $pdf->SetTextColor(0,0,0);

//                $pdf->SetXY(5, 24);
//                $t =  "Agent 'Satoshi' processed Thing "; 
//                $pdf->Write(0, $t);


//                $pdf->SetXY(5, 28);
//                $t = $this->uuid . ' on ';
//                $pdf->Write(0, $t);


//                $pdf->SetXY(5, 32);
//                $t = date("Y-m-d H:i:s") . ' and';
//                $pdf->Write(0, $t);


//                $pdf->SetXY(5, 36);
//                $t = 'created PUBLIC ' . $this->public_key;
//                $pdf->Write(0, $t);

//                $pdf->SetXY(5, 40);
//                $t = 'created PRIVATE ' . $this->secret_key;
//                $pdf->Write(0, $t);



                $pdf->SetFont('Helvetica','',10);
                $pdf->SetTextColor(255, 0, 0);





//              //$pdf->RotatedImage('circle.png',85,60,40,16,45);
//              //$pdf->RotatedText(100,60,'Hello!',45);

//              $pdf->SetXY(10, 44);
//              $pdf->Write(0, "PUBLIC: " . $public_key);

//                $pdf->SetXY(10, 48);
//                $pdf->Write(0, "PRIVATE: " . $private_key);




                //$key_thing = new Thing(null);
                //$key_thing->Create($this->from, 'satoshi', 's/ ' . $this->private_key);
                //$key_thing->flagGreen();


                // Generate some content for page 1  

                $tplidx2 = $pdf->importPage(2);  

                $pdf->addPage();
                $pdf->useTemplate($tplidx2,0,0);
                // Generate some content for page 2

                $pdf->SetFont('Helvetica','',6);
                $pdf->SetTextColor(0,0,0);

                $pdf->SetXY(5, 240);
                $t =  "Agent 'Satoshi' processed Thing "; 
                //$pdf->Write(0, $t);


  //              $pdf->SetXY(5, 28);
                $t .= $this->uuid . ' on ';
    //            $pdf->Write(0, $t);


                //$pdf->SetXY(5, 32);
                $t .= date("Y-m-d H:i:s") . ' with';
                //$pdf->Write(0, $t);

                $pdf->Write(0, $t);

                $pdf->SetXY(5, 243);
                $t = 'PUBLIC KEY ' . $this->public_key;
                $pdf->Write(0, $t);

                $pdf->SetXY(5, 246);
                $t = 'and SECRET KEY ' . $this->secret_key . ".";
                $pdf->Write(0, $t);

                $pdf->SetXY(5, 249);


$t = "Bitcoin pair algorithm used was PHPCoinAddress (https://github.com/zamgo/PHPCoinAddress).";

                $pdf->Write(0, $t);


//ob_clean(); //$pdf->Output('newpdf.pdf', 'I');
//exit();

//header('Content-Type: application/pdf');

                ob_start();
                $image = $pdf->Output('', 'I');
                $image = ob_get_contents();
                ob_clean();


//http://www.fpdf.org/en/script/script45.php

		$this->thing_report['pdf'] = $image;

//ob_clean();
//echo $this->thing_report['pdf'];
//exit();
		return $this->thing_report['pdf'];
}



        public function PNG() {
// Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-$

                // here DB request or some processing
                $codeText = $this->public_key;

                ob_clean();
                ob_start();

                QRcode::png($codeText,false,QR_ECLEVEL_Q,4); 


                $image = ob_get_contents();
                ob_clean();

// Can't get this text editor working yet 10 June 2017

//$textcolor = imagecolorallocate($image, 0, 0, 255);
// Write the string at the top left
//imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

$this->thing_report['png'] = $image;
//echo $this->thing_report['png']; // for testing.  Want function to be silent.


                return $this->thing_report['png'];
                }






}




// Stream handler to read from global variables
class VariableStream
{
    private $varname;
    private $position;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url['host'];
        if(!isset($GLOBALS[$this->varname]))
        {
            trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
            return false;
        }
        $this->position = 0;
        return true;
    }

    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_seek($offset, $whence)
    {
        if($whence==SEEK_SET)
        {
            $this->position = $offset;
            return true;
        }
        return false;
    }
    
    function stream_stat()
    {
        return array();
    }
}

class PDF_MemImage extends FPDF {
    function __construct($orientation='P', $unit='mm', $format='A4')
    {
        parent::__construct($orientation, $unit, $format);
        // Register var stream protocol
        stream_wrapper_register('var', 'VariableStream');
    }

    function MemImage($data, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the image contained in $data
        $v = 'img'.md5($data);
        $GLOBALS[$v] = $data;
        $a = getimagesize('var://'.$v);
        if(!$a)
            $this->Error('Invalid image data');
        $type = substr(strstr($a['mime'],'/'),1);
        $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
        unset($GLOBALS[$v]);
    }

    function GDImage($im, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the GD image associated with $im
        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        $this->MemImage($data, $x, $y, $w, $h, $link);
    }
}

?>
