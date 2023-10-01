<?php
namespace Nrwtaylor\StackAgentThing;

// Helps with remembering things.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Remember extends Agent
{
    function init()
    {
        if ($this->thing != true) {
            $this->thing_report = [
                'thing' => false,
                'info' => 'Tried to run remember on a null Thing.',
                'help' => "That isn't going to work",
            ];

            return $this->thing_report;
        }

        $this->agent_version = 'redpanda';

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = [
            'remember' => ['remember again'],
            'alt start' => ['maintain'],
        ];
    }

    function setRemember()
    {
        //     $thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
        //     $things = $thingreport['thing'];

        //     $this->reminder_ids = array();

        //     foreach ($things as $thing) {

        //           $this->reminder_ids[] = $thing['uuid'];

        // }

        $this->thing->Write(["remember", "status"], true);
    }

    public function get()
    {
        $time_string = $this->thing->Read([
            "remember",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["remember", "refreshed_at"],
                $time_string
            );
        }

        $this->reminder_ids = $this->thing->Read([
            "remember",
            "status",
        ]);
    }

    public function set()
    {
        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(
            ["remember", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }
    public function run()
    {
    }

    public function respond()
    {
    } // Turns off response.

    public function doRemember()
    {
        $this->thing->flagGreen();

        $this->creditRemember();

        //$this->doRemember();

        $choices = $this->thing->choice->makeLinks('remember');

        $this->thing_report = [
            'thing' => $this->thing,
            'choices' => $choices,
            'info' => 'This is a reminder.',
            'help' =>
                'This is probably stuff you want to remember.  Or forget.',
        ];

        if (isset($this->remember_status) and $this->remember_status == false) {
            $this->thing->log('set Remember.');
            $this->setRemember();
        }

        /*
        if (isset($this->thing->account)) {
            $this->thing->account['thing']->Credit(100);
            $this->thing->account['stack']->Debit(-100);
            $this->thing->log(
                'credited 100 to the Thing ' .
                    $this->thing->nuuid .
                    ".",
                "INFORMATION"
            );
            $this->response .= 'Credited 100 to the Thing ' .
                    $this->thing->nuuid .
                    ".";


        } else {
            $this->thing->log(
                'could not access accounts on Thing.',
                "WARNING"
            );
            $this->response .= 'Could not access accounts on Thing ' .
                    $this->thing->nuuid .
                    "."; 
        }
*/
    }

    public function creditRemember()
    {
        if (isset($this->thing->account)) {
            $this->thing->account['thing']->Credit(100);
            $this->thing->account['stack']->Debit(-100);
            $this->thing->log(
                'credited 100 to the Thing ' . $this->thing->nuuid . ".",
                "INFORMATION"
            );
            $this->response .=
                'Credited 100 to the Thing ' . $this->thing->nuuid . ".";
        } else {
            $this->thing->log('could not access accounts on Thing.', "WARNING");
            $this->response .=
                'Could not access accounts on Thing ' .
                $this->thing->nuuid .
                ".";
        }
    }

    public function makeSMS()
    {
        $sms = "REMEMBER | " . $this->response;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        $uuid_agent = new Uuid($this->thing, "uuid");
        // This is recursive.

        if ($uuid_agent->uuid != $this->uuid) {
            $thing = new Thing($uuid_agent->uuid);
            $remember_agent = new Remember($thing, "remember");
            $this->response .= $remember_agent->response . " ";
            return;
        }

        $this->start();
        $this->doRemember();
    }

    function start()
    {
        if (rand(0, 5) <= 3) {
            $this->thing->choice->Create('remember', $this->node_list, 'start');
        } else {
            $this->thing->choice->Create(
                'remember',
                $this->node_list,
                'alt start'
            );
        }
        $this->thing->flagGreen();
    }
}
