<?php
/**
 * Entity.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

// devstack need to think around designing for a maximum 4000 charactor json thing
// constraints are good.  Remember arduinos.  So perhaps all agents don't get saved.
// Only the necessary ones.

// dev more work needed

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Entity extends Agent
{
    public $var = 'hello';


    /**
     *
     */
    function init() {
        $this->keywords = array('next', 'accept', 'clear', 'drop', 'add', 'new', 'spawn', 'get');

        $this->default_id = "def0";
        if (isset($this->thing->container['api']['entity']['id'])) {
            $this->default_id = $this->thing->container['api']['entity']['id'];
        }

        $this->default_alias = "Thing";

        // Default to
        $this->requested_agent_name = "wumpus";


        $this->entity_agents = array("Entity", "Cat", "Dog", "Ant", "Crow", "Wumpus", "Basket");

        $matches = array();
        foreach ($this->entity_agents as $key=>$entity_agent) {
            if (strpos(strtolower($this->agent_input), strtolower($entity_agent)) !== false) {
                $matches[] = $entity_agent;
            }
        }
        $this->entity_agent = strtolower($this->entity_agents[0]);

        if (isset($matches[0])) {$this->entity_agent = $matches[0];}

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/' . $this->entity_agent;

        $this->state = "X";
    }


    /**
     *
     */
    function set() {
        $this->refreshed_at = $this->current_time;

        $this->entity_id->setVariable("id", $this->id);
        $this->entity_id->setVariable("refreshed_at", $this->current_time);

        $this->thing->Write( array("entity", "id"), $this->id );
        $this->thing->Write( array("entity", "refreshed_at"), $this->current_time );

        $this->refreshed_at = $this->current_time;
    }


    /**
     *
     * @return unknown
     */
    function nextEntitycode() {
        // #devstack

        $this->thing->log("next entitycode");
        // Pull up the current headcode
        $this->get();

        // One minute into the next entity
        $quantity = 1;
        $next_time = $this->thing->time(strtotime($this->end_at . " " . $quantity . " minutes"));

        $this->get($next_time);

        return $this->available;
    }


    /**
     *
     * @param unknown $requested_id (optional)
     */
    private function getEntity($requested_id = null) {

        $nuuid = new Nuuid($this->thing, "nuuid");
        $nuuid->extractNuuid($requested_id);

        if (!isset($nuuid->nuuid)) {
            $requested_nuuid = $this->thing->nuuid;

        }

        if ( (isset($nuuid->nuuid)) and ($nuuid->nuuid != null) ) {$requested_nuuid = $nuuid->nuuid;}

        //$requested_agent_name = "wumpus";
        if (isset(explode("_", $requested_id)[1])) {
            $requested_agent_name = explode("_", $requested_id)[1];
        }

        $input = "entity";
        if (isset($this->agent_input)) {$input = $this->agent_input;}
        if (isset($this->input)) {$input = $this->input;}
        if ( (isset($requested_agent_name)) and (isset($requested_nuuid)) ) {
            $input = $requested_agent_name . " " . $requested_nuuid;
        }
        $this->extractEntity($input);

        if (!isset($this->entity_agent_name)) {$this->entity_agent_name = "entity";}
        $requested_agent_name = $this->entity_agent_name;



        $this->thing->log("entity requested is " . $requested_id . ".");


        if (!isset($this->entities)) {$this->getEntities(); }
        //        if (!isset($this->id)) {$this->id = $this->default_id;}

        $matching_things = array();

        //if ($this->entities == null) {
        // Make the current thing an entity.
        // Give it an id.
        //    $this->id = $this->default_id;
        //    return;
        //}
        $match_list = array();

        if (isset($this->entities[0])) {



            foreach ($this->entities as $key=>$entity) {


                if ( (strtolower($entity['nuuid']) == strtolower($requested_nuuid)) ) {

                    // Consistently match the nuuid to a specific uuid.
                    $match_list[] = $entity;
                }
            }

            $flag_nuuid = false;

            foreach ($this->entities as $key=>$entity) {
                $entity_nuuid = substr($entity['uuid'], 0, 4);

                if ( (strtolower($requested_agent_name) == strtolower($entity['entity'])) ) {
                    // Consistently match the nuuid to a specific uuid.
                    //$this->things[] = new Thing($entity['uuid']);
                    $match_list[] = $entity;
                    $flag_nuuid = true;

                    //            if ( (strtolower($entity['nuuid']) == strtolower($requested_nuuid)) ) {
                    //                // Consistently match the nuuid to a specific uuid.
                    //                //$this->things[] = new Thing($entity['uuid']);
                    //                $match_list[] = $entity;
                    //            }


                }
            }

        }

        if ((isset($flag_nuuid)) and (!$flag_nuuid)) {

            $entity = array("uuid"=>$this->uuid,
                "entity"=>strtolower($requested_agent_name),
                "nuuid"=>substr($this->uuid, 0, 4),
                "refreshed_at"=>$this->current_time
            );
            $match_list[] = $entity;
        }




        if ($match_list == array()) {

            $entity = array("uuid"=>$this->uuid,
                "entity"=>strtolower($requested_agent_name),
                "nuuid"=>substr($this->uuid, 0, 4),
                "refreshed_at"=>$this->current_time
            );
            $match_list[] = $entity;

        }

        // Return the matching nuuid.
        // If not return the current crow.
        // Final run through.

        $match = $match_list[0];

        foreach ($match_list as $match) {


            if ((strtolower($match["entity"])  == strtolower($requested_agent_name)) and
                (strtolower($match['nuuid']) == strtolower($requested_nuuid))) {
                //    $match = $match;
                break;

            }

        }

        // Use the current uuid to create an entity if no match found.
        if (strtolower($requested_nuuid) != strtolower($match['nuuid'])) {

            $this->uuid = $this->thing->uuid;
            $this->subject = $this->thing->subject;
            $this->nom_from = null;
            $this->nom_to = null;

            $this->id = strtolower($this->entity_agent . "_" . $this->thing->nuuid);

            return;
        }


        // Otherwise use the found match.
        $this->thing = new Thing ($match['uuid']);

        $this->uuid = $this->thing->uuid;
        $this->subject = $this->thing->subject;
        $this->nom_from = null;
        $this->nom_to = null;

        $this->id = strtolower($this->entity_agent . "_" . $this->thing->nuuid);
    }

    /**
     *
     * @return unknown
     */
    function getEntities() {
        if (isset($this->entities)) {return;}

        $this->entitycode_list = array();

        $entities = new Findagent($this->thing, "entity");
        // Get up to 10000 entities. Make this a stack variable.
        $entities->horizon = 10000;
        $entities->findAgent("entity");
        $things = $entities->thing_report['things'];

        if ($things === true) {
            $this->entities = null;
            $this->entity_list = null;
            return $this->entity_list;
        }

        $this->thing->log('Agent "Entity" found ' . count($things) ." entity Things." );

        foreach (array_reverse($things) as $thing_object) {

            $uuid = $thing_object['uuid'];

            $variables_json= $thing_object['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);


            if (isset($variables['entity'])) {


                $matches = array();
                foreach ($this->entity_agents as $key=>$entity_agent) {
                    if (isset($variables[strtolower($entity_agent)])) {
                        $matches[] = strtolower($entity_agent);
                    }
                }


                if ((isset($matches)) and count($matches) != 2) {
                    // Some sort of invalid entity with more than one agent...
                    continue;
                }

                if (!isset($variables['entity']['id'])) {continue;}

                $id = $variables['entity']['id'];
                $nuuid = substr($uuid, 0, 4);
                $refreshed_at = $variables['entity']['refreshed_at'];

                $variables['entity'][] = $thing_object['task'];
                $this->entity_list[] = $variables['entity'];
                foreach ($this->entity_agents as $key=>$value) {
                    if (isset($variables[$value])) {
                    }
                }

                $entity = array("uuid"=>$uuid,
                    "entity"=>$matches[1],
                    "nuuid"=>$nuuid,
                    "refreshed_at"=>$refreshed_at
                );

                $this->entities[] = $entity;

            }
        }

        if ((!isset($this->entity_list)) and (!isset($this->entities))) {
            $entity_is = "wumpus";
            $entity = array("uuid"=>$this->uuid,
                "entity"=>$entity_is,
                "nuuid"=>substr($this->uuid, 0, 4),
                "refreshed_at"=>$this->current_time);
            $this->entities[] = $entity;
            $this->entity_list[] = $entity_is;
        }
        return $this->entity_list;
    }


    /**
     *
     * @param unknown $entity_id (optional)
     */
    function get($entity_id = null) {
        if (!isset($this->id)) {$this->getEntity();}
        // This is a request to get the coding from the Thing
        // and if that doesn't work then from the Stack.

        if (!isset($this->requested_agent_name)) {$agent_name = "wumpus";} else {
            $agent_name = $this->requested_agent_name;
        }

        $this->entity = new Variables($this->thing, "variables entity " . $this->from);

        $this->last_refreshed_at = $this->entity->getVariable("refreshed_at");

        if (!isset($this->entity->thing->choice->current_node)) {

            $this->entity->thing->choice->Create();
            // Hello

        }

        $this->start_nuuid = $this->thing->nuuid;
        $this->state = $this->entity->thing->choice->current_node;

    }


    /**
     *
     */
    function dropEntity() {
        // devstack
        $this->thing->log($this->agent_prefix . "was asked to drop an entity.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->entity)) {
            $this->entity->Forget();
            $this->entity = null;
        }

        $this->get();
    }


    /**
     *
     * @param unknown $im     (reference)
     * @param unknown $x1
     * @param unknown $y1
     * @param unknown $x2
     * @param unknown $y2
     * @param unknown $radius
     * @param unknown $color
     */
    function ImageRectangleWithRoundedCorners(&$im, $x1, $y1, $x2, $y2, $radius, $color) {
        // devstack move to Image agent.

        // draw rectangle without corners
        imagefilledrectangle($im, $x1+$radius, $y1, $x2-$radius, $y2, $color);
        imagefilledrectangle($im, $x1, $y1+$radius, $x2, $y2-$radius, $color);

        // draw circled corners
        imagefilledellipse($im, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $color);
    }


    /**
     *
     */
    function makeWeb() {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("entity web"=>array("entity", "crow"));

        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "entity web");
        $choices = $this->thing->choice->makeLinks('entity web');

        if (!isset($this->html_image)) {$this->makePNG();}

        $web = '<a href="' . $link . '">'. $this->html_image . "</a>";
        $web .= "<br>";

        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';


        $ago = $this->thing->human_time ( time() - strtotime($this->last_refreshed_at) );
        $web .= "Asserted about ". $ago . " ago.";

        $web .= "<br>";

        $web .= $this->makeSMS();

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    public function makeMessage() {
        $message = "Entity is " . strtoupper($this->id) . ".";
        $this->message = $message;
        $this->thing_report['message'] = $message;
    }


    /**
     *
     */
    public function makePNG() {
        if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png"); // long run
        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;

        $this->thing_report['png'] = $agent->image_string;
    }


    /**
     *
     * @param unknown $agent_name (optional)
     * @return unknown
     */
    function makeEntity($agent_name = null) {

        $this->entity_agent = $agent_name;
        $this->id = strtolower($this->entity_agent . "_" . $this->thing->nuuid);

        $agent_class_name = strtolower($agent_name);

        return;

        // So don't have to do this...
        $this->thing->log('Agent "Entity" will make a ' . $agent_name . ".");

//        $agent_class_name = strtolower($agent_name);


        if ($agent_class_name == null) {
            $agent_class_name = strtolower($this->agent_name);
        }
        try {

            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

            $this->thing->log( 'trying Agent "' . $agent_class_name . '".', "INFORMATION" );
            $agent = new $agent_namespace_name($this->thing, $agent_name . " spawn");
            return true;

            //                // If the agent returns true it states it's response is not to be used.
            //                if ((isset($agent->response)) and ($agent->response === true)) {
            //                    throw new Exception("Flagged true.");
            //                }

            //                $this->thing_report = $agent->thing_report;

            //                $this->agent = $agent;
            //                return true;

        } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.

            $this->thing->log( 'could not load "' . $agent_class_name . '".' , "WARNING" );
            $message = $ex->getMessage();
            // $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . '  ' . $file . ' line:' . $line;
            $this->thing->log($input , "WARNING" );

            // This is an error in the Place, so Bork and move onto the next context.
            // $bork_agent = new Bork($this->thing, $input);
            //continue;
            return false;
        }






        $ad_hoc = true;
        if ( ($ad_hoc != false) ) {
            // Ad-hoc headcodes allows creation of headcodes on the fly.
            // 'Z' indicates the associated 'Place' is offering whatever it has.
            // Block is a Place.  Train is a Place (just a moving one).
            $quantity = "Z";

            // Otherwise we needs to make trains to run in the headcode.

            $this->thing->log($this->agent_prefix . "was told the Place is Useable but we might get kicked out.");

            // So we can create this headcode either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->current_id = $id;
            $this->id = $id;

            $this->quantity = $quantity; // which is run_time

            if (isset($run_at)) {
                $this->run_at = $run_at;
            } else {
                $this->run_at = "X";
            }
        }

        $this->set();

        $this->thing->log('Agent "Entity" found id a pointed to it.');
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function entityTime($input = null) {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $entity_time = "x";
            return $entity_time;
        }


        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute =  date("i", $t);

        $entity_time = $this->hour . $this->minute;

        if ($input == null) {$this->headcode_time = $entity_time;}

        return $entity_time;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractEntities($input = null) {

        $thing = new Nuuid($this->thing, "nuuid");
        $thing->extractNuuids($input);

        $this->ids = $thing->nuuids;

        foreach ($this->entity_agents as $index=>$entity_agent) {

            if (strpos(strtolower($input), strtolower($entity_agent)) !== false) {

                $this->entity_agent_names[] = $entity_agent;

            }


        }


        return $this->ids;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractEntity($input = null) {
        $this->extractEntities($input);

        if (!isset($this->entity_agent_names)) {return true;}

        if ((count($this->entity_agent_names)) == 1) {
            $this->entity_agent_name = $this->entity_agent_names[0];
        } else {
            // Given more than one entity type.
            // No shapeshifters. Yet.
            return true;
        }


        $ids = $this->extractEntities($input);
        if (!(is_array($ids))) {return true;}

        if ((is_array($ids)) and (count($ids) == 1)) {
            $this->id = $ids[0];
            $this->thing->log('Agent "Entity" found a id (' . $this->id . ') in the text.');
            return $this->id;
        }

        if  ((is_array($ids)) and (count($ids) == 0)) {return false;}
        if  ((is_array($ids)) and (count($ids) > 1)) {return true;}

        return true;
    }


    /*
    function read()
    {
        $this->thing->log("read");

//        $this->get();
        return $this->available;
    }
*/


    /**
     *
     */
    function addEntity() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }


    /**
     *
     */
    public function makeImage() {
        $text = strtoupper($this->id);

        $image_height = 125;
        $image_width = 125;

        // here DB request or some processing
        //        $this->result = 1;
        //        if (count($this->result) != 2) {return;}

        //        $number = $this->result[1]['roll'];

        $image = imagecreatetruecolor($image_width, $image_height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);


        $this->ImageRectangleWithRoundedCorners($image, 0, 0, $image_width, $image_height, 12, $black);
        $this->ImageRectangleWithRoundedCorners($image, 6, 6, $image_width-6, $image_height-6, 12-6, $white);

        $font = $this->default_font;

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = array(72, 36, 24, 18, 12, 6);

        if (file_exists($font)) {

        foreach ($sizes_allowed as $size) {
            $angle = 0;
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0- min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0- min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] = max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, 'bb');

            //check width of the image
            $width = imagesx($image);
            $height = imagesy($image);
            if ($bbox['width'] < $image_width - 30) {break;}

        }

        $pad = 0;
        imagettftext($image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $text);
}
        //imagestring($image, 2, $image_width-75, 10, $text, $textcolor);
        imagestring($image, 2, $image_width-45, 10, $this->entity->nuuid, $textcolor);

        $this->image = $image;
    }


    /**
     *
     */
    function makeTXT() {
        if (!isset($this->entity_list)) {$this->getEntities();}
        //$this->getHeadcodes();

        $txt = 'These are ENTITIES for RAILWAY ' . $this->entity->nuuid . '. ';
        $txt .= "\n";

        if(!isset($this->entities)) {


            $txt .= "No entities found.";
            $this->txt = $txt;
            $this->thing_report['txt'] = $txt;

            return;
        }

        $count = "X";
        if (is_array($this->entities)) {
            $count = count($this->entities);
        }

        $txt .= "\n";
        foreach ($this->entities as $index=>$entity) {

            $txt .= $entity['nuuid'] . " " . $entity['entity'] . " " . $entity['refreshed_at'] . "\n";
        }


        $txt .= "Last " . $count. ' Entities retrieved.';

        $txt .= "\n";
        $txt .= "\n";

        $txt .= "\n";
        $txt .= "\n";



        //$txt = "Test \n";
        foreach (array_reverse($this->entities) as $entity) {

            //            $txt .= " " . str_pad(strtoupper($headcode['head_code']), 4, "X", STR_PAD_LEFT);
            //$txt .= " " . str_pad($train['alias'], 10, " " , STR_PAD_RIGHT);

            $refreshed_at = "X";
            if (isset($entity['refreshed_at'])) {
                // devstack
                // $agent = new Timestamp($this->thing, $headcode['refreshed_at']);
                $refreshed_at = strtoupper(date('Y M d D H:i', strtotime($entity['refreshed_at'])));
            }
            $txt .= " " . str_pad($refreshed_at, 20, " ", STR_PAD_LEFT);


            $txt .= " " . str_pad(strtoupper($entity['nuuid']), 4, "X", STR_PAD_LEFT);

            $flag_state = "X";
            if (isset($entity['flag']['state'])) {
                $flag_state = $entity['flag']['state'];

                //$txt .= " " . str_pad($headcode['flag']['state'], 8, " ", STR_PAD_LEFT);
            }
            $txt .= " " . str_pad($flag_state, 8, " ", STR_PAD_LEFT);

            //            if (isset($headcode['refreshed_at'])) {
            //                $txt .= " " . str_pad($headcode['refreshed_at'], 12, " ", STR_PAD_LEFT);
            //            }

            $runtime_minutes = "X";
            if (isset($entity['runtime']['minutes'])) {$runtime_minutes = $entity['runtime']['minutes'];}
            $txt .= " " . str_pad($runtime_minutes, 8, " ", STR_PAD_LEFT);

            $txt .= "\n";

        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }


    /**
     *
     */
    private function getFlag() {
        $this->flag = new Flag($this->thing, "flag");

        if (!isset($this->flag->state)) { $this->flag->state = "X";}
    }


    /**
     *
     * @return unknown
     */
    public function makeSMS() {

        //$s = "GREEN";
        if (!isset($this->flag->state)) {$this->getFlag();}
        //$s = strtoupper($this->flag->state);

        $sms_message = "ENTITY " . strtoupper($this->id) ." | " . $this->flag->state;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | ";

        //        $sms_message .= $this->route . " [" . $this->consist . "] " . $this->quantity;

        //        $sms_message .= " | index " . $this->index;
        //        $sms_message .= " | available " . $this->available;

        //$sms_message .= " | from " . $this->headcodeTime($this->start_at) . " to " . $this->headcodeTime($this->end_at);
        //$sms_message .= " | now " . $this->headcodeTime();
        $sms_message .= " | nuuid " . strtoupper($this->entity->nuuid);
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;

        return $sms_message;
    }


    /**
     *
     */
    public function respond() {


        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.
        $to = $this->thing->from;
        $from = "entity";

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->makeSMS();

        $test_message = "";
        if (isset($choices['link'])) {
            $test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
        }

        $test_message .= '<br>entity state: ' . $this->state . '<br>';
        $test_message .= '<br>' . $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;

        $this->makeMessage();
        //$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->makePNG();
        $this->makeWeb();

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

        $this->thing_report['help'] = 'This is a entity.';
    }


    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable) {
        if (
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {


        $this->response = null;
        $this->num_hits = 0;

        $keywords = $this->keywords;

        $this->requested_agent_name = null;
        $this->requested_agent_name = "wumpus";

        $this->thing->log( "input to entity agent is " . $this->input .".");
        $prior_uuid = null;

        // Is there a headcode in the provided datagram
        $x = $this->extractEntity($this->input);
        //        $agent_name = $this->entity_agent;
        if (isset($agent_name)) {
            $agent_name = strtolower($agent_name);
            $this->requested_agent_name = $agent_name;
        }

        $this->thing->log("entity says agent is " . $this->requested_agent_name . ".");

        $nuuid_agent = new Nuuid($this->thing, "nuuid");
        $nuuid_agent->extractNuuid($this->input);

        if (isset($nuuid_agent->nuuid)) {
            $nuuid = $nuuid_agent->nuuid;
        } else {
            $nuuid = null;
        }

        $entity_id = "entity_". $this->requested_agent_name . "_" .$nuuid;

        $this->thing->log("entity says entity_id is " . $entity_id . ".");
        $this->entity_id = new Variables($this->thing, "variables entity " . $this->from);

        if (!isset($this->id) or ($this->id == false)) {
            $this->id = $this->entity_id->getVariable('id', null);
            if (!isset($this->id) or ($this->id == false)) {
                $this->id = $this->getVariable('id', null);

                if (!isset($this->id) or ($this->id == false)) {
                    $this->id = $this->default_id;
                }
            }
        }
        $this->thing->log("this->id " . $this->id . ".");

        $this->getEntity($nuuid);

        $this->get();

        if ( ($this->agent_input == "extract") and (strpos(strtolower($this->subject), 'roll') !== false )   ) {

            if (strtolower($this->id[1]) == "d") {
                $this->response = true; // Which flags not to use response.
                //$this->response = "Not a headcode.";
                return;
            }
        }
        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {$this->response = "Extract";return;}

        $pieces = explode(" ", strtolower($this->input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($this->input == 'entity') {
//                $this->read();
                $this->response = "Read entity";
                return;
            }
        }


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                    case 'spawn':
                    case 'get':

                        if (!isset($nuuid)) {

                            // No nuuid.  So set it now to current one.
                            $nuuid = $this->thing->nuuid;


                        }

                        $id = "entity_". strtolower($this->entity_agent) . "_"  . $nuuid;

                        //$this->makeEntity(strtolower($this->entity_agent));
                        $this->getEntity($id);
                        $this->response = "Got entity devstack";
                        break;


                    case 'next':
                        $this->thing->log("read subject nextentity");
                        $this->nextentity();
                        $this->response = "Got next entity";
                        break;
                    case 'drop':
                        $this->dropentity();
                        $this->response = "Dropped entity";
                        break;
                    case 'add':
                        $this->get();
                        $this->response = "Added entity";
                        break;
                    default:
                    }
                }
            }
        }

        if ($this->isData($this->id)) {
            $this->set();
            $this->response = "Set entity to " . strtoupper($this->id);
            return;
        }

        $this->read();
        $this->response = "Read";
        //return "Message not understood";
        return false;
    }


}
