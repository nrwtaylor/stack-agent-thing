<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Pixel extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_state = "X";

        $this->keyword = "pixel";

        $this->test = "Development code"; // Always

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        // Get the current identities uuid.
        $default_pixel_id = new Identity($this->thing, "identity");
        $this->default_pixel_id = $default_pixel_id->uuid;

        // Set up default pixel settings
        $this->verbosity = 1;
        $this->requested_state = null;
        $this->default_state = "#ffffff";
        $this->node_list = ["green" => ["red" => ["green"]]];

        $this->show_uuid = "off";

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/pixel";

        $this->show_link = "off";

        $this->refreshed_at = null;

        $this->current_time = $this->thing->time();

        // devstack
        $this->associate_agent = new Associate($this->thing, $this->subject);

        $this->thing_report["help"] = "This Pixel is a colour.";
        $this->thing_report["info"] =
            "DOUBLE YELLOW means keep going. RED means stop.";
    }

    public function lastPixel()
    {
    }

    public function nextPixel()
    {
    }

    public function currentPixel()
    {
    }

    public function respondResponse()
    {
        $this->makeHelp();
        $this->makeInfo();
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        //$thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function set()
    {
        if (!isset($this->pixel_thing)) {
            //$this->pixel_thing = $this->thing;
            // Nothing to set
            //return true;
        }

        if (isset($this->pixel_thing->state)) {
            $this->pixel["state"] = $this->pixel_thing->state;
        }
        if (isset($this->pixel_thing->uuid)) {
            $this->pixel["id"] = $this->idPixel($this->pixel_thing->uuid);
        }

        if (isset($this->pixel_thing->uuid)) {
            $this->pixel["uuid"] = $this->pixel_thing->uuid;
        }
        $this->pixel["text"] = "pixel check";

        if (isset($this->pixel_thing->text)) {
            $this->pixel["text"] = $this->pixel_thing->text;
        }

        if (isset($this->pixel_thing->uuid)) {
            $this->pixel_thing->associate($this->pixel_thing->uuid);
        }

        if ($this->channel_name == "web") {
            $this->response .= "Detected web channel. ";
            // Do not effect a state change for web views.
            return;
        }
        $this->setPixel();
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    function isPixel($pixel = null)
    {
        return null;
    }

    public function get()
    {
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        if (is_string($this->channel_name)) {
            $this->response .= "Saw channel is " . $this->channel_name . ". ";
        } else {
            $this->response .= "No channel name. ";
        }
        $this->getPixel();

        if (!isset($this->pixels)) {
            return true;
        }

        foreach ($this->pixels as $i => $pixel) {
            if ($pixel["uuid"] == $this->pixel_thing->uuid) {
                $this->pixel_thing->state = $pixel["state"];
                return;
            }
        }
    }

    public function run()
    {
        // Get this too. Or put it in the run loop.
        $state = "X";
        if (isset($this->pixel_thing->state)) {
            $state = $this->pixel_thing->state;
        }

        $this->helpPixel($state);
        $this->infoPixel($state);
    }

    function helpPixel($text = null)
    {
        $a = $this->associationsPixel();

        $next_pixel_id = "X";
        if (isset($a[0]["id"])) {
            $next_pixel_id = $a[0]["id"];
        }
        $next_pixel_text = "Next PIXEL " . $next_pixel_id . ".";

        $help = "No pixel found. Text NEW PIXEL.";

        if (!$this->isBlank($this->hexColour($text))) {
            $help = "This is a " . strtoupper($text) . " pixel";
        }

        if (strtolower($text) == "x") {
            $help = "No pixel found. Text NEW PIXEL.";
            if (isset($this->pixel_thing)) {
                $help =
                    "Treat this pixel as if it is broken. Try PIXEL RED and see if it changes colour.";
            }
        }

        if (strtolower($text) == "red") {
            $help = "This Pixel is RED. Text PIXEL. Wait for it to change.";
        }

        if (strtolower($text) == "green") {
            $help =
                "This Pixel is GREEN. Keep going. Don't expect to stop. Text PIXEL.";
        }

        if (strtolower($text) == "yellow") {
            $help =
                "This Pixel is YELLOW. Expect to stop at the next one. " .
                $next_pixel_text;
        }

        $this->thing_report["help"] = $help;

        return $help;
    }

    function infoPixel($text = null)
    {
        $state = "X";
        $info = "Pixel is OFF. Or broken.";

        if (!$this->isBlank($this->hexColour($text))) {
            $info = "This is a " . strtoupper($text) . " pixel";
        }

        if (strtolower($text) == "x") {
            $info = "Pixel is OFF. Or broken.";
        }

        if (strtolower($text) == "#ff0000") {
            $info = "This Pixel is RED.";
        }
        if (strtolower($text) == "#00ff00") {
            $info = "This Pixel is GREEN.";
        }
        if (strtolower($text) == "#ffff00") {
            $info = "This Pixel is YELLOW.";
        }

        $this->thing_report["info"] = $info;

        return $info;
    }

    function changePixel($text)
    {
        if (!isset($this->pixel_thing)) {
            return true;
        }
        $state = null;
        $state = $text;
        $type = "hex colour";

        if (strtolower($text) == "x") {
            $state = "X";
            $type = "pixel x";
        }

        if (strtolower($text) == "red") {
            $state = "#ff0000";
            $type = "pixel red";
        }
        if (strtolower($text) == "green") {
            $state = "#00ff00";
            $type = "pixel green";
        }
        if (strtolower($text) == "yellow") {
            $state = "#ffff00";
            $type = "pixel yellow";
        }

        $this->pixel_thing->state = $state;
        $this->pixel_thing->text = $type;

        if ($state != null) {
            $this->response = "Selected " . $state . " pixel. ";
        }
    }

    function setPixel($text = null)
    {
        if (!isset($this->pixel_thing)) {
            return true;
        }

        $this->pixel_thing->Write(
            ["pixel", "state"],
            $this->pixel["state"]
        );

        $this->pixel_thing->Write(
            ["pixel", "text"],
            $this->pixel["text"]
        );

        $this->pixel_thing->Write(
            ["pixel", "refreshed_at"],
            $this->current_time
        );

        $this->pixel_thing->associate($this->pixel_thing->uuid);
    }

    function makePixel()
    {
        if (!isset($this->pixel_thing)) {
            return true;
        }
    }

    function newPixel()
    {
        $this->response .= "Called for a new pixel. ";
        $thing = new Thing(null);
        $thing->Create($this->from, "pixel", "pixel");

        $agent = new Pixel($thing, "pixel");

        $this->pixel_thing = $thing;
        $this->pixel_thing->state = "X";
        $this->pixel_thing->text = "new pixel";

        $this->pixel_id = $this->idPixel($thing->uuid);
    }

    function getPixelbyUuid($uuid)
    {
        if ($this->channel_name == "web") {
            $id = $this->idPixel($uuid);
            $this->getPixelbyId($id);
            return;
        }
        $thing = new Thing($uuid);
        if ($thing->thing == false) {
            $this->pixel_thing = false;
            $this->pixel_id = null;
            return true;
        }

        $pixel = $this->thing->json->jsontoArray($thing->thing->variables)[
            "pixel"
        ];

        $this->pixel_thing = $thing;
        $this->pixel_id = $thing->uuid;

        if (isset($pixel["state"])) {
            $this->pixel_thing->state = $pixel["state"];
        }
    }

    function getPixelbyId($id)
    {
        if (!isset($this->pixels)) {
            $this->getPixels();
        }
        $matched_uuids = [];
        foreach ($this->pixels as $i => $pixel) {
            if ($pixel["id"] == $id) {
                $matched_uuids[] = $pixel["uuid"];
                continue;
            }

            if ($this->idPixel($pixel["uuid"]) == $id) {
                $matched_uuids[] = $pixel["uuid"];
                continue;
            }
        }
        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->pixel_thing = new Thing($uuid);

        $pixel = $this->thing->json->jsontoArray(
            $this->pixel_thing->thing->variables
        )["pixel"];

        $this->pixel_id = $this->pixel_thing->uuid;

        if (isset($pixel["state"])) {
            $this->pixel_thing->state = $pixel["state"];
        }

        /* 
       $this->pixel = $this->thing->json->jsontoArray(
            $this->pixel_thing->thing->variables
        )['pixel'];

        $this->state = $this->pixel['state'];
        $this->pixel_id = $this->pixel_thing->uuid;
        $this->pixel['id'] = $this->pixel_id;
*/
    }

    // Take in a uuid and convert it to a pixel id (id here).
    function idPixel($text = null)
    {
        $pixel_id = $text;
        if ($text == null) {
            if (isset($this->pixel_thing->uuid)) {
                $pixel_id = $this->pixel_thing->uuid;
            }
        }

        $t = hash("sha256", $pixel_id);
        $t = substr($t, 0, 4);
        return $t;
    }

    public function textPixel($pixel = null)
    {
        if ($pixel == null) {
            $pixel = $this->pixel;
        }
        $id = "X";
        if (isset($pixel["id"])) {
            $id = strtoupper($pixel["id"]);
        }

        $uuid = "X";
        if (isset($pixel["uuid"])) {
            $uuid = $pixel["uuid"];
        }

        $state = "X";
        if (isset($pixel["state"])) {
            $state = strtoupper($pixel["state"]);
        }

        $text = "X";
        if (isset($pixel["text"])) {
            $text = $pixel["text"];
        }

        $refreshed_at = "X";
        if (isset($pixel["refreshed_at"])) {
            $refreshed_at = $pixel["refreshed_at"];
        }

        $text =
            $id .
            " " .
            //            $uuid .
            //            " " .
            " " .
            $state .
            " " .
            $text .
            " " .
            $refreshed_at .
            "\n";
        return $text;
    }

    public function getPixel($text = null)
    {
        if ($text != null) {
            $t = $this->getPixelbyId($text);
            return;
        }

        if (!isset($this->thing->thing->variables)) {
            $this->response .= "No stack found. ";
            return true;
        }

        if (
            isset(
                $this->thing->json->jsontoArray($this->thing->thing->variables)[
                    "pixel"
                ]
            )
        ) {
            // First is there a pixel in this thing.
            $pixel = $this->thing->json->jsontoArray(
                $this->thing->thing->variables
            )["pixel"];

            $pixel_id = "X";
            if (isset($this->pixel["id"])) {
                $this->pixel_id = $pixel["id"];
                $this->response .= "Saw " . $this->pixel_id . ". ";

                $this->getPixelbyId($this->pixel_id);
                return;
            }

            $pixel_id = "X";
            if (isset($pixel["uuid"])) {
                $this->pixel_id = $this->idPixel($pixel["uuid"]);
                $this->response .= "Saw " . $this->pixel_id . ". ";

                $this->getPixelbyUuid($pixel["uuid"]);
                return;
            }

            if (isset($this->pixel["refreshed_at"])) {
                $this->pixel_id = $this->thing->uuid;

                $this->response .= "Saw a pixel in the thing. ";

                $this->getPixelbyId($this->pixel_id);
                return;
            }

            // Get the most recent pixel command.
            //return;
        }
        // Haven't found the pixel in the thing.

        if (!isset($this->pixels)) {
            $this->getPixels();
        }

        foreach ($this->pixels as $i => $pixel) {
            if (isset($pixel["uuid"])) {
                $flag = $this->getPixelbyUuid($pixel["uuid"]);
                return;
            }

            if (isset($pixel["id"])) {
                $flag = $this->getPixelbyId($pixel["id"]);
                return;
            }
        }

        $this->response .= "Did not find a pixel. ";

        // Can't find a pixel.
        return false;
    }

    function getPixels()
    {
        $this->pixelid_list = [];
        $this->pixels = [];

        $things = $this->getThings("pixel");

        if ($things === null) {
            return;
        }
        if ($things === true) {
            return;
        }
        $count = count($things);
        // See if a headcode record exists.
        //$findagent_thing = new Findagent($this->thing, 'pixel');
        //$count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Pixel" found ' . $count . " pixel Things.");

        if (!$this->is_positive_integer($count)) {
            // No pixels found
        } else {
            foreach (array_reverse($things) as $uuid => $thing) {
                $associations = $thing->associations;

                $pixel = [];
                $pixel["associations"] = $associations;

                $variables = $thing->variables;
                if (isset($variables["pixel"])) {
                    if (isset($variables["pixel"]["refreshed_at"])) {
                        $pixel["refreshed_at"] =
                            $variables["pixel"]["refreshed_at"];
                    }

                    if (isset($variables["pixel"]["text"])) {
                        $pixel["text"] = $variables["pixel"]["text"];
                    }

                    if (isset($variables["pixel"]["state"])) {
                        $pixel["state"] = $variables["pixel"]["state"];
                    }

                    $pixel["uuid"] = $uuid;
                    $pixel["id"] = $this->idPixel($uuid);

                    $this->pixels[] = $pixel;
                    $this->pixelid_list[] = $uuid;
                }
            }
        }

        $refreshed_at = [];
        foreach ($this->pixels as $key => $row) {
            $refreshed_at[$key] = $row["refreshed_at"];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->pixels);

        return [$this->pixelid_list, $this->pixels];
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
        $this->choices = $choices;
    }

    function makeWeb()
    {
        $web = null;
        if (isset($this->pixel_thing)) {
            $web = "";
            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
            $web .= "<p>";

            $web .= $this->html_image;

            $web .= "<br>";

            $state_text = "X";
            if (isset($this->pixel_thing->state)) {
                $state_text = strtoupper($this->pixel_thing->state);
            }

            $web .= "PIXEL IS " . $state_text;
            $web = "";
            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
            $web .= "<p>";
            $web .= '<a href="' . $this->link . '">';
            //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/sig>
            $web .= $this->html_image;

            $web .= "</a>";
            $web .= "<br>";

            $state_text = "X";
            if (isset($this->pixel_thing->state)) {
                $state_text = strtoupper($this->pixel_thing->state);
            }

            $web .= "PIXEL IS " . $state_text . "<br>";

            $id_text = "X";
            if (isset($this->pixel_thing->uuid)) {
                $id_text = strtoupper($this->idPixel($this->pixel_thing->uuid));
            }

            $web .= "PIXEL ID " . $id_text . "<br>";

            $web .= "<p>";
        }

        if (!isset($this->pixels)) {
            $this->getPixels();
        }
        $pixel_table = '<div class="Table">
                 <div class="TableRow">
                 <div class="TableHead"><strong>ID</strong></div>
                 <div class="TableHead"><span style="font-weight: bold;">State</span></div>
                 <div class="TableHead"><strong>Text</strong></div></div>';

        if (isset($this->pixels) and is_array($this->pixels)) {
            $pixel_text = "";
            $pixels = [];
            foreach ($this->pixels as $i => $pixel) {
                //  if ($pixel['text'] == "pixel post") {
                if (isset($pixel["uuid"]) and !isset($pixel["id"])) {
                    $pixel["id"] = $this->idPixel($pixel["uuid"]);
                }

                $pixels[] = $pixel;
            }

            $refreshed_at = [];
            foreach ($pixels as $key => $row) {
                $refreshed_at[$key] = $row["id"];
            }
            array_multisort($refreshed_at, SORT_DESC, $pixels);

            foreach ($pixels as $i => $pixel) {
                $pixel_table .= '<div class="TableRow">';

                $pixel_table .=
                    '<div class="TableCell">' .
                    strtoupper($pixel["id"]) .
                    "</div>";

                $state = "X";
                if (isset($pixel["state"])) {
                    $state = $pixel["state"];
                }
                $pixel_table .=
                    '<div class="TableCell">' . strtoupper($state) . "</div>";

                $text = "X";
                if (isset($pixel["text"])) {
                    $text = $pixel["text"];
                }

                $pixel_table .=
                    '<div class="TableCell">' . strtoupper($text) . "</div>";
                $pixel_table .= "</div>";
            }
            $pixel_text = $pixel_table . "</div><p>";

            $web .= "<b>PIXELS FOUND</b><br><p>" . $pixel_text;
            $web .= "<p>";

            $web .= "<b>INFO</b><br><p>";
            $this->makeInfo();
            $web .= $this->info;
        }

        if (isset($this->associations->thing->thing->associations)) {
            $web .= "<p>";
            $association_text = "";

            $associations_array = json_decode(
                $this->associations->thing->thing->associations,
                true
            );
            foreach ($associations_array as $agent_name => $associations) {
                foreach ($associations as $i => $association_uuid) {
                    $association_text .=
                        strtoupper(
                            $this->idPixel($this->idPixel($association_uuid))
                        ) .
                        " " .
                        $agent_name .
                        "<br>";
                }
            }

            $web .= "<b>ASSOCIATIONS FOUND</b><br><p>" . $association_text;
        }

        $this->thing_report["web"] = $web;
    }

    public function makeInfo()
    {
        $id_text = "X";
        if (isset($this->pixel_thing->uuid)) {
            $id_text = strtoupper($this->idPixel($this->pixel_thing->uuid));
        }

        $web = "";
        if (isset($this->pixel_thing->uuid)) {
            if ($this->pixel_thing->uuid == $this->uuid) {
                $web .=
                    "The FORGET button will delete this PIXEL ID " .
                    $id_text .
                    ". There is no undo.<br>";
            } else {
                $web .=
                    "The FORGET button will not delete this PIXEL ID " .
                    $id_text .
                    ". The PIXEL will persist in the text CHANNEL. Text PIXEL THING LINK for a forgettable link.";
            }
        } else {
            $web .= "There are no pixels. Text NEW PIXEL to create a pixel.";
        }

        $this->info = $web;
        $this->thing_report["info"] = $web;
    }

    function makeLink()
    {
        $id_text = "X";
        if (isset($this->pixel_thing->uuid)) {
            $id_text = strtoupper($this->idPixel($this->pixel_thing->uuid));
        }

        $pixel_id = "pixel" . "-" . $id_text;

        $link = $this->web_prefix . "thing/" . $this->uuid . "/pixel";

        if (isset($this->pixel_thing->uuid) and $this->show_uuid == "on") {
            $uuid = $this->pixel_thing->uuid;
            $link = $this->web_prefix . "thing/" . $uuid . "/" . $pixel_id;
        }

        $this->link = $link;
        $this->thing_report["link"] = $link;
    }

    public function associationsPixel()
    {
        $association_array = [];
        if (isset($this->associations->thing->thing->associations)) {
            $associations_array = json_decode(
                $this->associations->thing->thing->associations,
                true
            );
            foreach ($associations_array as $agent_name => $associations) {
                foreach ($associations as $i => $association_uuid) {
                    $association_array[] = [
                        "text" =>
                            strtoupper($this->idPixel($association_uuid)) .
                            " " .
                            $agent_name,
                        "id" => $this->idPixel($association_uuid),
                        "agent_name" => $agent_name,
                    ];
                }
            }
        }

        return $association_array;
    }

    function makeHelp()
    {
        if (!isset($this->pixel_thing->state)) {
            $this->thing_report["help"] = "No pixel thing found.";
            return;
        }
        // Get the latest Help pixel.
        if (!isset($this->thing_report["help"])) {
            $this->helpPixel($this->pixel_thing->state);
        }
    }

    function makeTXT()
    {
        $pixel_id = "X";
        if (isset($this->pixel["id"])) {
            $pixel_id = $this->pixel["id"];
        }
        $txt = "This is PIXEL " . $pixel_id . ". ";

        $state = "X";
        if (isset($this->pixel_thing->state)) {
            $state = $this->pixel_thing->state;
        }

        $txt .= "There is a " . strtoupper($state) . " PIXEL. ";
        if ($this->verbosity >= 5) {
            $txt .=
                "It was last refreshed at " . $this->current_time . " (UTC).";
        }
        $txt .= "\n";
        foreach ($this->pixels as $i => $pixel) {
            $txt .= $this->textPixel($pixel);
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $this->makeLink();

        $state = "X";
        if (isset($this->pixel_thing->state)) {
            $state = $this->pixel_thing->state;
        }

        $pixel_id = "X";
        if (isset($this->pixel_thing->uuid)) {
            $pixel_id = $this->pixel_thing->uuid;
            $pixel_nuuid = strtoupper(substr($pixel_id, 0, 4));
            $pixel_id = $this->idPixel($pixel_id);

            if ($this->show_uuid == "on" and $this->show_link == "off") {
                $pixel_id = $this->pixel_thing->uuid;
            }
        }

        $state_text = "X";
        if ($state != null) {
            $state_text = strtoupper($state);
        }

        $sms_message = "PIXEL " . strtoupper($pixel_id) . " IS " . $state_text;

        $sms_message .= " | ";

        if ($this->verbosity > 6) {
            $sms_message .=
                " | previous state " . strtoupper($this->previous_state);
            $sms_message .= " state " . strtoupper($this->state);
            $sms_message .=
                " requested state " . strtoupper($this->requested_state);
            $sms_message .=
                " current node " .
                strtoupper($this->base_thing->choice->current_node);
        }

        if ($this->verbosity > 0) {
            //    $sms_message .= " | pixel id " . strtoupper($this->pixel['id']);
        }

        if (strtolower($this->input) == "pixels") {
            $sms_message .= " Active pixels: ";
            foreach ($this->pixels as $i => $pixel) {
                if (isset($pixel["id"])) {
                    $sms_message .= strtoupper($pixel["id"]) . " ";
                    $sms_message .= strtoupper($pixel["state"]) . " / ";
                } elseif (isset($pixel["uuid"])) {
                    $sms_message .= $this->idPixel($pixel["uuid"]) . " ";
                    $sms_message .= strtoupper($pixel["state"]) . " / ";
                }
            }
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }

            if ($this->state == "green") {
                $sms_message .= " | MESSAGE Red";
            }
        }
        $sms_message .= "" . trim($this->response);

        if ($this->show_link == "on") {
            $sms_message .= " " . $this->link;
        }

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    function makeMessage()
    {
        $state = "X";
        if (isset($this->pixel_thing->state)) {
            $state = $this->pixel_thing->state;
        }

        $message =
            "This is a PIXEL.  The pixel is a " .
            trim(strtoupper($state)) .
            " PIXEL. ";

        if ($state == "red") {
            $message .= "It is a BAD time at the moment. ";
        }

        if ($state == "green") {
            $message .= "It is a GOOD time now. ";
        }

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    public function makeImage()
    {
        $state = "X";
        if (isset($this->pixel_thing->state)) {
            $state = $this->pixel_thing->state;
        }

        // Create a 1x1 image

        //$this->image = imagecreatetruecolor(60, 125);
        $this->image = imagecreatetruecolor(1, 1);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->light_grey = imagecolorallocate($this->image, 210, 210, 210);
        $this->dark_grey = imagecolorallocate($this->image, 64, 64, 64);

        $this->red = imagecolorallocate($this->image, 231, 0, 0);

        $this->yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->green = imagecolorallocate($this->image, 0, 129, 31);

        $this->color_palette = [$this->red, $this->yellow, $this->green];

        // Draw a white rectangle
        if (!isset($state) or $state == false) {
            $color = $this->grey;
        } else {
            if (isset($this->{$state})) {
                $color = $this->{$state};
            } elseif (isset($this->{"pixel_" . $state})) {
                $color = $this->{"pixel_" . $state};
            }
        }

        $pixel_id = "X";
        if (isset($this->pixel_thing->uuid)) {
            $pixel_id = $this->pixel_thing->uuid;
        }

        $pixel_nuuid = strtoupper(substr($pixel_id, 0, 4));
        $pixel_id = $this->idPixel($pixel_id);

        $width = imagesx($this->image);
        $height = imagesy($this->image);

        $points = [0, 0, 6, 0, 0, 6];

        $hex_colour = "#ffffff";
        if (!$this->isBlank($this->hexColour($state))) {
            $hex_colour = $state;
        }

        $colour = $this->hexColorAllocate($this->image, $hex_colour);

        imagefilledrectangle($this->image, 0, 0, 1, 1, $colour);
    }

    // https://stackoverflow.com/questions/2957609/how-can-i-give-a-color-to-imagecolorallocate
    function hexColorAllocate($im, $hex)
    {
        $hex = ltrim($hex, "#");
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($im, $r, $g, $b);
    }

    public function uuidPixel()
    {
        $this->show_uuid = "on";
    }

    public function linkPixel()
    {
        $this->show_link = "on";
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }
        $agent = new Png($this->thing, "png");

        //$this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;

        $this->thing_report["png"] = $this->PNG;
    }

    public function makeJPEG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }
        $agent = new JPEG($this->thing, "jpeg");

        $agent->makeJPEG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->JPEG = $agent->JPEG;
        $this->JPEG_embed = $agent->JPEG_embed;
        $this->thing_report["jpeg"] = $agent->JPEG;
    }

    public function readPixel()
    {
    }

    public function readSubject()
    {
        $keywords = [
            "pixel",
            "uuid",
            "thing",
            "link",
            "uri",
            "url",
            "web",
            "x",
            "X",
            "red",
            "green",
            "yellow",
            "list",
            "new",
            "make",
            "last",
            "next",
        ];

        $input = $this->input;

        $prior_uuid = null;

        $colours = $this->extractColours($this->input);

        if (count($colours) !== 0) {
            $this->changePixel($colours[0]);
        }

        $filtered_input = $this->assert(strtolower($input));
        if ($filtered_input != "") {
            // Not an empty command.
            // Load in colours and see if there is a colour in the string.
            //$colour_names = $this->loadColours();
            $colour_hex = $this->texthexColour($filtered_input);
            if ($colour_hex !== false) {
                $this->changePixel($colour_hex);
                $this->response .= "Saw " . $filtered_input . " is a colour. ";
            }
        }

        $ngram_agent = new Ngram($this->thing, "ngram");
        $pieces = [];
        $arr = $ngram_agent->getNgrams(strtolower($this->input), 3);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($this->input), 2);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($this->input), 1);
        $pieces = array_merge($pieces, $arr);

        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                //$this->response .= "Got the current pixel. ";
                return;
            }

            if ($input == "pixels") {
                $this->response .= "Got active pixels. ";
                return;
            }
        }

        $uuid_agent = new Uuid($this->thing, "uuid");
        $t = $uuid_agent->extractUuid($input);
        if (is_string($t)) {
            $this->getPixelbyUuid($t);
            return;
        }

        // Okay maybe not a full UUID. Perhaps a NUUID.
        $nuuid_agent = new Nuuid($this->thing, "nuuid");
        $t = $nuuid_agent->extractNuuid($input);

        if (is_string($t)) {
            $response = $this->getPixelbyId($t);

            if ($response != true) {
                $this->response .= "Got pixel " . $t . ". ";

                return;
            }

            $this->response .= "Match not found. ";
        }

        // Lets think about Pixels.
        // A pixel is connected to another pixel.  Directly.

        // So look up the pixel in associations.

        // pixel - returns the uuid and the state of the current pixel

        if ($this->channel_name == "web") {
            $this->response .= "Made a web pixel panel. ";
            // Do not effect a state change for web views.
            return;
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "uuid":
                        case "thing":
                            $this->uuidPixel();
                            break;
                        case "url":
                        case "uri":
                        case "web":
                        case "link":
                            $this->linkPixel();
                            break;
                        case "x":
                            $this->changePixel("X");
                            return;

                        case "list":
                            $this->getPixels();
                            return;

                        case "make":
                        case "new":
                            $this->newPixel();
                            return;

                        case "back":

                        case "next":

                        default:
                    }
                }
            }
        }

        $this->readPixel();
        //$this->response .= "Did not see a command. ";

        // devstack
        //return "Message not understood";
        //return false;
    }
}
