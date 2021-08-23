<?php
/**
 * Ping.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Ping extends Agent
{
    public $var = "hello";
    private $data = "Ping"; // becomes this->data

    /**
     *
     */
    public function init()
    {
        // So I could call
        $this->test = false;
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["ping" => ["pong"]];
    }

    /**
     *
     */
    public function run()
    {
        $this->getPing();
    }

    function test()
    {
        $this->test_result = "Not OK";
        if ($this->ping_time <= 5) {
            $this->test_result = "OK";
        }
    }

    private function socketPing()
    {
        // Apparently this only works with root privilege.

        // Create a package.
        $type = "\x08";
        $code = "\x00";
        $checksum = "\x00\x00";
        $identifier = "\x00\x00";
        $seq_number = "\x00\x00";
        $package =
            $type . $code . $checksum . $identifier . $seq_number . $this->data;

        // Calculate the checksum.
        $checksum = $this->checksumPing($package);

        // Finalize the package.
        $package =
            $type . $code . $checksum . $identifier . $seq_number . $this->data;

        // Create a socket, connect to server, then read socket and calculate.
        if ($socket = socket_create(AF_INET, SOCK_RAW, 1)) {
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
                "sec" => 10,
                "usec" => 0,
            ]);
            // Prevent errors from being printed when host is unreachable.
            @socket_connect($socket, $this->host, null);
            $start = microtime(true);
            // Send the package.
            @socket_send($socket, $package, strlen($package), 0);
            if (socket_read($socket, 255) !== false) {
                $latency = microtime(true) - $start;
                $latency = round($latency * 1000, 4);
            } else {
                $latency = false;
            }
            socket_close($socket);

        } else {
            $latency = false;
        }

        return $latency;
    }

    private function checksumPing($data)
    {
        if (strlen($data) % 2) {
            $data .= "\x00";
        }

        $bit = unpack("n*", $data);
        $sum = array_sum($bit);

        while ($sum >> 16) {
            $sum = ($sum >> 16) + ($sum & 0xffff);
        }

        return pack("n*", ~$sum);
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["keyword"] = "pingback";
        $this->thing_report["help"] = "Checks if the stack is there.";
    }

    /**
     *
     */
    public function makeSMS()
    {
        $this->sms_message = "PING | A message from this Identity pinged us.";
        $this->sms_message .=
            " | Received " .
            $this->thing->human_time($this->ping_text) .
            " ago.";

        $this->thing_report["sms"] = $this->sms_message;
    }

    /**
     *
     */
    public function getPing()
    {
        $received_at = $this->created_at;
        $this->ping_time = time() - $received_at;

        if ($this->ping_time < 1) {
            $this->ping_text = "<1 second";
        } else {
            $this->ping_text = $this->ping_time;
        }
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "A message from this Identity pinged us.";
        $message .=
            " Received " . $this->thing->human_time($this->ping_text) . " ago.";

        $this->sms_message = $message;
        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function readSubject()
    {
        $ping_socket_latency = $this->socketPing();
        if ($ping_socket_latency !== false) {$this->response .= "Socket latency is " . $ping_socket_latency . ". ";}
        $this->response .= "Responded to a ping. ";
    }
}
