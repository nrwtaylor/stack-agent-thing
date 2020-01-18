<?php
/**
 * Bus.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Bus extends Agent {


    /**
     *
     * @param Thing   $thing
     * @return unknown
     */
    function init() {
        if ($this->thing != true) {

            $this->thing->log ( 'ran on a null Thing ' .  $thing->uuid .  '.');
            $this->thing_report['info'] = 'Tried to run Bus on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;

        }

        $this->thing_report['info'] = 'This is the bus agent.';
        $this->thing_report['help'] = 'This agent takes a Thing and runs the Bus agent on it.';

        $this->agent_name = 'bus';
        $this->agent_version = 'redpanda';

        $this->node_list = array('bus'=>array('what', 'where'), 'transit');

        $this->ignore_list = array('bus', 'transit', 'translink', 'gtfs');

        $this->namespace = "\\Nrwtaylor\\StackAgentThing\\";
    }


    /**
     *
     */
    function run() {
        $this->getLink();

        $this->getBus();

        $this->getHistory();
    }


    /**
     *
     */
    public function getHistory() {

        // $ignore_list = array('bus', 'transit', 'translink');

        $t = "";
        foreach ($this->history as $i=>$event) {
            $text = $event['text'];

            if (in_array(strtolower($event['text']), $this->ignore_list)) {continue;}

            $timestamp = $event['timestamp'];
            $ago = (strtotime($this->current_time) - strtotime($timestamp))/60;
            if ($ago > (2*60)) {break;}

            if (mb_strlen($this->response . $t . $event['text'] . ' / ') > 140) {$t .= "[...]"; break;}

            $t .= $event['text'] . " / ";

        }

        if ($t != "") {$this->response .= "Heard ". $t;}

    }


    /**
     *
     * @return unknown
     */
    public function getBus() {

        if (!isset($this->prior_agent)) {
            $this->response .= "Did not get any transit context. ";
            $this->thing_report['help'] = "Did not get any transit context. ";
            return true;
        }

        $time_agent = new Time($this->thing, "vancouver");
        $current_time_text = $time_agent->text;

        $this->current_time_text = $time_agent->text;

        $current_time = strtotime($current_time_text);


        if ($this->prior_agent != "Translink") {$this->response .= "Text <stop number>. "; return true;}

        try {
            $this->thing->log( $this->agent_prefix .'trying Agent "' . $this->prior_agent . '".', "INFORMATION" );
            $agent_class_name = $this->namespace . ucwords(strtolower($this->prior_agent));

            $agent = new $agent_class_name($this->prior_thing);
            $thing_report = $agent->thing_report;

            $this->text = $thing_report['sms'];

            $t ="";
            $buses = array();
            $tokens = explode("|", $this->text);
            foreach ($tokens as $i=>$token) {

                $b = explode(">", trim(($token)));

                if (count($b) != 2) {continue;}
                $arrival_text = trim(trim($b[0]));
                $arrival_array = explode(" ", $arrival_text);
                $route = $arrival_array[0];
                $time_string = $arrival_array[1];

                $clocktime_agent = new Clocktime($this->prior_thing, $time_string);
                $bus_time_text = $clocktime_agent->hour .":" . $clocktime_agent->minute . "";

                $t .= $bus_time_text . " ";

                $bus_time = strtotime($bus_time_text);

                $minutes = ($bus_time - $current_time ) / 60;

                $destination_text = trim($b[1]);
                $buses[] = array("wait_time"=>$minutes, "route"=>$route, "destination"=>$destination_text);
            }



            $wait_time = array();
            foreach ($buses as $key => $row) {
                $wait_time[$key] = $row['wait_time'];
            }
            array_multisort($wait_time, SORT_DESC, $buses);

            $t = "";
            foreach (array_reverse($buses) as $i=>$bus) {

                if ($bus['wait_time'] == -1) {$t .= $bus['route'] . " just arrived or left. "; continue;}
                if ($bus['wait_time'] < 0) {$t .= $bus['route'] . " -" . -$bus['wait_time'] . " minutes. "; continue;}
                if ($bus['wait_time'] == 0) {$t .= $bus['route'] . " just arriving or left. "; continue;}
                if ($bus['wait_time'] == 1) {$t .= $bus['route'] . " is expected now. "; continue;}

                $t .= $bus['route'] . " +" . $bus['wait_time'] . " minutes. ";

            }
            $this->response .= $t;

        } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.
            $this->thing->log( $this->agent_prefix .'borked on "' . $agent_class_name . '".', "WARNING" );
            $message = $ex->getMessage();
            //$code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . '  ' . $file . ' line:' . $line;


            // This is an error in the Place, so Bork and move onto the next context.
            $bork_agent = new Bork($this->thing, $input);
            return true;
        }
    }


    /**
     *
     */
    public function makeSMS() {

        $prior_agent_text = "";
        if (isset($this->prior_agent)) {
            $prior_agent_text = ucwords($this->prior_agent) ." " . $this->stop_text . " " .$this->current_time_text .  " | ";
        }

        $this->sms_message = "BUS | " . $prior_agent_text . $this->response;
        $this->thing_report['sms'] = $this->sms_message;

    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    function getLink() {
        $things = new FindAgent($this->thing, 'thing');
        $this->history = array();
        foreach ($things->thing_report['things'] as $thing) {

            $this->thing->log($thing['task'] . " " . $thing['nom_to'] . " " . $thing['nom_from']);

            if ($thing['nom_to'] == "usermanager") {
                continue;
            }

            if (in_array(strtolower($thing['task']), $this->ignore_list)) {continue;}

            $variables_json= $thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables['message']['agent'])) {continue;}
            //            } else {
            $block_thing_agent = $variables['message']['agent'];

            if ($block_thing_agent == "Translink") {

                $this->prior_agent = $variables['message']['agent'];
                $previous_thing = new Thing($thing['uuid']);

                // Get first thing.
                if (!isset($this->prior_thing)) {
                    $this->prior_thing = $previous_thing;
                    $this->stop_text = $thing['task'];
                }


                $this->history[] = array("agency"=>$this->prior_agent, "text"=>$thing['task'], "timestamp"=>$thing['created_at']);

            }
            //           }
        }
        return false;

    }




    /**
     *
     * @return unknown
     */
    public function readSubject() {
    }


    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'web/' . $this->uuid . '/thing';

        //$this->node_list = array("web"=>array("iching", "roll"));

        $web = "";

        //        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        //        $web .= "</a>";

        //        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';


        $prior_agent_text = "";
        if (isset($this->prior_agent)) {$prior_agent_text = ucwords($this->prior_agent);}

        $web .= 'The last agent to run was the ' . $prior_agent_text . ' Agent.<br>';

        $web .= "<br>";

        $web .= $this->response . "<br>";

        $this->thing_report['web'] = $web;


    }


}
