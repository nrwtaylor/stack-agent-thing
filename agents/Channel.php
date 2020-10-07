<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Channel extends Agent
{
    function init()
    {
        $this->node_list = ["channel" => ["cue primary channel"]];

        $this->verbosity = 9;

        $this->initChannels();
    }

    public function set()
    {
        $channel_name = "X";
        if (isset($this->channel_name)) {
            $channel_name = $this->channel_name;
        }

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["channel", "refreshed_at"],
            $this->thing->json->time()
        );
        $this->thing->json->writeVariable(["channel", "name"], $channel_name);
    }

    public function initChannels()
    {
        $resource_name = 'channel/channels.php';

        if (!file_exists($this->resource_path . $resource_name)) {
            return true;
        }

        $this->channels_resource = require $this->resource_path .
            $resource_name;
        $this->verbosityChannel();
    }

    public function variableChannel()
    {
    }

    public function verbosityChannel()
    {
        $sms_verbosity_levels = [
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
            "presence" => 2,
        ];
        $this->verbosity_levels['sms'] = $sms_verbosity_levels;
    }

    public function countChannels()
    {
        $things = $this->thing->db->fromCount();

        $channel_count = count($things);
        return $channel_count;
    }

    public function get()
    {
        if ($this->agent_input == "channel") {
            $this->thing->json->setField("variables");
            $this->channel_name = $this->thing->json->readVariable([
                "channel",
                "name",
            ]);
        } elseif ($this->agent_input != null) {
            $this->channel_name = $this->agent_input;
        }
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

    function resourceChannel($channel_name = null)
    {
        $channel_resource = null;
        if (isset($this->channels_resource[$channel_name])) {
            $channel_resource = $this->channels_resource[$channel_name];
        }

        if ($channel_resource == null) {
            $this->channel = $channel_name;

            if (!isset($this->channels_resource['unknown'])) {
                $this->channel = "null";
                return;
            }

            $channel_resource = $this->channels_resource['unknown'];
        }

        if ($channel_resource == null) {
            $this->channel = "null";
            return;
        }

        foreach ($channel_resource as $descriptor => $description) {
            $this->{$descriptor} = $description;
        }

        $this->channel = $channel_name;
    }

    function blankX()
    {
        $variables = [
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
            "presence",
        ];

        foreach ($variables as $key => $variable) {
            if (!isset($this->$variable)) {
                $this->$variable = "X";
            }
        }
    }

    function readFrom($text = null)
    {
        if (isset($this->channel_name)) {
            return;
        }

        if (strlen($this->from) == 16 and is_numeric($this->from)) {
            //$this->channel = "messenger";
            $this->resourceChannel('messenger');

            //$this->getMessenger();
            return;
        }

        if (filter_var($this->from, FILTER_VALIDATE_EMAIL)) {
            //$this->channel = "email";
            $this->resourceChannel('email');
            //$this->getEmail();
            return;
        }

        if (strlen($this->from) == 11 and is_numeric($this->from)) {
            // Comes in as 11.  Perhaps has a blank space.
            $this->resourceChannel('sms');
            //$this->getSMS();
            return;
        }

        if ($this->from == "console") {
            $this->resourceChannel('console');
            //            $this->getConsole();
            return;
        }

        //        $this->channel = "unknown";
        $this->resourceChannel("unknown");
    }

    public function readSubject()
    {
        $this->readFrom();
        $this->blankX();

        $input = $this->input;

        if (stripos($input, "count") !== false) {
            $channel_count = $this->countChannels();
            $this->response .=
                "Counted " .
                $channel_count .
                " unique channels on this stack. ";
            return;
        }

        $filtered_input = trim($this->assert($input));
        $this->resourceChannel($filtered_input);

        $status = true;
        return $status;
    }

    public function PNG()
    {
        $this->thing_report['png'] = null;
        return $this->thing_report['png'];
    }

    public function textChannel()
    {
        $order = [
            'CHANNEL',
            'x',
            'channel_name',
            'plain_text_statement',
            'x',
            'retention_policy',
            'reach',
            'fields',
            'eyes',
            'x',
            'latency',
            'characters',
            'threading',
            'x',
            'association',
            'cueing',
            'emoji',
            'images',
            'buttons',
            'x',
            'carousel',
            'attachments',
            'voice',
            'video',
            'presence',
        ];

        $t = "";
        foreach ($order as $descriptor) {
            if ($descriptor == "x") {
                $t .= "| ";
                continue;
            }

            $verbosity_level = 1;
            if (isset($this->verbosity_levels['sms'][$descriptor])) {
                $verbosity_level = $this->verbosity_levels['sms'][$descriptor];
            }

            if ($verbosity_level <= $this->verbosity) {
                if (isset($this->{$descriptor})) {
                    $text_descriptor = str_replace("_", " ", $descriptor);
                    $text_descriptor = str_replace("-", " ", $text_descriptor);

                    $t .= $text_descriptor . " " . $this->{$descriptor} . " ";
                    continue;
                }

                // Unrecognized. Add as plain text.
                $t .= $descriptor . ' ';
            }
        }
        return $t;
    }

    function makeSMS()
    {
        $sms = $this->textChannel();
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
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
