<?php
/**
 * Crow.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Crow extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init() {
        $this->test= "Development code";

        $this->primary_place = "roost";

        // Signals a Burnaby roost crows have been heard to emit.
        $this->signals = array("awk", "awk awk awk", "kaa", "clicking", "pitch drop awk", "unusually high pitched caw", "imitation call" );

        $this->created_at = $this->thing->thing->created_at;

        $this->default_state = "inside nest";

        $this->response .= "crow can hear ".  implode(" /", $this->signals) .".\n" ;

        // States the crow can be in.
        // Starting off point is basic ant states.
        $this->node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging", "foraging")), "midden work"=>"foraging");

        $this->thing_report['info'] = 'This is a crow.' ;
        $this->thing_report['help'] = 'This agent pretends very hard to be a crow.' ;

    }


    /**
     *
     */
    private function getState() {
        $this->state = $this->crow_thing->choice->load($this->primary_place);
        if ($this->state == false) {$this->state = $this->default_state;}
        $this->crow_thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        $this->crow_thing->choice->Choose($this->state);
        $choices = $this->crow_thing->choice->makeLinks($this->state);
    }


    /**
     *
     */
    private function setState() {
        $this->crow_thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        $this->crow_thing->choice->Choose($this->state);
        $choices = $this->crow_thing->choice->makeLinks($this->state);
    }

    private function getCrow($requested_nuuid = null) {


        $entity_input = "get crow";
        if ($requested_nuuid != null) {$entity_input = "get crow ".$requested_nuuid;} else {$entity_input = "get crow";}

if (!isset($this->crow_thing)) {$this->crow_thing = $this->thing;}

        $entity = new Entity($this->crow_thing, $entity_input );

        $this->crow_thing = $entity->thing;

        $this->state = $this->crow_thing->choice->load($this->primary_place);
        $this->uuid = $this->crow_thing->uuid;
        $this->nuuid = $this->crow_thing->nuuid;

        $this->getState();
        $this->getCave();

        $this->choices = $this->crow_thing->choice->makeLinks($this->state);

    }


    /**
     *
     */
    private function getCaves() {
        if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'wumpus/wumpus.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",", $line);
            $this->cave_names[$items[0]] = $items[1];

            // do something with $line
            $line = strtok( $separator );
        }

    }


    /**
     *
     * @param unknown $cave_number (optional)
     */
    private function getCave($cave_number = null) {

        $this->getCaves();

        $cave_number = "X";


        if ($cave_number == null) {$cave_number = $this->x;}


        $cave_name = "A dark room";
        if (isset($this->cave_names[strval($cave_number)])) {$cave_name = $this->cave_names[strval($cave_number)];}
        $this->cave_name = $cave_name;
    }


    /**
     *
     * @param unknown $place_name (optional)
     */
    private function getPlace($place_name = null) {
//        $place_agent = new Place($this->thing, "place");
        $this->place_agent = new Place($this->crow_thing, "place");
    }


    /**
     *
     */
    public function run() {
    }


    /**
     *
     */
    public function set() {

        $this->crow_tag= $this->crow_thing->nuuid;
        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}

        $crow = new Variables($this->thing, "variables crow " . $this->from);

        $crow->setVariable("tag", $this->crow_tag);

        $crow->setVariable("refreshed_at", $this->refreshed_at);



        // This is an idea that you can describe your state.
        // "I/we call this place < some symbol signal >"
        // "Awk."

        $this->crow_thing->json->writeVariable( array("crow", "place_name"), $this->place_name );
        $this->crow_thing->json->writeVariable( array("crow", "signal"), $this->signal );

        //        $this->crow_thing->choice->Choose($this->state);
        //        $this->state = $this->crow_thing->choice->load($this->primary_place);
        $this->setState();

    }


    /**
     *
     * @param unknown $crow_code (optional)
     * @return unknown
     */
    public function get($crow_code = null) {
        $crow = new Variables($this->thing, "variables crow " . $this->from);

        $this->crow_tag = $crow->getVariable("tag");
        $this->refreshed_at = $crow->getVariable("refreshed_at");

        if ($crow_code == null) {$crow_code = $this->crow_tag;}

        // Load up the appropriate crow_thing
        $this->getCrow($crow_code);


        $this->current_time = $this->crow_thing->json->time();
        $this->crow_thing->json->setField("variables");
        $this->time_string = $this->crow_thing->json->readVariable( array("crow", "refreshed_at") );

        if ($this->time_string == false) {
            $this->crow_thing->json->setField("variables");
            $this->time_string = $this->crow_thing->json->time();
            $this->crow_thing->json->writeVariable( array("crow", "refreshed_at"), $this->time_string );
        }

        $this->refreshed_at = strtotime($this->time_string);

        $this->place_name = strtolower($this->crow_thing->json->readVariable( array("crow", "place_name") ));
        $this->signal = $this->crow_thing->json->readVariable( array("crow", "signal") );


        if ( ($this->place_name == false) or ($this->place_name = "")) {
            $this->place_name = "X";
        }

        if ( ($this->signal == false) or ($this->signal = "")) {$this->signal = "X";}

        return array($this->place_name, $this->signal);
    }



    /**
     *
     */
    public function respond() {
        $this->thing->flagGreen();

        // Generate SMS response

        //  $this->message['sms'] = $litany[$this->state];
        $this->makeMessage();
        $this->makeSMS();
        // . " " . if (isset($this->response)) {$this->response;};

        $this->whatisthis = array('inside nest'=>'A dark place where crows hatch.',
            'nest maintenance'=>'The nest needs to be maintained. Otherwise it falls apart.',
            'patrolling'=>"Sit on top of something and watch for weird stuff. Define weird.",
            'foraging'=>"A crows got to eat.",
            'midden work'=>'Stuff the needs to be done.'
        );

        // Generate email response.

        $to = $this->thing->from;
        $from = "crow";

        $this->makeChoices();
        $this->makeWeb();
        $this->makeTXT();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

    }


    /**
     *
     * @return unknown
     */
    private function getCrows() {
        if (isset($this->crows)) {return;}

        //if ($requested_nuuid == null) {$requested_nuuid = $this->entity->id;}


        // Get up to 10000 crows.
        $crow = new Findagent($this->thing, "crow");
        $crow->horizon = 10000;
        $crow->findAgent("crow");
        $crow_things = $crow->thing_report['things'];

        $matching_uuids = array();

        foreach ($crow_things as $key=>$crow) {
            $crow_nuuid = substr($crow['uuid'], 0, 4);

            //  if (strtolower($crow_nuuid) == strtolower($requested_nuuid)) {
            // Consistently match the nuuid to a specific uuid.
            $this->crows[] = $crow;

            //            $this->crows[] = new Thing($crow['uuid']);
            //  }
        }

        if (!isset($this->crows[0])) {return true;}

        // Let's get some crow information.
        $crows_seen = "crows seen ";
        foreach ( $this->crows as $index=>$crow_thing) {
            $crow_nuuid = substr($crow_thing['uuid'], 0, 4);

            $variables = json_decode($crow_thing['variables']);

            // thing // headcode // place // quantity // number // button // message // clocktime


            $thing_status = null;
            if (isset($variables->thing->status)) {$thing_status = $variables->thing->status;}

            $number_number = null;
            if (isset($variables->number->number)) {$thing_status = $variables->number->number;}

            $message_agent = null;
            if (isset($variables->message->agent)) {$message_agent = $variables->message->agent;}

            $message_outcome = null;
            if (isset($variables->message->outcome)) {$message_outcome = $variables->message->outcome;}

            $place = null;
            if (isset($variables->place)) {$this->place = $variables->place;}


            $crows_seen .= $crow_nuuid. " ";

        }

        $crows_seen = trim($crows_seen);

        $crows_seen .= ".";
    }


    /**
     *
     */
    public function makeWeb() {
        $test_message = "<b>CROW " . strtoupper($this->crow_thing->nuuid) . "</b>" . '<br>';
        $test_message .= "<p>";
        $test_message .= '<p><b>Crow State</b>';

        $test_message .= '<br>Last thing heard: "' . $this->subject . '"<br>' . 'The next Crow choices are [ ' . $this->choices['link'] . '].';

        $state_text = "NOT SET";
        if (isset($this->state)) {$state_text = strtoupper($this->state);}
        $test_message .= '<br>Roost state: ' . $state_text;

        $test_message .= '<br>Place is ' . $this->place_name;
        $test_message .= '<br>Signal is ' . $this->signal;

        $test_message .= '<br>' .$this->crow_behaviour[$this->state] . '<br>';


        $test_message .= "<p>";
        $test_message .= '<p><b>Thing Information</b>';
        $test_message .= '<br>subject: ' . $this->subject . '<br>';
        $test_message .= 'created_at: ' . $this->created_at . '<br>';
        $test_message .= 'from: ' . $this->from . '<br>';
        $test_message .= 'to: ' . $this->to . '<br>';
        $test_message .= '<br>' .$this->thing_behaviour[$this->state] . '<br>';


        $test_message .= "<p>";
        $test_message .= '<p><b>Narratives</b>';
        $test_message .= '<br>' .$this->litany[$this->state] . '<br>';
        $test_message .= '<br>' .$this->crow_narrative[$this->state] . '<br>';

        // $test_message .= '<p>Agent "Crow" is responding to your web view of datagram subject "' . $this->subject . '", ';
        // $test_message .= "which was received " . $this->thing->human_time($this->thing->elapsed_runtime()) . " ago.";

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
        $test_message .= "<br>Thing happened about ". $ago . " ago.";

        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report['web'] = $test_message;


    }


    /**
     *
     */
    public function makeTXT() {
        $txt = "";
        $this->getCrows();
        foreach ($this->crows as $key=>$crow) {

            if (isset($crow->thing->uuid)) {
                $txt .= substr($crow->thing->uuid, 0, 4). " ";
            }
        }

        $txt .= "\n";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     */
    public function makeChoices() {
        $choices = $this->thing->choice->makeLinks($this->state);
        $this->choices = $choices;
        $this->thing_report['choices'] = $choices ;
    }


    /**
     *
     */
    public function makeMessage() {
        if (isset($this->response)) {$m = $this->response;} else {$m = "No response.";};
        $this->message = $m;
        $this->thing_report['message'] = $m;
    }


    /**
     *
     */
    public function makeSMS() {
        // Generate SMS response

        $narratives = array("predator"=>"Crow is Watching for predators.",
            "human"=>"Crow is analyzing humans.",
            "quest"=>"Crow is questing for the oracle.",
            "funnies"=>"Crow has found a Peanut's comic strip.");
        $key = array_rand($narratives);
        $patrolling_narrative = $narratives[$key];

        $behaviours = array("predator"=>"Crow is Watching for predators.",
            "human"=>"Crow is analyzing humans.",
            "quest"=>"Crow is questing for the oracle.",
            "funnies"=>"Crow has found a Peanut's comic strip.");
        $patrolling_behaviour = $behaviours[$key];

        $litanies = array("predator"=>"Crow is Watching for predators.",
            "human"=>"Crow is analyzing humans.",
            "quest"=>"Crow is questing for the oracle.",
            "funnies"=>"Crow has found a Peanut's comic strip.");
        $patrolling_litany = $litanies[$key];

        $this->litany = array('inside nest'=>'One of your records was displayed, perhaps by yourself.  A Crow arrived and is waiting in the nest.',
            'nest maintenance'=>'A record of yours was displayed again, perhaps by yourself.  This Crow is doing some nest maintenance.',
            'patrolling'=>$patrolling_litany,
            'foraging'=>"This crow is on it's last legs.  It has gone foraging for stack information about you to forget.",
            'midden work'=>'One of your records was displayed, perhaps by yourself.  An Crow arrived and is doing midden work.',
            'start'=>"Start.  Not normally means that you displayed a record, let's see if we get any more Crow messages."
        );

        // Not used. Consider removing.
        $this->thing_behaviour = array('inside nest'=>'A Thing was instantiated.',
            'nest maintenance'=>'A Thing was called.',
            'patrolling'=>"A Thing was called twice.",
            'foraging'=>"A Thing is searching.",
            'midden work'=>'A Thing is doing work.',
            'start'=>"Start. A Thing started."
        );

        // Behaviour
        $this->crow_behaviour = array('inside nest'=>'Crow is in the nest.',
            'nest maintenance'=>'Crow is doing some nest maintenance.',
            'patrolling'=>$patrolling_behaviour,
            'foraging'=>"This is foraging.",
            'midden work'=>'An Crow is doing midden work.',
            'start'=>"Crow egg."
        );

        // Narrative
        $this->crow_narrative = array('inside nest'=>'Everything is dark.',
            'nest maintenance'=>'You are a Nest Maintainer. What does that even mean?',
            'patrolling'=>$patrolling_narrative,
            'foraging'=>"Now you are a Forager. What are you foraging for?",
            'midden work'=>'You are a Midden Worker. Have fun.',
            'start'=>"Crow egg."
        );

        $this->prompt_litany = array('inside nest'=>'TEXT WEB / NEST MAINTENANCE',
            'nest maintenance'=>'TEXT WEB / PATROLLING / FORAGING',
            'patrolling'=>"TEXT WEB / FORGET",
            'foraging'=>"TEXT WEB / FORGET",
            'midden work'=>'TEXT WEB / FORGET',
            'start'=>"TEXT WEB / MIDDEN WORK / NEST MAINTENANCE"
        );


        $sms = "CROW " . strtoupper($this->crow_thing->nuuid);
        //        $sms .= " | " . $this->thing_behaviour[$this->state];
        $state_text = "NOT SET";
        if (isset($this->state)) {$state_text = strtoupper($this->state);}
        $sms .= " | " . $state_text;
        $sms .= " " . $this->response;
        $place_text = "X";
        if (isset($this->place_name)) {$place_text = strtoupper($this->place_name);}
        $sms .= " | place " . $place_text;

        $signal_text ="X";
        if (isset($this->signal)) {$signal_text = strtoupper($this->signal);}
        $sms .= " signal ". $this->signal;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function extractCrow($text = null) {

        if ($text == null) {$text = $this->subject;}

        $nuuid_agent = new Nuuid($this->thing, "nuuid");
        $nuuid_agent->extractNuuid($this->subject);

        $nuuid = null;
        if (isset($nuuid_agent->nuuid)) {$nuuid = $nuuid_agent->nuuid;}

        $this->crow_id = $nuuid;
    }


    /**
     *
     * @param unknown $nuuid (optional)
     */
    function nextCrow($nuuid = null) {

        $this->getCrows();
        $crow =  $this->crows[array_rand($this->crows)];
        $uuid = $crow['uuid'];

        $crow_nuuid = substr($crow['uuid'], 0, 4);

        $this->getCrow($crow_nuuid);

    }


    /**
     *
     */
    function doState() {
        if ((!isset($this->state)) or ($this->state == null)) {
            //$this->response = "detected state null - run subject discriminator";
            $this->thing->log($this->agent_prefix . 'state is null.');
        }

        $this->state = $this->crow_thing->choice->load('roost');

        if ($this->state == false) {
            //            $this->state = "inside nest";
            $this->getState();
        }

        // Will need to develop this to only only valid state changes.
        switch ($this->state) {
        case "foraging":
            //$this->thing->choice->Choose("foraging");
            $this->response .= "Foraging. ";
            break;
        case "inside nest":
            //$this->thing->choice->Choose("in nest");
            $this->response .= "Crow is Inside Nest. ";
            break;
        case "nest maintenance":
            $this->response .= "Crow is doing Nest Maintenance. ";
            //$this->thing->choice->Choose("nest maintenance");
            break;
        case "patrolling":
            $responses = array("Crow is Watching for predators. ",
                "Crow is analyzing humans. ",
                "Crow is questing for the oracle. ",
                "Crow has found a Peanut's comic strip. ");
            $this->response .= array_rand($responses);
            break;
        case "midden work":
            $this->response .= "Crow is doing Midden Work. ";
            $this->middenwork();

            // Need to figure out how to set flag to red given that respond will then reflag it$
            // Can green reflag red?  Think about reset conditions.

            break;

        case false:

        default:
            $this->thing->log('invalid state provided "' . $this->state. ".");
            // Over-ride
            $this->response = "Crow is broken. ";
        }

        //            $this->setState();


    }


    /**
     *
     * @param unknown $text
     */
    function doCrow($text) {

        // Well first off.
        // If there is no state. Give the crow one.
        if (!isset($this->state)) {$this->state = $this->default_state;}
        $this->getState();
        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index=>$ngram) {
            switch ($ngram) {
            case "tag":
            case "tag crow":
                $this->response .= "Tagged Crow " . $this->crow_thing->nuuid . ". ";
                break;

            case "next":
            case "next crow":
            case "crow next":
                $this->nextCrow();
                $this->response .= "Got the next Crow.";
                break;

            case "new":
            case "crow spawn":
            case "spawn crow":
            case "spawn":
                $this->spawnCrow();
                break;

            case "is":
            case "load":
            case "crow load":
            case "run":
            case "run crow":
                $this->assertCrow($filtered_text);
                break;
            case "call":
                $this->callCrow();
                break;
            case "end":
            case "kill":
                $this->kill();
                $this->response .= "Killed this Crow. ";
                break;
            case "forage":
            case "foraging":
                $this->foraging();
                break;
            case "inside nest":
                $this->crow_thing->choice->Choose("inside nest");
                $this->response .= "This Crow is Inside the Roost. ";
                break;
            case "nest maintenance":
                $this->nestmaintenance();
                break;
            case "watch":
            case "patrol":
            case "patrolling":
                $this->patrolling();
                break;
            case "midden":
            case "midden work":
                $this->middenwork();

                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                // Can green reflag red?  Think about reset conditions.

                break;
            case 'left':
                $this->response .= "Crow stepped left. ";
                break;

            case 'right':
                $this->response .= "Crow stepped right. ";
                break;

            case 'forward':
                $this->response .= "Crow moved forward. ";
                break;

            default:
                //$this->getCrow();

                //$this->response .= "No state change. ";
                //$this->spawn();
            }
        }
        //$this->setState();
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function assertCrow($input) {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "crow is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("crow is"));
        } elseif (($pos = strpos(strtolower($input), "crow")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("crow"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");


        $crow = $this->getCrow($filtered_input);

        if ($crow) {
            // Crow doesn't exist
            return true;
        }

        $response = "Asserted crow is " . $this->crow_thing->nuuid . ". ";
        $this->response .= $response;


    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->response .= 'Crow heard, "' . $this->input .'". ';

        $this->extractCrow($this->input);

        $nuuid = $this->crow_id;

        // A nuuid was seen.
        // So try to get the crow that corresponds.
        if ($nuuid != null) {
            $this->response .= "Got crow " . $nuuid . ". ";
            $this->getCrow($nuuid);
        }

        $this->doCrow($this->input);

        return false;
    }


    /**
     *
     */
    function nestmaintenance() {
        $this->crow_thing->choice->Choose("nest maintenance");
        $this->response .= "This Crow is doing Nest Maintenance. ";
        $this->state = $this->crow_thing->choice->load($this->primary_place);


    }


    /**
     *
     */
    function patrolling() {

        $this->crow_thing->choice->Choose("patrolling");
        $this->response .= "This Crow is Patrolling. ";
        $this->state = $this->crow_thing->choice->load($this->primary_place);

    }


    /**
     *
     */
    function foraging() {

        $this->crow_thing->choice->Choose("foraging");
        $this->response .= "This Crow is Foraging. ";
        $this->state = $this->crow_thing->choice->load($this->primary_place);

    }


    /**
     *
     */
    function middenwork() {

        $this->crow_thing->choice->Choose("midden work");
        $this->response .= "This Crow is doing Midden Work. ";
        $this->state = $this->crow_thing->choice->load($this->primary_place);


        $middenwork = "on";
        if ($middenwork != "on") {$this->response .= "No work done. ";return;}

        // So here we define what a midden work does when it is called by the agenthandler.
        // Midden Work is the building and maintenance work of the stack.
        // Midden Work is about putting Things back in their place.

        // First Thing that is out of place are the button clicks which are posterior uuid linked.

        // So explore the user's associations and replace any null@stackr. owners with ?

        // Options are the crow's identifier, the stack identifier, the user identifier, or
        // to determine the latest decision and the strongest decision path.

        // Strongest decision path is the one with the most engagement - ie button pressed multiple times.
        // Devstack: So think tokenlimiting on button pushes.

        // Latest decision is the last time the outcome was decided.

        // Midden Worker should build a uuid tree.

        // So first thing.  Get a list of all user Things.

        // Well as an Crow Midden Worker we don't know a huge amount.
        // Taking a s/ forget crow
        // We have(?) two accounts associated.  Which should be true until the Foraging state.

        // So first question is why is this crow being called?
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
        $haystack ="";
        $t = $this->thing->thing;
        $haystack .= json_encode($t);

        // Get stack related UUIDs (and add to the haystack)
        $t = $this->thing->db->UUids();
        $haystack .= json_encode($t);

        // Computers are very good at looking for needles in haystacks
        // So also add the words of any other crow.
        $t = $this->thing->db->agentSearch('crow');
        $haystack .= json_encode($t);

        // Add in words from this Crow's Uuid.
        // What is being said about Crow?
        $t = $this->thing->db->userSearch($this->uuid);
        $haystack .= json_encode($t);


        $thingreport = $this->thing->db->priorGet();
        $posterior_thing = $thingreport['thing'];

        $haystack .= json_encode($posterior_thing);

        // And we can do this...

        //echo "the thing is:";
        //print_r($this->thing);

        // But that really depends on the security of the Channel.

        // devstack use Uuid to extract Uuids.
        // $match = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/";

        // This is a loose screen on any alphanumeric sequence with UUID like hyphenation.

        // Some other screens
        //preg_match_all('/(\S{4,})/i', $haystack, $matches); // more than four letters long
        //preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $haystack, $matches);
        //preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/', $haystack, $matches);

        // But use this one.
        preg_match_all('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/', $haystack, $matches);

        // All Uuids visible to this Crow loaded.
        $arr = array_values(array_unique($matches[0]));


        // Now go through each Uuid
        // Make a list of the Uuids which mention this Crow's Uuid.
        $linked = array();

        foreach ($arr as $key=>$value) {
            $temp_thing = new Thing($value);

            if ($temp_thing == false) {break;}

            // print_r($temp_thing->thing);
            // print_r($temp_thing->thing->uuid);
            $haystack = json_encode($temp_thing->thing);

            if ( (strpos($haystack, $this->uuid) !== false) and ($value != $this->uuid) ) {
                // print_r($temp_thing->thing);
                $linked[] = $value;
            }
        }

        // And then don't do anything with the list.
        $this->response .= "Nothing happened. ";
    }


    /**
     *
     */
    function spawnCrow() {

        //$this->response .= "Spawning crow " . $this->thing->nuuid .". ";

        // Need to log it as an entity for it to spawn
$this->crow_thing = $this->thing;
        $entity = new Entity($this->crow_thing, "spawn crow " . $this->thing->nuuid);
//        $entity = new Entity($this->thing, "spawn crow " . $this->thing->nuuid);

        // Then all that Entity has to do is set an entity variable

        // And then here the thing get's loaded in.
        $this->crow_thing = $entity->thing;
        //        $this->thing = $entity->thing;

        $crow_pheromone['stack'] = 4;

        $this->crow_thing->choice->Create('roost', $this->node_list, "inside nest");

        $place = new Place($this->crow_thing, "place is roost");
        $this->place_name = $place->place_name;

        $response = "Spawned Crow " . $this->crow_thing->nuuid . " at " . $this->place_name . ". ";
        //                $response .= "Text LOAD CROW " . strtoupper($this->crow_thing->nuuid). ". ";
        $this->response .= $response;


        $this->thing->flagGreen();

        return;
    }


    /**
     *
     */
    function callCrow() {


        // Need to log it as an entity for it to spawn
        $entity = new Entity($this->thing, "crow");

        foreach ($entity->entities as $index=>$entity) {

            //$this->response .= $entity["entity"] . "_" . $entity["nuuid"] ." ";

            if ( strpos( strtolower($entity["entity"]) , 'crow') !== false) {
                $this->response .= $entity["nuuid"] ." ";
            }

        }

    }


    /**
     *
     * @return unknown
     */
    function kill() {
        // No messing about.
        return $this->thing->Forget();
    }


    /**
     *
     */
    function story() {

        // The 90s script
        $n = 'Information is stored as Things. Things are how ' . $this->short_name . '.';
        $n .= 'Stuff comes into a Thing, a Thing has several Agents that help it deal with Things.';
        $n .= 'Agents work for ' . $this->short_name . '.  Most of them providing ai interfaces';
        $n .= 'to services.  Basic SMS commands you can perform are "51380" or any other Translink';
        $n .= 'bus sign number.  And BALANCE, GROUP, JOIN, SAY and LISTEN.  I figure those';
        $n .= 'are handy if you are in Metro Vancouver.';

        $n .= 'And you can email those words to stack' . $this->mail_postfix . ', but my ask';
        $n .= 'right now is that you text "51380" to '. $this->sms_address .'.';
        $n .= "That is how I track new sign-ups, and kind of how people judge things.";

        $n .= $this->short_name . ' has no desire to collect your information.';
        $n .= "";
        $n .= 'The target stack setting is to FORGET Things within 4 hours.  You can';
        $n .= 'check how much information you have deposited with ' .$this->short_name .' with the ';
        $n .= 'BALANCE by texting (778) 401-2132 and/or by emailing BALANCE to stack' . $this->mail_postfix . '.';

        $n .= 'If it is near 0 units then we do not have much Things associated with ' . $this->from .'.';
        $n .= 'Balances over 100,000 do.  It costs ' . $this->short_name . ' computationally to calculate';
        $n .= 'the balance.  We charge for data retention.  If you seem to need this limited service ';
        $n .= 'will be offered it.';

        $n .= 'Which gets you where exactly? A place where this "Crow" is going to be useful.';
        $n .= 'In a place where your Things are eroding.  Like castles on the beach.';

        $n .= 'And where to explore ' . $this->short_name . ' you should click on [ Nest Maintenance ].' ;

        $ninety_seconds = $n;

        $what = 'And Things they are meant to be shared transparently, but not indiscriminately.';
        $what .= '';

        $why = $this->short_name . ' is intended as a vehicle to leverage Venture Capital investment in individual impact.';


    }


}
