<?php
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

    function init()
    {
        $this->keywords = array('next', 'accept', 'clear', 'drop','add','new');

        $this->default_id = "ad20";
        if (isset($this->thing->container['api']['entity']['id'])) {
            $this->default_id = $this->thing->container['api']['entity']['id'];
        }

        $this->default_alias = "Thing";

        $this->entity_agent = $this->agent_input;

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/entity';

        $this->state = "X";
    }

    function set()
    {
        $this->refreshed_at = $this->current_time;

        $this->entity_id->setVariable("id", $this->id);
        $this->entity_id->setVariable("refreshed_at", $this->current_time);

        $this->thing->json->writeVariable( array("entity", "id"), $this->id );
        $this->thing->json->writeVariable( array("entity", "refreshed_at"), $this->current_time );

    }

    function nextEntitycode()
    {
        // #devstack

        $this->thing->log("next entitycode");
        // Pull up the current headcode
        $this->get();

        // One minute into the next entity
        $quantity = 1;
        $next_time = $this->thing->json->time(strtotime($this->end_at . " " . $quantity . " minutes"));

        $this->get($next_time);

        return $this->available;
    }

    private function getEntity($requested_nuuid = null)
    {
        $this->getEntities();
        if (!isset($this->id)) {$this->id = $this->default_id;}

        $matching_things = array();

        if ($this->entities == null) {
            // Make the current thing an entity.
            // Give it an id.
            $this->id = $this->thing->nuuid;
            return;
        }

        foreach($this->entities as $key=>$entity) {
            $entity_nuuid = substr($entity['uuid'], 0, 4);

            if (strtolower($entity_nuuid) == strtolower($requested_nuuid)) {
                // Consistently match the nuuid to a specific uuid.
                $this->things[] = new Thing($crow['uuid']);
            }
        }

        if (!isset($this->things[0])) {return true;}

        $this->thing = $this->things[0];
        $this->id = $this->thing->nuuid;
    }


    function getEntities()
    {
        if (isset($this->entities)) {return;}


        $this->entitycode_list = array();

        $entities = new FindAgent($this->thing, "entity");
        // Get up to 10000 entities. Make this a stack variable.
        $entities->horizon = 10000;
        $entities->findAgent("entity");
        $things = $entities->thing_report['things'];

        $this->thing->log('Agent "Entity" found ' . count($things) ." entity Things." );

        $entity_agents = array("Cat", "Dog", "Ant", "Crow");

        if ($things === true) {
            $this->entities = null;
            $this->entity_list = null;
            return $this->entity_list;
        }

        foreach (array_reverse($things) as $thing_object) {

            $uuid = $thing_object['uuid'];

            $variables_json= $thing_object['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables['entity'])) {
                if(!isset($variables['entity']['id'])) {continue;}
                $id = $variables['entity']['id'];
                $nuuid = substr($uuid, 0, 4);

                $refreshed_at = $variables['entity']['refreshed_at'];

                $variables['entity'][] = $thing_object['task'];
                $this->entity_list[] = $variables['entity'];
                foreach($entity_agents as $key=>$value) {
                    if (isset($variables[$value])) {
                    }
                }


                $entity = array("uuid"=>$uuid,
                                    "nuuid"=>$nuuid,
                                    "refreshed_at"=>$refreshed_at
                                    );
                $this->entities[] = $entity;
            }
        }
        return $this->entity_list;
    }

    function get($entity_id = null)
    {
        if (!isset($this->id)) {$this->getEntity();}
        // This is a request to get the headcode from the Thing
        // and if that doesn't work then from the Stack.

        // 0. light engine with or without break vans.
        // Z. Always has been a special.
        // 10. Because starting at the beginning is probably a mistake. 
        // if you need 0Z00 ... you really need it.

        $agent_name = $this->entity_agent;

        $this->entity = new Variables($this->thing, "variables entity_" . $agent_name . " " . $this->id . " " . $this->from);

        $this->last_refreshed_at = $this->entity->getVariable("refreshed_at");

        $this->state = $this->thing->choice->current_node;

    }

    function dropEntity()
    {
        // devstack
        $this->thing->log($this->agent_prefix . "was asked to drop an entity.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->entity)) {
            $this->entity->Forget();
            $this->entity = null;
        }

        $this->get();
    }

    function ImageRectangleWithRoundedCorners(&$im, $x1, $y1, $x2, $y2, $radius, $color)
    {
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

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("entity web"=>array("entity", "entity 0Z99"));

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

    public function makeMessage()
    {
        $message = "Entity is " . strtoupper($this->id) . ".";
        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    public function makePNG()
    {
        if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png"); // long run
        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;

        $this->thing_report['png'] = $agent->image_string;
    }

    function makeEntity($id = null)
    {
        $id = $this->getVariable('id', $id);

        $this->thing->log('Agent "Entity" will make a id for ' . $id . ".");

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

    function entityTime($input = null)
    {
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

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $entity_time = $this->hour . $this->minute;

        if ($input == null) {$this->headcode_time = $entity_time;}

        return $entity_time;
    }

    function extractEntities($input = null)
    {
        $thing = new Nuuid($this->thing, "nuuid");
        $thing->extractNuuids($input);

        $this->ids = $thing->nuuids;

        return $this->ids;
    }

    function extractEntity($input)
    {
        $ids = $this->extractEntities($input);
        if (!(is_array($ids))) {return true;}

        if ((is_array($ids)) and (count($ids) == 1)) {
            $this->id = $ids[0];
            $this->thing->log('Agent "Entity" found a id (' . $this->id . ') in the text.');
            return $this->id;
        }

        if  ((is_array($ids)) and (count($ids) == 0)){return false;}
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


    function addEntity() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    public function makeImage()
    {
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


        $this->ImageRectangleWithRoundedCorners($image, 0,0, $image_width, $image_height, 12, $black);
        $this->ImageRectangleWithRoundedCorners($image, 6,6, $image_width-6, $image_height-6, 12-6, $white);

        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = array(72,36,24,18,12,6);

        foreach($sizes_allowed as $size) {
            $angle = 0;
            $bbox = imagettfbbox ($size, $angle, $font, $text); 
            $bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
            $bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
            $bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
            $bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
            extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 

         //check width of the image 
            $width = imagesx($image); 
            $height = imagesy($image);
            if ($bbox['width'] < $image_width - 30) {break;}

        }

        $pad = 0;
        imagettftext($image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $text);
        //imagestring($image, 2, $image_width-75, 10, $text, $textcolor);
        imagestring($image, 2, $image_width-45, 10, $this->entity->nuuid, $textcolor);

        $this->image = $image;
    }


    function makeTXT()
    {
        if (!isset($this->entity_list)) {$this->getEntities();}
        //$this->getHeadcodes();

        $txt = 'These are ENTITIES for RAILWAY ' . $this->entity->nuuid . '. ';
        $txt .= "\n";

        $count = "X";
        if (is_array($this->entities)) {
            $count = count($this->entities);
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

    private function getFlag()
    {
        $this->flag = new Flag($this->thing, "flag");

        if (!isset($this->flag->state)) { $this->flag->state = "X";}
    }

    private function makeSMS() {

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

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
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

    public function readSubject()
    {

        $this->response = null;
        $this->num_hits = 0;

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            if ($this->agent_input == "extract") {
                $input = strtolower($this->subject);
            } else {
                $input = strtolower($this->agent_input);
            }
        } else {
            $input = strtolower($this->from . " " . $this->subject);
        }

        $prior_uuid = null;

        // Is there a headcode in the provided datagram
        $x = $this->extractEntity($input);

        $agent_name = $this->entity_agent;

        $this->entity_id = new Variables($this->thing, "variables entity_" . $agent_name . " " . $this->from);

        if (!isset($this->id) or ($this->id == false)) {
            $this->id = $this->entity_id->getVariable('id', null);
            //var_dump($this->head_code);
            if (!isset($this->id) or ($this->id == false)) {
                $this->id = $this->getVariable('id', null);
                //var_dump($this->head_code);

                if (!isset($this->id) or ($this->id == false)) {
                    $this->id = "ae30";
                    //var_dump($this->head_code);
                }
            }
        }

        $this->get();

        if ( ($this->agent_input == "extract") and (strpos(strtolower($this->subject),'roll') !== false )   ) {

//            echo "headcode found was " . $this->head_code ."\n";

            if (strtolower($this->id[1]) == "d") {
                $this->response = true; // Which flags not to use response.  
                //$this->response = "Not a headcode."; 
                return;
            }
        }

        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {$this->response = "Extract";return;}

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {
            if ($input == 'entity') {
                $this->read();
                $this->response = "Read entity";
                return;
            }
        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {
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
