<?php
/**
 * Slug.php
 *
 * @package default
 */

// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Slug extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->node_list = ["slug" => ["slug"]];

        //$this->getSlug("123414sdfas asdfsad 234234 *&*dfg") ;
        $this->state = "X";
        if (isset($this->settings['state'])) {
            $this->state = $this->settings['state'];
        }

        if (
            isset(
                $this->thing->container['api']['slug']['allowed_slugs_resource']
            )
        ) {
            $this->allowed_slugs_resource =
                $this->thing->container['api']['slug'][
                    'allowed_slugs_resource'
                ];
        }
    }

    /**
     *
     */
    function get()
    {
        $this->alphanumeric_agent = new Alphanumeric(
            $this->thing,
            "alphanumeric"
        );
    }

    /**
     *
     */
    function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["slug", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    public function getSlug($text = null)
    {
        if ($text == null) {
            return true;
        }
        $slug = $this->extractSlug($text);
        $this->slug = $slug;
        return $slug;

        $slug = $this->alphanumeric_agent->filterAlphanumeric($text);

        $despaced_slug = preg_replace('/\s+/', ' ', $slug);
        $slug = str_replace("'", "", $despaced_slug);
        $slug = str_replace(" ", "-", $slug);
        $slug = strtolower($slug);
        $slug = trim($slug, "-");

        $this->slug = $slug;
        return $slug;
    }

    public function extractSlug($text = null)
    {
        if ($text == null) {
            return true;
        }

        $slug = str_replace('\'', "", $text);
        $slug = str_replace('/', " ", $text);

        //$slug = $this->alphanumeric_agent->filterAlphanumeric($slug);
        $slug = $this->filterAlphanumeric($slug);
        $slug = preg_replace('/\s+/', ' ', $slug);
        //$slug = str_replace("'","",$despaced_slug);
        //$slug = str_replace("/"," ",$slug);
        $slug = str_replace(" ", "-", $slug);
        $slug = strtolower($slug);
        $slug = trim($slug, "-");
        return $slug;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report['help'] = "This makes a slug from the datagram.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->subject;

        if ($this->agent_input == "slug") {
            $input = $this->subject;
        } elseif ($this->agent_input != null) {
            $input = $this->agent_input;
        }
        $filtered_input = $this->assert($input);

        if (!isset($this->slug) or $this->slug == false) {
            $this->getSlug($filtered_input);
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'slug') {
                $this->getSlug();
                $this->response = "Last slug retrieved.";
                return;
            }
        }

        $status = true;

        return $status;
    }

    public function getSlugs() {

        $allowed_endpoints = [];
        if (file_exists($this->resource_path .
            $this->allowed_slugs_resource)) {

        $allowed_endpoints = require $this->resource_path .
            $this->allowed_slugs_resource;
        }

        $this->slugs = $allowed_endpoints;
        return $this->slugs;

    }

    public function isSlug($text = null)
    {
        if ($text == null) {
            return false;
        }

        $allowed_endpoints = require $this->resource_path .
            $this->allowed_slugs_resource;

        if (in_array($text, $allowed_endpoints)) {
            return true;
        }

$hyphenated_text = strtolower(str_replace(" ","-",$text));
        if (in_array($hyphenated_text, $allowed_endpoints)) {
            return true;
        }



        return false;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = ["number" => ["number", "thing"]];

        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/uuid.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->subject . "<br>";

        /*
        if (!isset($this->slugs[0])) {
            $web .= "No slugs found<br>";
        } else {
            $web .= "First slug is ". $this->slugs[0] . "<br>";
            $web .= "Extracted slugs are:<br>";
        }
        foreach ($this->slugs as $key=>$slug) {
            $web .= $slug . "<br>";
        }

        if ($this->recognize_french == true) {
            // devstack
        }
*/
        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        $slug_text = "test";
        if (isset($this->slug)) {
            $slug_text = "Made slug " . $this->slug . ". ";
        }

        $sms = "SLUG | " . $slug_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
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
}
