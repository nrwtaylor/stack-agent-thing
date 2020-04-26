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


class Useful {

	function __construct(Thing $thing) {
		//echo "Receipt called";

		if ($thing->thing != true) {
			//print "falsey";

                echo '<pre> Agent "Useful" ran on a null Thing ';echo $thing->uuid;echo'</pre>';
                $this->thing_report = array('thing' => false, 
                                                'info' => 'Tried to run usfeul on a null Thing.',
                                                'help' => "That isn't going to work"
                                                        );



                return $this->thing_report;


			exit();

		}

		$this->agent_name = 'useful';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
        $this->test= "Development code";

        $this->agent_input = $text;

	
		$this->thing = $thing;
		$this->agent_version = 'redpanda';

$this->start_time = $this->thing->elapsed_runtime();


 $this->thing_report  = array("thing"=>$this->thing->thing);

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->web_prefix = $this->thing->container['stack']['web_prefix'];

		$this->node_list = array('start'=>
					array('roll','iching'),
				'alt start'=>array('maintain')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	


		// If readSubject is true then it has been responded to.

		$this->readSubject();
		$this->respond();


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;


		return;
	}

	public function respond() {

		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("useful",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);

		$this->thing->flagGreen();

//		$choices = $this->thing->choice->makeLinks('feedback');

     	$this->thing->account['thing']->Credit(500);
		$this->thing->account['stack']->Debit(-500);

        $choices = $this->thing->choice->makeLinks('start');



		$this->thing_report['choices'] = $choices;
        $this->thing_report['info'] = 'This is the Useful agent thanking you for letting us know Stackr was useful by giving you 500 credits.';
        $this->thing_report['help'] = 'We use this information to help us tailor our services.';


		return $this->thing_report;
	}

	public function readSubject() {

		$this->start();

		$status = true;
		return $status;		
	}

	function start() {

		if (rand(0,5) <= 3) {
			$this->thing->choice->Create('useful', $this->node_list, 'start');
		} else {
			$this->thing->choice->Create('useful', $this->node_list, 'alt start');
		}
		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}



}









?>
