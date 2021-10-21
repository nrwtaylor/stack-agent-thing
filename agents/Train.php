<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack

class Train extends Agent
{
    // A Train is a headcode with a alias (name).
    // It will respond to trains with a signal.

    // Red - Not available
    // Green - Slot allocated
    // Yellow - Next signal Red.
    // Double Yellow - Next signal Yellow

    // The Block keeps track of the Uuids of associated Resources.
    // And checks to see what the block signal should be.  And pass and collect tokens.

    // This is the train driver 1969.  They are an ex-British Rail signalperson.

    public $var = 'hello';

    public function init()
    {
        $this->keyword = "train";

        $this->start_time = $this->thing->elapsed_runtime();

        // devstack
        $this->test = "Development code"; // Always

        $this->num_hits = 0;

        $this->node_list = ["red" => ["green" => ["red"]]];
        $this->thing->choice->load('train');

        $this->agents = [
            'flag',
            'runtime',
            'runat',
            'endat',
            'state',
            'quantity',
            'alias',
            'name',
            'available',
            'resource',
            'link',
            'route',
            'consist',
        ];

        $this->keywords = [
            'train',
            'run',
            'change',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'run',
            'red',
            'green',
        ];

        $this->time_seperator = ":";

        $this->verbosity = 2;

        $this->default_runtime = $this->current_time;
        $this->negative_time = true;

        // This shoudl allow this to be used for Circuses too.
        // And to add Trains to named resources.
        // Later.
        $this->default_train_name = "train";
        $this->initTrain();
    }

    public function initTrain()
    {
    }

    public function idTrain($text = null)
    {
        if ($text == null) {
            return null;
        }
        //$stop_code = $text;

        //$stops = $this->get("stops", array("stop_code"=>$stop_code));

        if (!isset($this->trains)) {
            $this->getTrains();
        }

        $matches = [];

        foreach ($this->trains as $train) {
            if (!isset($train['alias']['alias'])) {
                continue;
            }

            if (
                strtolower($text) ==
                    strtolower($train['headcode']['head_code']) or
                strtolower($text) == strtolower($train['alias']['alias'])
            ) {
                $head_code = $train['headcode']['head_code'];
                $alias = $train['alias']['alias'];

                $matches[$alias] = $head_code;

                //break; //on first find
            }
        }

        if ($matches == []) {
            return false;
        }

        if (count($matches) == 1) {
            return [$alias, $head_code];
        }
        return true;

        $this->thing->log(
            "Matched head_code " . $head_code . " to alias " . $alias . "."
        );

        return $head_code;
    }

    public function findTrain($text = null)
    {
        if ($text == null) {
            return true;
        }
    }

    public function set()
    {
        // A block has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->train_thing)) {
            $this->train_thing = $this->thing;
        }

        if (!isset($this->requested_state) or $this->requested_state == null) {
            $this->requested_state = $this->state;
        }

        if (!isset($requested_state) or $requested_state == null) {
            $requested_state = $this->requested_state;
        }
        // Update calculated variables.
        //        $this->available = $this->getAgent('available')->available;
        $this->getAvailable();

        $this->variables_agent->setVariable("state", $requested_state);
        $this->variables_agent->setVariable("head_code", $this->head_code);

        $this->setAlias();
        $this->setIndex();
        $this->setRunat();
        $this->setQuantity();

        $this->variables_agent->setVariable("available", $this->available);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->variables_agent->setVariable("route", $this->route);
        $this->variables_agent->setVariable("consist", $this->consist);
        $this->variables_agent->setVariable("runtime", $this->runtime);

        $this->thing->choice->save('train', $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }

    public function nextTrain()
    {
        $this->thing->log("next train");
        // Pull up the current block
        // Find the end time of the block
        // which is $this->end_at

        // One minute into next block
        $runtime = 1;
        $next_time = $this->thing->time(
            strtotime($this->end_at . "+" . runtime . " minutes")
        );

        $this->get($next_time);

        // So this should create a block in the next minute.

        return $this->available;
    }

    public function parseTrain()
    {
        return;
        // Given closest train in $this->train_thing.

        if ($this->train_thing == false) {
            if (isset($this->variables_agent->head_code)) {
                // Load in headcode and associates variables
                // Look for X and Z variables and replace with variables
                // from ->variables_agent
            }
        }

        if (!isset($this->max_index)) {
            $this->max_index = 1;
        }
        $this->train_thing->index = $this->train_thing->getVariable(
            "train",
            "index"
        );
        if ($this->train_thing->index > $this->max_index) {
            $this->max_index = $this->train_thing->index;
        }

        $this->train_thing->head_code = $this->train_thing->getVariable(
            "train",
            "head_code"
        );
        $this->train_thing->alias = $this->train_thing->getVariable(
            "train",
            "alias"
        );

        $this->train_thing->run_at = $this->train_thing->getVariable(
            "train",
            "run_at"
        );
        $this->train_thing->quantity = $this->train_thing->getVariable(
            "train",
            "quantity"
        );
        $this->train_thing->available = $this->train_thing->getVariable(
            "train",
            "available"
        );
        $this->train_thing->refreshed_at = $this->train_thing->getVariable(
            "train",
            "refreshed_at"
        );

        $this->train_thing->route = $this->train_thing->getVariable(
            "train",
            "route"
        );
        $this->train_thing->consist = $this->train_thing->getVariable(
            "train",
            "consist"
        );
        $this->train_thing->runtime = $this->train_thing->getVariable(
            "train",
            "runtime"
        );
    }

    public function run()
    {
        $this->doTrain();
    }

    public function doTrain()
    {
        $available = $this->thing->human_time($this->available);

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        //$s = $this->block_thing->state;
        if (!isset($this->flag)) {
            $this->getFlag();
        }
    }

    function get()
    {
        //$thing = $this->thing;
        if (!isset($this->train_thing)) {
            $this->train_thing = $this->thing;
        }
        $this->getHeadcode();
        /*
        $this->variables_agent = new Variables(
            $this->train_thing,
            "variables " . $this->default_train_name . " " . $this->from
        );
*/

        $this->variables_agent = new Variables(
            $this->train_thing,
            "variables " .
                $this->default_train_name .
                "_" .
                $this->head_code .
                " " .
                $this->from
        );

        $this->train_thing = $this->variables_agent->thing;

        // Which is an object with all the variables.
        $this->priorTrain();
    }

    function priorTrain($text = null)
    {
        if (!isset($this->trains)) {
            $this->getTrains();
        }

        if (isset($this->trains[1])) {

            if ($this->head_code != $this->trains[1]['headcode']) {
                $this->response .= "Head code changed. ";

                foreach ($this->trains as $i => $train) {
                    if ($train['headcode'] == $this->head_code) {
                        $this->state = $train['state'];
                        $this->index = $train['index'];
                        $this->alias = $train['alias'];
                        $this->head_code = $train['headcode'];

                        if (!isset($this->runat)) {
                            $this->runat = new \stdClass();
                        }

                        $this->runat->day = $train['runat']['day'];
                        $this->runat->hour = $train['runat']['hour'];
                        $this->runat->minute = $train['runat']['minute'];

                        if (!isset($this->endat)) {
                            $this->endat = new \stdClass();
                        }

                        $this->endat->day = $train['endat']['day'];
                        $this->endat->hour = $train['endat']['hour'];
                        $this->endat->minute = $train['endat']['minute'];

                        $this->runtime = $train['runtime'];
                        $this->quantity = $train['quantity'];

                        $this->route = $train['route'];
                        $this->consist = $train['consist'];

                        $this->available = $train['available'];
                        if (!isset($this->flag)) {
                            $this->getFlag();
                        }
                        $this->flag = $train['flag'];
                        $this->train = $train;
                        return;
                    }
                }
            }
        }

        // devstack.
        $this->available_agent = $this->getAgent('Available', 'available');
        $available = "X";
        if ($this->available_agent == false) {
            $available = "Z";
        }
        if (isset($this->available_agent->available)) {
            $available = $this->available_agent->available;
        }
        $this->available = $available;

        $this->flag_agent = $this->getAgent('Flag', 'flag');
        $this->flag = $this->flag_agent->state;

        // Get headcode quantity
        $this->quantity_agent = $this->getAgent('Quantity', 'quantity');
        $this->quantity = $this->quantity_agent->quantity;

        $this->state_agent = $this->getAgent('State', 'state');
        $this->state = $this->state_agent->state;

        $this->alias_agent = $this->getAgent('Alias', 'alias');
        $this->alias = $this->alias_agent->alias;

        $this->runtime_agent = $this->getAgent('Runtime', 'runtime');
        $this->runtime = $this->runtime_agent->runtime;

        $this->resource_agent = $this->getAgent('Resource', 'resource');
        $this->resource = $this->resource_agent->resource_name;

        //$this->getConsist();
        //$this->consist_agent = $this->getAgent('Consist', 'consist');
        //$this->consist = implode('',$this->consist_agent->consist['vehicles']);

        //$this->getRoute();
        //$this->route_agent = $this->getAgent('Route', 'route');
        //$this->route = implode(' > ',$this->route_agent->route['places']);


        // devstack
        $this->runat_agent = $this->getAgent('Runat', 'runat');
        if (!isset($this->runat)) {
            $this->runat = new \stdClass();
        }

        $this->runat->day = $this->runat_agent->day;
        $this->runat->hour = $this->runat_agent->hour;
        $this->runat->minute = $this->runat_agent->minute;

        $this->endat_agent = $this->getAgent('Endat', 'endat');
        if (!isset($this->endat)) {
            $this->endat = new \stdClass();
        }

        $this->endat->day = $this->endat_agent->day;
        $this->endat->hour = $this->endat_agent->hour;
        $this->endat->minute = $this->endat_agent->minute;

        $this->getIndex();

        $this->train = [
            'runat' => $this->runat,
            'runtime' => $this->runtime,
            'alias' => $this->alias,
            'flag' => $this->flag,
        ];
        // Now have a train thing.
    }

    function getTrains($train_time = null)
    {
        if (isset($this->trains)) {
            return;
        }
        // devstack.
        // No need to load up all the trains yet.
//        return;
        // Loads current block into $this->block_thing

        $match = false;

        if ($train_time == null) {
            $train_time = $this->current_time;
        }

        $train_things = [];

        // Get recent train tags.
        // This will include simple 'train'
        // requests too.
        // Think about that.

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->max_index = 0;
        $this->trains = [];

        //$things = $findagent_thing->thing_report['things'];
        $things = $this->getThings('train');

if ($things == null) {return;}

        $this->thing->log('found ' . count($things) . " Train Agent Things.");

        foreach ($things as $uuid => $train_thing) {
            //            $thing = new Thing($train_thing['uuid']);

            //            $variables_json= $train_thing['variables'];
            //            $variables = $this->thing->json->jsontoArray($variables_json);

            //$uuid = $train_thing['uuid'];

            $variables = $train_thing->variables;
            //$variables = $this->thing->json->jsontoArray($variables_json);

            $refreshed_at = "X";

            //if (!isset($variables['train'])) {
            //    continue;
            //}

            if (isset($variables['train']['refreshed_at'])) {
                $refreshed_at = $variables["train"]["refreshed_at"];
            }

            $index = "X";
            if (isset($variables['index']['index'])) {
                $index = $variables["index"]['index'];
            }
            // Find the maximumum index in the last 99 things.
            if ($index > $this->max_index) {
                $this->max_index = $index;
            }
            $alias = "X";
            if (isset($variables['alias'])) {
                $alias = $variables["alias"]['alias'];
            }

            $state = "X";
            if (isset($variables['state']['state'])) {
                $state = $variables["state"]["state"];
            }

            $flag = "X";
            if (isset($variables['flag']['state'])) {
                $flag = $variables["flag"]["state"];
            }

            $runat_day = "X";
            $runat_hour = "X";
            $runat_minute = "X";

            if (isset($variables['runat']['day'])) {
                $runat_day = $variables["runat"]['day'];
            }
            if (isset($variables['runat']['hour'])) {
                $runat_hour = $variables["runat"]['hour'];
            }
            if (isset($variables['runat']['hour'])) {
                $runat_minute = $variables["runat"]['minute'];
            }

            $endat_day = "X";
            $endat_hour = "X";
            $endat_minute = "X";

            if (isset($variables['endat']['day'])) {
                $endat_day = $variables["endat"]['day'];
            }
            if (isset($variables['endat']['hour'])) {
                $endat_hour = $variables["endat"]['hour'];
            }
            if (isset($variables['endat']['minute'])) {
                $endat_minute = $variables["endat"]['minute'];
            }

            $quantity = "X";
            if (isset($variables['quantity']['quantity'])) {
                $quantity = $variables["quantity"]['quantity'];
            }

            $available = "X";
            if (isset($variables['available'])) {
                $available = $variables["available"];
            }

            $head_code = "X";
            //     $refreshed_at = $variables["train"]["refreshed_at"];

            if (isset($variables['headcode']['head_code'])) {
                $head_code = $variables["headcode"]['head_code'];
            }
            $route = "X";
            if (isset($variables['route']['route'])) {
                $route = $variables["route"]['route'];
            }

            $consist = "X";
            if (isset($variables['consist'])) {
                $consist = $variables["consist"];
            }
            $runtime = "X";
            if (isset($variables['runtime'])) {
                $runtime = $variables["runtime"]['minutes'];
            }

            //
            $train = [
                "state" => $state,
                "index" => $index,
                "headcode" => $head_code,
                "flag" => $flag,
                "runat" => [
                    "day" => $runat_day,
                    "hour" => $runat_hour,
                    "minute" => $runat_minute,
                ],
                "endat" => [
                    "day" => $endat_day,
                    "hour" => $endat_hour,
                    "minute" => $endat_minute,
                ],
                "runtime" => $runtime,
                "alias" => $alias,
                "available" => $available,
                "quantity" => $quantity,
                "route" => $route,
                "consist" => $consist,
                "refreshed_at" => $refreshed_at,
            ];

            $this->trains[] = $train;

            /*
            //// If the train time is in the run period of the train
            //// then this is a valid train to be running right now.
            if ( ( strtotime($train_time) >= strtotime($thing->run_at) ) 
                and ( strtotime($train_time) <= strtotime($thing->end_at) ) ) {

                $this->thing->log( 'Agent "Train" found ' . $this->trainTime($train_time) . ' in existing train #' . $thing->index . ' (' . $this->trainTime($thing->run_at) . " " . $thing->runtime . ').');
                $match = true;
                break; //Take first matching block.   Because this will be the last referenced train.

            }
*/
        }

        if ($this->trains == []) {
            $refreshed_at = $this->current_time;
            $train = [
                "state" => 'red',
                "index" => 1,
                "headcode" => '0z99',
                "flag" => 'green',
                "runat" => [
                    "day" => 'X',
                    "hour" => 'X',
                    "minute" => 'X',
                ],
                "endat" => [
                    "day" => 'X',
                    "hour" => 'X',
                    "minute" => 'X',
                ],
                "runtime" => 'X',
                "alias" => 'Train',
                "available" => 'Z',
                "quantity" => 'X',
                "route" => 'X',
                "consist" => 'X',
                "refreshed_at" => $refreshed_at,
            ];

            $this->trains[] = $train;
        }


    }

    public function selectTrain()
    {
        if (!isset($this->trains)) {
            $this->getTrains();
        }
        // First check to see if the provided head_code is in the list.
        $train = null;
        if (isset($this->head_code)) {
            foreach ($this->trains as $train) {
                if ($train['head_code'] == $this->head_code) {
                    //$this->train_thing = $train;
                    $match = true;
                    break;
                }
            }
        }
        if ($match != true) {
            foreach ($this->trains as $train) {
                //// If the train time is in the run period of the train
                //// then this is a valid train to be running right now.
                if (
                    strtotime($train_time) >= strtotime($thing->run_at) and
                    strtotime($train_time) <= strtotime($thing->end_at)
                ) {
                    $this->thing->log(
                        'Agent "Train" found ' .
                            $this->trainTime($train_time) .
                            ' in existing train #' .
                            $thing->index .
                            ' (' .
                            $this->trainTime($thing->run_at) .
                            " " .
                            $thing->runtime .
                            ').'
                    );
                    $match = true;
                    break; //Take first matching block.   Because this will be the last referenced train.
                }
            }
        }

        switch (true) {
            case $match != false:
                $this->thing->log($this->agent_prefix . "found a valid train.");
                $this->info = "current train retrieved";
                $this->response .= "Retrieved the current train. ";
                // Load the Train into this Thing.
                //$this->train_thing = $trea;

                // No nead to do this because the read agent will do.
                $this->index = $train['index'];
                $this->alias = $train['alias'];
                $this->head_code = $train['head_code'];
                $this->run_at = $train['run_at'];

                $this->runtime = $train['runtime'];
                $this->quantity = $train['quantity'];

                $this->route = $train['route'];
                $this->consist = $train['consist'];

                $this->available = $this->getAvailable();
                $this->end_at = $this->getEndat();

                $this->train_thing = $this->thing;

                $this->get();

                //$this->variables_agent = new Variables($this->train_thing,"variables " . $this->default_train_name . " " . $this->from);
                //$this->train_thing = true;
                break;

            case $match == false:
                // Recent train.  Perhaps running late?
                $train_thing = $findagent_thing->thing_report['things'][0];
                $this->info = "last train retrieved";
                $this->response .= "Retrieved the last train. ";
                // No valid train found, so make a block record in current Thing
                // and set flag to Green ie accepting trains.
                $this->thing->log(
                    'Agent "Train" did not find a valid train at traintime ' .
                        $this->trainTime($train_time) .
                        "."
                );

                $thing = new Thing($train_thing['uuid']);
                $this->train_thing = $thing;

                $thing->json->setField("variables");

                $this->index = $thing->getVariable("train", "index");
                if ($this->index > $this->max_index) {
                    $this->max_index = $this->index;
                }

                $this->head_code = $thing->getVariable("train", "head_code");
                $this->alias = $thing->getVariable("train", "alias");

                $this->run_at = $thing->getVariable("train", "run_at");
                $this->quantity = $thing->getVariable("train", "quantity");
                $this->available = $thing->getVariable("train", "available");
                $this->refreshed_at = $thing->getVariable(
                    "train",
                    "refreshed_at"
                );

                $this->route = $thing->getVariable("train", "route");
                $this->consist = $thing->getVariable("train", "consist");
                $this->runtime = $thing->getVariable("train", "runtime");

                $this->available = $this->getAvailable();
                $this->end_at = $this->getEndat();

                //                $this->train_thing = $thing;
                $this->thing->log(
                    'got last train ' .
                        $this->trainTime($train_time) .
                        ' in existing train #' .
                        $this->index .
                        ' (' .
                        $this->trainTime($this->runat) .
                        " " .
                        $this->runtime .
                        ').'
                );

                break;
            case false:
                $this->info = "special created";
                $this->response .= "Created a special train. ";
                $this->train_thing = $this->thing;
                $this->get();
                $this->train_thing->index = $this->max_index + 1;
                $this->head_code = "2Z" . rand(20, 29);
                $this->run_at = $this->current_time;
                $this->runtime = 22;
                break;

            default:
                $this->info = "bork";
                $this->train_thing = $this->thing;
                $this->head_code = "BORK";
        }

        /*

        // Set-up empty block variables.
        $this->flagposts = array();
        $this->trains = array();
        $this->bells = array();

            $this->train_thing->json->setField("associations");
            $this->associations = $this->train_thing->json->readVariable( array("agent") );

            foreach ($this->associations as $association_uuid) {

                $association_thing = new Thing($association_uuid);

                $association_thing->json->setField("variables");
                $this->flagposts[] = $association_thing->json->readVariable( array("flagpost") );

                $association_thing->json->setField("variables");
                $this->trains[] = $association_thing->json->readVariable( array("train") );

                $association_thing->json->setField("variables");
                $this->bells[] = $association_thing->json->readVariable( array("bell") );

            }


*/

        return $this->train_thing;
    }

    function dropTrain()
    {
        $this->thing->log("was asked to drop a train.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset block thing.

        // So this is currently dropping the current Thing not the Train
        // I think.
        // So take it out of the command roster. 1803 12 Nov

        // Dropping a Train means to
        // Stop running the current train.

        // And if no Train is running?
        // Is there a concept of a scheduled train?

        if (isset($this->train_thing)) {
            $this->train_thing->Forget();
            $this->train_thing = null;
        }

        //$this->get();

        //return;
    }

    function runTrain($headcode = null)
    {
        if ($headcode == null) {
            $headcode = $this->head_code;
        }

        //$this->head_code = "0Z" . $this->index;
        //$n = rand(1,49);
        //$n = str_pad($n, 2, '0', STR_PAD_LEFT);

        //$this->head_code = "5Z".$n;

        //if ($this->quantity == 0) {$this->quantity = 45;}
        //$this->runtime = 22;
        //$this->getAvailable();

        //if (!isset($this->head_code)) {
        //            $n = rand(1,49);
        //            $n = str_pad($n, 2, '0', STR_PAD_LEFT);
        //            $this->head_code = "5Z".$n;
        //    $this->getHeadcode();
        //}

        if (!isset($this->run_at)) {
            // get and extract neither found anything
            //$this->getRunat();
            $this->response .= "Set run at time to now. ";
            $this->run_at = $this->current_time;
        }

        if (!isset($this->runtime)) {
            // get and extract neither found anything
            //$this->getRuntime();
            $this->response .= "Set runtime to default. ";
            $this->runtime = 22;
        }

        $this->response .= "Runtime " . $this->runtime . ". ";
        $this->response .=
            "Runat " . $this->run_at->hour . " " . $this->run_at->minute . ". ";
        $this->response .= "Headcode is " . $this->head_code . ". ";

        $this->makeTrain(
            $this->head_code,
            $this->alias,
            $this->current_time,
            $this->runtime
        );

        $this->state = "running";

        //$this->makeTrain($this->current_time, $this->quantity, $this->available);
    }
    /*
    function getAlias() {

        $this->alias = "";
        return $this->alias;

        if ( (isset($this->alias)) and ($this->alias != false)) {
            return $this->alias;
        }

        $this->aliases = array("Logans run", "Kessler Run", "Orient Express", "Pineapple Express",
            "Dahjeeling Express", "Flying Scotsman", "Gilmore Special", "Rocky Mountaineer",
            "Atlantic","Alouette","The Ambassador","Atlantic Express","Atlantic Limited");

        $this->alias_thing = new \Nrwtaylor\StackAgentThing\Alias($this->train_thing, 'alias');

        $this->alias = $this->alias_thing->alias;

        // If it is still false assign an alias.
        if ($this->alias == false) {
            $k = array_rand($this->aliases);
            $this->alias = $this->aliases[$k];
        $this->alias_thing = new Alias($this->train_thing, 'alias is ' . $this->alias);

        }

//           $this->alias = "Orient Express";
        return $this->alias;
    }
*/
    function assertTrain($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "train is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("train is"));
        } elseif (($pos = strpos(strtolower($input), "train")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("train"));
        }
        $filtered_input = ltrim(strtolower($whatIWant), " ");

        list($head_code, $alias) = $this->idTrain($filtered_input);

        if ($head_code == null) {
            $this->makeTrain(null, $filtered_input);
        } else {
            $this->head_code = $head_code;
            $this->parseTrain();
        }
    }

    function makeTrain(
        $head_code,
        $alias = null,
        $run_at = null,
        $runtime = null
    ) {
        //        if ($alias == null) {$alias = "X";}

        // See if the code or name already exists
        foreach ($this->trains as $train) {
            if ($head_code == $train['headcode'] or $alias == $train['alias']) {
                $this->alias = $train['alias'];
                $head_code = $train['headcode'];
                $this->last_refreshed_at = $train['refreshed_at'];
            }
        }

        if ($alias == null) {
            $this->getAlias();
            $alias = $this->alias;
        }

        if ($head_code == null) {
            $this->getHeadcode();
            $head_code = $this->head_code;
        }

        if ($run_at == null) {
            $this->getRunat();

            if ($this->runat->minute == "X" or $this->runat->hour == "X") {
                $this->runat_agent->extractRunat($this->current_time);
            }

            $runat_day = $this->runat_agent->day;
            $runat_hour = $this->runat_agent->hour;
            $runat_minute = $this->runat_agent->minute;
        }

        $runtime_minutes = "X";
        if ($runtime == null) {
            $this->getRuntime(); // which is runtime
            if (!isset($this->runtime) or strtoupper($this->runtime) == "X") {
                $this->runtime = 22;
            }
            $runtime_minutes = $this->runtime;
        }

        //        $this->getAlias();

        //if ($this->verbosity > 2) {
            //$this->getRoute();
            //$this->getConsist();
        //}

        $this->state = "stopped";

        if ($runtime_minutes == "X") {
            $runtime_minutes = 45;
        }

        if ($runat == "X") {
            $runat = $this->current_time;
        }

        $this->getAvailable();

        $this->thing->log(
            'will make a Train with ' .
                $this->trainTime($run_at) .
                " " .
                $runtime_minutes .
                " " .
                $this->runtime .
                "."
        );

        $shift_override = true;
        $shift_state = "off";
        if (
            $shift_state == "off" or
            $shift_state == "null" or
            $shift_state == "" or
            $shift_override
        ) {
            // Only if the shift state is off can we
            // create blocks on the fly.

            // Otherwise we needs to make trains to run in the block.

            $this->thing->log(
                $this->agent_prefix . "found that this is the Off shift."
            );

            // So we can create this block either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->runat->day = $runat_day;
            $this->runat->hour = $runat_hour;
            $this->runat->minute = $runat_minute;
            $this->runtime = $runtime_minutes;
            $this->alias = $alias;

            //            $this->getEndat();
            //            $this->getAvailable();
        } else {
            $this->thing->log(
                $this->agent_prefix .
                    " checked the shift state: " .
                    $shift_state .
                    "."
            );
            // ... and decided there was already a shift running ...
            //            $this->run_at = "meep"; // We could probably find when the shift started running.
            $this->runat->day = "X";
            $this->runat->hour = "X";
            $this->runat->minute = "X";
            $this->runtime = "X";
            $this->available = "X";
            $this->end_at = "X";

            $this->alias = "MERP";
        }

        // So at this point $this->start_at, $this->end_at, $this->quantity,
        // $this->available, have all be established.

        //$this->getEndat();

        $this->getAvailable();
        $this->getEndat();

        //$this->set();

        $this->thing->log('found a run_at and a runtime and made a Train.');
    }

    function trainTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (is_object($input)) {
            $train_time = "X";
            return $train_time;
        }
        if (is_array($input)) {
            if (isset($input['hour']) and isset($input['minute'])) {
                if ($input['hour'] != false and $input['minute'] != false) {
                    $this->hour = $input['hour'];
                    $this->minute = $input['minute'];
                    $train_time =
                        $this->hour . $this->time_seperator . $this->minute;
                    return $train_time;
                }

                $train_time = "X";
                return $train_time;
            }
        }

        if (strtoupper($input) == "X") {
            $train_time = "X";
            return $train_time;
        }

        if (strtoupper($input) == "X") {
            $train_time = "X";
            return $train_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $train_time = $this->hour . $this->time_seperator . $this->minute;

        if ($input == null) {
            $this->train_time = $train_time;
        }

        return $train_time;
    }

    function trainDay($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (is_array($input)) {
            if (isset($input['day'])) {
                $train_day = $input['day'];
                return $train_day;
            }
        }

        if (strtoupper($input) == "X") {
            $train_day = "X";
            return $train_day;
        }

        $t = strtotime($input_time);

        //$train_day = "MON";

        $date = $input_time;
        $day = 1;
        $days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        $this->day = date('l', strtotime($input_time));
        $train_day = $this->day;

        if ($input == null) {
            $this->train_day = $train_day;
        }

        return $train_day;

    }

    function extractEndat()
    {
        if (!isset($this->events)) {
            $this->extractEvents($this->subject);
        }

        // If there is only one time, it is the run_at time

        if (is_array($this->events) and count($this->events) == 2) {
            //if (count($this->events) == 2) {
            $this->end_at = $this->events[1];
            $this->num_hits += 2;
            return $this->end_at;
        }

        $this->end_at = "X";
        return $this->end_at;
    }

    /*
    function getEndat()
    {
        // Avoid ping pong if no variables set.
        if (!isset($this->run_at) and !isset($this->runtime)) {
            $this->end_at = "X";
            return $this->end_at;
        }

        if (!isset($this->run_at)) {
            $this->getRunat();
        }

        if (!isset($this->runtime->minutes)) {
            $this->getRuntime();
        }

        switch (true) {
            case strtoupper($this->runat->hour) == "X" or
                strtoupper($this->runat->minute) == "X":
                // No runat available.  So endtime is X
                $this->end_at = "X";
                break;
            //case ($this->runtime == false):
            //    $this->end_at = "X";
            //    break;
            case strtoupper($this->runtime->minutes) == "X":
                // No runat available.  So endtime is X
                $this->end_at = "X";
                break;

            case strtoupper($this->runtime->minutes) == "Z":
                // No runat available.  So endtime is X
                $this->end_at = "X";
                break;

            default:

                //if ((!isset($this->runtime)) or ($this->runtime == false)) {$this->runtime = new Runtime($this->thing, "runtime 0");}

                // Trying to get this empty class creation fixed.

                $t =
                    $this->runat->hour . ":" . $this->runat->minute;

                $this->end_at = $this->thing->time(
                    strtotime($t . " + " . $this->runtime->minutes . " minutes")
                );
        }
        //        $this->end_at = "X";
        return $this->end_at;
    }
*/

    function getEndat()
    {
        $this->endat_agent = new Endat($this->train_thing, "endat");

        $day = $this->endat_agent->day;
        $hour = $this->endat_agent->hour;
        $minute = $this->endat_agent->minute;

        if (!isset($this->endat)) {
            $this->endat = new \stdClass();
        }

        $this->endat->day = $day;
        $this->endat->hour = $hour;
        $this->endat->minute = $minute;
    }

    function extractRunat($input)
    {
        if (!isset($this->runat_agent)) {
            $this->runat_agent = new Runat($this->train_thing, "runat");
        }

        $this->runat_agent->extractRunat($input);
        return;
        //$runat_agent = new Runat($this->variables_agent->thing, "runat");

        if (!isset($this->events)) {
            $this->extractEvents($this->subject);
        }

        if (is_array($this->events) and count($this->events) == 1) {
            //        if (count($this->events) == 1) {
            $this->run_at = $this->events[0];
            $this->num_hits += 1;
            return $this->run_at;
        }

        if (is_array($this->events) and count($this->events) == 2) {
            //        if (count($this->events) == 2) {
            $this->run_at = $this->events[0];
            $this->num_hits += 2;
            return $this->run_at;
        }

        $this->run_at = "X";

        return $this->run_at;
    }

    function setRunat()
    {
        //$this->runat_agent->timeRunat();

        $day = "X";
        if (isset($this->day)) {
            $day = $this->day;
        }
        $this->runat_agent->day = $day;

        $this->runat_agent->hour = $this->hour;
        $this->runat_agent->minute = $this->minute;

        $this->runat_agent->set();

        $t = new Runat($this->thing, "runat");

//        $t->day = $this->runat['day'];
//        $t->hour = $this->runat['hour'];
//        $t->minute = $this->runat['minute'];

        $t->day = $this->runat->day;
        $t->hour = $this->runat->hour;
        $t->minute = $this->runat->minute;



        $t->set();
    }

    function getRunat()
    {
        if (!isset($this->runat_agent)) {
            $this->runat_agent = new Runat($this->train_thing, "runat");
        }

        $day = $this->runat_agent->day;
        $hour = $this->runat_agent->hour;
        $minute = $this->runat_agent->minute;

        //        $this->runat = ["day" => $day, "hour" => $hour, "minute" => $minute];

        if (!isset($this->runat)) {
            $this->runat = new \stdClass();
        }

        $this->runat->day = $day;
        $this->runat->hour = $hour;
        $this->runat->minute = $minute;

        return;

        if (!isset($this->end_at) and !isset($this->runtime)) {
            if (!isset($this->runat)) {
                $this->hour = "X";
                $this->minute = "X";
            }
            return $this->runat;
        }

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        if (!isset($this->runtime)) {
            $this->getRuntime();
        }

        switch (true) {
            case strtoupper($this->end_at) != "X" and
                strtoupper($this->end_at) != "Z":
                $this->runat = strtotime(
                    $this->end_at . "-" . $this->runtime . "minutes"
                );

                break;
            default:
                $this->runat = $this->trainTime();
        }

        return $this->runat;
    }

    function getAvailable()
    {
        $this->available = "X";
        return;
        $available_agent = new Available($this->thing, "train");

        if (!isset($this->runat_agent)) {
            $this->runat_agent = new Runat($this->thing, "runat");
        }
        // Calculate the amount of time remaining for the train

        if (!isset($this->runat) and !isset($this->endat)) {
            if (!isset($this->available)) {
                $this->available = "X";
            }
        }

        if (!isset($this->runat)) {
            $this->getRunat();
        }

        if (!isset($this->runtime)) {
            $this->getRuntime();
        }

        if (!isset($this->endat)) {
            $this->getEndat();
        }
        $run_at_flag = false;

        if (strtoupper($this->runat->day) == "X") {
            $run_at_flag = true;
        }
        if (strtoupper($this->runat->minute) == "X") {
            $run_at_flag = true;
        }
        if (strtoupper($this->runat->hour) == "X") {
            $run_at_flag = true;
        }

        if ($this->runtime == "X" or $run_at_flag === true) {
            $this->response .=
                "The runtime and/or run at time is not specified. ";
            $this->available = "Z";
            return $this->available;
        }

        switch (true) {
            case $run_at_flag:
                // No runtime available.  So what
                // is available, is what there is...
                $this->available = "Z";
                break;
            case strtotime($this->current_time) <
                $this->runat_agent->timeRunat():
                // Current time is before the run at time.
                // So the full amount of time is available.
                $this->available = strtotime($this->endat) - $this->runat;
                break;
            case strtotime($this->current_time) >
                $this->runat_agent->timeRunat():
                // Current time is after the run time.
                // Return the number of minutes until
                // the end time.
                // Negative is how late the train is.
                $this->available =
                    $this->endat_agent->timeRunat() -
                    strtotime($this->current_time);

                break;
            default:
                $this->available = "X";
        }

        $this->thing->log(
            'Agent "Train" identified ' .
                $this->available .
                ' resource units available.'
        );

        return $this->available;
    }

    function setRuntime()
    {
        $this->runtime_agent->runtime = $this->runtime;
        $this->runtime_agent->set();

        $t = new Runtime($this->train_thing, "runtim");
        $t->runtime = $this->runtime;

        $t->set();
    }

    function getRuntime()
    {
        $this->runtime_agent = new Runtime($this->train_thing, "runtime");

        $this->runtime = $this->runtime_agent->runtime;
        if ($this->runtime_agent->runtime == false) {
            $runtime = "X";
        }
        $this->runtime = $runtime;

        return;

        // Because an Agent hasn't been written yet.
        // This will kind of cover Things until then.

        if (!isset($this->headcode_thing)) {
            $this->getHeadcode();
        }

        $this->runtime = new Runtime(
            $this->train_thing,
            "runtime " . $this->head_code
        );

        //$runtime = $this->headcode_thing->runtime; //which is runtime

        //        $runtime = $this->runtime->minutes;
        $runtime = $this->runtime;

        // Which can be <number>, "X" or "Z".
        if (strtoupper($runtime) == "X") {
            // Train must specifiy runtime.
            if (!isset($this->runtime)) {
                $this->runtime = "X";
            }
        }

        if (strtoupper($runtime) == "Z") {
            // Train must specifiy runtime.
            $this->runtime = "Z";
        }

        if (is_numeric($runtime)) {
            // Train must specifiy runtime.
            $this->runtime = $runtime;
        }
        return $this->runtime;
    }

    function setQuantity()
    {
        $this->quantity_agent->quantity = $this->quantity;
        $this->quantity_agent->set();

        $t = new Quantity($this->thing, "quantity");
        $t->quantity = $this->quantity;
        $t->set();
    }

    function getQuantity()
    {
        $this->quantity_agent = new Quantity($this->train_thing, "quantity");
        $this->quantity = $this->quantity_agent->quantity;

        //        $runtime = $this->getRuntime();
        //        $this->quantity = $runtime->minutes;
        return $this->quantity;
    }

    function getConsist()
    {
        $this->consist_agent = new Consist($this->train_thing, "consist");

        if ($this->consist_agent->consist !== false) {
            $this->consist = implode('',$this->consist_agent->consist['vehicles']);
        }

        if (!isset($this->consist)) {$this->consist = "X";}

        return $this->consist;
    }

    function getRoute()
    {
        $this->route_agent = new Route($this->train_thing, "route");
        if ($this->route_agent->route !== false) {
            $this->route = implode(' > ',$this->route_agent->route['places']);
        }
        if (!isset($this->route)) {$this->route = "X";}

        return $this->route;
    }

    function extractHeadcode($text = null)
    {
        //if (!isset($this->head_code)) {
        //    $n = rand(50,99);
        //    $this->head_code = "1Z" . $n;
        //}

        $this->headcode_thing = new Headcode($this->thing, 'extract');
        $this->requested_head_code = $this->headcode_thing->extractHeadcode(
            $text
        );

        return $this->requested_head_code;
    }

    function setHeadcode()
    {
        // May not be necessary. But...
        $this->headcode_thing->set();
    }

    function getHeadcode()
    {
        // This will trigger a request from the Agent
        // to return the current Headcode.

        //if (!isset($this->head_code)) {
        //    $n = rand(50,99);
        //    $this->head_code = "1Z" . $n;
        //}

        $this->headcode_thing = new Headcode($this->train_thing, 'headcode');
        $this->head_code = $this->headcode_thing->head_code;

        if ($this->head_code === false or $this->head_code === true) {
            $n = rand(1, 49);
            $n = str_pad($n, 2, '0', STR_PAD_LEFT);
            $this->head_code = "5Z" . $n;
            $this->setHeadcode();
            $this->response .= 'Set headcode ' . $this->head_code . ". ";
        }

        return $this->head_code;
    }

    function setAlias()
    {
        if (!isset($this->alias_agent)) {
            $this->alias_agent = new Alias($this->train_thing, 'alias');
        }

        $this->alias_agent->alias = $this->alias;

        $this->alias_agent->set();

        $t = new Alias($this->thing, "alias");
        $t->alias = $this->alias;
        $t->set();
    }

    function getAlias()
    {
        // This will trigger a request from the Agent
        // to return the current Headcode.

        //if (!isset($this->head_code)) {
        //    $n = rand(50,99);
        //    $this->head_code = "1Z" . $n;
        //}

        $this->alias_agent = new Alias($this->train_thing, 'alias');
        $this->alias = $this->alias_agent->alias;

        // devstack.
        // Figure this out.
        //        $this->alias = strtoupper($this->alias_agent->alias_id);

        $this->aliases = [
            "Logans run",
            "Kessler Run",
            "Orient Express",
            "Pineapple Express",
            "Dahjeeling Express",
            "Flying Scotsman",
            "Gilmore Special",
            "Rocky Mountaineer",
            "Atlantic",
            "Alouette",
            "The Ambassador",
            "Atlantic Express",
            "Atlantic Limited",
        ];

        if ($this->alias === false or $this->alias === true) {
            //$this->alias = "Blank";

            $k = array_rand($this->aliases);
            $this->alias = $this->aliases[$k];

            $this->alias_agent->set();
            $this->response .= 'Set alias ' . $this->alias . ". ";
        }

        return $this->alias;
    }

    function setIndex()
    {
        if (!isset($this->index_agent)) {
            $this->index_agent = new Index($this->train_thing, 'index');
        }

        $this->index_agent->index = $this->index;
        $this->index_agent->set();

        $t = new Index($this->thing, "index");
        $t->index = $this->index;
        $t->set();
    }

    function getIndex()
    {
        // This will trigger a request from the Agent
        // to return the current train index.

        $this->index_agent = new Index($this->train_thing, 'index');
        $this->index = $this->index_agent->index;

        if ($this->index === false or $this->index === true) {
            $this->index = 1;
            $this->index_agent->set();
            $this->response .= 'Set index ' . $this->index . ". ";
        }

        return $this->index;
    }

    function nextHeadcode()
    {
        // This will trigger a request from the Agent
        // to return the current Headcode.

        if (!isset($this->head_code)) {
            $n = rand(50, 99);
            $this->head_code = "1Z" . $n;
        }

        $this->headcode_thing = new Headcode(
            $this->variables_agent->thing,
            'headcode ' . $this->head_code
        );

        return $this->head_code;
    }

    function getFlag()
    {
        $this->flag_agent = new Flag($this->train_thing, 'flag');
        //$this->flag = $this->flag_thing->state;
        $flag = $this->flag_agent->state;
        if ($this->flag_agent->state === false) {
            $flag = "X";
        }
        $this->flag = $flag;
        return $this->flag;
    }

    function setFlag($colour)
    {
        //$this->runat_agent->timeRunat();

        //$this->flag->state = $this->flag;

        $this->flag_agent->set();

        $t = new Flag($this->thing, "flag");
        $t->state = $this->flag;
        $t->set();
    }

    function trains()
    {
    }

    function addTrain()
    {
        $this->makeTrain(null);
        //$this->get();
        //return;
    }

    function setState($input)
    {
        switch ($input) {
            case "red":
                if (
                    $this->state == "green" or
                    $this->state == "yellow" or
                    $this->state == "yellow yellow" or
                    $this->state == "X"
                ) {
                    $this->state = "red";
                }
                break;

            case "green":
                if ($this->state == "red" or $this->state == "X") {
                    $this->state = "green";
                }

                break;
        }

        return;
    }

    function reset()
    {
        $this->thing->log("reset");

        // Set elapsed time as 0 and state as stopped.
        $this->elapsed_time = 0;
        $this->thing->choice->Create('train', $this->node_list, 'red');
        /*
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("stopwatch", "refreshed_at"), $this->current_time);
        $this->thing->json->writeVariable( array("stopwatch", "elapsed"), $this->elapsed_time);
*/
        $this->thing->choice->Choose('start');

        //$this->set();

        return $this->quantity_available;
    }

    function stop()
    {
        $this->thing->log("stop");
        $this->thing->choice->Choose('red');
        //$this->set();
        //                $this->elapsed_time = time() - strtotime($time_string);
        return $this->quantity_available;
    }

    function start()
    {
        $this->thing->log("start");

        if ($this->previous_state == 'stop') {
            $this->thing->choice->Choose('start');
            $this->state = 'start';
            //$this->set();
            return;
        }

        if ($this->previous_state == 'start') {
            $t =
                strtotime($this->current_time) - strtotime($this->refreshed_at);

            $this->elapsed_time = $t + strtotime($this->elapsed_time);
            return;
        }

        $this->thing->choice->Choose('start');
        $this->state = 'start';
    }

    function makeTXT()
    {
     //   $txt =
     //       'This is a TRAIN for RAILWAY ' .
     //       $this->variables_agent->nuuid .
     //       '. ';
     //   $txt .= "\n";
    //    return;
$txt = "";
        if (!isset($this->trains)) {
            $this->getTrains();
        }
        $count = 0;
        if (is_array($this->trains)) {
            $count = count($this->trains);
        }

        $txt .= $count . ' Trains retrieved.';

        $txt .= "\n";

        $txt .= 'CURRENT TRAIN';
        $txt .= "\n";

        $txt .= str_pad($this->index, 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad($this->head_code, 4, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad($this->alias, 10, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad($this->flag, 6, " ", STR_PAD_LEFT);

$day = "X";
if (isset($this->day)) {
$day = $this->day;
}
        $txt .= " " . str_pad($day, 4, " ", STR_PAD_LEFT);

$run_at = "X";
if (isset($this->run_at)) {
$run_at = $this->run_at;
}


        $txt .= " " . str_pad($run_at, 6, " ", STR_PAD_LEFT);

$end_at = "X";
if (isset($this->end_at)) {
$end_at = $this->end_at;
}


        $txt .= " " . str_pad($end_at, 6, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad($this->runtime, 8, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad($this->available, 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad($this->quantity, 9, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad($this->consist, 6, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad($this->route, 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        $txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("HEAD", 4, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("ALIAS", 10, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("FLAG", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("DAY", 4, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("RUNAT", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("ENDAT", 6, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("RUNTIME", 8, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("AVAILABLE", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("QUANTITY", 9, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("CONSIST", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("ROUTE", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        foreach ($this->trains as $key => $train) {
            //$txt .= implode(" ", $train);

            $index_text = str_pad("X", 7, 'X', STR_PAD_LEFT);
            if (isset($train['index']['index'])) {
                $index_text = str_pad(
                    $train['index']['index'],
                    7,
                    '0',
                    STR_PAD_LEFT
                );
            }

            $txt .= $index_text;

            $text_headcode = "XXXX";
            //headcode", "head_code"
            if (isset($train['headcode']['head_code'])) {
                $text_headcode =
                    " " .
                    str_pad(
                        strtoupper($train['headcode']['head_code']),
                        4,
                        "X",
                        STR_PAD_LEFT
                    );
            }

            $txt .= $text_headcode;

            $alias_text = str_pad(" ", 10, 'X', STR_PAD_RIGHT);

            if (isset($train['alias']['alias'])) {
                $alias_text =
                    " " .
                    str_pad($train['alias']['alias'], 10, " ", STR_PAD_RIGHT);
            }

            $txt .= $alias_text;

            $flag_text = str_pad(" ", 6, 'X', STR_PAD_RIGHT);
            if (isset($train['flag']['state'])) {
                $flag_text =
                    " " .
                    str_pad($train['flag']['state'], 6, " ", STR_PAD_RIGHT);
            }

            $txt .= $flag_text;

$day = "X";
if (isset($train['run_at'])) {
            $day = strtoupper(substr($this->trainDay($train['run_at']), 0, 3));
}

            $txt .= " " . str_pad($day, 4, " ", STR_PAD_LEFT);

            $run_at_text = str_pad("XXXX", 6, " ", STR_PAD_LEFT);

            if (isset($train['run_at'])) {
                $run_at_text =
                    " " .
                    str_pad(
                        $this->trainTime($train['run_at']),
                        6,
                        " ",
                        STR_PAD_LEFT
                    );
            }

            $txt .= $run_at_text;


$train_time_end_at = "X";
if  (isset($train['end_at'])) {

$train_time_end_at =$train['end_at'];
}



            $txt .=
                " " .
                str_pad(
                    $this->trainTime($train_time_end_at),
                    6,
                    " ",
                    STR_PAD_LEFT
                );

            if (isset($train['runtime']['minutes'])) {
                $txt .=
                    " " .
                    str_pad($train['runtime']['minutes'], 8, " ", STR_PAD_LEFT);
            }
            $txt .= " " . str_pad($train['available'], 6, " ", STR_PAD_LEFT);

            $quantity_text = " " . str_pad(" ", 9, " ", STR_PAD_LEFT);

            if (isset($train['runtime']['minutes'])) {
                $quantity_text =
                    " " .
                    str_pad(
                        $train['quantity']['quantity'],
                        9,
                        " ",
                        STR_PAD_LEFT
                    );
            }

            $txt .= $quantity_text;
            $txt .= " " . str_pad($train['consist'], 6, " ", STR_PAD_LEFT);
            if (isset($train['route']['route'])) {
                $txt .=
                    " " .
                    str_pad($train['route']['route'], 6, " ", STR_PAD_LEFT);
            }

            $txt .= "\n";
        }



/*

        $table = [];


        foreach ($this->trains as $key => $train) {

            $head_code = "X";
            if (isset($train['headcode'])) {
                $head_code = $train['headcode'];
            }
            $table[$head_code] = [];
            $table[$head_code]['head_code'] = $head_code;


            $index = 'X';
            if (isset($train['index'])) {
                  $index = $train['index'];
            }
            $table[$head_code]['index'] = $index;

            $alias = "X";
            if (isset($train['alias'])) {
                $alias = $train['alias'];
            }
            $table[$head_code]['alias'] = $alias;


            $state = "X";
            if (isset($train['state'])) {
                $state =$train['state'];
            }
            $table[$head_code]['flag'] = $state;

            $flag = "X";
            if (isset($train['state'])) {
                $flag =$train['state'];
            }
            $table[$head_code]['flag'] = $flag;

            $run_at = "X";
            if (isset($train['runat'])) {
                $run_at = $this->trainTime($train['runat']);
            }
            $table[$head_code]['run_at'] = $run_at;


            $end_at = "X";
            if (isset($train['endat'])) {
                $end_at = $this->trainTime($train['endat']);
            }
            $table[$head_code]['end_at'] = $end_at;


            $runtime = "X";
            if (isset($train['runtime'])) {
                $runtime = $train['runtime'];
            }
            $table[$head_code]['runtime'] = $runtime;



            $available = $train['available'];
            $table[$head_code]['available'] = $available;


            $quantity = " ";
            if (isset($train['quantity'])) {
                $quantity = $train['quantity'];
            }
            $table[$head_code]['quantity'] = $quantity;

            $consist = $train['consist'];
            $table[$head_code]['consist'] = $consist;

            if (isset($train['route'])) {
               $route = $train['route'];
            }
            $table[$head_code]['route'] = $route;

        }
        $table_agent = new Table($this->thing, "table");

        $table_layout = [
            'index' => ['INDEX' => 7],
            'head_code' => ['HEAD' => 4],
            'alias' => ['ALIAS' => 10],
            'state' => ['STATE'=>6],
            'flag' => ['FLAG' => 6],
            'day' => ['DAY' => 4],

            'run_at' => ['RUNAT' => 6],
            'end_at' => ['ENDAT' => 6],

            'runtime' => ['RUNTIME' => 8],

            'available' => ['AVAILABLE' => 6],
            'quantity' => ['QUANTITY' => 9],
            'consist' => ['CONSIST' => 6],
            'route' => ['ROUTE' => 6],
        ];

$table_agent->table = $table_layout;






$table_text = $table_agent->textTable($table);
$txt .= $table_text;



*/






        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function getState()
    {
        if (isset($this->requested_state)) {
            $this->state = $this->requested_state;
        } else {
            if (!isset($this->previous_state)) {
                $this->previous_state = "train";
            }

            $this->state = $this->previous_state;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = $this->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;

        $this->message = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 18 Feb 2018 - testing if this works for email;

        $this->thing_report['info'] = "Took a look at the train.";
        //        if ($this->agent_input == null) {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
        //       }

        $this->thing_report['help'] =
            'This is a Train. Trains have Flags.  Messaging RED will show the Red Flag.  Messaging GREEN will show the Green Flag.';
    }

    function textTrain($array = null)
    {
        if ($array == null) {
            $array = $this;
        }
        $txt = "";

        foreach ($this->agents as $i => $agent_name) {
            $variable_name = strtolower($agent_name);

            $capitalize_flag = true;

            if (isset($this->{$variable_name})) {
                if ($this->{strtolower($agent_name)} === false) {
                    continue;
                }

                if ((strtolower($agent_name) == 'consist') or 
(strtolower($agent_name) == 'route')) {
                    $capitalize_flag = false;
                }

                $txt .= $agent_name . " ";

                if (is_string($this->{$variable_name})) {
                    $text_string = $this->{strtolower($agent_name)};
                    if ($capitalize_flag === true) {
                        $text_string = strtoupper($text_string);
                    }
                    $txt .= $text_string . " ";
                    continue;
                }

                if (is_array($this->{$variable_name})) {
                    $text_string = trim(implode(" ", $this->{$variable_name}));
                    if ($capitalize_flag === true) {
                        $text_string = strtoupper($text);
                    }
                    $txt .= $text_string . " ";
                    continue;
                }

                if (is_object($this->{$variable_name})) {
                    $agent_variable = (array) $this->{$variable_name};
                    $text_string = trim(implode(" ", $agent_variable));
                    if ($capitalize_flag === true) {
                        $text_string = strtoupper($text_string);
                    }
                    $txt .= $text_string . " ";
                    continue;
                }
            }
        }

        $txt .= "" . "now " . $this->trainTime();

        $txt = trim($txt);
        return $txt;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "train");
        $choices = $this->thing->choice->makeLinks('train');
        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    function makeWeb()
    {
        if (!isset($this->choices)) {
            $this->makeChoices();
        }

        $test_message = '<b>TRAIN ' . strtoupper($this->head_code);
        if ($this->alias != null) {
            $test_message .= ' "' . strtoupper($this->alias) . '"';
        }

        $test_message .= ' #' . $this->index . "</b>";
        $test_message .= "<p>";

        $test_message .= '<p><b>Railway Time</b>';
        $test_message .= '<br>' . $this->trainTime();

        if (isset($this->refreshed_at)) {
            $test_message .=
                '<br>refreshed at ' . $this->trainTime($this->refreshed_at);
        }

        $test_message .= "<p><b>Train Variables</b>";
        $test_message .= '<br>state ' . $this->state . '';
        $test_message .= '<br>flag ' . strtoupper($this->flag) . '';

        $this->getRoute();
        //$route_text = implode( ' > ', $this->route['places']);
        $test_message .= '<br>route ' . $this->route;

        $this->getConsist();
        $test_message .= '<br>consist ' . $this->consist;

        if (isset($this->jobs)) {
            $test_message .= '<br>jobs ' . $this->jobs;
        }

        $test_message .= "<p>";
        $test_message .= "<b>Schedule</b>";
        $test_message .= '<br>run_at ' . $this->trainTime($this->runat);
        $test_message .= '<br>end_at ' . $this->trainTime($this->endat);
        $test_message .= '<br>runtime ' . $this->runtime;

        //if (!isset($this->sms_message)) {$this->makeSMS;}
        $test_message .= '<p>';
        $test_message .= '<b>SMS Text</b>';
        $test_message .= '<br>' . $this->sms_message;

        $test_message .= '<p><b>Resources</b>';
        $test_message .= '<br>quantity ' . $this->quantity;
        $test_message .= '<br>available ' . $this->available;

        $test_message .= "<p><b>Agents</b>";
        $test_message .= "<br>" . $this->choices['link'];
        //$test_message .= '<br>current_node ' . $this->thing->choice->current_node;
        //$test_message .= "<br>" . $this->thing_report['choices']['button']; //words link button

        $test_message .=
            '<p>Agent "Train" is responding to your web view of datagram subject "' .
            $this->subject .
            '", ';
        $test_message .=
            "which was received " .
            $this->thing->human_time($this->thing->elapsed_runtime()) .
            " ago.";

        $this->web = $test_message;
        $this->thing_report['web'] = $test_message;
    }

    function makeEmail()
    {
        if (!isset($this->choices)) {
            $this->makeChoices();
        }

        $test_message =
            'Agent "Train" is responding to your email, subject line "' .
            $this->subject .
            '", ';

        $test_message .=
            "which was received " .
            $this->thing->human_time($this->thing->elapsed_runtime()) .
            " ago.";

        $test_message .= '<p>';
        $test_message .= '<br>TRAIN ' . strtoupper($this->head_code);
        $test_message .= ' ' . strtoupper($this->alias);
        $test_message .= ' ' . $this->index;

        $test_message .= "<br>";

        $test_message .= '<p>';
        $test_message .= "<br>Train Variables";
        $test_message .= '<br>state ' . $this->state . '';
        $test_message .= '<br>flag ' . strtoupper($this->flag) . '';

        //$route_text = implode( ' > ', $this->route['places']);
        $test_message .= '<br>route ' . $this->route;
        //$test_message .= '<br>route ' . $this->route;
        $test_message .= '<br>consist ' . $this->consist;

        if (isset($this->jobs)) {
            $test_message .= '<br>jobs ' . $this->jobs;
        }

        $test_message .= "<p>";
        $test_message .= "Schedule";
        $test_message .= '<br>run_at ' . $this->trainTime($this->runat);
        $test_message .= '<br>end_at ' . $this->trainTime($this->endat);
        $test_message .= '<br>runtime ' . $this->runtime;

        if (!isset($this->sms_message)) {
            $this->makeSMS();
        }
        $test_message .= '<p>';
        $test_message .= 'SMS Text';
        $test_message .= '<br>' . $this->sms_message;

        $test_message .= '<p>';
        $test_message .= '<br>Resources';
        $test_message .= '<br>quantity ' . $this->quantity;
        $test_message .= '<br>available ' . $this->available;

        if (isset($this->refreshed_at)) {
            $test_message .= '<br>refreshed_at ' . $this->refreshed_at;
        }

        $test_message .= '<p>';
        $test_message .= "<br>Choices";
        $test_message .= "<br>choices link " . $this->choices['link'];
        $test_message .=
            '<br>current_node ' . $this->thing->choice->current_node;
        $test_message .= "<br>" . $this->thing_report['choices']['button']; //words link button

        $test_message .= "<p>";
        $test_message .= 'End of Report';

        $this->email_message = $test_message;
        $this->thing_report['email'] = $test_message;
    }

    public function makeSMS()
    {
            $this->getRoute();
            $this->getConsist();
        //$sms = "TRAIN";
        //$this->sms_message = $sms;
        //$this->thing_report['sms'] = $sms;
        //return;
        $this->node_list = ["train"];

        $sms_message = "TRAIN ";
        $sms_message .= strtoupper($this->head_code);

        //      This line is not being accepted by FB Messenger !?
        //        $sms_message .= ' "' . strtoupper($this->alias). '"';
        //$this->getAlias();

        if ($this->alias != null) {
            //        $this->runtime = $this->runtime_agent->runtime;
            $sms_message .= " " . strtoupper($this->alias);
        }
        //$this->getAvailable();
        if ($this->r_type == 'instruction') {
            //$sms_message .= " false";

            if ($this->train_thing == false) {
                $sms_message .= " | train not running";
            } else {
                $sms_message .= " | ";

                if ($this->verbosity >= 2) {
                    $run_at = $this->trainTime($this->runat);
                    //if (!$this->thing->isData($run_at)) {$run_at = "X";}
                    $sms_message .=
                        "" . "run at " . $this->trainTime($this->runat);
                    $sms_message .= " " . "runtime " . $this->runtime;
                }

                if ($this->verbosity > 5) {
                    $sms_message .=
                        " " . "end at " . $this->trainTime($this->endat);
                    $sms_message .= " " . "now " . $this->trainTime();
                }
            }
        } else {
            $available_text = "X minutes. ";
            if (is_numeric($this->available)) {
                $available_text =
                    round($this->available / 60, 0) . ' minutes remaining. ';
            }
            if ($this->available == 'Z') {
                $available_text = 'Time is available. ';
            }

            if ($this->available == 'X') {
                $available_text = 'Specify the time remaining. ';
            }

            $sms_message .= " | " . $available_text;
        }

//        if ($this->verbosity > 2) {
/*
            if (!isset($this->route)) {
                $route = "X";
            } else {
                $route = $this->route;
            }

            if (!isset($this->consist)) {
                $consist = "Z";
            } else {
                $consist = $this->consist;
            }

            $route_description =
                $route . " [" . $consist . "] " . $this->runtime;
            $sms_message .= " | " . $route_description;
*/
            //     $sms_message .=
            //         " | nuuid " .
            //            $sms_message .=
            //         " | nuuid " .
            //          substr($this->variables_agent->variables_thing->uuid, 0, 4);
            //   substr($this->variables_agent->variables_thing->uuid, 0, 4);

//            $sms_message .=
//                " | nuuid " . substr($this->train_thing->uuid, 0, 4);
//        }

        if ($this->verbosity > 5) {
            $sms_message .=
                " | rtime " .
                number_format($this->thing->elapsed_runtime()) .
                "ms";
        }

        $sms_message .= $this->textTrain();

        //        if ($this->verbosity >= 1) {
        if (isset($this->response)) {
            $sms_message .= " | " . $this->response;
        }
        //        }

        if ($this->verbosity > 3) {
            if ($this->train_thing == false) {
                $sms_message .= " | MESSAGE RUN TRAIN";
            } else {
                $sms_message .= " | MESSAGE ?";
            }
        }

        // This below section needs to be refactored.
        // as Close Message.
        $postfix = "no";
        if ($postfix == "yes") {
            switch ($this->index) {
                case null:
                    $sms_message = "TRAIN | Next scheduled Train will be.";
                    $sms_message .= " | Headcode  " . $this->head_code;
                    $sms_message .= " | Route " . $this->route;
                    $sms_message .= " | Consist " . $this->consist;
                    $sms_message .= " | Start at " . $this->run_at;
                    $sms_message .= " | Runtime " . $this->quantity;
                    //$sms_message .= " | nuuid " . strtoupper($this->train_thing->nuuid);
                    $sms_message .= " | TEXT TRAIN ";
                    if ($head_code == "X") {
                        $sms_message .= "<head code>";
                    }

                    break;

                case '1':
                    $sms_message .=
                        " | TEXT TRAIN <four digit clock> <1-3 digit runtime>";
                    //$sms_message .=  " | TEXT ADD BLOCK";
                    break;
                case '2':
                    $sms_message .= " | TEXT DROP TRAIN";
                    //$sms_message .=  " | TEXT BLOCK";
                    break;
                case '3':
                    $sms_message .= " | TEXT TRAIN";
                    break;
                case '4':
                    $sms_message .= " | TEXT TRAIN";
                    break;
                default:
                    $sms_message .= " | TEXT ?";
                    break;
            }
        }

        // $sms_message .= "Report follows: " . $this->textTrain($this->train);

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;

        //return $this->sms_message;
    }

    function extractEvents($input)
    {
        if ($input == null) {
            $input = $this - subject;
        }

        // Extract runat signal
        $pieces = explode(" ", strtolower($input));
        $matches = 0;
        $this->events = [];
        foreach ($pieces as $key => $piece) {
            if (strlen($piece) == 4 and is_numeric($piece)) {
                $event_at = $piece;
                $this->events[] = $event_at;
                $matches += 1;
            }
        }

        return $this->events;
    }

    function extractRuntime($input)
    {
        $pieces = explode(" ", strtolower($input));

        // Extract runtime signal
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if ($piece == 'x' or $piece == 'z') {
                $this->runtime = $piece;
                $matches += 1;
                continue;
            }

            if (
                $piece == '5' or
                $piece == '10' or
                $piece == '15' or
                $piece == '20' or
                $piece == '25' or
                $piece == '30' or
                $piece == '45' or
                $piece == '55' or
                $piece == '60' or
                $piece == '75' or
                $piece == '90'
            ) {
                $this->runtime = $piece;
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 3 and is_numeric($piece)) {
                $this->runtime = $piece; //3 digits is a good indicator of a runtime in minutes
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 2 and is_numeric($piece)) {
                $this->runtime = $piece;
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 1 and is_numeric($piece)) {
                $this->runtime = $piece;
                $matches += 1;
                continue;
            }
        }

        if ($matches == 1) {
            return $this->runtime;
            $this->runtime = $piece;
            $this->num_hits += 1;
        }

        return true;
    }

    public function extractTrain($text = null)
    {
        $this->extractRunat($text);
        $this->extractEndat($text);
        $this->extractRuntime($text);
        $headcode = $this->extractHeadcode($text);

        $this->extracted_train['headcode'] = $headcode;
    }

    public function readTrain($text = null)
    {
    }

    public function readSubject()
    {
        // To get it working.
        $this->r_type = "keyword";

        // At this point the previous train will be loaded.

        $this->num_hits = 0;

        $input = $this->input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;


        $prior_uuid = null;


        $this->thing->log(
            $this->agent_prefix .
                '. Timestamp ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.'
        );

        $uuid_agent = new Uuid($this->train_thing, "uuid");
        $uuids = $uuid_agent->extractUuids($input);

        //        $uuids = $this->extractUuids($input);
        $this->thing->log(
            $this->agent_prefix . " counted " . count($uuids) . " uuids."
        );

        $pieces = explode(" ", strtolower($input));

        $this->extractTrain($haystack);

        // Code here to extract headcode
        // And compare it against the train one.

        if (
            $this->extracted_train['headcode'] != $this->head_code and
            $this->extracted_train['headcode'] != false
        ) {
        }

        if ($this->agent_input == "extract") {
            return;
        }
        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'train') {
                //                $this->getHeadcode();

                //                $this->parseTrain();
                $this->response .= "Current train retrieved. ";
                return;
            }

            if ($input == 'trains') {
                $this->headcode_thing->getHeadcodes();
                $headcode_text = "";
                foreach (
                    $this->headcode_thing->unique_headcodes
                    as $i => $headcode
                ) {
                    $headcode_text .= strtoupper($headcode['head_code']) . " ";
                }
                $this->response .= $headcode_text;

                $this->response .= "Looked for Current trains. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'red':
                            //     //$this->thing->log("read subject nextblock");
                            $this->setFlag('red');
                            break;
                        case 'green':
                            //     //$this->thing->log("read subject nextblock");
                            $this->setFlag('green');
                            break;
                        case 'accept':
                            $this->acceptThing();
                            break;
                        case 'clear':
                            $this->clearThing();
                            break;
                        case 'start':
                            $this->start();
                            break;
                        case 'stop':
                            $this->stop();
                            break;
                        case 'reset':
                            $this->reset();
                            break;
                        case 'split':
                            $this->split();
                            break;
                        case 'next':
                            $this->thing->log("read subject nexttrain");
                            $this->nextTrain();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextblock");
                            $this->dropTrain();
                            break;

                        case 'make':
                        case 'new':
                        case 'train':
                        case 'create':
                        case 'add':
                            // $this->assertTrain(strtolower($input));
                            $filtered_input = $this->assert(strtolower($input));
                            $this->readTrain($filtered_input);
                            if (empty($this->alias)) {
                                $this->alias = "X";
                            }

                            $this->response .=
                                'Asserted Train and found ' .
                                strtoupper($this->alias) .
                                ". ";
                            return;
                            break;

                        case 'add':
                            //     //$this->thing->log("read subject nextblock");
                            $this->makeTrain(null);
                            break;

                        case 'run':
                            $this->r_type = "instruction";
                            //     //$this->thing->log("read subject nextblock");
                            $this->runTrain(null);
                            break;

                        //   case 'red':
                        //     //$this->thing->log("read subject nextblock");
                        //        $this->setFlag('red');
                        //        break;

                        default:
                            $this->readTrain();
                    }
                }
            }
        }

        // Check whether Block saw a run_at and/or run_time
        // Intent at this point is less clear.  But Block
        // might have extracted information in these variables.

        // $uuids, $head_codes, $this->run_at, $this->run_time

        if (
            count($uuids) == 1 and
            count($head_codes) == 1 and
            isset($this->runat) and
            isset($this->runtime)
        ) {
            // Likely matching a head_code to a uuid.
        }

        if (isset($this->runat) and isset($this->runtime)) {
            $this->r_type = "instruction";
            //$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
            // Likely matching a head_code to a uuid.
            $this->makeTrain(
                $this->head_code,
                $this->alias,
                $this->runat,
                $this->runtime
            );
            return;
        }

        // If all else fails try the discriminator.

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['accept', 'clear'];
        $input_agent->aliases['accept'] = ['accept', 'add', '+'];
        $input_agent->aliases['clear'] = ['clear', 'drop', 'clr', '-'];

        $this->requested_state = $input_agent->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_state) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'reset':
                $this->reset();
                break;
            case 'split':
                $this->split();
                break;
        }

        return "Message not understood";

        return false;
    }
}
