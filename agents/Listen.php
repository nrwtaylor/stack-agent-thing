<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Listen extends Agent
{
    public $var = 'hello';

    function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        //        $this->sqlresponse = null;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->thing->log(
            'Agent "Listen" running on Thing ' . $this->uuid . ''
        );
        $this->thing->log('received this Thing "' . $this->subject . '"');

        $this->thing_report['info'] = "Listen request was handled by Group";
        $this->thing_report['help'] =
            'This is the group manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';

        $this->thing->log('group setup completed');
        $this->thing->log('completed');
    }

    function get()
    {
        // Read the group agent variable
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "group",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->group_id = $this->thing->json->readVariable([
            "group",
            "group_id",
        ]);
    }

    function run()
    {
        if ($this->group_id == false) {
            // No group_id found on this Thing either.
            //$this->startGroup();

            $this->findGroup();
        }
    }

    function findGroup()
    {
        $this->thing->log('looking for contextually close groups');

        $group_thing = new Group($this->thing, "find");
        $this->group_id = $group_thing->thingreport['groups'][0];

        $this->thing->log(
            'found a group nearby called ' . $this->group_id . ''
        );
    }

    public function joinGroup($group = null)
    {
        $this->thing->log('<pre> Agent "Listen" called joinGroup() </pre>');

        $join_agent = new Group($this->thing, "join " . $group);

        $this->response .= "Joined group " . $group;

        $this->thing_report['sms'] = $join_agent->thing_report['sms'];
    }

    function listenGroup()
    {
        $listen_agent = new Group($this->thing, "listen:" . $this->group_id);

        $things = $listen_agent->thing_report['things'];

        $this->response .=
            "Counted " . count($listen_agent->thing_report['things']) . " ";

        $tasks = "";
        foreach ($things as $thing) {
            $tasks .= $thing['task'];
            //$this->message .= $thing['task'];
            $this->response .=
                ' | "' .
                $thing['task'] .
                '" ' .
                number_format(time() - strtotime($thing['created_at'])) .
                "s ago.";

            //copied to group
        }

        $this->thing->log('heard ' . $tasks . '');
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report['num_hits'] = $this->num_hits;

        $this->makeChoices();

        $this->thing_report['log'] = $this->thing->log;
        return $this->thing_report;
    }

    public function makeSMS()
    {
        $sms = "LISTEN ";
        if (isset($this->group_id)) {
            $sms .= strtoupper($this->group_id);
        }
        $sms .= " ";
        $sms .= $this->response;
        $sms .= "" . " | TEXT GROUP";
        $this->thing_report['sms'] = $sms;
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks('start');

        $this->thing_report['choices'] = $choices;
    }
    private function nextWord($phrase)
    {
    }

    public function readSubject()
    {
        //$this->thing->log(
        //    '<pre> Agent "Listen" readSubject(), "' . $this->subject . '"</pre>'
        //);
        //$this->response = null;

        $keywords = ['listen', 'listne', 's/listen', 'ln', 'l'];

        // Make a haystack.  Using just the subject, because ...
        // ... because ... I don't want to repeating an agents request
        // and creating some form of unanticipated loop.  Can
        // change this when there is some anti-looping in the path
        // following.

        $input = strtolower($this->subject);

        $prior_uuid = null;

        // Split into 1-grams.
        $pieces = explode(" ", strtolower($input));

        // Keywording first
        if (count($pieces) == 1) {
            if (strtolower($input) == 'listen') {
                $this->thing->log('keyword heard');

                $this->listenGroup();

                $this->thing->log('found 1 piece ' . $input . '');

                return;
            }

            $this->response .=
                $this->agent_name . " request not understood: " . $input;
            return;
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'listen':
                            if ($key + 1 > count($pieces)) {
                                //echo "last word is stop";
                                $this->group = false;
                                $this->response .= "Request not understood. ";
                                return;
                            } else {
                                //echo "next word is:";
                                //var_dump($pieces[$index+1]);
                                $this->group = $pieces[$key + 1];
                                $this->response .= $this->joinGroup(
                                    $this->group
                                );

                                $this->thing->log('heard ' . $piece . '');

                                return;
                            }
                            break;

                        default:

                        //echo 'default';
                    }
                }
            }
        }
        $this->response .= "Request not understood. ";

        return;
    }
}
