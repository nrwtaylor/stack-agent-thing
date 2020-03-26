<?php
/**
 * Log.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Log extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     * @return unknown
     */
    function init()
    {

        if ($this->thing != true) {
            $this->thing->log('ran on a null Thing ' . $thing->uuid . '.');
            $this->thing_report['info'] = 'Tried to run Log on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
        }

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->node_list = array(
            'log' => array('privacy'),
            'code' => array('web', 'log'),
            'uuid' => array('snowflake', 'optin')
        );
    }

    public function run()
    {
        $this->getLink();
        $web_thing = new Thing(null);
        $web_thing->Create(
            $this->from,
            $this->agent_name,
            's/ record web view'
        );

        //$this->makeSnippet();
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            array("log", "received_at"),
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['info'] = 'This is the log agent.';
        $this->thing_report['help'] =
            'This agent shows the log file, and explains it.';

        $this->thing->log(
            '<pre> Agent "Log" credited 25 to the Thing account.  Balance is now ' .
                $this->thing->account['thing']->balance['amount'] .
                '</pre>'
        );

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    function makeSnippet()
    {
        $gap_time_max = 0;
        $lines = explode('<br>', $this->thing->log);
        $log = array();
        foreach ($lines as $i => $line) {
            $line = trim($line);
            $tokens = explode(" ", $line);
            $millisecond_text = $tokens[0];
            $milliseconds = intval(
                trim(str_replace(array("ms", ","), "", $millisecond_text))
            );

            $gap_time = 0;
            if (isset($last_milliseconds)) {
                $gap_time = $milliseconds - $last_milliseconds;
            }

            if ($gap_time > $gap_time_max) {
                $gap_time_max = $gap_time;
                $log_index_max = $i;
            }
            $t = array(
                "number" => $i,
                "line" => $line,
                "elapsed_time" => $milliseconds,
                "gap_time" => $gap_time
            );

            $log[] = $t;
            $last_milliseconds = $milliseconds;
        }

        // Find top-10 time gaps
        $gap_time_sorted_log = $log;
        $gap_time = array();
        foreach ($gap_time_sorted_log as $key => $row) {
            $gap_time[$key] = $row['gap_time'];
        }
        array_multisort($gap_time, SORT_DESC, $gap_time_sorted_log);

        $max_entries = 10;
        $count = 0;
        $highlight = array();
        foreach ($gap_time_sorted_log as $i => $log_entry) {
            $count += 1;
            //var_dump($log_entry['gap_time']);
            $highlight[] = $log_entry['number'];
            if ($count >= $max_entries) {
                break;
            }
        }

        $snippet = "";
        foreach ($log as $i => $log_entry) {
            $line = $log_entry['line'];
            //if ($i == $log_index_max) { $line = '<b>' . $log_entry['line'] . '</b>';}

            foreach ($highlight as $k => $highlight_index) {
                if ($highlight_index == $i) {
                    $line = '<b>' . $log_entry['line'] . '</b>';
                }
            }

            $snippet .= $line . '<br>';
        }
        $this->thing_report['snippet'] = $snippet;
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message = "LOG | No log found.";
        if (strtolower($this->prior_agent) == "php") {
            $this->sms_message = "LOG | No log available.";
        } else {
            $this->sms_message =
                "LOG | " .
                $this->web_prefix .
                "" .
                $this->link_uuid .
                "/" .
                strtolower($this->prior_agent) .
                ".log";
        }

        $this->sms_message .= " | TEXT INFO";
        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    function getLink()
    {
        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index = 0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            $this->thing->log(
                $block_thing['task'] .
                    " " .
                    $block_thing['nom_to'] .
                    " " .
                    $block_thing['nom_from']
            );

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {
                    break;
                }
            }
        }

        $previous_thing = new Thing($block_thing['uuid']);

        if (!isset($previous_thing->json->array_data['message']['agent'])) {
            $this->prior_agent = "php";
        } else {
            $this->prior_agent =
                $previous_thing->json->array_data['message']['agent'];
        }

        return $this->link_uuid;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->defaultButtons();

        $status = true;
        return $status;
    }

    /**
     *
     */
    function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "php"
        );
        $choices = $this->thing->choice->makeLinks('php');

        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    public function makePDF()
    {
        $this->thing->report['pdf'] = false;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'web/' . $this->uuid . '/thing';

        $this->node_list = array("web" => array("iching", "roll"));

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        //$web .= "</a>";
        //$web .= "<br>";
        //$web .= '<img src= "https://stackr.ca/thing/' . $this->link_uuid . '/flag.png">';

        $web = "";
        $web .= '<b>' . ucwords($this->prior_agent) . ' Agent</b>';

        //$web .= 'The last agent to run was the ' . ucwords($this->prior_agent) . ' Agent.<br>';

        $web .= '<br>This Thing said it heard, "' . $this->subject . '".';

        $web .=
            '<br>This will provide a full log description of what the code did with datagram.';

        $web .= '<br>' . $this->sms_message . "<br>";
        //$web .= 'About '. $this->thing->created_at;

        $received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(time() - $received_at);
        $web .= "About " . $ago . " ago.";

        $web .= "<br>";
        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function defaultButtons()
    {
        if (rand(1, 6) <= 3) {
            $this->thing->choice->Create('php', $this->node_list, 'start a');
        } else {
            $this->thing->choice->Create('php', $this->node_list, 'start b');
        }

        //$this->thing->choice->Choose("inside nest");
        $this->thing->flagGreen();
    }
}
