<?php
/**
 * Token.php
 *
 * @package default
 */

// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Token extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->node_list = ["token" => ["token"]];

        //$this->getSlug("123414sdfas asdfsad 234234 *&*dfg") ;
        $this->state = "X";
        if (isset($this->settings['state'])) {
            $this->state = $this->settings['state'];
        }

        $this->thing_report['help'] = "This gets tokens from the datagram.";
        $this->initTokens();
    }

    public function initTokens()
    {
        $this->tokens_resource = null;
        $resource_name = 'token/tokens.php';
        $uri = $this->resource_path . $resource_name;
        if (file_exists($uri)) {
            $this->tokens_resource = require $this->resource_path .
                $resource_name;
        }
        //$this->verbosityChannel();
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
            $this->mixed_agent = new Mixed($this->thing, "mixed");
        }
    }

    /**
     *
     */
    function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["token", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    public function getToken($text = null)
    {
        if ($text == null) {
            return true;
        }
        //if ($this->state == "off") {$this->slug = ""; return null;}

        //$alphanumeric_agent = new Alphanumeric($this->thing,"alphanumeric");
        $slug = $this->alphanumeric_agent->filterAlphanumeric($text);

        $despaced_slug = preg_replace('/\s+/', ' ', $slug);
        $slug = str_replace(" ", "-", $despaced_slug);
        $slug = strtolower($slug);
        $slug = trim($slug, "-");
        $this->slug = $slug;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        //$this->makeSMS();
        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        //$this->makeWeb();

        //$this->thing_report['thing'] = $this->thing->thing;

        // $this->thing_report['help'] = "This gets tokens from the datagram.";
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
            $t5 = explode('/', $token_string);
            $this->addTokens($t5);
        }

        foreach ($this->tokens as $i => $token_string) {
            $t5 = explode('\\', $token_string);

            $this->addTokens($t5);
        }

        //$mixed_agent = new Mixed($this->thing,"mixed");
        $mixeds = $this->mixed_agent->extractMixeds($this->input);

        $this->addTokens($mixeds);

        $this->getToken($this->input);
        $text = str_replace("-", " ", $this->input);

        $t = $this->pairTokens($text);
        $this->addTokens($t);

        $t = $this->tripletTokens($text);
        $this->addTokens($t);

        $this->trimTokens();

        $this->tokens = array_unique($this->tokens, SORT_REGULAR);

        $this->makeSnippet();
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

    public function makeSnippet()
    {
        $snippet = '<div class="thing snippet">';
        foreach ($this->tokens as $i => $token) {
            $snippet .= "" . $token . "" . "<br>";
        }
        $snippet .= '</div>';

        //echo $snippet;
        $this->thing_report['snippet'] = $snippet;
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
            //} else {
            //    $input = $this->agent_input;
        }
        //        $this->input = $input;
        $this->extractTokens();

        // dev not needed for now
        //        $this->extractSlugs($input);
        //        $this->extractSlug();

        if (!isset($this->token) or $this->token == false) {
            $this->getToken($input);
        }

        //if ($this->matchToken('red-token')) {
        if (
            stripos($input, 'red-token') !== false or
            stripos($input, 'red token') !== false
        ) {
            //if ($input == 'red-token') {
            $this->itemToken('red');
            $this->response .= "Made a red token payment link. ";
        }

        if ($input == 'purple-token') {
            $this->itemToken('purple');
        }

        if ($input == 'blue-token') {
            $this->itemToken('blue');
        }

        if ($input == 'yellow-token') {
            $this->itemToken('yellow');
        }

        if ($input == 'green-token') {
            $this->itemToken('green');
        }

        if ((stripos($input, 'channel-token') !== false) or
            (stripos($input, 'channel-token') !== false)) {
            $this->itemToken('channel');
            $this->response .= "Made a channel token payment link. ";
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'token') {
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
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = ["number" => ["number", "thing"]];
        $web = "";
        /*
        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/uuid.png">';
        $web .= "</a>";
*/
        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        //$web .= $this->subject . "<br>";

        $items = ['red', 'blue', 'yellow', 'green', 'channel'];

        foreach ($items as $item) {
            //if ($this->subject == $item . '-token') {
            //if ($this->subject == $item . '-token') {
            if ($this->matchToken($item . '-token')) {
                $this->itemToken($item);
                $web .= $this->web_token[$item];
            }
        }

        $web .= "<br>";

        $this->thing_report['web'] = $web;
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
        if (isset($this->token_item['title'])) {
            $slug_agent = new Slug($this->thing, "slug");
            $slug = $slug_agent->getSlug($this->token_item['title']);
            //if (($slug != "") and ($slug != null)) {
            $link = $this->web_prefix . 'thing/' . $this->uuid . '/' . $slug;
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
        $this->thing_report['sms'] = $sms;
    }

    public function itemToken($colour = null)
    {
        if ($colour == null) {
            return true;
        }

        $item = null;

        // Load token defintitions.
        $item = ['text' => ucwords($colour) . ' Token', 'price' => '1'];
        $item_id = 'grey' . '-token';

        if (isset($this->tokens_resource[$colour . '-token'])) {
            $item_id = $colour . '-token';
            $item = $this->tokens_resource[$colour . '-token'];
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
        $this->thing_report['help'] = $help_text;
        $this->thing_report['info'] = 'Generates tokens.';

        //$this->response .= "Made " . $colour . " token. ";

        $this->web_token[$colour] = $web;
    }

    /**
     *
     */
    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    /**
     *
     * @return unknown
     */
    /*
    public function makePNG() {
        $text = "thing:".$this->alphas[0];

        ob_clean();

        ob_start();

        QRcode::png($text, false, QR_ECLEVEL_Q, 4);

        $image = ob_get_contents();
        ob_clean();

        $this->thing_report['png'] = $image;
        return $this->thing_report['png'];
    }
*/
}
