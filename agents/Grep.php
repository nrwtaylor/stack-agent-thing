<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);


class Grep
{
	function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
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

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		$this->node_list = array("start","optin"=>array("uuid","snowflake"));

		$this->thing->log( '<pre> Agent "Age" running on Thing ' .  $this->uuid .  ' </pre>' );




        $this->readSubject();

        // Move this out



                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("grep", "refreshed_at") );

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }


$text = $this->grep_phrase;

        // Search how?

        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch($text, 3);
$agent_things = $thing_report['things'];

        // Searches
        //$this->thing->db->setUser($this->from);
        $thing_report = $this->thing->db->userSearch($text);
$user_things = $thing_report['thing']; // Fix this discrepancy thing vs things


        // Or this.
        $thing_report = $this->thing->db->variableSearch(null, $text);
$variable_things = $thing_report['things'];

$this->things = array_merge($agent_things, $user_things, $variable_things);

//foreach ($this->things as $thing) {
//var_dump($thing['task']);
//}


 	    $this->sms_message = "";
  	    $reset = false;
        if ( $this->things == false  ) {
			// No age information store found.
            $this->resetCounts();
		} else {
			foreach ($this->things as $thing) {

//echo $thing->subject;
//exit();
                $uuid = $thing['uuid'];

                $variables_json= $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                //if(isset($variables['age']['mean'])) {$this->age = $variables['age']['mean'];}
                if(isset($variables['grep']['count'])) {$this->count = $variables['grep']['count'];}
                //if(isset($variables['age']['sum'])) {$this->sum = floatval($variables['age']['sum']);}
                //if(isset($variables['age']['sum_squared'])) {$this->sum_squared = floatval($variables['age']['sum_squared']);}
                //if(isset($variables['age']['sum_squared_difference'])) {$this->sum_squared_difference = floatval($variables['age']['sum_squared_difference']);}

                if(isset($variables['grep']['earliest'])) {$this->earliest_known = strtotime($variables['grep']['earliest']);}

				if ((!isset($this->count)) or ($this->count == false)
					 ) {
                    
//					$this->resetCounts();

				} else {

					// Successfully loaded an age Thing

                    $this->age_thing = new Thing($uuid);

					//$this->age_thing = $thing;
					break;

                		}

            $this->count = 0;
            $this->age_thing = new Thing(null);


			//$this->resetCounts();

			}

		}

//		$this->readSubject();
		$this->setSignals();

		return;
	}

	function getBalance()
    {
	}

	function resetCounts()
    {
        //$this->sms_message = "Stream stats reset. | ";
        $this->count = 0;
        //$this->sum = 0;
        //$this->sum_squared = 0;
        //$this->sum_squared_difference = 0;

//        $this->age_thing = new Thing(null);
//        //$this->age_thing->Create($this->from , 'grep', 's/ user grep');
		//$this->age_thing->flagGreen();

        return;
    }

    function stackAge()
    {
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

                $this->earliest_seen_population = $created_at;

            }

		}

		$this->earliest_known = $this->earliest_seen_population;

		$this->total_things = count($things);
//		$this->sum = $this->sum;

		$this->sample_count = 0;
		$this->count = $this->count;

		$start_time = time();

		$variables = array();


		$this->earliest_seen_sample = false;

        // 
        shuffle($things);

		while ($this->total_things > 0) {

			// Initially tried this.

		 //       shuffle($things);
      		$thing = array_pop($things);
            $created_at = strtotime( $thing['created_at'] );


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
		}

		$end_time = time();
		$this->calc_time =  $end_time-$start_time;

		$this->age_oldest = time() - $this->earliest_seen_population;

		// Store counts
		$this->age_thing->db->setFrom($this->from);

		$this->age_thing->json->setField("variables");
        $this->age_thing->json->writeVariable( array("grep", "count") , $this->count  );
		$this->age_thing->json->writeVariable( array("grep", "earliest"), $this->earliest_known   );

		$this->age_thing->flagGreen();

        return;
	}

    function makeTxt()
    {
        $txt = "grep for " . $this->grep_phrase . "\n";
        foreach($this->things as $thing) {
            $txt .= "created " . $thing['created_at']  . "";
            //$txt .= '"' . $thing['task'] .'". ';
            $txt .= " " . $thing['task']  . "\n";
        }

        $this->thing_report['txt'] = $txt;
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
//var_dump($this->things[0]['task']);
//exit();

		$this->sms_message = 'GREP | "' . $this->things[0]['task'] .'".';

  //              $this->sms_message .= "This is an attempt to retrieve stuff. | ";


        $this->sms_message .= " | created " . $this->things[0]['created_at']  . "";

        $link = $this->web_prefix . "thing/" . $this->things[0]['uuid'] . "/grep";
        $this->sms_message .= " | " . $link;

		$this->sms_message .= " | COUNT " . number_format ( $this->total_things ) . "";


		$this->thing_report['thing'] = $this->thing->thing;
		//$this->thing_report['created_at'] = $this->created_at;
		$this->thing_report['sms'] = $this->sms_message;


		// While we work on this
		$this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
        }

        $this->makeTxt();

		return $this->thing_report;
	}






	public function readSubject()
    {
        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $grep_phrase = str_replace("grep is","",$input);
        $grep_phrase = trim(str_replace("grep","",$grep_phrase));

        $this->grep_phrase = $grep_phrase;
	}

}

?>
