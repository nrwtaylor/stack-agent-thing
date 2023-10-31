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
        $time_string = $this->thing->Read(["group", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["group", "refreshed_at"], $time_string);
        }

        $this->group_id = $this->thing->Read(["group", "group_id"]);
    }

    public function makeChoices()
    {
        $this->createChoice(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $this->choices = $this->linksChoice('start');
    }

    public function isGroup($text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }
        $groups = $this->findGroup($text);
        if ($groups === false) {
            return false;
        }
        $tokens = $this->extractGroups($text);
        foreach ($tokens as $j => $token) {
            foreach ($groups as $i => $group) {
                if (strtolower($group) == strtolower($token)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function joinGroup($group_id = null)
    {
        $names = $this->thing->Write(["group", "action"], 'join');

        // Find out if the group exists

        $this->listenGroup($group_id);

        $c = count($this->members);

        if ($c == 0) {
            $this->thing->log('group ' . $group_id . ' nothing heard');
        } else {
        }

        $this->thing->log('joined group ' . $group_id . '');
        $this->group_id = $group_id;

        $names = $this->thing->Write(["group", "group_id"], $this->group_id);

        // Super primitive, but it does have this.
        $time_string = $this->thing->time();
        $this->thing->Write(["group", "refreshed_at"], $time_string);

        $this->thing->log('joined group ' . $group_id);

        $this->thing_report['group'] = $this->group_id;
    }

    public function nullAction()
    {
        $names = $this->thing->Write(["group", "action"], 'null');

        $this->response .= "Null action. ";
    }

    public function leaveGroup($group = null)
    {
        $names = $this->thing->Write(["group", "action"], 'leave');

        $this->response .= "Left group. ";
    }

    public function startGroup($type = null)
    {
        $names = $this->thing->Write(["group", "action"], 'start');

        if ($type == null) {
            $type = 'alphafour';
        }

        $s = substr(
            str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4)),
            0,
            4
        );
        $this->group_id = $s;

        //$this->message = $this->group_id;
        $this->response .= "Type 'SAY' followed by your message. ";

        $names = $this->thing->Write(["group", "group_id"], $this->group_id);

        $time_string = $this->thing->time();
        $this->thing->Write(["group", "refreshed_at"], $time_string);

        $this->createChoice(
            $this->agent_name,
            $this->node_list,
            "new group"
        );
        $this->choices = $this->linksChoice('new group');
    }

    public function findGroup($name = null)
    {

        // devstack call variables directly

        // Retries the last <99> group names.

        $names = $this->thing->Write(["group", "action"], 'find');

//        $thingreport = $this->thing->db->setUser($this->from);
//>thing_report["png"]        $thingreport = $this->thing->db->variableSearch(null, "group_id", 99);
$thingreport = [];
        $groups = [];

        foreach ($thingreport['things'] as $thing_obj) {
            $thing = new Thing($thing_obj['uuid']);

            $group_id = $thing->Read(["group", "group_id"]);

            if ($group_id == false or $group_id == null) {
            } else {
                $groups[] = $group_id;
            }

            $refreshed_at = $thing->Read(["group", "refreshed_at"]);
        }

        if (count($groups) == 0) {
            $this->response .= "";
            $this->response .= "No group found. ";
            $this->thing_report['groups'] = false;
            $this->group_id = null;
        } else {
            $this->group_id = $groups[0];

            $this->thing->Write(["group", "group_id"], $this->group_id);

            $this->thing_report['groups'] = $groups;
        }

/*
        $this->createChoice(
            $this->agent_name,
            $this->node_list,
            "start"
        );

        $this->choices = $this->linksChoice("listen");
*/


        $this->groups = $groups;
        return $this->thing_report['groups'];
    }

    public function listenGroup($group = null)
    {
        $this->members = [];
        $names = $this->thing->Write(["group", "action"], 'listen');

        if ($group == null) {
            $group = $this->group_id;
        }

        $this->group_id = $group;

        $agent = "say:" . $group;

        $this->thing->db->setFrom("null" . $this->mail_postfix);
        $t = $this->thing->db->agentSearch($agent, 10);

        $this->thing->db->agentSearch($this->from);
        $this->thing->db->setFrom($this->from);

        $this->thing_report['things'] = $t['things'];

        $age_low = null;
        $age_high = null;

        $ages = [];

        $things = $this->thing_report['things'];
        if ($things === false) {
            return $this->members;
        }

        if (count($things) != 0) {
            $this->response .= "";
        }

        foreach ($things as $thing) {
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
                'Earliest heard ' .
                $this->thing->human_time(max($ages)) .
                ' ago. ';
        } else {
            $this->response .=
                'Earliest heard ' .
                $this->thing->human_time(max($ages)) .
                ' ago. ';
        }

        $this->createChoice(
            $this->agent_name,
            $this->node_list,
            "listen"
        );
        $this->choices = $this->linksChoice("listen");

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
                $web .= $text['task'] . "<br>";
            }
        }

        $web .= "<p>";
        $web .= "<b>URL</b><br><p>";
        $web .=
            $this->group_id . ' ' . '<a href="' . $link . '">' . $link . '</a>';

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
        $sms .= " | " . $this->response;
        $sms .= "| TEXT " . $choice_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function textGroups($text = null)
    {
        $t = "";
        $this->findGroup(); // Might need to call this in the set-up.

        $groups = [];
        if (
            isset($this->thing_report['groups']) and
            $this->thing_report['groups'] != false
        ) {
            $groups = $this->thing_report['groups'];
        }
        $groups = array_unique($groups);
        foreach ($groups as $i => $group) {
            $t .= $group . " ";
        }
        return trim($t);
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

        $groups = [];
        if (
            isset($this->thing_report['groups']) and
            $this->thing_report['groups'] != false
        ) {
            $groups = $this->thing_report['groups'];
        }
        foreach ($groups as $i => $group) {
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
            $this->response .= 'Agent input was ' . $this->agent_input . '. ';
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

            if ($input == 'groups') {
                $this->response .= "Heard request for groups. ";
                $this->response .= $this->textGroups() . " ";
                /*
                if ($this->group_id != null) {
                    $this->response .=
                        "Retrieved the current group identity. Group is " .
                        $this->group_id .
                        ". ";
                } else {
                    $this->findGroup();
                    $this->response .= "Found group " . $this->group_id . ". ";
                }
*/
                $this->num_hits += 1;
                return;
            }

            if ($input == 'group') {
                if ($this->group_id != null) {
                    $this->response .=
                        "Retrieved the current group identity. Group is " .
                        $this->group_id .
                        ". ";
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
                    $this->joinGroup($this->thing_report['groups'][0]);
                }

                $this->num_hits += 1;
                return;
            }
            if (
                isset($this->subject[0]) and
                ctype_alpha($this->subject[0]) == true
            ) {
                // Strip out first letter and process remaning 4 or 5 digit number
                //$input = substr($input, 1);
            }

            if (is_numeric($this->subject) and strlen($input) == 5) {
            }

            if (is_numeric($this->subject) and strlen($input) == 4) {
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
