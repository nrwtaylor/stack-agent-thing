<?php
/**
 * Token.php
 *
 * @package default
 */

// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Token extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->node_list = ["token" => ["token"]];

        $this->item_minimum_price = 0.79; // Start here.

        //$this->getSlug("123414sdfas asdfsad 234234 *&*dfg") ;
        $this->state = "X";
        if (isset($this->settings["state"])) {
            $this->state = $this->settings["state"];
        }

        $this->thing_report["help"] = "This gets tokens from the datagram.";
        $this->initTokens();
    }

    public function initTokens()
    {
        $this->tokens_resource = null;
        $resource_name = "token/tokens.php";
        $uri = $this->resource_path . $resource_name;
        if (file_exists($uri)) {
            $this->tokens_resource = require $this->resource_path .
                $resource_name;
        }

        $resource_name = "item/items.php";
        $uri = $this->resource_path . $resource_name;
        if (file_exists($uri)) {
            $this->items_resource = require $this->resource_path .
                $resource_name;
        }
    }

    /**
     *
     */
    function get()
    {
        if (!isset($this->alphanumeric_agent)) {
            $this->alphanumeric_agent = new Alphanumeric(
                $this->thing,
                "alphanumeric"
            );
        }

        if (!isset($this->mixed_agent)) {
            $this->mixed_agent = new _Mixed($this->thing, "mixed");
        }
    }

    /**
     *
     */
    function set()
    {
        $this->thing->Write(["token", "refreshed_at"], $this->thing->time());
    }

    public function getToken($text = null)
    {
        if ($text == null) {
            return true;
        }
        $slug = $this->alphanumeric_agent->filterAlphanumeric($text);

        $despaced_slug = preg_replace("/\s+/", " ", $slug);
        $slug = str_replace(" ", "-", $despaced_slug);
        $slug = strtolower($slug);
        $slug = trim($slug, "-");
        $this->slug = $slug;
    }

    public function countTokens($input = null)
    {
        if ($input === null) {
            return true;
        }
        $tokens = explode(" ", $input);
        return count($tokens);
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function testToken($text)
    {
        $text = "20th Century Limited (Great Trains) by Zimmerman, Karl";
    }

    public function extractTokens($text = null)
    {
        // devstack
        if ($text == null) {
            $text = $this->input;
        }

        $sentence_agent = new Sentence($this->thing, $text);

        foreach ($sentence_agent->sentences as $i => $sentence) {
            $extract_string = str_replace("(", $this->uuid, $sentence);
            $extract_string = str_replace(")", $this->uuid, $extract_string);

            $t = explode($this->uuid, $extract_string);
            $this->addTokens($t);

            $extract_string = str_replace("[", $this->uuid, $sentence);
            $extract_string = str_replace("]", $this->uuid, $extract_string);

            $t = explode($this->uuid, $extract_string);
            $this->addTokens($t);

            $extract_string = str_replace("'", $this->uuid, $sentence);
            $extract_string = str_replace("'", $this->uuid, $extract_string);

            $t = explode($this->uuid, $extract_string);
            $this->addTokens($t);

            $extract_string = str_replace('"', $this->uuid, $sentence);
            $extract_string = str_replace('"', $this->uuid, $extract_string);

            $t = explode($this->uuid, $extract_string);
            $this->addTokens($t);

            $extract_string = str_replace("<", $this->uuid, $sentence);
            $extract_string = str_replace(">", $this->uuid, $extract_string);

            $t = explode($this->uuid, $extract_string);
            $this->addTokens($t);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t2 = explode("-", $token_string);
            $this->addTokens($t2);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t3 = explode(",", $token_string);

            $this->addTokens($t3);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t4 = explode(";", $token_string);
            $this->addTokens($t4);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t5 = explode(":", $token_string);
            $this->addTokens($t5);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t5 = explode("/", $token_string);
            $this->addTokens($t5);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t5 = explode("\\", $token_string);

            $this->addTokens($t5);
        }

        //$mixed_agent = new Mixed($this->thing,"mixed");
        //        $mixeds = $this->mixed_agent->extractMixeds($this->input);
        $mixeds = $this->mixed_agent->extractMixeds($text);

        $this->addTokens($mixeds);

        //        $this->getToken($this->input);
        $this->getToken($this->input);

        //$text = str_replace("-", " ", $this->input);
        $text = str_replace("-", " ", $text);

        $t = $this->pairTokens($text);
        $this->addTokens($t);

        $t = $this->tripletTokens($text);
        $this->addTokens($t);

        $t = $this->quadTokens($text);
        $this->addTokens($t);

        $this->trimTokens();

        $this->tokens = array_unique($this->tokens, SORT_REGULAR);

        $this->makeSnippet();
        return $this->tokens;
    }

    public function trimTokens($arr = null)
    {
        if ($arr == null) {
            $arr = $this->tokens;
        }

        foreach ($arr as $i => &$token) {
            $arr[$i] = trim($token);
        }

        $this->tokens = $arr;
        return $arr;
    }

    public function pairTokens($str)
    {
        $t = [];
        $tokens = explode(" ", $str);
        $i = 0;
        foreach ($tokens as $i => $token) {
            if ($i > count($tokens) - 2) {
                break;
            }
            $t[] = $tokens[$i] . " " . $tokens[$i + 1];
            $i += 1;
        }
        return $t;
    }

    public function tripletTokens($str)
    {
        $t = [];
        $tokens = explode(" ", $str);
        $i = 0;
        foreach ($tokens as $i => $token) {
            if ($i > count($tokens) - 3) {
                break;
            }

            $t[] = $tokens[$i] . " " . $tokens[$i + 1] . " " . $tokens[$i + 2];
            $i += 1;
        }

        return $t;
    }

    public function quadTokens($str)
    {
        $t = [];
        $tokens = explode(" ", $str);
        $i = 0;
        foreach ($tokens as $i => $token) {
            if ($i > count($tokens) - 4) {
                break;
            }

            $t[] =
                $tokens[$i] .
                " " .
                $tokens[$i + 1] .
                " " .
                $tokens[$i + 2] .
                " " .
                $tokens[$i + 3];

            $i += 1;
        }

        return $t;
    }

    public function makeSnippet()
    {
        $snippet = '<div class="thing snippet">';
        foreach ($this->tokens as $i => $token) {
            $snippet .= "" . $token . "" . "<br>";
        }
        $snippet .= "</div>";

        $this->thing_report["snippet"] = $snippet;
    }

    public function addTokens($arr = null)
    {
        if ($arr == null) {
            return true;
        }
        if (!isset($this->tokens)) {
            $this->tokens = [];
        }
        $this->tokens = array_merge($this->tokens, $arr);
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.
        $input = $this->agent_input;
        if ($this->agent_input == null or $this->agent_input == "") {
            $input = $this->subject;
        }

        if ($this->agent_input == "token") {
            $input = $this->subject;
        }
        //        $this->input = $input;
        $this->extractTokens();

        // dev not needed for now
        //        $this->extractSlugs($input);
        //        $this->extractSlug();

        if (!isset($this->token) or $this->token == false) {
            $this->getToken($input);
        }
        // Get the recognized tokens.
        foreach ($this->tokens_resource as $token_slug => $token) {
            $token_text = str_replace("-", " ", $token_slug);
            $token_name = str_replace("-token", "", $token_slug);
            //if ($this->matchToken('red-token')) {
            if (
                stripos($input, $token_slug) !== false or
                stripos($input, $token_text) !== false
            ) {
                //if ($input == 'red-token') {
                $this->itemToken($token_name);
                $this->response .=
                    "Made a " . $token_name . " token payment link. ";
                $this->score = strlen($input);
                return;
            }
            //     return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "token") {
                $this->getToken();
                $this->response .= "Last token retrieved. ";
                return;
            }
        }

        $status = true;

        return $status;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/uuid";

        $this->node_list = ["number" => ["number", "thing"]];
        $web = "";

        $web .= "<br>";
        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";

        //        $items = ['red', 'blue', 'yellow', 'green', 'channel'];

        foreach ($this->items_resource as $item_slug => $item) {
            $item_slug = str_replace("-token", "", $item_slug);
            //if ($this->subject == $item . '-token') {
            //if ($this->subject == $item . '-token') {
            if ($this->matchToken($item_slug . "-token")) {
                // Check for some conditions which are problematic.
                if (!isset($item["price"])) {
                    $web .= "<div>Unpriced item retrieved.</div>";
                    continue;
                }
                if ($item["price"] <= $this->item_minimum_price) {
                    $this->freeToken($item_slug);
                    $web .= $this->web_token[$item_slug];
                    continue;
                }

                //
                $this->itemToken($item_slug);
                $web .= $this->web_token[$item_slug];
            }
        }

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    public function matchToken($text = null)
    {
        $slug_agent = new Slug($this->thing, "slug");
        $subject_slug = $slug_agent->getSlug($this->subject);

        if ($this->subject == $text) {
            return true;
        }
        if (stripos($subject_slug, $text) !== false) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    function makeSMS()
    {
        $link_text = "";
        if (isset($this->token_item["title"])) {
            $slug_agent = new Slug($this->thing, "slug");
            $slug = $slug_agent->getSlug($this->token_item["title"]);
            //if (($slug != "") and ($slug != null)) {
            $link = $this->web_prefix . "thing/" . $this->uuid . "/" . $slug;
            $link_text = $link;
        }

        $sms = "TOKEN";
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}
        $sms .= " | ";
        if (isset($this->token)) {
            $sms .= " | " . $this->token;
            //$this->sms_message .= 'devstack';
        }
        $sms .= $link_text;
        $sms .= " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function subsetsTokens($tokens)
    {
        $number_of_tokens = count($tokens);
        $subsets = [];
        foreach (range(0, $number_of_tokens) as $start => $values) {
            foreach (range(0, $number_of_tokens) as $end => $valuee) {
                if ($start > $end) {
                    continue;
                }

                $selected_tokens = [];
                foreach (range($start, $end) as $index => $value) {
                    $selected_tokens[] = trim($tokens[$value]);
                }

                $text = trim(implode(" ", $selected_tokens));

                if ($text == "") {
                    continue;
                }

                $subsets[] = $text;

                //}
            }
        }
        $subsets = array_unique($subsets);

        usort($subsets, function ($a, $b) {
            return mb_strlen($a) - mb_strlen($b);
        });
        // Sort so longest string to shortest string
        $subsets = array_reverse($subsets);

        return $subsets;
    }

    public function itemToken($colour = null)
    {
        if ($colour == null) {
            return true;
        }

        $item = null;

        // Load token defintitions.
        $item = ["text" => ucwords($colour) . " Token", "price" => "1"];
        $item_id = "grey" . "-token";

        if (isset($this->tokens_resource[$colour . "-token"])) {
            $item_id = $colour . "-token";
            $item = $this->tokens_resource[$colour . "-token"];
        }

        // TODO: Check item creation
        // TODO: Build out consistent buttoning

        $item_agent = new Item($this->thing, $item_id);

        $item = $item_agent->item;
        $this->token_item = $item;

        $payment_agent = new Payment($this->thing, "payment");

        $payment_agent->makeSnippet();

        $web = $payment_agent->snippet;

        $help_text = "Support us.";

        $this->help = $help_text;
        $this->thing_report["help"] = $help_text;
        $this->thing_report["info"] = "Generates tokens.";

        //$this->response .= "Made " . $colour . " token. ";

        $this->web_token[$colour] = $web;
    }

    public function freeToken($colour = null)
    {
        if ($colour == null) {
            return true;
        }

        $item = null;

        // Load token defintitions.
        $item = ["text" => ucwords($colour) . " Token", "price" => "1"];
        $item_id = "grey" . "-token";

        if (isset($this->tokens_resource[$colour . "-token"])) {
            $item_id = $colour . "-token";
            $item = $this->tokens_resource[$colour . "-token"];
        }

        // TODO: Check item creation
        // TODO: Build out consistent buttoning

        $item_agent = new Item($this->thing, $item_id);

        $item = $item_agent->item;
        $this->token_item = $item;

        $payment_agent = new Payment($this->thing, "payment");
        $payment_agent->makeSnippet();
        $web = $payment_agent->snippet;

        $button_agent = new Button($this->thing, "button");
        $button_agent->makeSnippet();
        $web = $button_agent->snippet;

        $help_text = "Support us.";

        $this->help = $help_text;
        $this->thing_report["help"] = $help_text;
        $this->thing_report["info"] = "Generates tokens.";

        //$this->response .= "Made " . $colour . " token. ";

        $this->web_token[$colour] = $web;
    }

    /**
     *
     */
    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
        $this->choices = $choices;
    }
}
