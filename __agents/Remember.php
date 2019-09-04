<?php
namespace Nrwtaylor\StackAgentThing;
// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Remember {

	function __construct(Thing $thing) {
		//echo "Receipt called";

                if ($thing->thing != true) {
                        //print "falsey";

//	                echo '<pre> Agent "Remember" ran on a null Thing ';
//echo $thing->uuid;echo'</pre>';

        	        $this->thing_report = array('thing' => false, 
						'info' => 'Tried to run remember on a null Thing.',
						'help' => "That isn't going to work"
							);

                	return $this->thing_report;
                }

	
		$this->thing = $thing;
		$this->agent_name = 'remember';
		$this->agent_version = 'redpanda';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->web_prefix = $this->thing->container['stack']['web_prefix'];

		$this->node_list = array('remember'=>
					array('remember again'),
				'alt start'=>array('maintain')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log('<pre> Agent "Remember" running on Thing ' . $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Remember" received this Thing "' . $this->subject . '".</pre>');



//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	






                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("remember", "refreshed_at") );

                if ($time_string == false) {
                        $this->thing->json->setField("variables");
                        $time_string = $this->thing->json->time();
                        $this->thing->json->writeVariable( array("remember", "refreshed_at"), $time_string );
                }

                $this->thing->json->setField("variables");
                $this->remember_status = $this->thing->json->readVariable( array("remember", "status") );

                if ( ($this->remember_status == false) ) {
                        $this->thing->log( '<pre> Agent "Remember" setRemember() </pre>' );
                        $this->setRemember();
                } 




		// If readSubject is true then it has been responded to.

		$this->readSubject();
		$this->respond();


		$this->thing->log('<pre>Agent "Remember" completed.</pre>');
        $this->thing_report['log'] = $this->thing->log;
		return;
	}


        function setRemember() {

           //     $thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
           //     $things = $thingreport['thing'];

           //     $this->reminder_ids = array();

           //     foreach ($things as $thing) {

             //           $this->reminder_ids[] = $thing['uuid'];

               // }

                $this->thing->json->setField("variables");
                $this->thing->json->writeVariable(array("remember",
                        "status"),  true );

                return;
        }




	public function respond() {


		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("remember",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);




                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("remember", "refreshed_at") );

                if ($time_string == false) {
                        $this->thing->json->setField("variables");
                        $time_string = $this->thing->json->time();
                        $this->thing->json->writeVariable( array("remember", "refreshed_at"), $time_string );
                }

                $this->thing->json->setField("variables");
                $this->reminder_ids = $this->thing->json->readVariable( array("remember", "status") );

                if ( ($this->reminder_ids == false) ) {
                        $this->thing->log( '<pre> Agent "Reminder" setReminders() </pre>' );
                        $this->setRemember();
                } 



//echo "<pre>";
//print_r($this->thing);
//echo "</pre>";
//exit();

		$this->thing->flagGreen();

		$choices = $this->thing->choice->makeLinks('remember');
if ( isset($this->thing->account) ) {
        	$this->thing->account['thing']->Credit(100);
		$this->thing->account['stack']->Debit(-100);
                $this->thing->log( '<pre> Agent "Remember" credited 100 to the Thing ' . $this->thing->nuuid . ".", "INFORMATION");
} else {

                $this->thing->log( '<pre> Agent "Remember" could not access accounts on Thing.</pre>', "WARNING");


}


                $choices = $this->thing->choice->makeLinks('remember');

		$this->thing_report = array('thing' => $this->thing->thing, 'choices' => $choices, 'info' => 'This is a reminder.','help' => 'This is probably stuff you want to remember.  Or forget.');

                //echo '<pre> Agent "Remember" credited 100 to the thing account</pre>';


		return $this->thing_report;
	}

	public function readSubject() {

		$this->start();

		$status = true;
		return $status;		
	}

	function start() {

		if (rand(0,5) <= 3) {
			$this->thing->choice->Create('remember', $this->node_list, 'start');
		} else {
			$this->thing->choice->Create('remember', $this->node_list, 'alt start');
		}
		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}



}


?>
