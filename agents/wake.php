<?php
// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';
//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';


class Wake {

	function __construct(Thing $thing, $input = null) {
		//echo "Receipt called";




		$this->thing = $thing;
		$this->agent_name = 'wake';
		$this->agent_version = 'redpanda';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->web_prefix = $this->thing->container['stack']['web_prefix'];


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		echo '<pre> Agent "Wake" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Wake" received this Thing "';echo $this->subject;echo'"</pre>';

$this->node_list = array("start"=>array("sleep"=>array("wake"=>array("sleep"))));

		// Set up reminders

                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("wake", "refreshed_at") );

                if ($time_string == false) {
                        $this->thing->json->setField("variables");
                        $time_string = $this->thing->json->time();
                        $this->thing->json->writeVariable( array("wake", "refreshed_at"), $time_string );
                }

                $this->thing->json->setField("variables");
                $this->wake_time = $this->thing->json->readVariable( array("wake", "wake_time") );

                if ( ($this->wake_time == false) ) {
			$this->thing->log( '<pre> Agent "Wake" setReminders() </pre>' );
                        $this->setWaketime();
                } 


                $this->thing->json->setField("variables");
                $this->wake_time = $this->thing->json->readVariable( array("wake", "wake_state") );

                if ( ($this->wake_time == false) ) {
                        $this->thing->log( '<pre> Agent "Wake" state </pre>' );
                        $this->setWaketime();
                } 




//var_dump($this->reminder_ids);
//exit();


		// If readSubject is true then it has been responded to.

		$this->readSubject();
		$this->respond();


		echo '<pre> Agent "Reminder" completed</pre>';
		return;
	}

	function setWaketime() {

		//$thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
                //$things = $thingreport['thing'];
		
	

                $this->thing->json->setField("variables");
                $this->thing->json->writeVariable(array("wake",
                        "wake_time"),  $this->wake_time );

                $this->thing->json->setField("variables");
                $this->thing->json->writeVariable(array("wake",
                        "state"),  'sleep' );


		return;
	}

	

	public function respond() {

		// Thing actions

//		$this->thing->json->setField("settings");
//		$this->thing->json->writeVariable(array("reminder",
//			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
//			);

		$this->thing->flagGreen();


		$choices = $this->thing->choice->makeLinks('feedback');

//		echo '<pre> Agent "Reminder" Thing : ';print_r($this->thing->thing);echo'</pre>';
//		echo '<pre> Agent "Reminder" Thing : ';print_r($choices);echo'</pre>';
//var_dump($choices);
//exit();

		// Compose email


//		$stackr_url = 'https://stackr.co';


		$subject = "Three things from your stack on a " . date("l") . ' in ' . date("F");

		$uuid = $this->uuid;
		$sqlresponse = $this->sqlresponse;

		$url = $this->web_prefix . "api/redpanda/thing/" . $uuid . "/random";

//		$thingreport = $this->thing->db->userRecords($this->from,30);

$thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
		$things = $thingreport['thing'];

		$this->ranked_things =array();

//		foreach ($things as $thing) {
		foreach ($this->reminder_ids as $uuid) {
// Build a haystack


//			$temp_thing = new Thing($thing['uuid']);
                        $temp_thing = new Thing($uuid);


			$haystack = strtolower($temp_thing->to . $temp_thing->subject);



			if ( isset($temp_thing->account) ) {
				$rank_score = $temp_thing->account['thing']->balance['amount'];
			} else {
				$rank_score = null;
			}

			$this->ranked_things[] = array("name"=>$temp_thing->uuid,
							"likes"=>$rank_score);

//echo "<pre>";print_r($this->ranked_things);echo "</pre>";


		}


		$things = $this->get_flavors_by_likes(30);
//		$things = $thingreport['thing'];


		$message = "So here are three things you put on the stack.  That's what you wanted.<br>";
		//$message .= "<ul>";
		$i = 0 ;

		$subjects = array();

		foreach ($things as $ranked_thing) {
		
			$thing = new Thing($ranked_thing['name']);

		//	var_dump($thing);
		//	exit();

			//var_dump($thing);
			//$t = '';
//			$message .= '*' . $thing['task'] . ' ';
//			$message .= $stackr_url . '/thing/' . $thing['uuid'] . '/forget';
//			$message .= "\r\n";


			if ( isset($thing->account) ){ 
				$message .= '<li>' . $thing->account['thing']->balance['amount'] . ' | ' . $thing->subject . ' ';
			} else {
				$message .= '<li>' . 'null' . ' | ' . $thing->subject . ' ';
			}
		$message .= '<a href="' . $this->web_prefix . 'thing/' . $thing->uuid . '/forget">Forget</a>';
			$message .= ' | <a href="' . $this->web_prefix . 'thing/' . $thing->uuid . '/remember">Remember</a>';
			$message .= "</li>";

			$subjects[] = $thing->subject;

//
//			if ($i == 2) {break;}
//			$i = $i + 1;
		}
		$message .= "</ul>";
		$message .= '<br><br>';

$max_sms_length = 150;

$length_budgets = array();
$total_chars = 0;
foreach ($subjects as $subject) {
	$chars = strlen($subject) + 1;
	$total_chars += $chars;
}

//echo "total chars". $total_chars;

$this->sms_message = "REMINDER | ";

foreach ($subjects as $subject) {

	$char_budget = intval( (strlen($subject) + 1) / $total_chars * $max_sms_length);
		echo $char_budget;

	$this->sms_message .= substr($subject, 0 , $char_budget) . '/' ;

}
//substr('abcdef', 0, 4)
//echo $this->sms_message;

$this->sms_message .= " | REPLY ?";

                if ( is_numeric($this->from) ) {
                        require_once '/var/www/html/stackr.ca/agents/sms.php';

//                        $this->readSubject();

                        $sms_thing = new Sms($this->thing, $this->sms_message);
                        $thing_report['info'] = 'SMS sent';

                //return $thing_report;
                }




		$this->thing->email->sendGeneric($this->from,'reminder',$subject,$message,$choices);
		echo '<pre> Agent "Reminder" email sent</pre>';

		$this->thing_report = array('thing' => $this->thing->thing, 'choices' => $choices, 'info' => 'This is a reminder.','help' => 'This is probably stuff you want to remember.  Or forget.');

		$this->thing_report['sms'] = $this->sms_message;

		return $this->thing_report;
			}

// https://teamtreehouse.com/community/how-to-retrieve-highest-4-values-from-an-associative-array-in-php

//function get_all_flavors() {

//$flavors = array(
//    array("name" => "Vanilla", "likes" => 312),
//    array("name" => "Cookie Dough", "likes" => 976),
//    array("name" => "Peppermint", "likes" => 12),        
//    array("name" => "Cake Batter", "likes" => 598),
//    array("name" => "Avocado Chocolate", "likes" => 6),        
//    array("name" => "Jalapeno So Spicy", "likes" => 3),        
//);

//return $flavors;

//}

function get_flavors_by_likes($number) {

//$all = $this->get_all_flavors();
$all = $this->ranked_things;
$total_flavors = count($all);
$position = 0;

$popular = $all;
usort($popular, function($a, $b) {
    return $b['likes'] - $a['likes'];
});

return array_slice($popular, 0, $number);

}


	public function readSubject() {

		$this->start();

		$status = true;
	return $status;		
	}



	function start() {

//		$this->thing = new Thing(null);
//		$this->thing->Create("redpanda.stack@gmail.com", "reminder", "start");

		//$choice = new Choice($ant_thing->uuid);

//		echo $thing->uuid . "<br>";

		//$current_node = "inside nest";

		if (rand(0,5)<=3) {
			$this->thing->choice->Create('reminder', $this->node_list, 'feedback');
		} else {
			$this->thing->choice->Create('reminder', $this->node_list, 'feedback2');
		}
		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}



}

?>
