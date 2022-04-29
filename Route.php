<?php
/**
 * Route.php
 *
 * @package default
 */

// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Route extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->node_list = ["route" => ["route"]];

        //$this->getRoute("123414sdfas asdfsad 234234 *&*dfg") ;
        $this->state = "X";
        if (isset($this->settings['state'])) {
            $this->state = $this->settings['state'];
        }

        if (
            isset(
                $this->thing->container['api']['route']['allowed_routes_resource']
            )
        ) {
            $this->allowed_routes_resource =
                $this->thing->container['api']['route'][
                    'allowed_routes_resource'
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
       // $this->alphanumeric_agent = new Alphanumeric(
       //     $this->thing,
      //      "alphanumeric"
      //  );
        $this->getRoutes();
    }

    /**
     *
     */
    function set()
    {
        $this->thing->Write(
            ["route", "refreshed_at"],
            $this->thing->time()
        );
    }

    public function getRoute($text = null)
    {
        if ($text == null) {
            return true;
        }
        $route = $this->extractRoute($text);
        $this->route = $route;
        return $route;
   }

   public function pdfRoute($token) {

     $pdf_route = $this->web_prefix . $token . '.pdf';
     return $pdf_route;

    }

    public function extractRoute($text = null)
    {
        if ($text == null) {
            return true;
        }

        $route = str_replace('\'', "", $text);
        $route = str_replace('/', " ", $text);

        //$route = $this->alphanumeric_agent->filterAlphanumeric($route);
        $route = $this->filterAlphanumeric($route);
        $route = preg_replace('/\s+/', ' ', $route);
        //$route = str_replace("'","",$despaced_route);
        //$route = str_replace("/"," ",$route);
        $route = str_replace(" ", "-", $route);
        $route = strtolower($route);
        $route = trim($route, "-");
        return $route;
    }

    public function deRoute($text = null)
    {
        if ($text == null) {
            return true;
        }

        $deroute = str_replace('-', " ", $text);
        return $deroute;
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
        $this->thing_report['help'] = "This makes a route from the datagram.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->subject;

        if ($this->agent_input == "route") {
            $input = $this->subject;
        } elseif ($this->agent_input != null) {
            $input = $this->agent_input;
        }
        $filtered_input = $this->assert($input);

        if (!isset($this->route) or $this->route == false) {
            $this->getRoute($filtered_input);
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'route') {
                $this->getRoute();
                $this->response = "Last route retrieved.";
                return;
            }
        }

        $status = true;

        return $status;
    }

    public function getRoutes() {

        $allowed_endpoints = [];
        if (file_exists($this->resource_path .
            $this->allowed_routes_resource)) {

        $allowed_endpoints = require $this->resource_path .
            $this->allowed_routes_resource;
        }

        $this->routes = $allowed_endpoints;
        return $this->routes;

    }

    public function hasRoute($text = null) {
// dev



//
        if ($text == null) {
            return false;
        }

//$destarred_text = trim(strtolower(str_replace('*'," ",$text)));
//$text = $destarred_text;
        //$allowed_endpoints = require $this->resource_path .
        //    $this->allowed_routes_resource;

        if (in_array($text, $this->routes)) {
            return true;
        }

foreach($this->routes as $i => $route_text) {

$destarred_route_text = trim(strtolower(str_replace('*'," ",$route_text)));


if (strpos($text, $destarred_route_text) !== false) {
    return true;
}

}


        $hyphenated_text = strtolower(str_replace(" ","-",$text));
        if (in_array($hyphenated_text, $this->routes)) {
            return true;
        }

foreach($this->routes as $i => $route_text) {

$destarred_route_text = trim(strtolower(str_replace('*'," ",$route_text)));


if (strpos($hyphenated_text, $destarred_route_text) !== false) {
    return true;
}

}



return false;

    }


    public function isRoute($text = null)
    {
        if ($text == null) {
            return false;
        }

        $allowed_endpoints = require $this->resource_path .
            $this->allowed_routes_resource;

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

$route = trim(str_replace('s/ pdf route','', $this->subject));

$link = $this->pdfRoute($route);
        $this->node_list = ["number" => ["number", "thing"]];
$web = "";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';


        $web = '<a href="' . $link . '">';
/*
        $web .=
            '<img src= "' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/uuid.png">';
*/
$web .= "Downloadable instructions (pdf)";
        $web .= "</a>";

        $web .= "<br>";
        $web .= $this->subject . "<br>";

        /*
        if (!isset($this->routes[0])) {
            $web .= "No routes found<br>";
        } else {
            $web .= "First route is ". $this->routes[0] . "<br>";
            $web .= "Extracted routes are:<br>";
        }
        foreach ($this->routes as $key=>$route) {
            $web .= $route . "<br>";
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
        $route_text = "test";
        if (isset($this->route)) {
            $route_text = "Made route " . $this->route . ". ";
        }

        $sms = "ROUTE | " . $route_text;

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
