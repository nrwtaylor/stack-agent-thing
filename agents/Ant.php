<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ant extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code";

        // Example API
        //        $this->api_key = $this->thing->container['api']['translink'];

        // Load in some characterizations.
        //        $this->short_name = $this->thing->container['stack']['short_name'];
        //        $this->web_prefix = $this->thing->container['stack']['web_prefix'];
        //        $this->mail_prefix = $this->thing->container['stack']['mail_prefix'];
        //        $this->mail_postfix = $this->thing->container['stack']['mail_postfix'];

        $this->sms_seperator =
            $this->thing->container['stack']['sms_separator']; // |
        $this->sms_address = $this->thing->container['stack']['sms_address'];

        // Get some stuff from the stack which will be helpful.
        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];

        // Load in time quantums
        $this->cron_period = $this->thing->container['stack']['cron_period']; // 60s
        $this->thing_resolution =
            $this->thing->container['stack']['thing_resolution']; // 1ms

        // Load in a pointer to the stack record.
        $this->stack_uuid = $this->thing->container['stack']['uuid'];

        // For the Ant
        $this->created_at = $this->thing->created_at;

        //		$this->sqlresponse = null;

        $this->node_list = [
            "inside nest" => [
                "dead",
                "nest maintenance" => [
                    "dead",
                    "patrolling" => ["dead", "foraging"],
                    "foraging",
                ],
            ],
            "midden work" => ["foraging", "dead"],
        ];

        $info =
            'The "Ant" agent provides an button driven interface to manage access to your information on ' .
            $this->short_name;
        $info .=
            'from the web.  The Management suggests you explore the NEST MAINTENANCE button';

        // The 90s script
        $n =
            'Information iss stored as Things. Things are how ' .
            $this->short_name .
            '.';
        $n .=
            'Stuff comes into a Thing, a Thing has several Agents that help it deal with Things.';
        $n .=
            'Agents work for ' .
            $this->short_name .
            '.  Most of them providing ai interfaces';
        $n .=
            'to services.  Basic SMS commands you can perform are "51380" or any other Translink';
        $n .=
            'bus sign number.  And BALANCE, GROUP, JOIN, SAY and LISTEN.  I figure those';
        $n .= 'are handy if you are in Metro Vancouver.';

        $n .=
            'And you can email those words to stack' .
            $this->mail_postfix .
            ', but my ask';
        $n .=
            'right now is that you text "51380" to ' . $this->sms_address . '.';
        $n .=
            "That is how I track new sign-ups, and kind of how people judge things.";

        $n .= $this->short_name . ' has no desire to collect your information.';
        $n .= "";
        $n .=
            'The target stack setting is to FORGET Things within 4 hours.  You can';
        $n .=
            'check how much information you have deposited with ' .
            $this->short_name .
            ' with the ';
        $n .=
            'BALANCE by texting (778) 401-2132 and/or by emailing BALANCE to stack' .
            $this->mail_postfix .
            '.';

        $n .=
            'If it is near 0 units then we do not have much Things associated with ' .
            $this->from .
            '.';
        $n .=
            'Balances over 100,000 do.  It costs ' .
            $this->short_name .
            ' computationally to calculate';
        $n .=
            'the balance.  We charge for data retention.  If you seem to need this limited service ';
        $n .= 'will be offered it.';

        $n .=
            'Which gets you where exactly? A place where this "Ant" is going to be useful.';
        $n .=
            'In a place where your Things are eroding.  Like castles on the beach.';

        $n .=
            'And where to explore ' .
            $this->short_name .
            ' you should click on [ Nest Maintenance ].';

        $ninety_seconds = $n;

        $what =
            'And Things they are meant to be shared transparently, but not indiscriminately.';
        $what .= '';

        $why =
            $this->short_name .
            ' is intended as a vehicle to leverage Venture Capital investment in individual impact.';

        $this->thing->log(
            '<pre> Agent "Ant" running on Thing ' . $this->uuid . ' </pre>'
        );
        $this->thing->log(
            '<pre> Agent "Ant" received this Thing "' .
                $this->subject .
                '"</pre>'
        );

        // Read the subject as passed to this class.
        // No charge to read the subject line.  By machine.

        $this->default_state = 'inside nest';
        $this->stateAnt();

        // Err ... making sure the state is saved.
        $this->thing->choice->Choose($this->state);
        if ($this->stateAnt() === true) {
            $this->response .= "Could not save state. ";
        }

        $this->thing->log(
            $this->agent_prefix . 'state is "' . $this->state . '".'
        );
        $this->thing->log($this->agent_prefix . 'completed.');

        $this->thing->flagRed();
    }

    function run()
    {
        $pheromone_agent = new Pheromone($this->thing, "pheromone");
        $this->pheromone_value = $pheromone_agent->value;

        $this->left_count += rand(0, 1);
        $this->right_count += rand(0, 1);

        $steps = $this->left_count + $this->right_count;
        $maximum_steps = rand(1, 99);
        //$maximum_steps = 10;
        if ($steps > $maximum_steps) {
            // Forget? Or flag green.
            //$this->thing->Forget();
            $this->thing->flagGreen();
        }
    }

    public function stateAnt()
    {
        $state = $this->thing->choice->load('hive');

        if ($state != false) {
            $this->state = $state;
        }

        if ($state === false) {
            $this->state = $this->default_state;
            return true;
        }
    }

    function set()
    {
        $this->thing->Write(["ant", "left_count"], $this->left_count);
        $this->thing->Write(["ant", "right_count"], $this->right_count);

        $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE");
    }

    public function get($ant_code = null)
    {
        $this->current_time = $this->thing->time();

        // Borrow this from iching
        $this->time_string = $this->thing->Read(["ant", "refreshed_at"]);

        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($ant_code == null) {
            $ant_code = $this->uuid;
        }

        if ($this->time_string == false) {
            $this->time_string = $this->thing->time();
            $this->thing->Write(["ant", "refreshed_at"], $this->time_string);
        }

        $this->refreshed_at = strtotime($this->time_string);

        $this->left_count = $this->thing->Read(["ant", "left_count"]);
        $this->right_count = $this->thing->Read(["ant", "right_count"]);
    }

    public function respondResponse()
    {
        // Thing actions

        $this->whatisthis = [
            'inside nest' =>
                'Each time the ' .
                $this->short_name .
                ' service is accessed, Stackr creates a uniquely identifable Thing.
				This one is ' .
                $this->uuid .
                '.
				This message from the "Ant" ai which was been tasked with mediating web access to this Thing. 
				Manage Things on ' .
                $this->short_name .
                ' using the [ NEST MAINTENANCE ] command.  
				If Ant\'s are bothing you, you can either use the [ FORGET ] command
				to stop receiving notifications for the Thing, or you can turn [ ANT OFF ].
				"Ant" is how ' .
                $this->short_name .
                ' manages interactions with your Things by other identities.
				[ANT OFF] will stop any "Ant" agent responding.  You can say [ NEST MAINTENANCE ] later if you change your mind.',
            'nest maintenance' =>
                'A Things of yours was displayed again, perhaps by yourself.  This Ant is doing some nest maintenance.',
            'patrolling' =>
                "A Thing associated with " .
                "this identity" .
                " was displayed (or requested by) a device.  That's twice now.  This Ant is patrolling.",
            'foraging' =>
                "This ant is on it's last legs.  It has gone foraging for stack information about you to forget.",
            'midden work' =>
                'One of your records was displayed, perhaps by yourself.  An Ant spawned and is doing midden work.',
            'start' =>
                "Start. Not normally means that you displayed a record, let's see if we get any more Ant messages.",
        ];

        // Generate email response.

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] =
            'This is the "Ant" Agent. It organizes your Things.';

        $this->thing->flagRed();
    }

    public function makeWeb()
    {
        $this->makeChoices();

        $test_message =
            "<b>ANT " . strtoupper($this->thing->nuuid) . "</b>" . '<br>';
        $test_message .= "<p>";
        $test_message .= '<p><b>Ant State</b>';

        $test_message .=
            '<br>Last thing heard: "' .
            $this->subject .
            '"<br>' .
            'The next Ant choices are [ ' .
            $this->choices['link'] .
            '].';
        $test_message .= '<br>Hive state: ' . $this->state;
        $test_message .= '<br>left_count is ' . $this->left_count;
        $test_message .= '<br>right count is ' . $this->right_count;

        $test_message .= '<br>' . $this->ant_behaviour[$this->state] . '<br>';

        $test_message .= "<p>";
        $test_message .= '<p><b>Thing Information</b>';
        $test_message .= '<br>subject: ' . $this->subject . '<br>';
        $test_message .= 'created_at: ' . $this->created_at . '<br>';
        $test_message .= 'from: ' . $this->from . '<br>';
        $test_message .= 'to: ' . $this->to . '<br>';
        $test_message .= '<br>' . $this->thing_behaviour[$this->state] . '<br>';

        $test_message .= "<p>";
        $test_message .= '<p><b>Narratives</b>';
        $test_message .= '<br>' . $this->litany[$this->state] . '<br>';
        $test_message .= '<br>' . $this->ant_narrative[$this->state] . '<br>';

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
        $ago = $this->thing->human_time(
            strtotime($this->thing->time()) - strtotime($refreshed_at)
        );
        $test_message .= "<br>Thing happened about " . $ago . " ago.";

        $this->thing_report['web'] = $test_message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks($this->state);
        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    public function makeMessage()
    {
        if (isset($this->response)) {
            $m = $this->response;
        } else {
            $m = "No response.";
        }
        $this->message = $m;
        $this->thing_report['message'] = $m;
    }

    public function makeSMS()
    {
        // Generate SMS response
        $this->litany = [
            'inside nest' =>
                'One of your records was displayed, perhaps by yourself.  An Ant spawned and is waiting in the nest.',
            'nest maintenance' =>
                'A record of yours was displayed again, perhaps by yourself.  This Ant is doing some nest maintenance.',
            'patrolling' =>
                "A record of yours was displayed.  That's twice now.  This Ant is patrolling.",
            'foraging' =>
                "This ant is on it's last legs.  It has gone foraging for stack information about you to forget.",
            'midden work' =>
                'One of your records was displayed, perhaps by yourself.  An Ant spawned and is doing midden work.',
            'start' =>
                "Start.  Not normally means that you displayed a record, let's see if we get any more Ant messages.",
            'dead' => "This ant is dead.",
        ];

        $this->thing_behaviour = [
            'inside nest' => 'A Thing was instantiated.',
            'nest maintenance' => 'A Thing was instantiated again.',
            'patrolling' => "A Thing was instantiated twice.",
            'foraging' => "A Thing is searching the stack.",
            'midden work' => 'A Thing is doing stack work.',
            'start' => "Start. A Thing started.",
            'dead' => "Flag green.",
        ];

        // Behaviour
        $this->ant_behaviour = [
            'inside nest' => 'Ant spawned and is waiting in the nest.',
            'nest maintenance' => 'Ant is doing some nest maintenance.',
            'patrolling' => "That's twice now. This Ant is patrolling.",
            'foraging' =>
                "This ant is on it's last legs.  It has gone foraging for stack information about you to forget.",
            'midden work' => 'An Ant spawned and is doing midden work.',
            'start' => "Ant egg.",
            'dead' => "Dead ant.",
        ];

        // Narrative
        $this->ant_narrative = [
            'inside nest' => 'Everything is dark.',
            'nest maintenance' =>
                'You are a Nest Maintainer. What does that even mean?',
            'patrolling' => "Now you are a Patroller.",
            'foraging' => "Now you are a Forager. What are you foraging for?",
            'midden work' => 'You are a Midden Worker. Have fun.',
            'start' => "Ant egg.",
            'dead' => "Dead ant.",
        ];

        $this->prompt_litany = [
            'inside nest' => 'TEXT WEB / NEST MAINTENANCE',
            'nest maintenance' => 'TEXT WEB / PATROLLING / FORAGING',
            'patrolling' => "TEXT WEB / FORGET",
            'foraging' => "TEXT WEB / FORGET",
            'midden work' => 'TEXT WEB / FORGET',
            'start' => "TEXT WEB / MIDDEN WORK / NEST MAINTENANCE",
            'dead' => "TEXT ANT",
        ];

        $sms = "ANT | " . $this->thing->nuuid;
        $sms .= " | " . $this->thing_behaviour[$this->state];
        $sms .= " | " . $this->ant_behaviour[$this->state];
        $sms .= " " . trim($this->response);
        $sms .= " | " . trim($this->prompt_litany[$this->state]);

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        if ($this->state == null) {
            $this->thing->log(
                $this->agent_prefix .
                    'state is null.  Subject discriminator run.'
            );

            switch ($this->subject) {
                case "spawn":
                    $this->thing->log("spawn Thing");
                    $this->spawn();
                    $this->thing->log("spawned Thing");

                    $place = new Place($this->thing, "hive");
                    $this->response .= "Spawned an Ant at Hive. ";
                    break;
                case "kill":
                    $this->kill();
                    $this->response .= "Killed this Ant. ";
                    break;
                case "foraging":
                    $this->thing->choice->Choose("foraging");
                    $this->response .= "This Ant is Foraging. ";
                    $this->foraging();
                    break;
                case "inside nest":
                    $this->thing->choice->Choose("inside nest");
                    $this->response .= "This Ant is Inside the Hive. ";
                    break;
                case "nest maintenance":
                    $this->thing->choice->Choose("nest maintenance");
                    $this->response .= "This Ant is doing Nest Maintenance. ";
                    break;
                case "patrolling":
                    $this->thing->choice->Choose("patrolling");
                    $this->response .= "This Ant is Patrolling. ";
                    break;
                case "midden work":
                    $this->thing->choice->Choose("midden work");
                    $this->response .= "This Ant is doing Midden Work. ";
                    $this->middenwork();

                    // Need to figure out how to set flag to red given that respond will then reflag it as green.
                    // Can green reflag red?  Think about reset conditions.

                    break;
                default:
                    $this->response .= "Ant spawned. ";
                    $this->thing->log("spawn Thing default");
                    $this->spawn();
                    $this->thing->log("spawned Thing");
            }
        }

        //$this->state = $this->thing->choice->load('hive');

        $this->stateAnt();

        // Will need to develop this to only only valid state changes.

        switch ($this->state) {
            case "spawn":
                $this->response .= "Spawned Ant. ";
                break;
            case "kill":
                $this->response .= "Dead Ant. ";
                break;
            case "foraging":
                $this->response .= "Foraging. ";
                break;
            case "inside nest":
                $this->response .= "Ant is Inside Nest. ";
                break;
            case "nest maintenance":
                $this->response .= "Ant is doing Nest Maintenance. ";
                break;
            case "patrolling":
                $this->response .= "Ant is Patrolling. ";
                break;
            case "midden work":
                $this->response .= "Ant is doing Midden Work. ";
                $this->middenwork();

                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                // Can green reflag red?  Think about reset conditions.

                break;

            default:
                $this->thing->log(
                    $this->agent_prefix .
                        'invalid state provided "' .
                        $this->state .
                        '".'
                );
                $this->response .= "Ant is broken.";

            // this case really shouldn't happen.
            // but it does when a web button lands us here.
        }

        $input = strtolower($this->subject);
        // Accept ant commands
        $this->keywords = ["forward", "left", "right"];

        $pieces = explode(" ", strtolower($input));
        /*
        if (count($pieces) == 1) {
            if ($input == 'ant') {
                $this->getPlace();
                $this->response = "Last 'place' retrieved.";
                return;
            }

        }
*/
        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'left':
                            $this->left_count += 1;
                            $this->response .= "Ant moved left.";
                            break;

                        case 'right':
                            $this->right_count += 1;
                            $this->response .= "Ant moved right.";
                            break;

                        case 'forward':
                            $this->left_count += 1;
                            $this->right_count += 1;
                            $this->response .= "Ant moved forward.";
                            break;
                    }
                }
            }
        }

        // Update Ant's state tree
        $this->thing->choice->Create('hive', $this->node_list, $this->state);

        return false;
    }

    function middenwork()
    {
        // Midden Work is the building and maintenance work of the stack.
        // Midden Work is about putting Things back in their place.

        // So here we define what a foraging does when it is called by the agenthandler.

        // First Thing that is out of place are the button clicks which are posterior uuid linked.

        // So explore the user's associations and replace any null@stackr. owners with ?

        // Options are the ant's identifier, the stack identifier, the user identifier, or
        // to determine the latest decision and the strongest decision path.

        // Strongest decision path is the one with the most engagement - ie button pressed multiple times.
        // Devstack: So think tokenlimiting on button pushes.

        // Latest decision is the last time the outcome was decided.

        // Midden Worker should build a uuid tree.

        // So first thing.  Get a list of all user Things.

        // Well as an Ant Midden Worker we don't know a huge amount.
        // Taking a s/ forget ant
        // We have(?) two accounts associated.  Which should be true until the Foraging state.

        // So first question is why is this ant being called?
        // Errr. Because the state is midden work and the flag is red.

        // Ok stuck, because the midden worker doesn't know enough and is null@<mail_postfix>

        // Then what?
        // Then we figure it out.

        // Then use an agent state?
        // getState($agent = null)
        // ->db->agentSearch($agent, limit)
        // ->db->userSearch($keyword)
        // ->UUids($uuid = null)

        // Form a haystack from the whole thing.
    }

    function foraging()
    {
        $middenwork = "on";
        if ($middenwork != "on") {
            $this->response = "No work done.";
            return;
        }

        // Form a haystack from the whole thing.
        $haystack = "";
        $t = $this->thing->thing;
        $haystack .= json_encode($t);

        // Get stack related UUIDs (and add to the haystack)
        $t = $this->thing->db->UUids();
        $haystack .= json_encode($t);

        // Computers are very good at looking for needles in haystacks
        // So also add the words of any other ant.
        $t = $this->thing->db->agentSearch('ant');
        $haystack .= json_encode($t);

        // Add in words from this Ant's Uuid.
        // What is being said about Ant?
        $t = $this->thing->db->userSearch($this->uuid);
        $haystack .= json_encode($t);

        $thingreport = $this->thing->db->priorGet();
        $posterior_thing = $thingreport['thing'];

        $haystack .= json_encode($posterior_thing);

        // devstack use Uuid to extract Uuids.
        // $match = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/";

        // This is a loose screen on any alphanumeric sequence with UUID like hyphenation.

        // Some other screens
        //preg_match_all('/(\S{4,})/i', $haystack, $matches); // more than four letters long
        //preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $haystack, $matches);
        //preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/', $haystack, $matches);

        // But use this one.
        preg_match_all('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/', $haystack, $matches);

        // All Uuids visible to this Ant loaded.
        $arr = array_values(array_unique($matches[0]));

        // Now go through each Uuid
        // Make a list of the Uuids which mention this Ant's Uuid.
        $linked = [];

        foreach ($arr as $key => $value) {
            $temp_thing = new Thing($value);

            if ($temp_thing == false) {
                break;
            }

            // print_r($temp_thing->thing);
            // print_r($temp_thing->thing->uuid);
            $haystack = json_encode($temp_thing->thing);

            // Add pheromone to thing.
            $pheromone_agent = new Pheromone($temp_thing, "pheromone");

            if (
                strpos($haystack, $this->uuid) !== false and
                $value != $this->uuid
            ) {
                // print_r($temp_thing->thing);
                $linked[] = $value;
            }
        }

        // And then don't do anything with the list.
        $this->response .=
            "Visited " .
            count($linked) .
            " Uuids and added Pheromone to each. ";
    }

    function spawn()
    {
        $ant_pheromone['stack'] = 4;
        if (rand(0, 5) + 1 <= $ant_pheromone['stack']) {
            $this->thing->choice->Create(
                'hive',
                $this->node_list,
                "inside nest"
            );
        } else {
            $this->thing->choice->Create(
                'hive',
                $this->node_list,
                "midden work"
            );
        }

        $this->thing->flagRed();
    }
}
