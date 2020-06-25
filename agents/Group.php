<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Group extends Agent
{
    public $var = 'hello';

    public function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.
        $this->time_units = "hrs";

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.

        $this->node_list = [
            "start" => [
                "listen" => ["say hello" => ["listen"]],
                "new group" => ["say hello"],
            ],
        ];

        if ($this->response == true) {
            $this->thing_report['info'] = 'No group response created.';
            $this->thing_report['help'] =
                'This is the group manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';
            $this->thing_report['num_hits'] = $this->num_hits;

            $this->thing->log(
                'Agent "Group" completed with ' . $this->num_hits . ' hits.'
            );

            return;
        }

        $this->thing_report['info'] =
            'This is the group manager responding to a request.';
        $this->thing_report['help'] =
            'This is the group manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';

        $this->thing_report['num_hits'] = $this->num_hits;
        //$this->thing_report['log'] = $this->thing->log;
    }

    public function run()
    {
        // Seems like the best place if choices are to be optional.
        $this->makeChoices();
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "group",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["group", "refreshed_at"],
                $time_string
            );
        }

        $this->thing->json->setField("variables");
        $this->group_id = $this->thing->json->readVariable([
            "group",
            "group_id",
        ]);
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $this->choices = $this->thing->choice->makeLinks('start');
    }

    public function isGroup($text)
    {
        $this->extractGroups($text);

        if (!is_array($this->groups)) {
            return false;
        }

        if (count($this->groups) == 0) {
            return false;
        }

        return true;
    }

    public function joinGroup($group_id = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(["group", "action"], 'join');

        // Find out if the group exists

        $this->listenGroup($group_id);

        $c = count($this->members);

        if ($c == 0) {
            $this->thing->log('group ' . $group_id . ' nothing heard');
        } else {
        }

        $this->thing->log('joined group ' . $group_id . '');
        $this->group_id = $group_id;

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["group", "group_id"],
            $this->group_id
        );

        // Super primitive, but it does have this.
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["group", "refreshed_at"],
            $time_string
        );

        $this->thing->log('joined group ' . $group_id);

        $this->thing_report['group'] = $this->group_id;
    }

    public function nullAction()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(["group", "action"], 'null');

        $this->response .= "Null action. ";
    }

    public function leaveGroup($group = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["group", "action"],
            'leave'
        );

        $this->response = "Left group.";
    }

    public function startGroup($type = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["group", "action"],
            'start'
        );

        if ($type == null) {
            $type = 'alphafour';
        }

        $s = substr(
            str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4)),
            0,
            4
        );
        $this->group_id = $s;

        $this->message = $this->group_id;
        $this->sms_message .= " | " . "Type 'SAY' followed by your message.";

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["group", "group_id"],
            $this->group_id
        );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["group", "refreshed_at"],
            $time_string
        );

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "new group"
        );
        $this->choices = $this->thing->choice->makeLinks('new group');

        $this->sms_message =
            " | " . strtoupper($this->group_id) . " | " . $this->sms_message;

        return $this->message;
    }

    public function findGroup($name = null)
    {
        // devstack call variables directly

        // Retries the last <99> group names.

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(["group", "action"], 'find');

        $thingreport = $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->variableSearch(null, "group_id", 99);

        $groups = [];

        foreach ($thingreport['things'] as $thing_obj) {
            $thing = new Thing($thing_obj['uuid']);

            $thing->json->setField("variables");
            $group_id = $thing->json->readVariable(["group", "group_id"]);

            if ($group_id == false or $group_id == null) {
            } else {
                $groups[] = $group_id;
            }

            $thing->json->setField("variables");
            $refreshed_at = $thing->json->readVariable([
                "group",
                "refreshed_at",
            ]);
        }

        if (count($groups) == 0) {
            $this->sms_message .= "";
            $this->sms_message .= " | No group found.";
            $this->thingreport['groups'] = false;
            $this->group_id = null;
        } else {
            $this->group_id = $groups[0];

            $this->thing->json->writeVariable(
                ["group", "group_id"],
                $this->group_id
            );

            $this->thingreport['groups'] = $groups;
        }

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $this->choices = $this->thing->choice->makeLinks("listen");

        return $this->thingreport['groups'];
    }

    public function listenGroup($group = null)
    {
        $this->members = [];
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["group", "action"],
            'listen'
        );

        if ($group == null) {
            $group = $this->group_id;
        }

        $this->group_id = $group;

        $agent = "say:" . $group;

        $this->thing->db->setFrom("null" . $this->mail_postfix);
        $t = $this->thing->db->agentSearch($agent, 10);

        $this->thing->db->agentSearch($this->from);

        $this->thing_report['things'] = $t['things'];

        $age_low = null;
        $age_high = null;

        $ages = [];

        if (count($this->thing_report['things']) != 0) {
            $this->sms_message .= " |";
        }

        foreach ($this->thing_report['things'] as $thing) {
            $age = time() - strtotime($thing['created_at']);
            $ages[] = $age;

            $heard = $thing['task'];

            //$this->sms_message .= " '" . $heard . "'";
            $this->members[] = $heard;
            //			if ( ($age_low == null) or ($age_low < $age) ) {$age_low = $age;}
            //			if ( ($age_high == null) or ($age_high > $age) ) {$age_high = $age;}
        }

        //$this->sms_message .= ' | Showing messages ' . $this->retain_for . $this->time_units . " and more recent.";

        if (count($this->thing_report['things']) == 0) {
            $this->response .= 'Nothing heard. ';
        } elseif (count($this->thing_report['things']) == 1) {
            $this->response .=
                ' Earliest heard ' .
                $this->thing->human_time(max($ages)) .
                ' ago';
        } else {
            $this->response .=
                ' Earliest heard ' .
                $this->thing->human_time(max($ages)) .
                ' ago';
        }

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "listen"
        );
        $this->choices = $this->thing->choice->makeLinks("listen");

        $this->members = $this->thing_report['things'];

        //$to = "say:". $this->group_id
        return $this->members;
    }

    public function getGroup($input)
    {
        if (!isset($this->groups)) {
            $this->groups = $this->extractGroups($input);
        }

        if (count($this->groups) == 1) {
            $this->group = $this->groups[0];
            return $this->group;
        }

        return false;
    }

    public function extractGroups($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        //https://stackoverflow.com/questions/45016327/extract-four-character-matches-from-strings
        if (!isset($this->groups)) {
            $this->groups = [];
        }

        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0-9+#-]+)/ for capturing ?
        $pattern = "|[A-Za-z]{4}|";
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];

        $this->groups = $arr;
        return $this->groups;
    }

    // -----------------------

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.

        // $to = $this->thing->from;
        // $from = "group";

        if ($this->agent_input == "join") {
            return $this->thing_report;
        }
        if ($this->agent_input == "find") {
            return $this->thing_report;
        }
        if ($this->agent_input == "listen") {
            return $this->thing_report;
        }

        $this->message = $this->sms_message;

        $this->thing_report['email'] = $this->message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    private function nextWord($phrase)
    {
    }

    public function makeWeb()
    {
        $web = '<b>Group Agent</b><br><p>';
        $web .= '<p>';
        $web .= 'Group id is ' . $this->group_id . '.';
        $web .= '<p>';

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/' . "group";
        $qr_agent = new Qr($this->thing, $link);

        $web .= $qr_agent->html_image;

        if (isset($this->members)) {
            $web .= "<p>";

            foreach ($this->members as $i => $text) {
                $web .= $text . "<br>";
            }
        }

        $web .= "<p>";
        $web .= "<b>HELP</b><br><p>";
        $web .= $this->thing_report['help'];

        $web .= "<p>";
        $web .= "<b>INFORMATION</b><br><p>";
        $web .= $this->thing_report['info'];

        $this->thing_report['web'] = $web;
    }

    public function makeSMS()
    {
        $sms_end = strtoupper(strip_tags($this->choices['link']));

        $choice_text = implode("", explode("FORGET", $sms_end, 2));

        $sms = strtoupper($this->agent_name);
        if (isset($this->group_id)) {
            $sms .= " " . strtoupper($this->group_id);
        }
        $sms .= " | " . $this->response . "| TEXT " . $choice_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        /*
        if ($this->agent_input != 'screen') {
$this->response .= "Didn't see screen. So did not screen. ";

            $this->thing->log('"Group" respond() ');

            //$this->thing_report = $this->respond();
            return;
        }
*/

        $keywords = ['new', 'join', 'leave', 'listen'];

        $input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if ($this->agent_input == 'extract') {
            $this->extractGroups();
            return;
        }

        if ($this->agent_input == 'find') {
            $this->thing->log('received "find".');
            $this->findGroup();
            return;
        }

        if ($this->agent_input == 'listen') {
            $this->listenGroup();
            //$this->sms_message .= "quez";
            return;
        }

        if ($this->agent_input == 'join') {
            $this->joinGroup();
            return;
        }

        if (
            $this->group_id != false and
            strpos(strtolower($this->subject), strtolower($this->group_id)) !==
                false
        ) {
            $this->listenGroup($this->group_id);
            $this->num_hits += 1;

            return;
        }

        // Or we see if the group name matches one of the users.

        $this->findGroup(); // Might need to call this in the set-up.
        foreach ($this->thingreport['groups'] as $group) {
            if (
                strpos(strtolower($this->subject), strtolower($group)) !== false
            ) {
                if ($this->group_id == $group) {
                    $this->listenGroup($group);
                    $this->num_hits += 1;
                    $this->response .= 'Listened to group ' . $group . '. ';

                    return;
                }

                if ($this->group_id != $group) {
                    $this->joinGroup($group);
                    $this->num_hits += 1;
                    $this->response .= 'Switched to group ' . $group . '. ';

                    return;
                }
            }
        }

        if (strpos(strtolower($this->agent_input), "listen:") !== false) {
            $group = str_replace("listen:", "", $this->agent_input);
            $this->listenGroup($group);
            return;
        }
        // added 18 Jul
        if (strpos(strtolower($this->agent_input), "join:") !== false) {
            $this->response .= 'Agent input was ' . $this->agent_input;
            $group = str_replace("join:", "", $this->agent_input);
            $this->joinGroup($group);
            return;
        }

        if (count($pieces) == 1) {
            if ($input == 'new') {
                $this->startGroup();
                $this->response .= "Started group. ";
                $this->num_hits += 1;
                return;
            }

            if ($input == 'group') {
                if ($this->group_id != null) {
                    $this->response .=
                        "Group " . $this->group_id . " is already set. ";
                } else {
                    $this->findGroup();
                    $this->response .= "Found group " . $this->group_id . ". ";
                }
                $this->num_hits += 1;
                return;
            }

            if ($input == 'join') {
                if ($this->group_id != null) {
                    // Group is already set.
                    // Report the group.

                    $this->joinGroup($this->group_id);
                } else {
                    $this->findGroup();
                    $this->response .= "Found group. ";
                    $this->joinGroup($this->thingreport['groups'][0]);
                }

                $this->num_hits += 1;
                return;
            }

            if (ctype_alpha($this->subject[0]) == true) {
                // Strip out first letter and process remaning 4 or 5 digit number
                //$input = substr($input, 1);
            }

            if (is_numeric($this->subject) and strlen($input) == 5) {
                //return $this->response;
            }

            if (is_numeric($this->subject) and strlen($input) == 4) {
                //return $this->response;
            }

            $this->nullAction();
            return;
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'join':
                            if ($key + 1 > count($pieces)) {
                                $this->group = false;
                                return "Request not understood";
                            } else {
                                $this->group_id = $pieces[$key + 1];
                                $this->joinGroup($this->group_id);
                                $this->num_hits += 1;
                                return;
                            }
                            break;

                        case 'listen':
                            if ($key + 1 > count($pieces)) {
                                $this->group = false;
                                return "Request not understood";
                            } else {
                                if (isset($pieces[$key + 1])) {
                                    $this->group = $pieces[$key + 1];

                                    $this->listenGroup($this->group);
                                    $this->num_hits += 1;
                                    return;
                                }
                            }
                            break;

                        case 'find':
                            if ($key + 1 > count($pieces)) {
                                $this->group = false;
                                return "Request not understood";
                            } else {
                                $this->group = $pieces[$key + 1];
                                $this->findGroup($this->group);
                                $this->num_hits += 1;
                                return;
                            }
                            break;

                        case 'new':
                            $this->startGroup();
                            $this->num_hits += 1;

                            return;
                        case 'start':
                            $this->startGroup();
                            $this->num_hits += 1;
                            return;

                        case 'group':
                            $this->response .= "Saw group. ";

                            // exit() This doesn't trigger.  Group must be picked up before this.
                            if ($key + 1 > count($pieces)) {
                                $this->group = false;
                                return "Request not understood";
                            } else {
                                $this->group = $pieces[$key + 1];
                                $this->joinGroup($this->group);
                                $this->num_hits += 1;
                                return;
                            }
                            break;

                        default:
                    }
                }
            }
        }

        if (ctype_alnum($input) and strlen($input) == 4) {
            $this->joinGroup($input);
            $this->thing->log('Agent "Group" calling joinGroup()');
            $this->response .= 'Joined group ' . $input . '. ';
            // 4 digit alphanumeric received
            $this->num_hits += 1;
            return;
        }

        $this->response .= 'Did not recognize the command. ';
    }
}
