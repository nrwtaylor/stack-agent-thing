<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack lots of work here

class Route extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->keywords = ['next', 'accept', 'clear', 'drop', 'add', 'new'];

        $this->default_route = "Place";

        $this->default_alias = "Thing";

        $this->test = "Development code"; // Always iterative.

        // Agent variables
        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

        $this->variables = new Variables(
            $this->thing,
            "variables route " . $this->from
        );

        $this->state = null; // to avoid error messages
    }

    public function setRoute($route = null)
    {
        $route = null;
        if (isset($this->route)) {
            $route = $this->route;
        }

        $route['refreshed_at'] = $this->current_time;

        $this->thing->json->writeVariable(["route"], $route);
    }

    function set()
    {
        $this->setRoute();

        //$this->route ="meep";
        $this->variables->setVariable("route", $this->route);
        $this->variables->setVariable("head_code", $this->head_code);
        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    function getRoutes()
    {
        $this->routes = [];
        // See if a route record exists.
        $findagent_thing = new Findagent($this->thing, 'route');

        foreach (
            array_reverse($findagent_thing->thing_report['things'])
            as $thing_object
        ) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            $variables_json = $thing_object['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables['route'])) {
                //$head_code = $variables['route']['head_code'];
                //$route = $variables['route']['route'];

                //$variables['headcode'][] = $thing_object['task'];
                $this->routes[] = $variables['route'];
            }
        }
        return $this->routes;
    }

    function getRoute($selector = null)
    {
        $this->route = "Place";

        if (!isset($this->routes)) {
            $this->getRoutes();
        }

        foreach ($this->routes as $key => $route) {
            //var_dump( $key);
            //echo $route['route'];;
        }
    }

    function get($route = null)
    {
        // This is a request to get the headcode from the Thing
        // and if that doesn't work then from the Stack.

        // 0. light engine with or without break vans.
        // Z. Always has been a special.
        // 10. Because starting at the beginning is probably a mistake.
        // if you need 0Z00 ... you really need it.

        // Take a look at this thing for IChing variables.

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "route",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["route", "refreshed_at"],
                $time_string
            );
        }

        $this->thing->json->setField("variables");
        $this->route = $this->thing->json->readVariable(["route"]);

        if (!isset($this->route)) {
            $this->route = $this->variables->getVariable('route');
            $this->head_code = $this->variables->getVariable('head_code');
        }

        $this->getRoute();
    }

    function makeRoute($head_code = null)
    {
        //$this->route = "Place";
    }

    function deprecate_headcodeTime($input = null)
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

        //echo $t->format("Y-m-d H:i:s");
        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $headcode_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->headcode_time = $headcode_time;
        }

        return $headcode_time;
    }

    //function read($text = null)
    //{
    //    $this->thing->log("read");
    //}

    function addHeadcode()
    {
        $this->get();
    }

    function makeTXT()
    {
        $txt = "Test \n";
        foreach ($this->routes as $i => $route) {
            //$txt .= $variable['head_code'] . " | " . $variable['route'];

            if (!isset($route['places'])) {
                continue;
            }
            $txt .= $this->textRoute($route);
            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report['email'] = $this->thing_report['sms'];
        $this->thing_report['message'] = $this->thing_report['sms']; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report['help'] = 'This is a route.';
    }

    public function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function placesRoute($text = null)
    {
        $tokens = array_map('trim', explode('>', $text));

        $places = [];
        foreach ($tokens as $i => $token) {
            $places[] = $token;
        }

        return $places;
    }

    public function isRoute($route = null)
    {
        if ($route == null) {
            return false;
        }
        if ($route == []) {
            return false;
        }

        if (isset($route['places'])) {
            return true;
        }

        return false;
    }

    public function readRoute($text = null)
    {
        $places = $this->placesRoute($text);

        $route = ['places' => $places];
        return $route;
    }

    public function textRoute($route = null)
    {
        if ($route == null) {
            return true;
        }
        if ($this->isRoute($route) === false) {
            return true;
        }

        $text = implode(' > ', $route['places']);
        $text = ucwords($text);
        return $text;
    }

    public function makeSMS()
    {
        $sms =
            "ROUTE " . $this->textRoute($this->route) . " " . $this->response;

        //        $sms_message .= " | headcode " . strtoupper($this->head_code);
        //        $sms_message .= " | nuuid " . strtoupper($this->variables->nuuid);
        //        $sms_message .=
        //            " | ~rtime " .
        //            number_format($this->thing->elapsed_runtime()) .
        //            "ms";
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    // TODO
    public function nextRoute()
    {
        $this->response .= "Request for the next route seen. ";
    }

    // TODO
    public function dropRoute()
    {
        $this->response .= "Request to drop route seen. ";
    }

    // TODO
    public function addRoute()
    {
        $this->response .= "Request to add route seen. ";
    }

    public function recognizeRoute($route)
    {
        if ($this->isRoute($route) === false) {
            return false;
        }

        if (!isset($this->routes)) {
            $this->getRoutes();
        }

        foreach ($this->routes as $i => $known_route) {
            if ($this->isRoute($known_route) === false) {
                continue;
            }
            if ($known_route['places'] === $route['places']) {
                $this->response .= "Recognized route. ";
                return $this->routes[$i];
            }
        }

        $this->response .= "New route seen. ";

        return false;
    }

    public function readSubject()
    {
        $input = $this->input;

        // Is there a headcode in the provided datagram
        $headcode = new Headcode($this->thing, "extract");
        if (isset($headcode->head_code)) {
            $this->head_code = $headcode->head_code;
        }
        //if (!isset($this->head_code)) {$this->route = "Place";}
        //var_dump($this->head_code);
        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {
            return;
        }

        if ($input == "route") {
            return;
        }

        $pos = stripos($input, "route");
        if ($pos === 0) {
            $input = trim(substr_replace($input, "", 0, strlen('route')));
        }

        $route = $this->readRoute($input);

        if ($this->isRoute($route) === true) {
            $this->recognizeRoute($route);

            $this->route = $route;
        }

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'route') {
                $this->readRoute();
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextheadcode");
                            $this->nextRoute();
                            break;

                        case 'drop':
                            $this->dropRoute();
                            break;

                        case 'add':
                            $this->addRoute();
                            break;

                        default:
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }
    }
}
