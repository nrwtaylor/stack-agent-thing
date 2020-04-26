<?php

error_reporting(E_ALL);ini_set('display_errors', 1);



require_once('/var/www/html/stackr.ca/lib/fpdf.php');
require_once('/var/www/html/stackr.ca/lib/fpdi.php');

require_once '/var/www/html/stackr.ca/agents/message.php';

//include_once('/var/www/html/stackr.ca/src/pdf.php'); 


class Age {

	function __construct(Thing $thing) {
		$this->thing = $thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        	$this->uuid = $thing->uuid;
	        $this->to = $thing->to;
        	$this->from = $thing->from;
        	$this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start");

		echo '<pre> Agent "Age" running on Thing ';echo $this->uuid;echo'</pre>';


                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("age", "refreshed_at") );

                if ($time_string == false) {
                        // Then this Thing has no group information
                        //$this->thing->json->setField("variables");
                        //$time_string = $this->thing->json->time();
                        //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
                }

		$this->thing->db->setFrom($this->from);
		$thing_report = $this->thing->db->agentSearch('age', 3);
		$things = $thing_report['things'];


 		$this->sms_message = "";
  		$reset = false;


              if ( $things == false  ) {

			// No age information store found.
                        $this->resetCounts();

		
		} else {

			foreach ($things as $thing) {


				$thing = new Thing($thing['uuid']);
		//		var_dump($thing);

                		$thing->json->setField("variables");
                		$this->age = $thing->json->readVariable( array("age", "mean") );
                		$this->count = $thing->json->readVariable( array("age", "count") );
                		$this->sum = floatval( $thing->json->readVariable( array("age", "sum") ) );
                		$this->sum_squared = floatval( $thing->json->readVariable( array("age", "sum_squared") )  );
                		$this->sum_squared_difference = floatval( $thing->json->readVariable( array("age", "sum_squared_difference") )  );

				$this->earliest_seen = strtotime ( $thing->json->readVariable( array("age", "earliest_seen") )  );


//var_dump ($this->age == false);
//var_dump ($this->count == false);
//var_dump ($this->sum == false);
//var_dump ($this->sum_squared == false);
//var_dump ($this->sum_squared_difference == false);

				if ( ($this->age == false) or
					($this->count == false) or
					($this->sum == false) or
					($this->sum_squared == false) or
					($this->sum_squared_difference == false) ) {

					//$this->resetCounts();
				} else {

					// Successfully loaded an age Thing

					$this->age_thing = $thing;
					break;

                		}

			$this->resetCounts();

			}

		}

		$this->getSubject();
		$this->setSignals();

		return;
	}

	function getBalance() {

	}

	function resetCounts() {

                $this->sms_message = "Reset stream stats. | ";
                $this->count = 0;
                $this->sum = 0;
                $this->sum_squared = 0;
                $this->sum_squared_difference = 0;

                $this->age_thing = new Thing(null);
                $this->age_thing->Create($this->from , 'age', 's/ user age');
		$this->age_thing->flagGreen();

		return;
	}

        function stackAge() {

                // Calculate streamed adhoc sample statistics
		// Like calculating stream statistics.
		// Keep track of counts.  And sums.  And squares of sums.
		// And sums of differences of squares.

		// Get all users records
		$this->thing->db->setUser($this->from);
                $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

                $things = $thingreport['thing'];


		foreach ($things as $thing) {

			$created_at = $thing['created_at'];

		}
//echo "meep";
//	exit();

		$this->total_things = count($things);
		$this->sum = $this->sum;

		$this->sample_count = 0;
		$this->count = $this->count;

		$start_time = time();

		$variables = array();
shuffle($things);
		while ($this->total_things > 0) {

//		        shuffle($things);
        		$thing = array_pop($things);


			$temp_thing = new Thing($thing['uuid']);

			$created_at = strtotime($temp_thing->thing->created_at);

			//var_dump($this->earliest_seen);

			if ( ($created_at < $this->earliest_seen  ) or ($this->earliest_seen == false) ) {

				$this->earliest_seen = $created_at;

			}

			$time_now = time();

			$variable = $time_now - $created_at; //age
			$variables[] = $variable;

			if ( $variable == 0 ) {
				echo "age = 0";
				continue;
				exit();
			} 

			$this->sample_count += 1;

			$this->count += 1;
			$this->sum += $variable;
			$this->sum_squared += $variable * $variable;

			if ( (time() - $start_time) > 2) {
				// timed out
				break;
			}

			if ($this->sample_count > $this->total_things  /20) {
				// 5% should be enough for sampling
				break;
			}

		}


		// Calculate the mean
		$this->mean = $this->sum / $this->count;
		
		// Calculate the sum squared difference
		$this->sum_squared_difference = $this->sum_squared_difference;

		foreach ($variables as $variable) {

			$squared_difference = ($variable -$this->mean) * ($variable - $this->mean);
			$this->sum_squared_difference += $squared_difference;

		}

		// Calculate the variance.  Precursor to standard deviation.
		$this->variance = $this->sum_squared_difference / $this->count;

		// Calculation the standard deviation.
		$this->standard_deviation = sqrt( $this->variance );

		$end_time = time();
		$this->calc_time =  $end_time-$start_time;

		// Store counts
		$this->age_thing->db->setFrom($this->from);

		$this->age_thing->json->setField("variables");
		$this->age_thing->json->writeVariable( array("age", "mean") , $this->mean  );
                $this->age_thing->json->writeVariable( array("age", "count") , $this->count  );
                $this->age_thing->json->writeVariable( array("age", "sum") , $this->sum );
                $this->age_thing->json->writeVariable( array("age", "sum_squared") , floatval( $this->sum_squared ) );
                $this->age_thing->json->writeVariable( array("age", "sum_squared_difference") , floatval( $this->sum_squared_difference ) );

		$this->age_thing->json->writeVariable( array("age", "earliest_seen"), $this->earliest_seen   );


		$this->age_thing->flagGreen();

                return $this->mean;
	}


	public function setSignals() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 


		$this->stackAge();


 		$this->thing->json->setField("variables");

//		$this->PNG();
//		$this->PDF();

		$this->sms_message = "AGE = " . number_format ($this->mean) . " | " . $this->sms_message;
                $this->sms_message .= "SD " . number_format ($this->standard_deviation) . " | ";
		$this->sms_message .= number_format( $this->sample_count ) . " Things sampled from " . number_format( $this->total_things ) . " in " . $this->calc_time . "s | ";
		$this->sms_message .= "COUNT " . number_format ( $this->count ) . " | ";

		if (false) {
			$this->sms_message .= "SUM " . number_format( $this->sum ) . " | ";
                	$this->sms_message .= "SUM SQUARED " . number_format( $this->sum_squared ) . " | ";
			$this->sms_message .= "SUM SQUARED DIFFERENCE " . number_format( $this->sum_squared_difference ) . " | ";
		}

		$this->sms_message .= 'TEXT BALANCE';


		$this->thing_report['thing'] = $this->thing->thing;
		//$this->thing_report['created_at'] = $this->created_at;
		$this->thing_report['sms'] = $this->sms_message;


		// While we work on this
		$this->thing_report['email'] = $this->sms_message;

                $message_thing = new Message($this->thing, $this->thing_report);



		return $this->thing_report;
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
                $pdf = new FPDI('P','mm','Letter');

                $pdf->setSourceFile('/var/www/html/stackr.ca/templates/satoshi.pdf');

                $tplidx1 = $pdf->importPage(1, '/MediaBox');  
                $pdf->addPage();  
                $pdf->useTemplate($tplidx1,0,0,215);  


                // Generate secret key first
//                QRcode::png($this->secret_key,"/var/www/html/stackr.ca/temp/file.png", QR_ECLEVEL_L, 4,1);               
//		$pdf->Image("/var/www/html/stackr.ca/temp/file.png",168,125,40);

                // Overwrite secret key
                // Create public code and publish as available Thing png image
//                QRcode::png($this->public_key,"/var/www/html/stackr.ca/temp/file2.png", QR_ECLEVEL_L, 4,1);
//                $pdf->Image("/var/www/html/stackr.ca/temp/file2.png",115,125,40);
		//$pdf->MemImage($image, 115,125,40);


		

                $pdf->SetFont('Helvetica','',10);
                $pdf->SetTextColor(0,0,0);

                $pdf->SetXY(5, 24);
                $t =  "Agent 'Satoshi' processed Thing "; 
                $pdf->Write(0, $t);


                $pdf->SetXY(5, 28);
                $t = $this->uuid . ' on ';
                $pdf->Write(0, $t);


                $pdf->SetXY(5, 32);
                $t = date("Y-m-d H:i:s") . ' and';
                $pdf->Write(0, $t);


                $pdf->SetXY(5, 36);
                $t = 'created PUBLIC ' . $this->public_key;
                $pdf->Write(0, $t);

                $pdf->SetXY(5, 40);
                $t = 'created PRIVATE ' . $this->secret_key;
                $pdf->Write(0, $t);



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

//ob_clean();
//$pdf->Output('newpdf.pdf', 'I');

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

//echo header('Content-Type: image/png');
//echo header('Content-Type: text/html');

                QRcode::png($codeText,false,QR_ECLEVEL_Q,4); 

//echo header('Content-Type: text/html');


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
class meepVariableStream
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

class meepPDF_MemImage extends FPDF {
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
