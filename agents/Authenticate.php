<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';

ini_set("allow_url_fopen", 1);

class Authenticate extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code";

        $this->node_list = [
            "authenticate request" => [
                "authenticate verify" => ["authenticate request"],
            ],
        ];

        $this->response .= 'Start state is ';
        $this->state = $this->thing->choice->load('token'); //this might cause problems
        $this->response .= $this->state . ". ";

        $this->thing->account['thing']->Debit(10);
    }

    public function set() {

        $this->thing->choice->Choose($this->state);
        $this->state = $this->thing->choice->load('token');
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $choices = $this->thing->choice->makeLinks($this->state);

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];


        /*
		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Authenticate state: ' . $this->state . '<br>';
		
		$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);
*/
    }

    public function makeSMS() {

        $sms = "AUTHENTICATE | ";
        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }

    public function readSubject()
    {
        $this->response = null;

        if ($this->state == null) {
            switch ($this->subject) {
                case "authenticate request":
                    $this->response .= "Create. ";
                    $this->create();
                    break;
                case "authenticate verify":
                    $this->response .= "Verify";
                    break;

                default:
                    $this->create();
            }
        }

        $this->state = $this->thing->choice->load('authenticate');

        switch ($this->state) {
            case "authenticate request":
                $this->authenticateRequest();

                break;
            case "authenticate verify":
                //$this->kill();
                break;

            default:
                $this->response .= "Not found. ";

            // this case really shouldn't happen.
            // but it does when a web button lands us here.

            //if (rand(0,5)<=3) {
            //         $this->thing->choice->Create('hive', $this->node_list, "inside nest");
            //} else {
            //	$this->thing->choice->Create('hive', $this->node_list, "midden work");
            //}
        }

        $this->thing->choice->Create(
            'authenticate',
            $this->node_list,
            $this->state
        );

        return false;
    }

    function authenticateRequest()
    {
        $this->response .= "Authenticated. ";
    }

    function create()
    {
        $ant_pheromone['stack'] = 4;

        if (rand(0, 5) + 1 <= $ant_pheromone['stack']) {
            $this->thing->choice->Create(
                'token',
                $this->node_list,
                "authenticate request"
            );
        } else {
            $this->thing->choice->Create(
                'token',
                $this->node_list,
                "authenticate request"
            );
        }

        $this->thing->flagGreen();
    }
}
