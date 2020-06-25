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

    function set()
    {
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

//            $thing = new Thing($uuid);
//            $variables = $thing->account['stack']->json->array_data;

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

        if (!isset($this->route)) {
            $this->route = $this->variables->getVariable('route');
            $this->head_code = $this->variables->getVariable('head_code');
        }

        $this->getRoute();
    }

    function dropRoute()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop a route.");

        return;
        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->headcode_thing)) {
            $this->headcode_thing->Forget();
            $this->headcode_thing = null;
        }

        $this->get();
    }

    function makeRoute($head_code = null)
    {
        $this->route = "Place";
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

        //echo $t->format("Y-m-d H:i:s");
        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $headcode_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->headcode_time = $headcode_time;
        }

        return $headcode_time;
    }

    function read()
    {
        $this->thing->log("read");

        //        $this->get();
        return;
    }

    function addHeadcode()
    {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    function makeTXT()
    {
        $txt = "Test \n";
        foreach ($this->routes as $variable) {
            //$txt .= $variable['head_code'] . " | " . $variable['route'];

            if (isset($varibale['route'])) {
                $txt .= $variable['route'];
            }
            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = "route";

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

//        $this->makeTXT();
/*
        $sms_message = "ROUTE " . ucwords($this->route);

        //        $sms_message .= " | headcode " . strtoupper($this->head_code);
        $sms_message .= " | nuuid " . strtoupper($this->variables->nuuid);
        $sms_message .=
            " | ~rtime " .
            number_format($this->thing->elapsed_runtime()) .
            "ms";

        $this->thing_report['sms'] = $sms_message;
*/
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

        //echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
        //echo $message;

        return;
    }

public function makeSMS() {

        $sms_message = "ROUTE " . ucwords($this->route);

        //        $sms_message .= " | headcode " . strtoupper($this->head_code);
//        $sms_message .= " | nuuid " . strtoupper($this->variables->nuuid);
//        $sms_message .=
//            " | ~rtime " .
//            number_format($this->thing->elapsed_runtime()) .
//            "ms";

        $this->thing_report['sms'] = $sms_message;


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
        /*
        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->from . " " . $this->subject);
        }
*/
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

        $this->get();

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'route') {
                $this->read();
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextheadcode");
                            $this->nextheadcode();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextheadcode");
                            $this->dropheadcode();
                            break;

                        case 'add':
                            //     //$this->thing->log("read subject nextheadcode");
                            //$this->makeheadcode();
                            $this->get();
                            break;

                        default:
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }

        if ($this->isData($this->route)) {
            $this->set();
            return;
        }

        $this->read();

        return "Message not understood";

        return false;
    }
}
