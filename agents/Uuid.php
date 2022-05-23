<?php
/**
 * Uuid.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

use Ramsey\Uuid\Uuid as RamseyUuid;
//use Ramsey\Uuid\Exception\UnsatisfiedDependencyException as MerpB;

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
        $this->node_list = [];

        $this->aliases = ["learning" => ["good job"]];
        $this->makePNG();

        $this->thing_report['help'] =
            "Makes a universally unique identifier. Try NUUID.";

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';
    }

    /**
     *
     */
    function getQuickresponse()
    {
        $this->qr_agent = new Qr($this->thing, $this->link);
        $this->quick_response_png = $this->qr_agent->PNG_embed;
        $this->html_image = $this->qr_agent->html_image;
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

    public function isUuid($text = null) {

        if ($text == null) {return false;}

$uuids = $this->extractUuids($text);
//        if (!isset($this->uuids)) {$this->extractUuids($text);}

        if (count($uuids) != 1) {return false;} // Too many. Is not A uuid.


//        if (count($this->uuids) != 1) {return false;} // Too many. Is not A uuid.

//        if (strtolower($this->uuids[0]) == strtolower($text)) {
        if (strtolower($uuids[0]) == strtolower($text)) {

            return true;

        }

        return false;

    }

    // dev problem redeclaring class name.
    public static function createUuid() {

        return (string) RamseyUuid::uuid4();

    }

    public function hasUuid($text = null) {

        if ($text == null) {return false;}

        if (!isset($this->uuids)) {$this->extractUuids($text);}

        if (count($this->uuids) > 0) {return true;} // Too many. Is not A uui>
        return false;

    }


    public function set()
    {
        $this->thing->Write(
            ["uuid", "refreshed_at"],
            $this->thing->time()
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

        $uuids = $this->extractUuids($input);

        $stripped_input = $input;
        foreach ($uuids as $i => $uuid) {
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

    public function decimalUuid($uuid = null)
    {
        $hex = str_replace("-", "", $uuid);

        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd(
                $dec,
                bcmul(
                    strval(hexdec($hex[$i - 1])),
                    bcpow('16', strval($len - $i))
                )
            );
        }
        return $dec;
    }

    /**
     *
     */
    public function binaryUuid()
    {
        $hex = str_replace("-", "", $this->uuid);

        $bin = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd(
                $dec,
                bcmul(
                    strval(hex2bin($hex[$i - 1])),
                    bcpow('16', strval($len - $i))
                )
            );
        }

    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractUuid($input)
    {
        if (!isset($this->uuids)) {
            $this->uuids = $this->extractUuids($input);
        }
        $uuids = $this->uuids;

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

    public function readUuid($text = null)
    {
        $text = $this->input;
        return $text;
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
//            $this->getQuickresponse();
        }
//        $web .= $this->html_image;

        $web .= "</a>";

        $web .= "<br>";
        $web .= $this->readUuid() . "<br>";
            "CREATED AT " .
            strtoupper(date('Y M d D H:m', strtotime($this->created_at))) .
            "<br>";

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

    public function validateUuids($uuids = null)
    {
        if ($uuids == null) {
            $uuids = $this->uuids;
        }

        foreach ($uuids as $i => $uuid) {
            $t = new Thing($uuid);
            if ($t->thing !== false) {
                if ($t->from == hash('sha256', $this->from)) {
                    $this->response .= 'Channel ' . $uuid . '. ';
                } else {
                    $this->response .= 'Recognized ' . $uuid . '. ';
                }
            } else {
                $this->response .= 'Did not recognize ' . $uuid . '. ';
            }
        }
    }
    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;
        $this->extractUuid($input);

        if ($this->uuids == []) {
            $this->response .= "Got uuid " . $this->uuid . ". ";
        }

        // Then look for messages sent to UUIDS
        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        if (preg_match($pattern, $this->to)) {
            $this->thing->log('Agent "UUID" found a UUID in address.');
        }

        $status = true;
        return $status;
    }

    /**
     *
     */
    function makeSMS()
    {
        $response_text = $this->response;
        if ($this->response == "") {$response_text = "No UUID response from this channel.";} 

        $sms = "UUID | ";
        $sms .= "" . $response_text;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
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
