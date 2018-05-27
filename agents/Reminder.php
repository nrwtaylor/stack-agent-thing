<?php
namespace Nrwtaylor\StackAgentThing;
// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Reminder {

	function __construct(Thing $thing, $input = null)
    {
		//echo "Receipt called";

		$this->thing = $thing;
		$this->agent_name = 'reminder';
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

		$this->thing->log( '<pre> Agent "Reminder" running on Thing ' . $this->thing->nuuid . '.</pre>');
		$this->thing->log( '<pre> Agent "Reminder" received this Thing "' . $this->subject .  '".</pre>');

        $this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	

		// Set up reminders

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("reminder", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("reminder", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->reminder_ids = $this->thing->json->readVariable( array("reminder", "uuids") );

        if ( ($this->reminder_ids == false) ) {
	        $this->thing->log( '<pre> Agent "Reminder" setReminders() </pre>' );
            $this->setReminders();
        }


		// If readSubject is true then it has been responded to.


		$this->readSubject();


		$this->respond();

		$this->thing->log( '<pre> Agent "Reminder" completed.</pre>');
        $this->thing_report['log'] = $this->thing->log;

		return;
	}

	function setReminders()
    {
		$thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
        $things = $thingreport['thing'];
		$this->reminder_ids = array();

        if (count($things) == 0) {
            $this->reminder_ids = null;
        } else {

            foreach ($things as $thing) {
			    $this->reminder_ids[] = $thing['uuid'];
		    }
        }

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("reminder",
            "uuids"),  $this->reminder_ids );
		return;
	}

	public function respond()
    {
		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("reminder",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);

		$this->thing->flagGreen();


		$choices = $this->thing->choice->makeLinks('feedback');


		// Compose email

		$subject = "Three things from your stack on a " . date("l") . ' in ' . date("F");

		$uuid = $this->uuid;
		$sqlresponse = $this->sqlresponse;

		$url = $this->web_prefix . "api/redpanda/thing/" . $uuid . "/random";

//		$thingreport = $this->thing->db->userRecords($this->from,30);

        $thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
		$things = $thingreport['thing'];

		$this->ranked_things =array();

		foreach ($this->reminder_ids as $uuid) {

            $temp_thing = new Thing($uuid);
			$haystack = strtolower($temp_thing->to . $temp_thing->subject);

			if ( isset($temp_thing->account) ) {
				$rank_score = $temp_thing->account['thing']->balance['amount'];
			} else {
				$rank_score = null;
			}

			$this->ranked_things[] = array("name"=>$temp_thing->uuid,
							"likes"=>$rank_score);
		}


		$things = $this->get_flavors_by_likes(30);

		$message = "So here are three things you put on the stack.  That's what you wanted.<br>";
		//$message .= "<ul>";
		$i = 0 ;

		$subjects = array();

		foreach ($things as $ranked_thing) {

			$thing = new Thing($ranked_thing['name']);

			if ( isset($thing->account) ){ 
				$message .= '<li>' . $thing->account['thing']->balance['amount'] . ' | ' . $thing->subject . ' ';
			} else {
				$message .= '<li>' . 'null' . ' | ' . $thing->subject . ' ';
			}
    		$message .= '<a href="' . $this->web_prefix . 'thing/' . $thing->uuid . '/forget">Forget</a>';
			$message .= ' | <a href="' . $this->web_prefix . 'thing/' . $thing->uuid . '/remember">Remember</a>';
			$message .= "</li>";

			$subjects[] = $thing->subject;

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
		//echo $char_budget;

	$this->sms_message .= substr($subject, 0 , $char_budget) . '/' ;

}
//substr('abcdef', 0, 4)
//echo $this->sms_message;

$this->sms_message .= " | REPLY ?";



//		$this->thing->email->sendGeneric($this->from,'reminder',$subject,$message,$choices);
		$this->thing->log( '<pre> Agent "Reminder" email sent.</pre>');

		$this->thing_report = array('thing' => $this->thing->thing, 'choices' => $choices, 'info' => 'This is a reminder.','help' => 'This is probably stuff you want to remember.  Or forget.');

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $message;
        $this->thing_report['message'] = $message;

        $message_thing = new Message($this->thing, $this->thing_report);
                //$thing_report['info'] = 'SMS sent';


                $this->thing_report['info'] = $message_thing->thing_report['info'] ;



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



	function start()
    {
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
