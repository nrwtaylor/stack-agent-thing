<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Channel extends Agent
{
    function init()
    {
        $this->start_time = microtime(true);

        $this->thing->log(
            '<pre> Agent "Channel" started running on Thing ' .
                date("Y-m-d H:i:s") .
                '</pre>'
        );
        $this->node_list = array("channel" => array("cue primary channel"));

    }

    public function set()
    {
        $channel_name = "X";
        if (isset($this->channel_name)) {
            $channel_name = $this->channel_name;
        }

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            array("channel", "refreshed_at"),
            $this->thing->json->time()
        );
        $this->thing->json->writeVariable(
            array("channel", "name"),
            $channel_name
        );
    }

    public function get()
    {
        if ($this->agent_input == "channel") {
            $this->thing->json->setField("variables");
            //$this->roll = strtolower($this->thing->json->readVariable( array("roll", "roll") ));
            $this->channel_name = $this->thing->json->readVariable(array(
                "channel",
                "name"
            ));
        } elseif ($this->agent_input != null) {
            $this->channel_name = $this->agent_input;
        }

        //        $this->thing->json->setField("variables");
        //$this->refreshed_at = strtolower($this->thing->json->readVariable( array("roll", "roll") ));
        //        $this->channel_name = $this->thing->json->readVariable( array("channel", "name") );
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // Allow for channel specific injections.
        switch ($this->channel_name) {
            case null:
                break;
            case 'email':
                break;
            case 'sms':
                break;
            case '3':
                break;
            case '4':
                break;
            default:
                break;
        }

        $this->thing_report['message'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    function getMessenger()
    {
        $this->channel_name = "messenger";
        $this->plain_text_statement .= "Public plain text at some point";

        $this->retention_policy = "private potentially forever";
        $this->privacy_expectation = "key access";

        $this->key .= " | key " . $this->uuid . " ";
        $this->reach = "X-X";
        $this->fields = "Z";
        $this->eyes = 2;

        $this->latency = "seconds";
        $this->characters = "unicode";
        $this->threading = "no";

        $this->images = "PNG";

        $this->ux_ui = "cue based";
    }

    function getEmail()
    {
        $this->channel_name = "email";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private potentially forever";

        $this->privacy_expectation = "none";

        $this->reach = "X-X";
        $this->fields = 2;
        $this->eyes = 2;

        $this->latency = "seconds";
        $this->characters = "unicode";
        $this->threading = "no";
        $this->images = "PNG";

        $this->emoji = "yes";

        $this->attachments = "yes";

        $this->voice = "file attachment";
        $this->video = "file attachment";

        $this->presence = "none";

        $this->ux_ui = "client based";
    }

    function getGmail()
    {
        $this->channel_name = "gmail";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private potentially forever";

        $this->privacy_expectation = "none";

        $this->reach = "1-X";
        $this->fields = 2;
        $this->eyes = 2;

        $this->latency = "minutes";
        $this->characters = "unicode";
        $this->threading = "no";
        $this->images = "PNG";

        $this->emoji = "not reviewed";

        $this->attachments = "yes";

        $this->voice = "file attachment";
        $this->video = "file attachment";

        $this->presence = "none";
    }

    function getGooglecalendar()
    {
        $this->channel_name = "google calender";
        $this->plain_text_statement = "encrypted screen delivery";
        $this->retention_policy = "private potentially forever";

        $this->privacy_expectation = "key access";

        $this->reach = "1-X";
        $this->fields = "Z";
        $this->eyes = 2;

        $this->latency = "seconds";
        $this->characters = "unicode";
        $this->threading = "no";
        $this->images = "no";

        $this->emoji = "yes";

        $this->attachments = "awkward";

        $this->voice = "not reviewed";
        $this->video = "not reviewed";

        $this->presence = "none";
        $this->ux_ui = "not reviewed";
    }

    function blankX()
    {
        $variables = array(
            "channel_name",
            "plain_text_statement",
            "retention_policy",
            "reach",
            "fields",
            "eyes",
            "latency",
            "characters",
            "threading",
            "association",
            "cueing",
            "emoji",
            "images",
            "buttons",
            "carousel",
            "attachments",
            "voice",
            "video",
            "presence"
        );

        foreach ($variables as $key => $variable) {
            if (!isset($this->$variable)) {
                $this->$variable = "X";
            }
        }
    }

    function getSlack()
    {
        $this->channel_name = "slack";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private forever";
        $this->privacy_expectation = "none";
        $this->reach = "1-X";
        $this->fields = "Z";
        $this->eyes = "Z";

        $this->association = "3rd party";

        $this->emoji = "yes";

        $this->latency = "seconds";
        $this->characters = "alphanumber";
        $this->threading = "no";
        $this->images = "PNG";
        $this->voice = "not typically used";
        $this->video = "not typically used";

        $this->presence = "sensitive";

        $this->ux_ui = "not reviewed";
    }

    function getMordok()
    {
        $this->channel_name .= "mordok";
        $this->plain_text_statement .= "Public plain text at some point";
        $this->retention_policy = "emphemeral days";

        $this->reach = "1-1";
        $this->fields = 2;
        $this->eyes = "X";

        $this->latency = "seconds";
        $this->characters = "alphanumeric";
        $this->threading = "no";

        $this->association = "yes";
        $this->cueing = "yes";

        $this->emoji = "yes";
        $this->images = "PNG";
        $this->buttons = "yes";

        $this->carousel = "no";

        $this->attachments = "no";
        $this->voice = "no";
        $this->video = "no";

        $this->presence = "none";
        $this->ux_ui = "cue based";
    }

    function getSMS()
    {
        $this->channel_name = "SMS";
        $this->plain_text_statement = "Public plain text";
        $this->retention_policy = "private indefinite";

        $this->reach = "1-1";
        $this->fields = 2;
        $this->eyes = "X";

        $this->latency = "seconds";
        $this->characters = "alphanumeric";
        $this->threading = "no";

        $this->association = "no";
        $this->cueing = "no";

        $this->emoji = "yes";
        $this->images = "PNG";
        $this->buttons = "no";

        $this->carousel = "no";

        $this->attachments = "MMS";
        $this->voice = "no";
        $this->video = "no";

        $this->presence = "none";
        $this->ux_ui = "client based";
    }

    function getConsole()
    {
        $this->channel_name = "console";
        $this->plain_text_statement = "Public plain text";
        $this->retention_policy = "private indefinite";

        $this->reach = "1-1";
        $this->fields = 2;
        $this->eyes = "1";

        $this->latency = "seconds";
        $this->characters = "alphanumeric";
        $this->threading = "no";

        $this->association = "no";
        $this->cueing = "no";

        $this->emoji = "yes";
        $this->images = "PNG";
        $this->buttons = "no";

        $this->carousel = "no";

        $this->attachments = "MMS";
        $this->voice = "no";
        $this->video = "no";

        $this->presence = "none";
        $this->ux_ui = "client based";
    }

    function getSlack2()
    {
        $this->channel_name = "slack";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private forever";
        $this->privacy_expectation = "none";
        $this->reach = "1-X";
        $this->fields = "Z";
        $this->eyes = "Z";

        $this->latency = "seconds";
        $this->characters = "alphanumber";
        $this->threading = "no";
        $this->images = "PNG";

        $this->voice = "not typically used";
        $this->video = "not typically used";

        $this->presence = "slow";

        $this->ux_ui = "slow app start-up";
    }

    function readFrom()
    {
        if (isset($this->channel_name)) {
            return;
        }

        if (strlen($this->from) == 16 and is_numeric($this->from)) {
            //$this->channel = "messenger";
            $this->getMessenger();
            return;
        }

        if (filter_var($this->from, FILTER_VALIDATE_EMAIL)) {
            //$this->channel = "email";
            $this->getEmail();
            return;
        }

        if (strlen($this->from) == 11 and is_numeric($this->from)) {
            // Comes in as 11.  Perhaps has a blank space.
            //$this->channel = "SMS";
            $this->getSMS();
            return;
        }

        if ($this->from == "console") {
            //$this->channel = "console";
            $this->getConsole();
            return;
        }

        $this->channel = "unknown";
        return;
    }

    public function readSubject()
    {
        $this->readFrom();
        $this->blankX();

        $status = true;
        return $status;
    }

    public function PNG()
    {
        $this->thing_report['png'] = null;
        return $this->thing_report['png'];
    }

    function makeSMS()
    {
        $sms_verbosity_levels = array(
            "channel_name" => 1,
            "plain_text_statement" => 2,
            "retention_policy" => 2,
            "reach" => 5,
            "fields" => 5,
            "eyes" => 9,
            "latency" => 6,
            "characters" => 4,
            "threading" => 4,
            "association" => 3,
            "cueing" => 2,
            "emoji" => 2,
            "images" => 7,
            "buttons" => 7,
            "carousel" => 7,
            "attachments" => 8,
            "voice" => 8,
            "video" => 8,
            "presence" => 2
        );

        $this->verbosity = 9;

        $this->sms_message = "CHANNEL ";
        $this->sms_message .= " | " . $this->channel_name;
        $this->sms_message .= " | " . $this->plain_text_statement;
        $this->sms_message .= " | " . $this->retention_policy;

        $this->sms_message .= " | reach " . $this->reach;
        $this->sms_message .= " fields " . $this->fields;
        $this->sms_message .= " eyes " . $this->eyes;

        $this->sms_message .= " | latency " . $this->latency;
        $this->sms_message .= " characters " . $this->characters;
        $this->sms_message .= " threading " . $this->threading;

        $this->sms_message .= " | assocation " . $this->association;
        $this->sms_message .= " cueing " . $this->cueing;

        $this->sms_message .= " | emoji " . $this->emoji;
        $this->sms_message .= " images " . $this->images;
        $this->sms_message .= " buttons " . $this->buttons;

        $this->sms_message .= " | carousel " . $this->carousel;

        $this->sms_message .= " attachments " . $this->attachments;
        $this->sms_message .= " voice " . $this->voice;
        $this->sms_message .= " video " . $this->video;

        $this->sms_message .= " presence " . $this->presence;

        $run_time = microtime(true) - $this->start_time;
        $milliseconds = round($run_time * 1000);
        $this->sms_message .=
            " | ~rtime " . number_format($milliseconds) . "ms";

        $this->sms_message .= " | TEXT " . strtoupper($this->channel_name);

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeEmail()
    {
        if (!isset($this->sms_message)) {
            $this->makeSMS();
        }
        $this->email_message = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
    }
}
