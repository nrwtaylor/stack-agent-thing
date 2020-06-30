<?php
/**
 * Uuid.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// Recognizes and handles UUIDS.
// Does not generate them.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Uuid extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];
        //var_dump("uuid");
        //var_dump($this->thing->thing->created_at);
        //var_dump($this->thing->thing->created_at);
        //        $this->created_at =  strtotime($this->thing->thing->created_at);

        $this->thing->log(
            'started running on Thing ' . date("Y-m-d H:i:s") . ''
        );

        //       $this->node_list = array("uuid"=>
        //           array("uuid", "snowflake"));
        $this->node_list = [];

        $this->aliases = ["learning" => ["good job"]];
        $this->makePNG();

        $this->thing_report['help'] =
            "Makes a universally unique identifier. Try NUUID.";

        $this->thing->log('Agent "Uuid" found ' . $this->uuid);

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->created_at = strtotime($this->thing->thing->created_at);
    }

    /**
     *
     */
    function getQuickresponse()
    {
        $this->uuid_agent = new Qr($this->thing, $this->link);
        $this->quick_response_png = $this->uuid_agent->PNG_embed;
        $this->html_image = $this->uuid_agent->html_image;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractUuids($input)
    {
        if (!isset($this->uuids)) {
            $this->uuids = [];
        }

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->uuids = $arr;
        return $arr;
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["uuid", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function stripUuids($input = null)
    {
        if ($input == null) {
            $input = $this->input;
        }
        if (!isset($this->uuids)) {
            $this->extractUuids($input);
        }
        $stripped_input = $input;
        foreach ($this->uuids as $i => $uuid) {
            $stripped_input = str_replace(
                strtolower($uuid),
                " ",
                strtolower($stripped_input)
            );
        }

        if ($input == $this->input) {
            $this->stripped_input = $stripped_input;
        }

        return $stripped_input;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractUuid($input)
    {
        $uuids = $this->extractUuids($input);
        if (!is_array($uuids)) {
            return true;
        }

        if (is_array($uuids) and count($uuids) == 1) {
            $this->uuid = $uuids[0];
            $this->thing->log(
                'found a uuid (' . $this->uuid . ') in the text.'
            );
            return $this->uuid;
        }

        if (is_array($uuids) and count($uuids) == 0) {
            return false;
        }
        if (is_array($uuids) and count($uuids) > 1) {
            return true;
        }

        return true;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $alt_text = "a QR code with a uuid";
        $web = '<a href="' . $link . '">';
        //$web_prefix = "http://localhost:8080/";
        if (!isset($this->html_image)) {
            $this->getQuickresponse();
        }
        $web .= $this->html_image;

        $web .= "</a>";

        $web .= "<br>";

        $web .=
            "CREATED AT " .
            strtoupper(date('Y M d D H:m', $this->created_at)) .
            "<br>";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions
        $this->thing_report['email'] = $this->thing_report['sms'];

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // Then look for messages sent to UUIDS
        $this->thing->log('Agent "UUID" looking for UUID in address.');

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        if (preg_match($pattern, $this->to)) {
            $this->thing->log('Agent "UUID" found a  UUID in address.');
        }

        $status = true;
        return $status;
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message = "UUID | ";
        $this->sms_message .= $this->uuid;
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create("uuid", $this->node_list, "uuid");

        $choices = $this->thing->choice->makeLinks("uuid");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }
}
