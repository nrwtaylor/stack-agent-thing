<?php
/**
 * Signal.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Signal {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function __construct(Thing $thing, $agent_input = null) {
        $this->start_time = $thing->elapsed_runtime();

        //if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;
        $this->agent_name = "signal";
        $this->keyword = "signal";
        $this->agent_prefix = 'Agent "' . ucwords($this->keyword) . '" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . ".", "INFORMATION");

        // $this->start_time = $this->thing->elapsed_runtime();

        $this->test= "Development code"; // Always

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;
        $this->thing->log($this->agent_prefix . 'received this Thing, "' . $this->subject .  '".', "DEBUG") ;


        // Get the current identities uuid.
        $default_signal_id = new Identity($this->thing, "identity");
        $this->default_signal_id = $default_signal_id->uuid;

        // Set up default signal settings
        $this->verbosity = 1;
        $this->requested_state = null;
        $this->default_state = "green";
        $this->node_list = array("green"=>array("red"=>array("green")));

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/signal';

        $this->refreshed_at = null;

        $this->current_time = $this->thing->time();

        // Get the current Identities signal
        $this->signal = new Variables($this->thing, "variables signal " . $this->from);

        //        $this->associations = new Associations($this->thing, "associations " . $this->from);
        $this->associations = new Associations($this->thing, $this->subject);

        //var_dump($this->associations->associations_list);
        //exit();
        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4);

        $this->thing->log($this->agent_prefix . ' got signal variables. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;


        // At this point the signal object
        // has the current signal variables loaded.
        $this->readSubject();
        $this->thing->log($this->agent_prefix . ' completed read. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;

        if ($this->agent_input == null) {$this->Respond();}
        $this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;
        if (isset($this->response)) {$this->thing_report['response'] = $this->response;}


        return;

    }


    /**
     *
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null) {

        if ($requested_state == null) {
            if (!isset($this->requested_state)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_state = "green"; // If not sure, show green.
            }
            $requested_state = $this->requested_state;
        }

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        $this->signal->setVariable("state", $this->state);

        if (!isset($this->signal_id)) {$this->signal_id = $this->uuid;}

        $this->associations->setAssociation($this->signal_id);


        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4);
        //$this->variables_thing->setVariable("signal_id", $this->nuuid);

        $this->signal->setVariable("refreshed_at", $this->current_time);

        //$this->makeChoices();
        //$this->makePNG();

        $this->thing->log($this->agent_prefix . 'set Signal to ' . $this->state, "INFORMATION");


        return;
    }


    /**
     *
     * @param unknown $str
     * @return unknown
     */
    function is_positive_integer($str) {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }


    /**
     *
     * @param unknown $signal (optional)
     * @return unknown
     */
    function isSignal($signal = null) {
        // Validates whether the Signal is green or red.
        // Nothing else is allowed.

        if ($signal == null) {
            if (!isset($this->state)) {$this->state = "red";}

            $signal = $this->state;
        }

        if (($signal == "red") or
            ($signal == "green") or
            ($signal == "yellow") or
            ($signal == "double yellow")

        ) {return false;}

        return true;
    }


    /**
     *
     */
    function get() {
        // get gets the state of the Signal the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->signal->getVariable("state");
        $this->signal_id = $this->signal->getVariable("signal_id");
        $this->refreshed_at = $this->signal->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");


        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isSignal($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        //        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);
        //        $check = $this->thing->choice->current_node;

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' SIGNAL.' , "INFORMATION");

        return;

    }


    /**
     *
     * @param unknown $selector (optional)
     * @return unknown
     */
    function getSignal($selector = null) {
        //var_dump($this->signals);
        foreach ($this->signals as $signal) {
            // Match the first matching place
            if (($selector == null) or ($selector == "")) {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->signal_id = $this->last_signal_id;
                $this->signal = new Variables($this->thing, "variables " . $this->signal_id . " " . $this->from);
                return $this->signal_id;
            }

            if (($signal['is'] == $selector)) {
                $this->refreshed_at = $place['refreshed_at'];
                $this->signal_id = $signal['is'];
                $this->signal = new Variables($this->thing, "variables " . $this->signal_id . " " . $this->from);
                return $this->signal_id;
            }
        }

        return true;
    }


    /**
     *
     * @return unknown
     */
    function getSignals() {
        $this->signalid_list = array();
        $this->signals = array();

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, 'signal');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Signal" found ' . count($findagent_thing->thing_report['things']) ." signal Things." );

        //        if ($findagent_thing->thing_reports['things'] == false) {
        //                $place_code = $this->default_place_code;
        //                $place_name = $this->default_place_name;
        //            return array($this->placecode_list, $this->placename_list, $this->places);
        //        }

        if ( ($findagent_thing->thing_report['things'] == true)) {}

        //var_dump(count($findagent_thing->thing_report['things']));
        //var_dump($findagent_thing->thing_report['things'] == true);


        if (!$this->is_positive_integer($count)) {
            // No places found
        } else {




            foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object) {
                $uuid = $thing_object['uuid'];

                $associations_json= $thing_object['associations'];
                $associations = $this->thing->json->jsontoArray($associations_json);
                var_dump($associations);
                if (isset($variables['signal'])) {

                    $signal_id = $this->default_signal_id;
                    $refreshed_at = "meep getSignal";

                    if (isset($variables['signal']['signal_id'])) {$signal_id = $variables['signal']['signal_id'];}
                    if (isset($variables['signal']['refreshed_at'])) {$refreshed_at = $variables['signal']['refreshed_at'];}


                    $this->signals[] = array("id"=>$signal_id, "refreshed_at"=>$refreshed_at);
                    $this->signalid_list[] = $signal_id;
                    //                  }
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_places = array();
        foreach (array_reverse($this->signals) as $key=>$signal) {

            $signal_id = $signal['id'];

            if (!isset($signal['refreshed_at'])) {continue;}
            $refreshed_at = $signal['refreshed_at'];

            if (isset($filtered_signals[$signal_id]['refreshed_at'])) {
                if (strtotime($refreshed_at) > strtotime($filtered_signals[$signal_id]['refreshed_at'])) {
                    $filtered_places[$signal_name] = array("id"=>$signal_id, 'refreshed_at' => $refreshed_at);
                }
                continue;
            }

            $filtered_places[$signal_id] = array("id"=>$signal_id, 'refreshed_at' => $refreshed_at);


        }

        $refreshed_at = array();
        foreach ($this->signals as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->signals);

        /*
// Get latest per place
$this->places = array();
foreach($filtered_places as $key=>$filtered_place) {
//var_dump($filtered_place);

        $this->places[] = $filtered_place;
}
*/
        $this->old_signals = $this->signals;
        $this->signals = array();
        foreach ($this->old_signals as $key =>$row) {

            //var_dump( strtotime($row['refreshed_at']) );
            if ( strtotime($row['refreshed_at']) != false) {
                $this->signals[] = $row;
            }
        }



        // Add in a set of default places
        $file = $this->resource_path .'signal/signals.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");


        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of place names.
                // Common ones.
                $signal_id= $line;
                // This is where the place index will be called.
                //                $signal_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);

                $this->signalid_list[] = $signal_id;


                $this->signals[] = array("id"=>$signal_id);

            }

            fclose($handle);
        } else {
            // error opening the file.
        }

        // Indexing not implemented
        $this->max_index = 0;

        return array($this->signalid_list, $this->signals);

    }


    /**
     *
     * @return unknown
     */
    function read() {
        //$this->thing->log("read");

        $this->get();
        return $this->state;
    }



    /**
     *
     * @param unknown $choice (optional)
     * @return unknown
     */
    function selectChoice($choice = null) {

        if ($choice == null) {
            if (!isset($this->state)) {
                $this->state = $this->default_state;
            }
            $choice = $this->state;
        }

        if (!isset($this->state)) {
            $this->state = "X";
        }
        $this->previous_state = $this->state;
        $this->state = $choice;

        //$this->thing->choice->Choose($this->state);
        //$this->thing->choice->save($this->keyword, $this->state);


        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $this->state . '".', "INFORMATION");

        return $this->state;
    }


    /**
     *
     */
    function makeChoices() {

        //        $this->thing->choice->Choose($this->state);
        //        $this->thing->choice->save($this->keyword, $this->state);

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);

        $choices = $this->signal->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }


    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/signal.png">';
        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->sms_message;

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    private function Respond() {

        // At this point state is set
        $this->set($this->state);

        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = $this->keyword;

        if ($this->state == "inside nest") {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $this->makeSMS();
        $this->makeMessage();

        $this->thing_report['email'] = $this->message;

        $this->makePNG();
        //        $this->makeChoices(); // Turn off because it is too slow.

        $this->makeTXT();

        $this->makeChoices();
        $this->makeWeb();



        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        //$this->thing_report['help'] = 'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. RED means danger.';
        $this->makeHelp();

        return;
    }


    /**
     *
     */
    function makeHelp() {
        if ($this->state == "green") {
            $this->thing_report['help'] = 'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. GREEN means available.';
        }

        if ($this->state == "red") {
            $this->thing_report['help'] = 'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. RED means stop.';
        }

        if ($this->state == "red") {
            $this->thing_report['help'] = 'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. YELLOW means plan to stop.';
        }

        if ($this->state == "red") {
            $this->thing_report['help'] = 'This Signal is either RED, GREEN, YELLOW or DOUBLE YELLOW. DOUBLE YELLOW means keep going.';
        }


    }


    /**
     *
     */
    function makeTXT() {
        $txt = 'This is SIGNAL POLE ' . $this->signal->nuuid . '. ';
        $txt .= 'There is a '. strtoupper($this->state) . " SIGNAL. ";
        if ($this->verbosity >= 5) {
            $txt .= 'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     */
    function makeSMS() {

        $sms_message = "SIGNAL IS " . strtoupper($this->state);

        if ($this->verbosity > 6) {
            $sms_message .= " | previous state " . strtoupper($this->previous_state);
            $sms_message .= " state " . strtoupper($this->state);
            $sms_message .= " requested state " . strtoupper($this->requested_state);
            $sms_message .= " current node " . strtoupper($this->base_thing->choice->current_node);
        }
        //        if ($this->verbosity > 2) {
        //            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        //        }

        if ($this->verbosity > 0) {
            $sms_message .= " | signal id " . strtoupper($this->signal_id);
        }


        if ($this->verbosity >= 9) {
            $sms_message .= " | base nuuid " . strtoupper($this->signal->thing->nuuid);
        }

        //if ($this->verbosity > 0) {
        //    $sms_message .= " | nuuid " . $this->signal->nuuid;
        //}

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }


            if ($this->state == "green") {
                $sms_message .= ' | MESSAGE Red';
            }
        }
        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;

    }


    /**
     *
     */
    function makeMessage() {

        $message = 'This is a SIGNAL POLE.  The signal is a ' . trim(strtoupper($this->state)) . " SIGNAL. ";

        if ($this->state == 'red') {
            $message .= 'It is a BAD time at the moment. ';
        }

        if ($this->state == 'green') {
            $message .= 'It is a GOOD time now. ';
        }

        //$test_message .= 'And the signal is ' . strtoupper($this->state) . ".";

        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;


    }


    /**
     *
     */
    public function makeImage() {
        //var_dump ($this->state);
        //exit();
        // here DB request or some processing
        //        $codeText = "thing:".$this->state;

        // Create a 55x30 image

        $this->image = imagecreatetruecolor(60, 125);
        //$red = imagecolorallocate($this->image, 255, 0, 0);
        //$green = imagecolorallocate($this->image, 0, 255, 0);
        //$grey = imagecolorallocate($this->image, 100, 100, 100);

        //$this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);
        //$this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->red = imagecolorallocate($this->image, 231, 0, 0);

        $this->yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->green = imagecolorallocate($this->image, 0, 129, 31);

        $this->color_palette = array($this->red,
            $this->yellow,
            $this->green);

        // Draw a white rectangle
        if ((!isset($this->state)) or ($this->state == false)) {
            $color = $this->grey;
        } else {
            if (isset($this->{$this->state})) {
                $color = $this->{$this->state};
            } elseif (isset($this->{'signal_' . $this->state})) {
                $color = $this->{'signal_' . $this->state};
            }
        }

        // Bevel top of signal image

        $points = array(0, 0, 6, 0, 0, 6);
        imagefilledpolygon($this->image, $points, 3, $this->white);

        $points = array(60, 0, 60-6, 0, 60, 6);
        imagefilledpolygon($this->image, $points, 3, $this->white);


        $green_x = 30;
        $green_y = 50;

        $red_x = 30;
        $red_y = 100;

        $yellow_x = 30;
        $yellow_y = 75;

        $double_yellow_x = 30;
        $double_yellow_y = 25;

        if ($this->state == "green") {
            imagefilledellipse($this->image, $green_x, $green_y, 20, 20, $this->green);
        }

        if ($this->state == "red") {
            imagefilledellipse($this->image, $red_x, $red_y, 20, 20, $this->red);
        }

        if ($this->state == "yellow") {
            imagefilledellipse($this->image, $yellow_x, $yellow_y, 20, 20, $this->yellow);
        }

        if ($this->state == "double yellow") {
            imagefilledellipse($this->image, $yellow_x, $yellow_y, 20, 20, $this->yellow);

            imagefilledellipse($this->image, $double_yellow_x, $double_yellow_y, 20, 20, $this->yellow);

        }

        return;

        //imagefilledrectangle($image, 0, 0, 200, 125, ${$this->state});
        if ($this->state == "rainbow") {
            //    $color = $this->grey;
            foreach (range(0, 5) as $n) {
                $a = $n * (200/6);
                $b = $n *(200/6) + (200/6);
                $color = $this->color_palette[$n];

                //                imagefilledrectangle($this->image, $a, 0, $b, 125, $color);
                $a = $n * (125/6);
                $b = $n *(125/6) + (200/6);

                imagefilledrectangle($this->image, 0, $a, 200, $b, $color);

            }
        } else {
            if (!isset($color)) {$color = $this->grey;}
            imagefilledrectangle($this->image, 0, 0, 200, 125, $color);
        }

        $light_text_list = array("red");
        if (in_array($this->state, $light_text_list)) {
            $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        } else {
            $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        }

        // Write the string at the top left
        imagestring($this->image, 2, 150, 100, $this->signal->nuuid, $textcolor);
    }


    /**
     *
     */
    public function makePNG() {
        if (!isset($this->image)) {$this->makeImage();}
        $agent = new Png($this->thing, "png");

        //$this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->response = null;

        $keywords = array('signal', 'red', 'green', 'yellow');

        if (isset($this->agent_input)) {
            $input = $this->agent_input;
        } else {
            $input = strtolower($this->subject);
        }
        //$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;
        //$haystack = $this->agent_input . " " . $this->from;
        //$haystack = $input . " " . $this->from;
        $haystack = "";

        //  $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // This gives us the other Things which are associated with the signal
        var_dump($this->associations->agent_associations);

        //        var_dump($this->associations->association_name);

        foreach ($this->associations->thing_objects as $key=>$thing_object) {


            $uuid = $thing_object['uuid'];
            $agent = $thing_object['nom_to'];
            echo "agent: " . $agent;
            echo "uuid: ". $thing_object['uuid'];

            $variables_json= $thing_object['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables['agent'])) {

                //$place_code = $this->default_place_code;
                //$place_name = $this->default_place_name;
                //$refreshed_at = "meep getPlaces";

                if (isset($variables['thing']['status'])) {$thing_status = $variables['thing']['status'];}
                if (isset($variables['agent']['refreshed_at'])) {$refreshed_at = $variables['agent']['refreshed_at'];}
            }


            if (isset($variables['agent'])) {

                //$place_code = $this->default_place_code;
                //$place_name = $this->default_place_name;
                //$refreshed_at = "meep getPlaces";

                if (isset($variables['agent']['state'])) {$agent_state = $variables['agent']['state'];}
                if (isset($variables['agent']['refreshed_at'])) {$refreshed_at = $variables['agent']['refreshed_at'];}

            }

            if (isset($variables['flag'])) {

                //$place_code = $this->default_place_code;
                //$place_name = $this->default_place_name;
                //$refreshed_at = "meep getPlaces";

                if (isset($variables['flag']['state'])) {$flag_state = $variables['flag']['state'];}
                if (isset($variables['flag']['refreshed_at'])) {$refreshed_at = $variables['flag']['refreshed_at'];}
            }

            if (isset($variables['signal'])) {

                //$place_code = $this->default_place_code;
                //$place_name = $this->default_place_name;
                //$refreshed_at = "meep getPlaces";

                if (isset($variables['signal']['id'])) {$signal_id = $variables['signal']['id'];}
                if (isset($variables['signal']['refreshed_at'])) {$refreshed_at = $variables['place']['refreshed_at'];}
            }

            if (isset($variables['block'])) {

                //$place_code = $this->default_place_code;
                //$place_name = $this->default_place_name;
                //$refreshed_at = "meep getPlaces";

                if (isset($variables['block']['id'])) {$block_id = $variables['block']['id'];}
                if (isset($variables['block']['refreshed_at'])) {$refreshed_at = $variables['block']['refreshed_at'];}
            }

            if (!isset($thing_status)) {$thing_status = "X";}
            if (!isset($agent_state)) {$agent_state = "X";}
            if (!isset($flag_state)) {$flag_state = "X";}
            if (!isset($signal_id)) {$signal_id = "X";}
            if (!isset($block_id)) {$block_id = "X";}

            echo $uuid . " " . $thing_status . " " .$agent_state . " " . $flag_state . " " . $signal_id . " " . $block_id. "\n";




        }



        //$nuuid = new Nuuid($this->thing);
        //$nuuid->extractNuuid($input);

        //if (isset($nuuid->nuuid)) {
        //    echo "Found: ".$nuuid->nuuid;
        //}

        //$uuid = new Uuid($this->thing);
        //$uuid->extractUuid($input);

        if (isset($uuid->uuid)) {
            echo "Found: ".$uuid->uuid;
            $this->signal_thing = new Thing($uuid->uuid);
            //$this->signal = $this->signal_thing->variables;
            echo "Loaded thing";

            $variables = $this->signal_thing->account['stack']->json->array_data;
            //var_dump($variables);
            echo var_dump($variables["signal"]);

            //                $this->thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->variables_thing->$variable_name );
            $this->signal_thing->json->writeVariable( array("signal", "state"), "yellow" );

            $variables = $this->signal_thing->account['stack']->json->array_data;

            echo var_dump($variables["signal"]);




        }

        // Is there a headcode in the provided datagram

        //$part_uuid = $uuid;

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {

            if ($input == $this->keyword) {
                $this->get();
                $this->response = "Got the signal.";
                $this->getSignals();
                $this->getSignal();


                return;
            }

        }

        if (count($pieces) == 3) {

            if ($input == "signal double yellow") {

                $this->selectChoice('double yellow');
                $this->response = "Selected a double yellow signal.";

                return;
            }

        }

        // Lets think about Signals.
        // A signal is connected to another signal.  Directly.

        // So look up the signal in associations.

        // signal - returns the uuid and the state of the current signal


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {

                    case 'red':
                        $this->thing->log($this->agent_prefix . 'received request for RED SIGNAL.', "INFORMATION");
                        $this->selectChoice('red');
                        $this->response = "Selected a red signal.";
                        return;

                    case 'green':
                        $this->selectChoice('green');
                        $this->response = "Selected a green signal.";
                        return;

                    case 'yellow':

                        $this->selectChoice('yellow');
                        $this->response = "Selected a yellow signal.";
                        return;

                    case 'list':
                        $this->getSignals();
                        $this->response = "Got a list of signals.";
                        var_dump($this->signals);
                        return;

                    case 'back':

                    case 'next':

                    default:

                    }
                }
            }
        }
        // If all else fails try the discriminator.
        //        if (!isset($haystack)) {$this->response = "Did nothing."; return;}
        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_state) {
        case 'green':
            $this->selectChoice('green');
            $this->response = "Asserted a Green Signal.";
            return;
        case 'red':
            $this->selectChoice('red');
            $this->response = "Asserted a Red Signal.";
            return;
        }

        $this->read();
        $this->response = "Looked at the Signal.";

        // devstack
        return "Message not understood";
        return false;
    }


    /*
	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}
*/


    /**
     *
     * @param unknown $input
     * @param unknown $discriminators (optional)
     * @return unknown
     */
    function discriminateInput($input, $discriminators = null) {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = array('red', 'green');
        }



        $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
        }

        $aliases = array();

        $aliases['red'] = array('r', 'red', 'on');
        $aliases['green'] = array('g', 'grn', 'gren', 'green', 'gem', 'off');
        //$aliases['reset'] = array('rst','reset','rest');
        //$aliases['lap'] = array('lap','laps','lp');

        $words = explode(" ", $input);

        $count = array();

        $total_count = 0;
        // Set counts to 1.  Bayes thing...
        foreach ($discriminators as $discriminator) {
            $count[$discriminator] = 1;
            $total_count = $total_count + 1;
        }
        // ...and the total count.

        foreach ($words as $word) {
            foreach ($discriminators as $discriminator) {
                if ($word == $discriminator) {
                    $count[$discriminator] = $count[$discriminator] + 1;
                    $total_count = $total_count + 1;
                    //echo "sum";
                }
                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                        //echo "sum";
                    }
                }
            }
        }

        $this->thing->log('Agent "Signal" matched ' . $total_count . ' discriminators.', "DEBUG");
        // Set total sum of all values to 1.

        $normalized = array();
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key=>$value) {
            //echo $key, $value;

            if ( isset($max) ) {$delta = $max-$value; break;}
            if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
        }


        //echo '<pre> Agent "Usermanager" normalized discrimators "';print_r($normalized);echo'"</pre>';


        if ($delta >= $minimum_discrimination) {
            //echo "discriminator" . $discriminator;
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }


}


?>
