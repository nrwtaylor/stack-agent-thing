<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Resource extends Agent
{
    // This is a resource.

    // A resource for this machine is piles and streams of text.

    // And recognizing which pieces of text are valuable.
    // Responds (devstack) to "resource is".
    // And (devstack) nextResource.

    public $var = 'hello';

    function init()
    {
        $this->start_time = microtime(true);
        $this->start_time = $this->thing->elapsed_runtime();

        $this->keywords = [
            'make',
            'resource',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'new',
            'here',
            'there',
        ];

        if (
            isset(
                $this->thing->container['api']['resource'][
                    'default_resource_name'
                ]
            )
        ) {
            $this->default_resource_name =
                $this->thing->container['api']['resource'][
                    'default_resource_name'
                ];
        }

        $this->default_resource_quantity = "Z";

        if (
            isset(
                $this->thing->container['api']['resource'][
                    'default_resource_quantity'
                ]
            )
        ) {
            $this->default_resource_quantity =
                $this->thing->container['api']['resource'][
                    'default_resource_quantity'
                ];
        }

        $this->default_alias = "Thing";

        $this->test = "Development code"; // Always iterative.

        $this->thing_report['help'] =
            'Try MAKE RESOURCE FUEL. And QUANTITY 10.';
    }

    // devstack
    // Get list of channel resources.
    function nextResource($file_name, $selector_array = null)
    {
        //if ($file_name == null) {
        $resource_name = "resources/places_canada/cgn_bc_csv_eng.csv";
        //}

        $split_time = $this->thing->elapsed_runtime();

        //        $file = $GLOBALS['stack_path'] . 'resources/translink/' . $file_name . '.txt';
        $file = $GLOBALS['stack_path'] . $resource_name;

        $handle = fopen($file, "r");
        $line_number = 0;
        while (!feof($handle)) {
            $line = trim(fgets($handle));
            $line_number += 1;
            // Get headers
            if ($line_number == 1) {
                $i = 0;
                $field_names = explode(",", $line);

                foreach ($field_names as $field) {
                    $field_names[$i] = preg_replace(
                        '/[\x00-\x1F\x80-\xFF]/',
                        '',
                        $field
                    );
                    $i += 1;
                }
                continue;
            }

            $arr = [];
            $field_values = explode(",", $line);
            $i = 0;
            foreach ($field_names as $field_name) {
                if (!isset($field_values[$i])) {
                    $field_values[$i] = null;
                }
                $arr[$field_name] = $field_values[$i];
                $i += 1;
            }

            // If there is no selector array, just return it.
            if ($selector_array == null) {
                yield $arr;
                continue;
            }

            if (array_key_exists(0, $selector_array)) {
            } else {
                $selector_array = [$selector_array];
            }

            // Otherwise see if it matches the selector array.
            $match_count = 0;
            $match = true;
            foreach ($arr as $field_name => $field_value) {
                // Look for all items in the selector_array matching
                if ($selector_array == null) {
                    continue;
                }

                foreach ($selector_array as $selector) {
                    foreach ($selector as $selector_name => $selector_value) {
                        if ($selector_name != $field_name) {
                            continue;
                        }

                        if ($selector_value == $field_value) {
                            $match_count += 1;
                        } else {
                            $match = false;
                            break;
                        }
                    }
                }
            }

            if ($match == false) {
                continue;
            }

            yield $arr;
        }

        fclose($handle);
    }

    function getNumber()
    {
        $agent = new Number($this->thing, "number");

        $numbers = $agent->numbers;

        if (count($numbers) == 1) {
            $this->resource_quantity = $numbers[0];
            $this->response .= "Saw a number. ";
        } else {
            $this->resource_quantity = "X";
        }
    }

    function set()
    {
        // devstack is this needed.
        if ($this->agent_input == "extract") {
            //$this->set();
            return;
        }

        $resource = new Variables(
            $this->thing,
            "variables resource " . $this->from
        );

        $resource->setVariable("resource_quantity", $this->resource_quantity);
        $resource->setVariable("resource_name", $this->resource_name);
        $resource->setVariable("refreshed_at", $this->current_time);

        $place = new Variables(
            $this->thing,
            "variables " . $this->resource_name . " " . $this->from
        );

        $place->setVariable("resource_name", $this->resource_name);
        $place->setVariable("resource_quantity", $this->resource_quantity);
    }

    // devstack
    function nextPlace()
    {
        $this->thing->log("next place");
        // Pull up the current headcode
        $this->get();

        // Find the end time of the headcode
        // which is $this->end_at

        // One minute into next headcode
        $quantity = 1;
        $next_resource = $this->thing->json->time(
            strtotime($this->end_at . " " . $quantity . " minutes")
        );

        // So a resource can be "09:45 15 minutes"
        // devstack

        $this->get($next_resource);

        // So this should create a headcode in the next quantity unit.

        return $this->available;
    }

    function findResource($category = null, $value = null)
    {
        if ($value == null) {
            $value = "Vancouver"; // Largest population center ... see what resources there are
        }

        if ($category == null) {
            $category = "Geographical Name";
        }
        // Is this find?

        //$selector_array = array(array("stop_id"=>$station_id_input));
        $selector_array = [$category => $value];

        $this->resources = [];
        for (
            $resources = $this->nextResource("meep", $selector_array);
            $resources->valid();
            $resources->next()
        ) {
            $resource = $resources->current();
            $id = $resource['CGNDB ID'];

            $code = $resource['Concise Code'];
            $description = $resource['Generic Term'];

            $latitude = $resource['Latitude'];
            $longitude = $resource['Longitude'];
            $name = $resource['Geographical Name'];

            $resource = [
                "name" => $name,
                "code" => $code,
                "latitude" => $latitude,
                "longitude" => $longitude,
                "description" => $description,
                "quantity" => 1,
                "id" => $id,
            ];

            // Not sure about using BC place code to identify resource
            // Decided id is a better generalizaion.
            $this->resources[$name][$id] = $resource;
        }
    }

    public function isResource($text = null)
    {
        if ($text == null) {
            return false;
        }

        $resourcename_list = array_map('strtolower', $this->resourcename_list);

        foreach ($resourcename_list as $i => $resourcename) {
            if ($resourcename == strtolower($text)) {
                return true;
            }
        }

        return false;
    }

    function getResource($selector = null)
    {
        foreach ($this->resources as $resource) {
            // so this is where it doesn't do anything useful.
            // need to get places returning known relevant places

            if ($resource['name'] == $selector) {
                $this->resource_name = $resource['name'];
                $this->resource_quantity = $resource['quantity'];
                $this->place = new Variables(
                    $this->thing,
                    "variables " . $this->resource_name . " " . $this->from
                );

                //$this->response .= "Got resource. ";

                return [$this->resource_name, $this->resource_quantity];
            }
        }

        return true;
    }

    // devstack
    // use getThings

    function getResources()
    {
        $this->resourcename_list = [];
        $this->resources = [];

        $things = $this->getThings('resource');

        if (!is_array($things)) {
            return;
        }

        //        $findagent_thing = new Findagent($this->thing, 'resource');

        $this->thing->log(
            'Agent "Place" found ' . count($things) . " resource Things."
        );

        //        if ($findagent_thing->thing_report['things'] == true) {
        //        }

        if (count($things) == 0) {
            // No places found
        } else {
            //$this->response .= "Found some resources on the stack. ";
            foreach (array_reverse($things) as $thing) {
                // While timing is an issue of concern

                //     $uuid = $thing_object['uuid'];

                // refactor to avoid unnecessary Thing
                //     $thing = new Thing($uuid);
                //     $variables = $thing->account['stack']->json->array_data;

                $subject = $thing->subject;
                $variables = $thing->variables;
                $created_at = $thing->created_at;

                if (isset($variables['resource'])) {
                    $resource_quantity = $this->default_resource_quantity;
                    $resource_name = $this->default_resource_name;

                    if (isset($variables['resource']['resource_quantity'])) {
                        $resource_quantity =
                            $variables['resource']['resource_quantity'];
                    }
                    if (isset($variables['resource']['resource_name'])) {
                        $resource_name =
                            $variables['resource']['resource_name'];
                    }

                    $this->resources[$resource_name] = [
                        "quantity" => $resource_quantity,
                        "name" => $resource_name,
                    ];
                    $this->resourcename_list[] = $resource_name;
                }
            }
        }

        // Add in a set of default places

        $default_resourcename_list = [
            "Fuel",
            "Food",
            "Water",
            "Communications",
        ];

        foreach ($default_resourcename_list as $resource_name) {
            //$place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);

            //$this->placecode_list[] = $place_code;
            $this->resourcename_list[] = $resource_name;
            $this->resources[] = [
                "quantity" => $resource_quantity,
                "name" => $resource_name,
            ];
        }

        // Indexing not implemented
        $this->max_index = 0;

        $this->resourcename_list = array_unique($this->resourcename_list);

        return [$this->resourcename_list, $this->resources];
    }

    public function get($resource_name = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.

        $resource_name = "X";

        if (isset($this->resource_name) and $resource_name != null) {
            $resource_name = $this->resource_name;
        }

        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);
        $flag_variable_name = "_" . $this->head_code;

        $this->place = new Variables(
            $this->thing,
            "variables " .
                $resource_name .
                $flag_variable_name .
                " " .
                $this->from
        );

        $this->resource_quantity = $this->place->getVariable(
            "resource_quantity"
        );
        $this->resource_name = $this->place->getVariable("resource_name");

        return [$this->resource_quantity, $this->resource_name];
    }

    function dropResource()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop a Place.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->resource)) {
            $this->resource->Forget();
            $this->resource = null;
        }

        $this->get();
    }

    function makeResource($resource_name = null)
    {
        if ($resource_name == null) {
            return true;
        }

        // See if the resource name already exists
        foreach ($this->resources as $resource) {
            if ($resource_name == $resource['name']) {
                return true;
            }
        }

        // Will be useful when devstack makePlace
        //$place_name = $this->getVariable('place_name', $place_name);

        $this->thing->log(
            'Agent "Resource" will make a Resource for ' . $resource_name . "."
        );

        $ad_hoc = true;

        if ($ad_hoc != false) {
            $this->default_resource_quantity = "X";

            //$this->response .= "Resource is Useable but we might lose it.";

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if (!isset($this->resource_quantity)) {
                $this->resource_quantity = $this->default_resource_quantity;
            }

            $this->current_resource_name = $resource_name;
            $this->resource_name = $resource_name;

            $this->place = new Variables(
                $this->thing,
                "variables " . $this->resource_name . " " . $this->from
            );

            $this->set();

            $this->getResources();
            $this->getResource($this->resource_name);
            $this->thing->log("resource name is " . $this->resource_name);

            $this->resource_thing = $this->thing;

            $this->response .=
                "Made new resource : " .
                strtoupper($this->resource_name) .
                ". ";
        }

        $this->thing->log('found a Resource and pointed to it.');
    }

    function resourceTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $resource_time = "x";
            return $resource_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $resource_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->resource_time = $resource_time;
        }

        return $resource_time;
    }

    public function extractResources($input = null)
    {
        if (!isset($this->resource_names)) {
            $this->resource_names = [];
        }

        if (!isset($this->resources)) {
            $this->getResources();
        }

        foreach ($this->resources as $resource) {
            $resource_name = strtolower($resource['name']);
            $resource_quantity = strtolower($resource['quantity']);
            if ($resource_name == "" or $resource_quantity == "") {
                continue;
            }

            if (strpos($input, $resource_name) !== false) {
                $this->resource_names[] = $resource_name;
            }
        }

        $this->resource_names = array_unique($this->resource_names);

        return $this->resource_names;
    }

    public function extractResource($input)
    {
        $this->resource_name = null;

        $resource_names = $this->extractResources($input);

        if (count($resource_names) == 1) {
            if (isset($resource_names[0])) {
                $this->resource_name = $resource_names[0];
            }

            $this->thing->log(
                $this->agent_prefix .
                    'found a resource name (' .
                    $this->resource_name .
                    ') in the text.'
            );
            return $this->resource_name;
        }

        if (count($resource_names) == 1) {
            $this->resource_name = $this->resource_names[0];
        }
        return $this->resource_name;
    }

    function assertResource($input)
    {
        if (($pos = strpos(strtolower($input), "resource is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("resource is")
            );
        } elseif (($pos = strpos(strtolower($input), "resource")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("resource"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($this->getResource($filtered_input)) {
            //true so make a place
            $this->makeResource($filtered_input);
        }
    }

    function readResource($variables = null)
    {
        $this->thing->log("read");

        $this->response .= "No resource files found. ";
    }

    function addResource()
    {
        //$this->makeResource();
        $this->get();
        return;
    }

    function makeTXT()
    {
        if (!isset($this->placecode_list)) {
            $this->getResources();
        }

        $this->getResources();

        if (!isset($this->resource)) {
            $txt = "Not here";
        } else {
            $txt =
                'These are RESOURCES for RAILWAY ' .
                $this->resource->nuuid .
                '. ';
        }
        $txt .= "\n";
        $txt .= "\n";

        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("QUANTITY", 8, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        // Resources must have a name.  Otherwise it's not a resource.
        foreach ($this->resources as $key => $resource) {
            $txt .=
                " " .
                str_pad(strtoupper($resource['name']), 40, " ", STR_PAD_RIGHT);
            $txt .=
                " " .
                "  " .
                str_pad(
                    strtoupper($resource['quantity']),
                    5,
                    "X",
                    STR_PAD_LEFT
                );

            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function makeSMS()
    {
        $sms = "RESOURCE " . strtoupper($this->resource_name);
        $sms .= " ";
        $sms .= strtoupper($this->head_code);
        $sms .= " ";

        $sms .= "|";

        /*
$resource_quantity = "X";

        if (!isset($this->resource_quantity)) {

if (strtolower($this->last_resource_name) == strtolower($this->resource_name) ) {
        $resource_quantity =  $this->last_resource_quantity;
}

}
$this->resource_quantity = $resource_quantity;

        $sms .= strtoupper($this->resource_quantity);
*/

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

        //        $this->makeSMS();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        switch (true) {
            case $this->agent_input == "extract":
                $input = strtolower($this->from . " " . $this->subject);
                break;
            case $this->agent_input != null:
                $input = strtolower($this->agent_input);
                break;
            case true:
                $input = strtolower($this->subject);
        }

        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
        //$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a resource in the provided datagram
        $this->extractResource($input);
        $this->getNumber($input);
        if ($this->agent_input == "extract") {
            return;
        }

        // Return the current resource

        $this->last_resource = new Variables(
            $this->thing,
            "variables resource " . $this->from
        );
        $this->last_resource_quantity = $this->last_resource->getVariable(
            'resource_quantity'
        );
        $this->last_resource_name = $this->last_resource->getVariable(
            'resource_name'
        );

        // If at this point we get false/false, then the default Place has not been created.

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword

        /*
        if (count($pieces) == 1) {

            if ($input == 'resource') {
                $this->readResource();
                return;
            }

        }
*/
        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextheadcode");
                            $this->nextResource();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextheadcode");
                            $this->dropResource();
                            break;

                        case 'make':
                        case 'new':
                        case 'create':
                        case 'place':
                        case 'add':
                            //var_dump("merp");
                            $this->assertResource(strtolower($input));
                            return;

                        default:
                    }
                }
            }
        }

        if ($this->resource_name != null) {
            $this->getResource($this->resource_name);
            $this->response .= "Got resource. ";

            $this->thing->log(
                $this->agent_prefix .
                    'using extracted resource_name ' .
                    $this->resource_name .
                    ".",
                "INFORMATION"
            );
            return;
        }

        if ($this->last_resource_name != null) {
            $this->getResource($this->last_resource_name);
            $this->thing->log(
                $this->agent_prefix .
                    'using extracted last_resource_name ' .
                    $this->last_resource_name .
                    ".",
                "INFORMATION"
            );
            return;
        }

        //$input = $this->assert($input);
        $resource = $this->assert($input);
        if (!$this->getResource(strtolower($resource))) {
            // Resource was found
            // And loaded
            return;
        }

        //    function makePlace($place_code = null, $place_name = null) {
        $this->makeResource($resource);
        $this->set();

        $this->thing->log(
            $this->agent_prefix .
                'using default_resource_name ' .
                $this->default_resource_name .
                ".",
            "INFORMATION"
        );

        return;

        if (
            $this->isData($this->resource_name) or
            $this->isData($this->resource_quantity)
        ) {
            $this->set();
            return;
        }

        $this->readResource();

        $this->response .= "Not understood. ";
        return "Message not understood";

        return false;
    }
}
