<?php
namespace Nrwtaylor\StackAgentThing;

// devstack need to think around designing for a maximum 4000 charactor json thing
// constraints are good.  Remember arduinos.  So perhaps all agents don't get saved.
// Only the necessary ones.

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Headcode extends Agent
{
    // This is a headcode.  You will probably want to read up about
    // the locomotive headcodes used by British Rail.

    // A headcode takes the form (or did in the 1960s),
    // of NANN.  Where N is a digit from 0-9, and A is an uppercase character from A-Z.

    // This implementation recognizes lowercase and uppercase characters as the same.
    // 0t80. OT80. HEADCODE 0t90.

    // The headcode is used by the Train agent to create the proto-train.
    // A Train must have a Headcode to run.  Rule #1.
    // RUN TRAIN.

    // A headcode must have a route. Route is a text string.  Examples of route are:
    //  Gilmore > Hastings > Place
    //  >> Gilmore >>
    //  > Hastings
    // ADD PLACE. ROUTE IS Gilmore> Hastings > Place.

    // A headcode may have a consist. (Z - indicates train may fill consist.
    // X - indicates train should specify the consist. (devstack: "Input" agent)
    // NnXZ is therefore a valid consist. As is "X" or "Z".
    // A consist must always resolve to a locomotive.  Specified as uppercase letter.
    // The locomotive closest to the first character is the engine.  And gives
    // commands to following locomotives to follow.
    // #devstack
    // The ordered-ness of Consist will come from building out of the orderness of Route.

    // This is the headcode manager.  This person is pretty special.
    // HEADCODE.

    public $var = "hello";
    function init()
    {
        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $this->keywords = ["next", "accept", "clear", "drop", "add", "new"];

        $this->default_head_code = "0Z99";
        if (isset($this->thing->container["api"]["headcode"]["head_code"])) {
            $this->default_head_code =
                $this->thing->container["api"]["headcode"]["head_code"];
        }
        // But for now use this below.

        // You will probably see these a lot.
        // Unless you learn headcodes after typing SYNTAX.
        if (!isset($this->default_head_code)) {
            $this->default_head_code = "0Z99";
        }

        $this->default_alias = "Thing";

        $this->test = "Development code"; // Always iterative.

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/headcode";

        $this->state = null; // to avoid error messages

        if (!isset($this->response)) {
            $this->response .= "No response found. ";
        }

    }

    function set()
    {
        // Apparently not needful of a variable.
        // It is a context. The managing Agents define it.
        // $this->head_code = "0Z15";
        // $headcode = new Variables($this->thing, "variables headcode " . $this->from);

        // Added test 26 July 2018
        $this->refreshed_at = $this->current_time;

        // Write the headcode with the Variables agent.
        // No time needed(?).  Variables handles that.
        // headcode_id suggests that this is the identifier of the Headcode.
        // To distinguish from headcode.
        $this->headcode_id->setVariable("head_code", $this->head_code);

        // Don't use an index with headcodes.
        // But allow Headcode to access the current index.
        // But won't need this line.  Keep it to just head_code.
        // No Name either.  Trains have names.
        //   $this->headcode->setVariable("index", $this->index);

        $this->headcode_id->setVariable("refreshed_at", $this->current_time);

        $this->thing->Write(
            ["headcode", "head_code"],
            $this->head_code
        );
        $this->thing->Write(
            ["headcode", "refreshed_at"],
            $this->current_time
        );

        $this->headcode->setVariable("consist", $this->consist);
        $this->headcode->setVariable("run_at", $this->run_at);
        $this->headcode->setVariable("quantity", $this->quantity);
        $this->headcode->setVariable("available", $this->available);

    }

    function nextHeadcode()
    {
        // #devstack

        $this->thing->log("next headcode");
        // Pull up the current headcode
        $this->get();

        // Find the end time of the headcode
        // which is $this->end_at

        // One minute into next headcode
        $quantity = 1;
        $next_time = $this->thing->time(
            strtotime($this->end_at . " " . $quantity . " minutes")
        );

        $this->get($next_time);

        // So this should create a headcode in the next quantity unit.

        return $this->available;
    }

    function getVariable($variable_name = null, $variable = null)
    {
        // devstack remove?

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset($this->{"default_" . $variable_name})) {
            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

    function getRoute()
    {
        //$this->route = $this->thing->Read( array("headcode", "route") );
        //            $this->route = "na";

        //$route_agent = new Route($this->thing, $this->head_code);
        //$this->route = $route_agent->route;
        $this->route = "Place";
    }

    function getRunat()
    {
        if (isset($run_at)) {
            $this->run_at = $run_at;
        } else {
            $this->run_at = "X";
        }
    }

    function getQuantity()
    {
        $this->quantity = "X";

    }

    function getHeadcodes()
    {
        $this->headcode_list = [];
        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, "headcode");

        if ($findagent_thing->thing_report["things"] === true) {
            $head_code = "X";
            $this->headcode_list[] = $head_code;

            $headcode = [
                "head_code" => $head_code,
                "refreshed_at" => $this->thing->time(), // ?
                "flag" => "X",
                "consist" => "X",
                "route" => "X",
                "runtime" => "X",
                "quantity" => "X",
                "route" => "X",
            ];

            $this->headcodes[] = $headcode;
            $this->response .= "Could not get a headcode. ";

            return;
        }

        $this->thing->log(
            'Agent "Headcode" found ' .
                count($findagent_thing->thing_report["things"]) .
                " headcode Things."
        );

        foreach (
            array_reverse($findagent_thing->thing_report["things"])
            as $thing_object
        ) {
            // While timing is an issue of concern

            $uuid = $thing_object["uuid"];

            //            $thing= new Thing($uuid);
            //            $variables = $thing->account['stack']->json->array_data;

            $variables_json = $thing_object["variables"];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables["headcode"])) {
                $head_code = $variables["headcode"]["head_code"];
                $refreshed_at = $variables["headcode"]["refreshed_at"];

                $variables["headcode"][] = $thing_object["task"];
                $this->headcode_list[] = $variables["headcode"];

                // https://gist.github.com/JeffreyWay/3194444
                // $name = $name ?: 'joe';

                if (!isset($variables["consist"])) {
                    $variables["consist"] = "X";
                }
                if (!isset($variables["route"])) {
                    $variables["route"] = "X";
                }
                if (!isset($variables["runtime"])) {
                    $variables["runtime"] = "X";
                }
                if (!isset($variables["quantity"])) {
                    $variables["quantity"] = "X";
                }
                if (!isset($variables["flag"])) {
                    $variables["flag"] = "X";
                }
                if (!isset($variables["route"])) {
                    $variables["route"] = "X";
                }

                //$route = $route ?: $variables['route'];
                //$runtime = $runtime ?: $variables['runtime'];
                //$quantity = $quantity ?: $variables['quantity'];

                $headcode = [
                    "head_code" => $head_code,
                    "refreshed_at" => $refreshed_at,
                    "flag" => $variables["flag"],
                    "consist" => $variables["consist"],
                    "route" => $variables["route"],
                    "runtime" => $variables["runtime"],
                    "quantity" => $variables["quantity"],
                    "route" => $variables["route"],
                ];

                $this->headcodes[] = $headcode;

                if (!isset($this->unique_headcodes[$head_code])) {
                    $this->unique_headcodes[$head_code] = $headcode;
                }
                if (
                    strtotime($refreshed_at) >
                    strtotime(
                        $this->unique_headcodes[$head_code]["refreshed_at"]
                    )
                ) {
                    $this->unique_headcodes[$head_code] = $headcode;
                }
            }
        }

        return $this->headcode_list;
    }

    function get($head_code = null)
    {
        // This is a request to get the headcode from the Thing
        // and if that doesn't work then from the Stack.

        // 0. light engine with or without break vans.
        // Z. Always has been a special.
        // 10. Because starting at the beginning is probably a mistake.
        // if you need 0Z00 ... you really need it.

        if (($head_code == null) and (!isset($this->head_code))) {
           $head_code = $this->default_head_code;
        }

        if (($head_code == null) and (isset($this->head_code))) {
           $head_code = $this->head_code;
        }

        $this->headcode = new Variables(
            $this->thing,
            "variables " . $head_code . " " . $this->from
        );
        $this->last_refreshed_at = $this->headcode->getVariable("refreshed_at");

        // Don't need this as can access headcode variables at $this->headcode
        //$this->head_code = $this->headcode->getVariable("head_code");

        $this->consist = $this->headcode->getVariable("consist");
        $this->run_at = $this->headcode->getVariable("run_at");
        $this->quantity = $this->headcode->getVariable("quantity");
        $this->available = $this->headcode->getVariable("available");

        $this->getRoute();
        $this->getConsist();
        $this->getRunat();
        $this->getQuantity();
        $this->getAvailable();
    }

    function dropHeadcode()
    {
        // devstack
        $this->thing->log(
            $this->agent_prefix . "was asked to drop a headcode."
        );

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->headcode)) {
            $this->headcode->Forget();
            $this->headcode = null;
        }

        $this->get();
    }

    function ImageRectangleWithRoundedCorners(
        &$im,
        $x1,
        $y1,
        $x2,
        $y2,
        $radius,
        $color
    ) {
        // devstack move to Image agent.

        // draw rectangle without corners
        imagefilledrectangle(
            $im,
            $x1 + $radius,
            $y1,
            $x2 - $radius,
            $y2,
            $color
        );
        imagefilledrectangle(
            $im,
            $x1,
            $y1 + $radius,
            $x2,
            $y2 - $radius,
            $color
        );

        // draw circled corners
        imagefilledellipse(
            $im,
            $x1 + $radius,
            $y1 + $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x2 - $radius,
            $y1 + $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x1 + $radius,
            $y2 - $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x2 - $radius,
            $y2 - $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $this->node_list = ["headcode web" => ["headcode", "headcode 0Z99"]];

        // Make buttons
/*
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "headcode web"
        );
        $choices = $this->thing->choice->makeLinks("headcode web");
*/
        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web =
            '<a href="' .
            $link .
            '" alt="Tile with ' .
            strtoupper($this->head_code) .
            " and " .
            $this->thing->nuuid .
            '">' .
            $this->html_image .
            "</a>";
        $web .= "<br>";

        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->last_refreshed_at)
        );
        $web .= "Asserted about " . $ago . " ago.";

        $web .= "<br>";

if (!isset($this->sms_message)) {
$this->makeSMS();
}
        $web .= $this->sms_message;

        $this->thing_report["web"] = $web;
    }

    public function makeMessage()
    {
        $message = "Headcode is " . strtoupper($this->head_code) . ".";
        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }

        $agent = new Png($this->thing, "png"); // long run
        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;

        $this->thing_report["png"] = $agent->image_string;
    }

    function makeHeadcode($head_code = null)
    {
        $head_code = $this->getVariable("head_code", $head_code);

        $this->thing->log(
            'Agent "Headcode" will make a headcode for ' . $head_code . "."
        );

        $ad_hoc = true;
        if ($ad_hoc != false) {
            // Ad-hoc headcodes allows creation of headcodes on the fly.
            // 'Z' indicates the associated 'Place' is offering whatever it has.
            // Block is a Place.  Train is a Place (just a moving one).
            $quantity = "Z";

            // Otherwise we needs to make trains to run in the headcode.

            $this->thing->log(
                $this->agent_prefix .
                    "was told the Place is Useable but we might get kicked out."
            );

            // So we can create this headcode either from the variables provided to the function,
            // or leave them unchanged.
            if (!isset($this->max_index)) {$this->max_index = 0;}

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->current_head_code = $head_code;
            $this->head_code = $head_code;

            $this->quantity = $quantity; // which is run_time

            if (isset($run_at)) {
                $this->run_at = $run_at;
            } else {
                $this->run_at = "X";
            }

            $this->getEndat();
            $this->getAvailable();

            // devstack?
            $this->headcode_thing = $this->thing;
        }

        // Need to code in the X and <number> conditions for creating new headcodes.

        // Write the variables to the db.
        $this->set();

        $this->thing->log('Agent "Headcode" found headcode a pointed to it.');
    }

    function headcodeTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $headcode_time = "x";
            return $headcode_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $headcode_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->headcode_time = $headcode_time;
        }

        return $headcode_time;
    }

    function getEndat()
    {
        if ($this->run_at != "x" and $this->quantity != "x") {
            $this->end_at = $this->thing->time(
                strtotime($this->run_at . " " . $this->quantity . " minutes")
            );
        } else {
            $this->end_at = "x";
        }

        return $this->end_at;
    }

    function getAvailable()
    {
        // This proto-typical headcode manages (available) time.
        // From start_at and current_time we can calculate elapsed_time.

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        if (strtotime($this->current_time) < strtotime($this->run_at)) {
            $this->available =
                strtotime($this->end_at) - strtotime($this->run_at);
            // $this->available = $this->quantity;
        } else {
            $this->available =
                strtotime($this->end_at) - strtotime($this->current_time);
        }

        // Allow negative block ticks (time quanta)
        // This is needed to track behind block completion.

        $this->thing->log(
            'Agent "Headcode" identified ' .
                $this->available .
                " resource units available."
        );
    }

    function extractConsists($input)
    {
        // devstack: probably need a word lookup
        // or at least some thinking on how to differentiate Headcode from NnX
        // as a valid consist.

        if (!isset($this->consists)) {
            $this->consists = [];
        }

        $pattern = "|[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
        $this->consists = $m[0];

        return $this->consists;
    }

    function getConsist($input = null)
    {
        $consists = $this->extractConsists($input);

        if (
            is_array($consists) and
            count($consists) == 1 and
            strtolower($consists[0]) != "train"
        ) {
            $this->consist = $consists[0];
            $this->thing->log(
                'Agent "Headcode" found a consist (' .
                    $this->consist .
                    ") in the text."
            );
            return $this->consist;
        }

        $this->consist = "X";

        if (is_array($consists) and count($consists) == 0) {
            return false;
        }
        if (is_array($consists) and count($consists) > 1) {
            return false;
        }

        return true;
    }

    function extractHeadcodes($input = null)
    {
        if (!isset($this->head_codes)) {
            $this->head_codes = [];
        }

        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0$

        $pattern = "|\b\d{1}[A-Za-z]{1}\d{2}\b|";
        preg_match_all($pattern, $input, $m);
        $this->head_codes = $m[0];

        return $this->head_codes;
    }

    function extractHeadcode($input)
    {
        $head_codes = $this->extractHeadcodes($input);
        if (!is_array($head_codes)) {
            return true;
        }

        if (is_array($head_codes) and count($head_codes) == 1) {
            $this->head_code = $head_codes[0];
            $this->thing->log(
                'Agent "Headcode" found a headcode (' .
                    $this->head_code .
                    ") in the text."
            );
            return $this->head_code;
        }

        if (is_array($head_codes) and count($head_codes) == 0) {
            return false;
        }
        if (is_array($head_codes) and count($head_codes) > 1) {
            return true;
        }

        return true;
    }

    function addHeadcode()
    {
        $this->get();
    }

    public function makeImage()
    {
        $text = strtoupper($this->head_code);

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

        $this->ImageRectangleWithRoundedCorners(
            $image,
            0,
            0,
            $image_width,
            $image_height,
            12,
            $black
        );
        $this->ImageRectangleWithRoundedCorners(
            $image,
            6,
            6,
            $image_width - 6,
            $image_height - 6,
            12 - 6,
            $white
        );

        $font = $this->default_font;
if (file_exists($font)) {
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = [72, 36, 24, 18, 12, 6];

        foreach ($sizes_allowed as $size) {
            $angle = 0;
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] =
                max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
                min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] =
                max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
                min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, "bb");

            //check width of the image
            $width = imagesx($image);
            $height = imagesy($image);
            if ($bbox["width"] < $image_width - 30) {
                break;
            }
        }

        $pad = 0;
        imagettftext(
            $image,
            $size,
            $angle,
            $width / 2 - $bb_width / 2,
            $height / 2 + $bb_height / 2,
            $grey,
            $font,
            $text
        );
}
        //imagestring($image, 2, $image_width-75, 10, $text, $textcolor);
        imagestring(
            $image,
            2,
            $image_width - 45,
            10,
            $this->headcode->nuuid,
            $textcolor
        );

        $this->image = $image;
    }

    function makeTXT()
    {
        if (!isset($this->headcode_list)) {
            $this->getHeadcodes();
        }

        if (!isset($this->headcodes)) {
            $this->getHeadcodes();
        }

        $txt =
            "These are HEADCODES for RAILWAY " . $this->headcode->nuuid . ". ";
        $txt .= "\n";

        $count = "X";
        if (isset($this->headcodes) and is_array($this->headcodes)) {
            $count = count($this->headcodes);
        }


        $txt .= "Last " . $count . " Headcodes retrieved.";

        $txt .= "\n";
        $txt .= "\n";

        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("RUN AT", 20, " ", STR_PAD_RIGHT);

        $txt .= " " . str_pad("HEAD", 4, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("FLAG", 8, " ", STR_PAD_LEFT);

        //        $txt .= " " . str_pad("ALIAS", 10, " " , STR_PAD_RIGHT);
        //$txt .= " " . str_pad("DAY", 4, " ", STR_PAD_LEFT);

        //$txt .= " " . str_pad("RUNAT", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("RUNTIME", 8, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("AVAILABLE", 9, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("QUANTITY", 9, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("CONSIST", 9, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("ROUTE", 9, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";
if (isset($this->headcodes)) {
        //$txt = "Test \n";
        foreach (array_reverse($this->headcodes) as $headcode) {
            //            $txt .= " " . str_pad(strtoupper($headcode['head_code']), 4, "X", STR_PAD_LEFT);
            //$txt .= " " . str_pad($train['alias'], 10, " " , STR_PAD_RIGHT);

            $refreshed_at = "X";
            if (isset($headcode["refreshed_at"])) {
                // devstack
                // $agent = new Timestamp($this->thing, $headcode['refreshed_at']);
                $refreshed_at = strtoupper(
                    date("Y M d D H:i", strtotime($headcode["refreshed_at"]))
                );
            }
            $txt .= " " . str_pad($refreshed_at, 20, " ", STR_PAD_LEFT);

            $txt .=
                " " .
                str_pad(
                    strtoupper($headcode["head_code"]),
                    4,
                    "X",
                    STR_PAD_LEFT
                );

            $flag_state = "X";
            if (isset($headcode["flag"]["state"])) {
                $flag_state = $headcode["flag"]["state"];

                //$txt .= " " . str_pad($headcode['flag']['state'], 8, " ", STR_PAD_LEFT);
            }
            $txt .= " " . str_pad($flag_state, 8, " ", STR_PAD_LEFT);

            //            if (isset($headcode['refreshed_at'])) {
            //                $txt .= " " . str_pad($headcode['refreshed_at'], 12, " ", STR_PAD_LEFT);
            //            }

            $runtime_minutes = "X";
            if (isset($headcode["runtime"]["minutes"])) {
                $runtime_minutes = $headcode["runtime"]["minutes"];
            }
            $txt .= " " . str_pad($runtime_minutes, 8, " ", STR_PAD_LEFT);

            if (isset($headcode["run_at"])) {
                $txt .=
                    " " . str_pad($headcode["run_at"], 8, " ", STR_PAD_LEFT);
            }
            if (isset($headcode["available"])) {
                $txt .=
                    " " . str_pad($headcode["available"], 9, " ", STR_PAD_LEFT);
            }
            if (isset($headcode["quantity"])) {
                $quantity = "X";
                if (isset($headcode["quantity"]["quantity"])) {
                    $quantity = $headcode["quantity"]["quantity"];
                }
                $txt .= " " . str_pad($quantity, 9, " ", STR_PAD_LEFT);
            }
            if (isset($headcode["consist"])) {
                $consist = "Z";
                if (is_string($headcode["consist"])) {
                    $consist = $headcode["consist"];
                }
                $txt .= " " . str_pad($consist, 9, " ", STR_PAD_LEFT);
            }
            if (isset($headcode["route"])) {
                $route = $headcode["route"];
                if (is_array($headcode["route"])) {
                    $route = "X";
                    if (isset($headcode["route"]["places"])) {
                        $route = implode(">", $headcode["route"]["places"]);
                    }
                }
                $txt .= " " . str_pad($route, 9, " ", STR_PAD_LEFT);
            }
            $txt .= "\n";
        }
}
        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    private function getFlag()
    {
        $this->flag = new Flag($this->thing, "flag");

        if (!isset($this->flag->state)) {
            $this->flag->state = "X";
        }
    }

    public function makeSMS()
    {
        if (!isset($this->flag->state)) {
            $this->getFlag();
        }

        $flag_state = "X";
        if (isset($this->flag->state)) {
            $flag_state = $this->flag->state;
        }

        $sms_message = "HEADCODE " . strtoupper($this->head_code);

        if ($flag_state != false) {
            $sms_message .= " " . strtoupper($flag_state);
        }

        $sms_message .= " | ";
        $sms_message .= $this->response;

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;

        return $sms_message;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $available = $this->thing->human_time($this->available);

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index; //
        }

        $this->thing_report["email"] = $this->sms_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report["help"] = "This is a headcode.";
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

        //$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a headcode in the provided datagram
        $x = $this->extractHeadcode($input);

        // devstack
        // Revisit for issues with headcode extraction from uuid.
        // ie 1a23
        $head_codes = array_unique($this->head_codes);
        $this->head_codes = [];
        $uuid_agent = new Uuid($this->thing, "uuid");
        $uuid_agent->extractUuids($input);
        foreach ($head_codes as $j => $head_code) {
            foreach ($uuid_agent->uuids as $i => $uuid) {
                if (stripos($uuid, $head_code) !== false) {
                } else {
                    $this->head_codes[] = $head_code;
                }
            }
        }

        $this->headcode_id = new Variables(
            $this->thing,
            "variables headcode " . $this->from
        );

        if (!isset($this->head_code) or $this->head_code == false) {
            $this->head_code = $this->headcode_id->getVariable(
                "head_code",
                null
            );
            if (!isset($this->head_code) or $this->head_code == false) {
                $this->head_code = $this->getVariable("head_code", null);

                if (!isset($this->head_code) or $this->head_code == false) {
                    $this->head_code = "0Z10";
                }
            }
        }

        $this->get();

        if (
            $this->agent_input == "extract" and
            strpos(strtolower($this->subject), "roll") !== false
        ) {
            if (strtolower($this->head_code[1]) == "d") {
                $this->response = true; // Which flags not to use response.
                //$this->response = "Not a headcode.";
                return;
            }
        }

        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {
            $this->response .= "Extract. ";
            return;
        }

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "headcode") {
//                $this->read();
                $this->response .= "Read headcode. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "next":
                            $this->thing->log("read subject nextheadcode");
                            $this->nextheadcode();
                            $this->response .= "Got next headcode. ";
                            break;

                        case "drop":
                            $this->dropheadcode();
                            $this->response .= "Dropped headcode. ";
                            break;

                        case "add":
                            $this->get();
                            $this->response .= "Added headcode. ";
                            break;

                        default:
                    }
                }
            }
        }

        // Check whether headcode saw a run_at and/or run_time
        // Intent at this point is less clear.  But headcode
        // might have extracted information in these variables.

        if ($this->isData($this->head_code)) {
            $this->set();
            $this->response .=
                "Set headcode to " . strtoupper($this->head_code) . ". ";
            return;
        }

        //        $this->read();
        $this->response .= "Read. ";

        return "Message not understood";

        return false;
    }

    /* More on headcodes

http://myweb.tiscali.co.uk/gansg/3-sigs/bellhead.htm
1 Express passenger or mail, breakdown train en route to a job or a snow plough going to work.
2 Ordinary passenger train or breakdown train not en route to a job
3 Express parcels permitted to run at 90 mph or more
4 Freightliner, parcels or express freight permitted to run at over 70 mph
5 Empty coaching stock
6 Fully fitted block working, express freight, parcels or milk train with max speed 60 mph
7 Express freight, partially fitted with max speed of 45 mph
8 Freight partially fitted max speed 45 mph
9 Unfitted freight (requires authorisation) engineers train which might be required to stop in section.
0 Light engine(s) with or without brake vans

E     Train going to       Eastern Region
M         "     "     "         London Midland Region
N         "     "     "         North Eastern Region (disused after 1967)
O         "     "     "         Southern Region
S          "     "     "         Scottish Region
V         "     "     "         Western Region

*/
}
