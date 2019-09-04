<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Group
{
    public $var = 'hello';


    public function __construct(Thing $thing, $agent_input = null)
    {
        if ($agent_input == null) {
            $agent_input = null;
        }
        $this->agent_input = strtolower($agent_input);
        $this->thing = $thing;

        // STiCKY FOUR DIGIT CODE GENERATE.
        // JOIN AND LEAVE not yet created.

        $this->thing = $thing;
        $this->agent_name = 'group';
        $thing_report['thing'] = $this->thing->thing;
        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


        $this->api_key = $this->thing->container['api']['translink'];

        $this->retain_for = 4; // Retain for at least 4 hours.
        $this->time_units = "hrs";

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        $this->num_hits = 0;

        $this->sms_message = "";

        $this->response = false;

        $this->sqlresponse = null;

        // Allow for a new state tree to be introduced here.

        $this->node_list = array( "start"=> array("listen"=> array("say hello"=> array("listen") ),
                                                   "new group"=>array("say hello")
                                        ) );

        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $this->choices = $this->thing->choice->makeLinks('start');


        $this->thing->log('<pre> Agent "Group" running on Thing ' . $this->thing->nuuid . '.</pre>');
        $this->thing->log('<pre> Agent "Group" received this Thing "' . $this->subject . '".</pre>');


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable(array("group", "refreshed_at"));

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(array("group", "refreshed_at"), $time_string);
        }

        $this->thing->json->setField("variables");
        $this->group_id = $this->thing->json->readVariable(array("group", "group_id"));

        //$this->group_id = false;


        // So if there is no group_id set on the Thing, and
//                if ( ($this->group_id == false) and ($this->agent_input == null) ) {
        //echo "meep";
        //			$this->thing->log( '<pre> Agent "Group" startGroup() </pre>' );

        // First try to find an existing group.
        //			$thingreport = $this->findGroup();
        //			if ( count($thingreport['things']) == 0 ) {
        //                      	$this->startGroup();
        //			}


        //              }




        $this->readSubject();

        if ($this->response == true) {
            $this->thing_report['info'] = 'No group response created.';
            $this->thing_report['help'] = 'This is the group manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';
            $this->thing_report['num_hits'] = $this->num_hits;


            $this->thing->log('<pre> Agent "Group" completed with ' . $this->num_hits . ' hits.</pre>');

            return;
        }


        if (($this->agent_input != 'screen')) {
            $this->thing->log('<pre> Agent "Group" respond() </pre>');

            $this->thing_report = $this->respond();
        }

        $this->PNG();

        $this->thing_report['info'] = 'This is the group manager responding to a request.';
        $this->thing_report['help'] = 'This is the group manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';
        $this->thing_report['num_hits'] = $this->num_hits;

        $this->thing->log('<pre> Agent "Group" completed</pre>');
        $this->thing_report['log'] = $this->thing->log;

        return;
    }

    public function joinGroup($group_id = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "action"), 'join');

        // Find out if the group exists

        //$thing_report = $this->thing->db->byWordlist( array($group_id) );
        //$c = count( $thing_report['things'] );

        $thing_report = $this->listenGroup($group_id);
        $c = count($thing_report['things']);

        $this->sms_message .= ' | Joined group '. strtoupper($group_id);


        if ($c == 0) {
            $this->thing->log('<pre> Agent "Group" group ' . $group_id . ' nothing heard </pre>');
        } else {
            //$this->thing->log( '<pre> Agent "Group" group ' . $group_id . ' not found </pre>' );
                  //      $this->message = 'Joined group. Activity heard (' . $c . ') in Group: '. $group_id;
                  //      $this->sms_message = 'Joined group. Activity heard ('. $c .') in Group: ' . $group_id;
        }

        $this->thing->log('<pre> Agent "Group" joined group ' . $group_id . ' </pre>');
        $this->group_id = $group_id;

        //$this->message = "joinGroup " . $this->group_id;
        //$this->sms_message = $this->group_id;

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "group_id"), $this->group_id);

        // Super primitive, but it does have this.
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(array("group", "refreshed_at"), $time_string);

        $this->thing->log('<pre> Agent "Group" joined group ' . $group_id . ' </pre>');

        $this->thing_report['group'] = $this->group_id;

        return $this->message;

    }

    public function nullAction()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "action"), 'null');

        $this->message = "Request not understood.";
        $this->sms_message = "Request not understood.";
        $this->response = true;
        return $this->message;
    }


    public function leaveGroup($group = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "action"), 'leave');

        $this->message = "Left group.";
        $this->sms_message = "Left group.";

        $this->sms_message = " | ". strtoupper($this->group_id) . " | " .$this->sms_message;

        return $this->message;
    }


    public function startGroup($type = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "action"), 'start');


        if ($type == null) {
            $type = 'alphafour';
        }

//
        //		if ($this->group_id == null) {

        $s = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4)), 0, 4);
        $this->group_id = $s;

        //		}

        $this->message = $this->group_id;
        //$this->sms_message .= " | "  .$this->group_id;
        $this->sms_message .= " | " . "Type 'SAY' followed by your message.";


        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "group_id"), $this->group_id);

        //if ($time_string == false) {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(array("group", "refreshed_at"), $time_string);



        $this->thing->choice->Create($this->agent_name, $this->node_list, "new group");
        $this->choices = $this->thing->choice->makeLinks('new group');

        $this->sms_message = " | ". strtoupper($this->group_id) . " | " .$this->sms_message;


        return $this->message;
    }

    public function findGroup($name = null)
    {
        // Retries the last <99> group names.

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "action"), 'find');

        $thingreport = $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->variableSearch(null, "group_id", 99);

        //		echo "<br>";

        $groups = array();

        foreach ($thingreport['things'] as $thing_obj) {
            $thing = new Thing($thing_obj['uuid']);

            $thing->json->setField("variables");
            $group_id = $thing->json->readVariable(array("group", "group_id"));

            if (($group_id == false) or ($group_id == null)) {
            } else {
                $groups[] = $group_id;
            }

            $thing->json->setField("variables");
            $refreshed_at = $thing->json->readVariable(array("group", "refreshed_at"));
        }


        if (count($groups) == 0) {
            $this->sms_message .= "";
            $this->sms_message .= " | No group found.";
            $this->thingreport['groups'] = false;
            $this->group_id = null;
        } else {
            $this->group_id = $groups[0];

            $this->thing->json->writeVariable(array("group", "group_id"), $this->group);

            $this->sms_message .= " | This is the Group chat function.  Commands: SAY, LISTEN, JOIN.";
            $this->thingreport['groups'] = $groups;
        }

        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $this->choices = $this->thing->choice->makeLinks("listen");

        //$this->sms_message = " | ". strtoupper( $this->group_id ) . " | " .$this->sms_message;


        return $this->thingreport['groups'];
        //exit();
    }

    public function listenGroup($group = null)
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(array("group", "action"), 'listen');


        if ($group == null) {
            $group = $this->group_id;
        }

        $this->group_id = $group;
        //$this->group_id = "meepmepp";

        $agent = "say:" . $group;

        $this->thing->db->setFrom("null" . $mail_postfix);
        $t = $this->thing->db->agentSearch($agent, 10);

        $this->thing->db->agentSearch($this->from);


        $this->thing_report['things'] = $t['things'];



        $age_low = null;
        $age_high = null;

        //$this->sms_message .= " | " . $group ;
        $ages = array();

        if (count($this->thing_report['things']) != 0) {
            $this->sms_message .= " |";
        }

        foreach ($this->thing_report['things'] as $thing) {
            $age = (time() - strtotime($thing['created_at']));
            $ages[] = $age;
            //echo $age;
            //exit();
            //echo $thing['task'];
            //$this->sms_message .= ' | "' . $thing['task'] . '" ' . $age . "s ago.";

            //			$heard = substr(strstr($thing['task']," "), 1);

            $heard = $thing['task'];

            $this->sms_message .= " '" . $heard . "'";


            //			if ( ($age_low == null) or ($age_low < $age) ) {$age_low = $age;}
//			if ( ($age_high == null) or ($age_high > $age) ) {$age_high = $age;}
        }




        //$this->sms_message .= ' | Showing messages ' . $this->retain_for . $this->time_units . " and more recent.";
        //$this->sms_message .= "meep";

        if (count($this->thing_report['things']) == 0) {
            $this->sms_message .= ' | Nothing heard.';
        } elseif (count($this->thing_report['things']) == 1) {
            $this->sms_message .= ' | Earliest heard ' . $this->thing->human_time(max($ages)) . ' ago';
        } else {
            $this->sms_message .= ' | Earliest heard ' . $this->thing->human_time(max($ages)) . ' ago';
        }




        $this->thing->choice->Create($this->agent_name, $this->node_list, "listen");
        $this->choices = $this->thing->choice->makeLinks("listen");

        $this->sms_message = strtoupper($this->group_id) . $this->sms_message;

        //$to = "say:". $this->group_id
        return $this->thingreport['things'];
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
        //exit();
        //https://stackoverflow.com/questions/45016327/extract-four-character-matches-from-strings
        if (!isset($this->groups)) {
            $this->groups = array();
        }

        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0-9+#-]+)/ for capturing ?
        $pattern = "|[A-Za-z]{4}|";
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->groups = $arr;
        return $this->groups;
    }




    // -----------------------

    private function respond()
    {


        // Thing actions
        $this->thing->flagGreen();



        // Generate email response.

        $to = $this->thing->from;

        $from = "group";

        $this->thing_report['choices'] = $this->choices;

        $sms_end = strtoupper(strip_tags($this->choices['link']));

        $x = implode("", explode("FORGET", $sms_end, 2));

        $this->sms_message =  strtoupper($this->agent_name) . " | " . $this->sms_message . " | TEXT" . $x;

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

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }

    private function nextWord($phrase)
    {
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = array('new', 'join', 'leave', 'listen');

        $input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if ($this->agent_input == 'extract') {
            $this->extractGroups();
            return $this->response;
        }


        if ($this->agent_input == 'find') {
            $this->thing->log('Agent "Group" received "find".');
            $this->findGroup();
            return $this->response;
        }

        if ($this->agent_input == 'listen') {
            //echo "listen";
            $this->listenGroup();
            //$this->sms_message .= "quez";
            return $this->response;
        }

        if ($this->agent_input == 'join') {
            echo "join";
            $this->joinGroup();
            return $this->response;
        }

        // Or if $this->agent_input is found.

        //if ($this->agent_input != null) {

        //echo $this->subject;
        //exit();
        // agent passes a group - valid or not



        if (strpos(strtolower($this->subject), strtolower($this->group_id)) !== false) {
            $this->listenGroup($this->group_id);
            $this->num_hits += 1;

            return $this->response;
        }
            

        // Or we see if the group name matches one of the users.

        $this->findGroup(); // Might need to call this in the set-up.
        foreach ($this->thingreport['groups'] as $group) {

            if (strpos(strtolower($this->subject), strtolower($group)) !== false) {
                if ($this->group_id == $group) {
                    $this->listenGroup($group);
                    $this->num_hits += 1;
                    return $this->response;
                }

                if ($this->group_id != $group) {
                    $this->joinGroup($group);
                    $this->num_hits += 1;
                    return $this->response;
                }
            }
        }

        //		echo $group;
        //		$this->listenGroup($group);
        //$this->num_hits += 1;
//
        //				return $this->response;
        //			}
        //}

        //echo "meep";
        //exit();


        if (strpos(strtolower($this->agent_input), "listen:") !== false) {
            //$this->sms_message .= "meep b";
            echo $this->agent_input;

            $group = str_replace("listen:", "", $this->agent_input);
            //echo $group;
            $this->listenGroup($group);
            return $this->response;
        }
        // added 18 Jul
        if (strpos(strtolower($this->agent_input), "join:") !== false) {
            echo $this->agent_input;

            $group = str_replace("join:", "", $this->agent_input);
            //echo $group;
            $this->joinGroup($group);
            return $this->response;
        }


        if (count($pieces) == 1) {

                //        $input = $this->subject;

//                        if ( ctype_alnum($input) and strlen($input) == 4 ) {
            //				$this->response = $this->joinGroup($input);
            //				$this->thing->log( '<pre> Agent "Group" calling joinGroup() </pre>' );

            //                              // 4 digit alphanumeric received
            //                            $this->num_hits += 1;
            //				return $this->response;
            //                      }


            if ($input == 'new') {
                $this->response = $this->startGroup();
                $this->num_hits += 1;
                return $this->response;
            }


            if ($input == 'group') {
                if ($this->group_id != null) {
                    //exit();
                    // Group is already set.
                    // Report the group.

                    $this->sms_message = strtoupper($this->group_id);
                    $this->message = strtoupper($this->group_id);
                } else {
                    $this->response = $this->findGroup();
                    //echo $this->group_id;
                    //$this->listenGroup();
                }

                $this->num_hits += 1;
                return $this->response;
            }

            if ($input == 'join') {
                if ($this->group_id != null) {
                    // Group is already set.
                    // Report the group.


                    $this->joinGroup($this->group_id);

                    //$this->sms_message = $this->group_id;
                    //$this->message = $this->group_id;
                } else {
                    $this->response = $this->findGroup();
                    $this->joinGroup($this->thingreport['groups'][0]);
                }

                $this->num_hits += 1;
                return $this->response;
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
            return $this->response;

            return "Request '" . $input . " 'not understood: ";
        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'join':

                            if ($key + 1 > count($pieces)) {
                                //echo "last word is stop";
                                $this->group = false;
                                return "Request not understood";
                            } else {
                                $this->group_id = $pieces[$key+1];
                                $this->response = $this->joinGroup($this->group_id);
                                $this->num_hits += 1;
                                return $this->response;
                            }
                            break;

                                                case 'listen':

                                                        if ($key + 1 > count($pieces)) {
                                                            //echo "last word is listen";
                                                            $this->group = false;
                                                            return "Request not understood";
                                                        } else {
                                                            $this->group = $pieces[$key+1];
                                                            $this->response = $this->listenGroup($this->group);
                                                            $this->num_hits += 1;
                                                            return $this->response;
                                                        }
                                                        break;



                                                case 'find':
                            echo "find";
                                                        if ($key + 1 > count($pieces)) {
                                                            //echo "last word is find";
                                                            $this->group = false;
                                                            return "Request not understood";
                                                        } else {
                                                            $this->group = $pieces[$key+1];
                                                            $this->response = $this->findGroup($this->group);
                                                            $this->num_hits += 1;
                                                            return $this->response;
                                                        }
                                                        break;


                        case 'new':
                            $this->response = $this->startGroup();
$this->num_hits += 1;

                            return $this->response;
                                                case 'start':
                                                        $this->response = $this->startGroup();
$this->num_hits += 1;
                                   return $this->response;
                                                        //echo 'bus';
                                                        //break;

                                                case 'group':
                                                        echo "group";
                            // exit() This doesn't trigger.  Group must be picked up before this.
                                                        if ($key + 1 > count($pieces)) {
                                                            //echo "last word is group";
                                                            $this->group = false;
                                                            return "Request not understood";
                                                        } else {
                                                            $this->group = $pieces[$key+1];
                                                            $this->response = $this->joinGroup($this->group);
                                                            $this->num_hits += 1;
                                                            return $this->response;
                                                        }
                                                        break;


                        default:

                            //echo 'default';

                    }
                }
            }
        }


        if (ctype_alnum($input) and strlen($input) == 4) {
            $this->response = $this->joinGroup($input);
            $this->thing->log('<pre> Agent "Group" calling joinGroup() </pre>');

            // 4 digit alphanumeric received
            $this->num_hits += 1;
            return $this->response;
        }



        return "Message not understood";
    }



    public function PNG()
    {
        // Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

        //I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and tes$
        //No the problem was not a whitespace, but the UTF BOM encoding character at the begining of one of my inluded files...
        //So beware of your included files!
        //Make sure they are not encoded in UTF or otherwise in UTF without BOM.
        //Hope it save someone's time.

        //http://php.net/manual/en/function.imagepng.php

        //header('Content-Type: text/html');
        //echo "Hello World";
        //exit();

        //header('Content-Type: image/png');
        //QRcode::png('PHP QR Code :)');
        //exit();
        // here DB request or some processing

        //		if ($this->group_id == null) {
        //			$this->findGroup();
        //		}

        $codeText = "group:".$this->group_id;

        ob_clean();
        ob_start();

        QRcode::png($codeText, false, QR_ECLEVEL_Q, 4);
        $image = ob_get_contents();

        ob_clean();
        // Can't get this text editor working yet 10 June 2017

        //$textcolor = imagecolorallocate($image, 0, 0, 255);
        // Write the string at the top left
        //imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

        $this->thing_report['png'] = $image;
        //echo $this->thing_report['png']; // for testing.  Want function to be silent.

        return $this->thing_report['png'];
    }
}




?>



