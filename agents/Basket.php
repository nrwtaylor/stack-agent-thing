<?php
/**
 * Basket.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Basket extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {

        $this->agent_name = "basket";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        $this->nuuid_agent = new Nuuid($this->thing, "nuuid");
        $this->uuid_agent = new Uuid($this->thing, "Uuid");

    }


    /**
     *
     */
    function run() {

        //$this->doBasket();

        $this->thing_report['sms'] = "Merp";

    }


    /**
     *
     */
    public function set() {

        $this->basket_tag= $this->basket_thing->nuuid;
        $this->thing->console("setting basket tag " . $this->basket_tag . "\n");

        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}

        $basket = new Variables($this->thing, "variables basket " . $this->from);

        $basket->setVariable("tag", $this->basket_tag);

        $basket->setVariable("refreshed_at", $this->refreshed_at);



        // This is an idea that you can describe your state.
        // "I/we call this place < some symbol signal >"
        // "Awk."

        $this->basket_thing->Write( array("basket", "name"), $this->name );
        $this->basket_thing->Write( array("basket", "sign"), $this->sign );

    }

    function assertIs($input)
    {
$agent_name = "basket";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $agent_name. " is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("basket is")); 
        } elseif (($pos = strpos(strtolower($input), "basket")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("basket")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $basket = $this->getBasket($filtered_input);

        if ($basket) {
            //true so make a place
            //$this->makePlace(null, $filtered_input);
// Crow doesn't exist
//return true;

$this->basket_tag = $this->basket_thing->nuuid;

return;
        }

        $response = "Asserted basket is " . $this->basket_thing->nuuid . ". ";
        $this->response .= $response;


    }

    /**
     *
     * @param unknown $basket_code (optional)
     * @return unknown
     */
    public function get($basket_code = null) {
        $this->thing->console("Asked to get basket " . $basket_code . ".\n");

        $basket = new Variables($this->thing, "variables basket " . $this->from);

        $this->basket_tag = $basket->getVariable("tag");
        $this->refreshed_at = $basket->getVariable("refreshed_at");

        if ($this->basket_tag != false) {$basket_code = $this->basket_tag;}

        // Load up the appropriate crow_thing
        $this->thing->console("got basket code " .$basket_code . "\n");
        $this->getBasket($basket_code);

        if (!isset($this->basket_thing)) {$this->newBasket();}


        $this->current_time = $this->basket_thing->time();
        $this->time_string = $this->basket_thing->Read( array("basket", "refreshed_at") );

        if ($this->time_string == false) {
            $this->time_string = $this->basket_thing->time();
            $this->basket_thing->Write( array("basket", "refreshed_at"), $this->time_string );
        }

        $this->refreshed_at = strtotime($this->time_string);

        $this->name = strtolower($this->basket_thing->Read( array("basket", "name") ));
        $this->sign = $this->basket_thing->Read( array("basket", "sign") );


        if ( ($this->name == false) or ($this->name = "")) {
            $this->name = "X";
        }

        if ( ($this->sign == false) or ($this->sign = "")) {$this->sign = "X";}

        //$this->getBasket($this->basket_uuid);

        return array($this->name, $this->sign);
    }


    //function get() {
    //$this->basket_uuid = "23c9a180-28cf-4ca5-aefe-8ba3a5a88cd8";

    // Get the basket
    //$this->getBasket($this->basket_uuid);

    //}
    // This is a place to work on associating things together.

    //    function newBasket() {}


    /**
     *
     * @param unknown $text (optional)
     */
    function doBasket($text = null) {

        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index=>$ngram) {

            switch ($ngram) {
            case "add to":
            case "put in":
            case "place inside":

                // If there is no uuid/nuuid.
                // Get the last thing.
                // Put it in the current basket.

                // Last thing?  Is that helpful. Or useful.
                $thing = $this->thing->db->priorGet();
                $last_thing = $thing['thing'];
                $uuid = $last_thing->uuid;
                $this->putinBasket($uuid);


                $this->response .= "Put the last thing in the basket. ";
                break;
            case "drop":
            case "from drop":
            case "drop from":
            case "push out":
            case "place outside":

                // If there is no uuid/nuuid.
                // Get the last thing.
                // Put it in the current basket.

                // Last thing?  Is that helpful. Or useful.
                //$thing = $this->thing->db->priorGet();
                //$last_thing = $thing['thing'];
                //$uuid = $last_thing->uuid;

                $this->register_nuuid = $this->nuuid_agent->extractNuuid($this->input);

                $this->dropfromBasket($this->register_nuuid);


                $this->response .= "Dropped " . $this->register_nuuid . ". ";
                break;

            case "new":

                // If there is no uuid/nuuid.
                // Get the last thing.
                // Put it in the current basket.

                // Last thing?  Is that helpful. Or useful.
                //$thing = $this->thing->db->priorGet();
                //$last_thing = $thing['thing'];
                //$uuid = $last_thing->uuid;

                $this->newBasket();

                $this->response .= "Got a new empty basket. ";
                break;

            case "name is":
            case "name":
$this->nameBasket($filtered_text);
                break;


            case "is":
            case "load":
            case "crow load":
            case "run":
            case "run crow":
//                $this->getCrow($text);
//$this->set();
$this->assertCrow($filtered_text);
                break;


            case "list":
            case "inventory":
            case "what is in":
            case "contents":
            case "contains":

                $this->inventoryBasket();
                $this->response .= "Got the contents of the Basket. ";
                break;

            case "uuid":

                // If there is no uuid/nuuid.
                // Get the last thing.
                // Put it in the current basket.

                // Last thing?  Is that helpful. Or useful.
                //$thing = $this->thing->db->priorGet();
                //$last_thing = $thing['thing'];
                //$uuid = $last_thing->uuid;

                $this->response .= "uuid " . $this->basket_thing->uuid .". ";
                break;


            default:

                if (($pos = strpos(strtolower($filtered_text), "uuid")) !== FALSE) { 

                   $this->response .= "uuid " . $this->basket_thing->uuid .". ";
                   break;
                }

$n = null;
if (isset($this->register_nuuid)) {
$n = $this->register_nuuid;
}

                $this->getBasket($n);
                $this->response .= "Looked at the basket " . $this->basket_tag . " ";
                $this->response .= "basket name ". $this->name . " basket sign " . $this->sign . ".";

            }

        }


        // Get the basket
        //$this->getBasket($basket_uuid);

        $this->setBasket();

        $this->inventoryBasket();

    }



    /**
     *
     * @param unknown $search_text (optional)
     */
    function getBasket($search_text = null) {

        $this->thing->console("Asked to get basket using words: " . $search_text . ".\n");

        $requested_nuuid = $this->nuuid_agent->extractNuuid($search_text);
        $this->thing->console("requested nuuid " . $requested_nuuid . "\n");

        if ($requested_nuuid == null) {return;}
        $entity_input = "get basket";
        if ($requested_nuuid != null) {$entity_input = "get basket ".$requested_nuuid;} else {$entity_input = "get basket";}

        $this->thing->console("entity input " . $entity_input . "\n");

        $entity = new Entity($this->thing, $entity_input );

        // Make a new basket if one does not exist.
        if ($entity == false) {$this->newBasket();}

        $this->basket_thing = $entity->thing;


    }

function nameBasket($text = null) {

if ($text == null) {return;}

$this->name = $text;

}

    /**
     *
     */
    function newBasket() {

        $this->thing->console( "Asked to get a new basket " . $this->thing->nuuid . ".\n");

        // Need to log it as an entity for it to spawn
        $entity = new Entity($this->thing, "spawn basket " . $this->thing->nuuid);
//        $entity = new Entity($this->thing, "spawn basket");

        // And then here the thing get's loaded in.
        $this->basket_thing = $entity->thing;

        $this->thing->console("Got new basket nuuid " . $this->basket_thing->nuuid . "\n");

$this->basket_tag = $this->basket_thing->nuuid;
$this->register_nuuid = $this->basket_thing->nuuid;
        $this->thing->console("basket tag " . $this->basket_tag . "\n");

$this->assertIs("basket");

        $this->response .= "basket_thing->nuuid " . $this->basket_thing->nuuid .". ";


    }


    /**
     *
     * @param unknown $search_text (optional)
     */
    function getItems($search_text = null) {


        $this->thing->console("getting a list of items in basket " . $this->basket_thing->nuuid . ".\n");

//        $this->items = json_decode($this->basket_thing->thing->associations);
        $this->items = $this->basket_thing->associations;


        // Try some other things.


        $search_text = "";
        $associated_things = $this->thing->db->associationSearch($search_text, $max = null);

        $agent_search = "basket";
        $max = 10000;
        $a = $this->thing->db->agentSearch($agent_search, $max);


    }


    /**
     *
     */
    function dev() {

        $search_text = "";
        $associated_things = $this->thing->db->associationSearch($search_text, $max = null);

        $agent_search = "basket";
        $max = 10000;
        $a = $this->thing->db->agentSearch($agent_search, $max);
    }


    /**
     *
     */
    function getThing() {

        $this->thing->console("Asked to get a thing. Made a jelly sandwich.\n");
        // Make something and put it in the basket
        $picanic_thing = new Thing(null);
        $picanic_thing->Create("picanic", "basket" , "s/ jelly sandwich");

        $this->picanic_thing = $picanic_thing;
    }


    /**
     *
     * @param unknown $picnic_uuid (optional)
     */
    function putinBasket($picnic_uuid = null) {
$this->thing->console( "Putin basket " . $picnic_uuid . "\n");
        //$this->getBasket();

        if ($picnic_uuid == null) {

            //        if ($picanic_thing == null) {
            $this->getThing();
            //        } else {
            //            $this->picanic_thing = $picanic_thing;
            //        }

            $picnic_uuid = $this->picanic_thing->uuid;

        }
$this->thing->console("Putin basket " . $picnic_uuid . "\n");

        //$this->basket_thing->associations->setField("associations");
        $this->basket_thing->associations->pushStream($picnic_uuid);

        $picnic_nuuid = substr($picnic_uuid, 0, 4);

$this->getItems();

        $this->response = "Added " . $picnic_nuuid .". ";

    }


    /**
     *
     * @param unknown $text (optional)
     */
    function dropfromBasket($text = null) {

        //if ($text == null) {$text = $this->input;}

        $this->thing->console( "asked to drop " . $text . "\n");


        $this->inventoryBasket();
        $items = $this->items->agent;

        foreach ($items as $index=>$item) {
            //foreach($this->items as $index=>$item) {
            if (stripos($item, $text) !== false) {
                $this->thing->console("matched " . $text . "\n");

                //                $this->place_codes[] = $place_code;
                //           }

                $drop_list[] = array("index"=>$index, "item"=>$item);
            }
        }
        if ((isset($drop_list)) and (count($drop_list) == 1)) {
            $drop_index = $drop_list[0]["index"];

            $this->thing->console("Dropping index " . $drop_index . ".\n");

          //  $this->basket_thing->associations->setField('associations');

            $this->basket_thing->associations->popstream($drop_index);
            $this->inventoryBasket();
        }

    }


    /**
     *
     */
    function takeoutBasket() {

        $pos = -1; // Last added
        $pos = 0; // First added

        //$this->basket_thing->associations->setField('associations');
        $this->basket_thing->associations->popStream($pos);


    }


    /**
     *
     */
    function inventoryBasket() {

        $this->getItems();

if (!isset($this->items)) {
$this->inventory_response = "Basket is empty. ";
return;
}
        $items = $this->items->agent;

        $this->inventory_response = "inventory ";
        foreach ($items as $index=>$item) {

            $thing = new Thing($item);


            $this->thing->console(substr($thing->uuid, 0, 4) . " " . $thing->subject . "\n");
            $this->inventory_response .= " ". $thing->nuuid;

        }



    }


    /**
     *
     */
    function setBasket() {

        // Get the basket

        //$basket_uuid = "23c9a180-28cf-4ca5-aefe-8ba3a5a88cd8";
        //$this->basket_thing = new Thing($basket_uuid);

        // Make something and put it in the basket
        //$this->putinBasket();
        //$this->basket->json->popStream($pos);

    }


    // What stack variables do we need to get to make this work?

    /**
     *
     */
    private function getNegativetime() {

        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "cat");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }


    /**
     *
     * @return unknown
     */
    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "cat";

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {
$this->getBasket();
        $this->inventoryBasket();
$this->getitems();

        $this->node_list = array("cat"=>array("cat", "dog"));
        $m = strtoupper($this->agent_name) . " " . strtoupper($this->basket_thing->nuuid) . " | " . $this->response . " | " . $this->inventory_response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function doCat($text = null) {
        // Yawn.

        $this->getNegativeTime();

        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $this->response .= strtolower($v);
            $this->cat_message .= $this->response;
        } else {
            $this->cat_message .= $this->agent_input;
        }

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        //$uuids = $this->uuid_agent->extractUuids($this->input);
        $nuuids = $this->nuuid_agent->extractNuuids($this->input);

        $nuuid = $this->nuuid_agent->extractNuuid($this->input);

        if ($nuuid != false) {
            $this->register_nuuid = $nuuid;
        }

        if ($nuuid != false) {$this->register_nuuid = $nuuid;}
        $this->doBasket($this->input);
        return false;
    }


}
