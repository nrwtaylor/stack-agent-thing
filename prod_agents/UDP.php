<?php
namespace Nrwtaylor\StackAgentThing;

class UDP extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    public function initUDP()
    {
        $this->default_server_ip = "192.168.0.255";
        $this->default_server_port = 10110;

        $this->default_server_ip = $this->settingsAgent(
            ["udp", "default_server_ip"],
            "192.168.0.255"
        );

        $this->default_server_port = $this->settingsAgent(
            ["udp", "default_server_port"],
            10110
        );
    }

    function run()
    {
        $this->doUDP();
    }

    public function doUDP()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "UDP | " . strtolower($v) . ".";

            $this->udp_message = $response; // mewsage?
        } else {
            $this->udp_message = $this->agent_input;
        }

        $server_ip = $this->server_ip;
        $server_port = $this->server_port;

        //$server_ip = '127.0.0.1';
        //$server_port = 43278;

        $message = '$GPGLL,3854.928,N,08102.497,W,062554.83,V*02';

        $this->sendUDP([
            "from" => $server_ip . ":" . $server_port,
            "subject" => $message,
        ]);
    }

    public function sendUDP($datagram = null)
    {
        if ($datagram === null) {
            return true;
        }
        if (!is_array($datagram)) {
            return true;
        }

        $server_ip = $this->default_server_ip;
        $server_port = $this->default_server_port;

        if (!isset($datagram["from"])) {
            return true;
        }
        $parts = explode(":", $datagram["from"]);
        if (count($parts) !== 2) {
            return true;
        }

        $server_ip = $parts[0];
        $server_port = intval($parts[1]);

        // test
        $message = "PyHB";
        // nmea test with valid nmea string
        $message = '$GPGLL,3854.928,N,08102.497,W,062554.83,V*02';

        if (isset($datagram["subject"])) {
            $message = $datagram["subject"];
        }

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);

        $bytes_sent = socket_sendto(
            $socket,
            $message,
            strlen($message),
            0,
            $server_ip,
            $server_port
        );

        if ($bytes_sent === false) {
            $this->response .= "Socket not sent. ";
            return;
        }
        $this->response .= "Socket sent. ";
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is a handler for UDP messages.";
        $this->thing_report["help"] =
            "This is about sending messages to hosted ports.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["udp" => ["udp"]];
        $this->sms_message = "" . $this->udp_message . "  " . $this->response;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "udp");
        $choices = $this->thing->choice->makeLinks("udp");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        // test
        $this->server_ip = $this->default_server_ip;
        $this->server_port = $this->default_server_port;

        return false;
    }
}
