<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);


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

		$this->thing->log( '<pre> Agent "Age" running on Thing ' .  $this->uuid .  ' </pre>' );


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

                $uuid = $thing['uuid'];

                $variables_json= $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if(isset($variables['age']['mean'])) {$this->age = $variables['age']['mean'];}
                if(isset($variables['age']['count'])) {$this->count = $variables['age']['count'];}
                if(isset($variables['age']['sum'])) {$this->sum = floatval($variables['age']['sum']);}
                if(isset($variables['age']['sum_squared'])) {$this->sum_squared = floatval($variables['age']['sum_squared']);}
                if(isset($variables['age']['sum_squared_difference'])) {$this->sum_squared_difference = floatval($variables['age']['sum_squared_difference']);}

                if(isset($variables['age']['earliest'])) {$this->earliest_known = strtotime($variables['age']['earliest']);}


/*

				$thing = new Thing($thing['uuid']);
		//		var_dump($thing);

                		$thing->json->setField("variables");
                		$this->age = $thing->json->readVariable( array("age", "mean") );
                		$this->count = $thing->json->readVariable( array("age", "count") );
                		$this->sum = floatval( $thing->json->readVariable( array("age", "sum") ) );
                		$this->sum_squared = floatval( $thing->json->readVariable( array("age", "sum_squared") )  );
                		$this->sum_squared_difference = floatval( $thing->json->readVariable( array("age", "sum_squared_difference") )  );

                                $this->earliest_known = strtotime ( $thing->json->readVariable( array("age", "earliest") )  );
*/

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

                    $this->age_thing = new Thing($uuid);

					//$this->age_thing = $thing;
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

                $this->sms_message = "Stream stats reset. | ";
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

		// Get the earliest from the current data set

		$this->earliest_seen_population = false;
		foreach ($things as $thing) {

			$created_at = strtotime( $thing['created_at'] );

                        if ( ($created_at < $this->earliest_seen_population  ) or ($this->earliest_seen_population == false)  ) {

		//		echo $this->earliest_seen_population . " " .
		//			 number_format( time()- ( $this->earliest_seen_population )) . "<br>" ;

                                $this->earliest_seen_population = $created_at;

                        }

		}

		$this->earliest_known = $this->earliest_seen_population;

//echo "<br>";
//echo $this->earliest_seen_population;

//exit();
//echo $this->earliest;
//echo "<br>";
//echo $this->earliest_seen;
//echo "meep";
//exit();

		$this->total_things = count($things);
		$this->sum = $this->sum;

		$this->sample_count = 0;
		$this->count = $this->count;

		$start_time = time();

		$variables = array();


		$this->earliest_seen_sample = false;
shuffle($things);
		while ($this->total_things > 0) {

			// Initially tried this.

		 //       shuffle($things);
        		$thing = array_pop($things);

			// Initially tried this.

			//$temp_thing = new Thing($thing['uuid']);
			//$created_at = strtotime($temp_thing->thing->created_at);

			// But of course this is way faster.  Face palm.
                        $created_at = strtotime( $thing['created_at'] );




			//var_dump($this->earliest_seen);

			if ( ($created_at < $this->earliest_seen_sample  ) or ($this->earliest_seen_sample == false) ) {

				$this->earliest_seen_sample = $created_at;

			}



			$time_now = time();

			$variable = $time_now - $created_at; //age
			$variables[] = $variable;


			// Not because this is an age sample ignore 0 age.

			if ( $variable == 0 ) {
				//echo "age = 0";
				continue;
				exit();
			} 


                        if ( (time() - $start_time) > 2) {
                                $this->thing->log( "Sampled for more than 2s");
                                // timed out
                                break;
                        }

                        if ($this->sample_count > $this->total_things  / 4) {
                                //echo " Sampled 1 in 4";
                                // 20% should be enough for sampling
                                break;
                        }


			$this->sample_count += 1;

			$this->count += 1;
			$this->sum += $variable;
			$this->sum_squared += $variable * $variable;


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


		$this->age_oldest = time() - $this->earliest_seen_population;

		// Store counts
		$this->age_thing->db->setFrom($this->from);

		$this->age_thing->json->setField("variables");
		$this->age_thing->json->writeVariable( array("age", "mean") , $this->mean  );
                $this->age_thing->json->writeVariable( array("age", "count") , $this->count  );
                $this->age_thing->json->writeVariable( array("age", "sum") , $this->sum );
                $this->age_thing->json->writeVariable( array("age", "sum_squared") , floatval( $this->sum_squared ) );
                $this->age_thing->json->writeVariable( array("age", "sum_squared_difference") , floatval( $this->sum_squared_difference ) );

		$this->age_thing->json->writeVariable( array("age", "earliest"), $this->earliest_known   );


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

		$this->sms_message = "AGE = " . $this->thing->human_time ($this->mean) . " to " . 
			$this->thing->human_time ( $this->age_oldest ) .  " | " . $this->sms_message;

                $this->sms_message .= "This is the Mean to Oldest age of the Things you have deposited. | ";


                if (false) {
//                        $this->sms_message .= "OLDEST " . number_format( time() - $this->earliest ) . " | ";
                        $this->sms_message .= "OLDEST " . $this->thing->human_time( $this->age_oldest )
				. " to " . $this->thing->human_time ( time() - $this->earliest_seen_population )  . " | ";
                }

                //$this->sms_message .= "SD " . number_format ($this->standard_deviation) . " | ";
		//$this->sms_message .= number_format( $this->sample_count ) . " Things sampled from " . number_format( $this->total_things ) . " in " . $this->calc_time . "s | ";
		$this->sms_message .= "COUNT " . number_format ( $this->total_things ) . " | ";

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

}

?>
