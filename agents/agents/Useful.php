<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Useful extends Agent {

	public function init() {
		//echo "Receipt called";


		if ($this->thing != true) {
			//print "falsey";

                echo '<pre> Agent "Useful" ran on a null Thing ';echo $thing->uuid;echo'</pre>';
                $this->thing_report = array('thing' => false, 
                                                'info' => 'Tried to run usfeul on a null Thing.',
                                                'help' => "That isn't going to work"
                                                        );



                return $this->thing_report;

		}

		$this->node_list = array('start'=>
					array('roll','iching'),
				'alt start'=>array('maintain')
					);

	}

	public function respondResponse() {

		// Thing actions

		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("useful",
			"refreshed_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
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
		$this->thing->flagGreen();
	}



}
