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

        $this->url = $this->settingsAgent(["ping", "url"], "localhost");

        $this->node_list = ["ping" => ["pong"]];
    }

    public function get()
    {
        $this->getPing();
    }

    /**
     *
     */
    public function run()
    {
        //        $this->getPing();
    }
    /*
    public function set() {
       if (!isset($this->ping)) {return true;}

       $this->thing->Write('ping', $this->ping);
    }
*/
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

    public function getPings()
    {
        $jobs = [];
        $agent_name = "ping";
        $things = $this->getThings("ping");

        if ($things == []) {
            $this->pings = $jobs;
            return $this->pings;
        }

        foreach (array_reverse($things) as $thing) {
            $subject = $thing->subject;
            $variables = $thing->variables;
            $created_at = $thing->created_at;

            if (isset($variables[$agent_name])) {
                $job = ["subject" => $subject, "created_at" => $created_at];

                $job = array_merge($job, $variables[$agent_name]);

                $jobs = $job;
            }
        }

        $this->pings = array_merge($jobs, $this->pings);
        return $this->pings;
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
        $sms = strtoupper($this->agent_name) . " | ";
        //$sms = "PING | A message from this Identity pinged us.";
        if (!isset($this->ping) or $this->ping == null) {
            $sms .= $this->message . " ";
        }

        $sms .= $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    public function getPing()
    {
        $received_at = $this->created_at;

//        $this->ping_time = time() - $received_at;
        $this->ping_time = strtotime($this->current_time) - $received_at;

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
        if ($this->ping_time < 1) {
            $this->ping_text = "<1 second";
        } else {
            $this->ping_text = $this->thing->human_time($this->ping_time);
        }

        $message = "";
        //$message = "A message from this Identity pinged us.";
        $message .= "Received " . $this->ping_text . " ago.";

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }
    // https://stackoverflow.com/questions/8030789/pinging-an-ip-address-using-php-and-echoing-the-result
    public function addressPing($ip = null)
    {
        // Do not all user provided input ...

        $ip = $this->url;

        //    $pingresult = exec("/bin/ping -n 3 $ip", $outcome, $status);
        $pingresult = exec("/bin/ping -c 3 $ip", $lines, $status);

        if (0 == $status) {
            $status = "OK";
        } else {
            $status = "NOT OK";
        }
        $this->response .= "Address $ip responded " . $status . ". ";

        $last_line = $lines[count($lines) - 1];
        $this->response .= 'Heard "' . $last_line . '". ';
        $number_part = explode("=", $last_line)[1];
        $numbers = explode("/", $number_part);

        foreach ($numbers as $i => $number) {
            $numbers[$i] = trim(rtrim($number, "ms"));
        }

        $this->ping = [
            "minimum" => $numbers[0],
            "average" => $numbers[1],
            "maximum" => $numbers[2],
            "standard_deviation" => $numbers[3],
        ];
    }

    public function hostPing($thing_object = null)
    {
        if ($thing_object == null) {
            return true;
        }

        if (is_array($thing_object)) {
            if (count($thing_object) == 1 and $this->isUrl($thing_object[0])) {
                $text = $thing_object[0];
                $this->response .= "Pinging " . $text . ". ";
                $this->addressPing($text);
            }
        }
    }

    public function extractPing($text = null)
    {
        $parts = explode("=", $text);

        $data = "";
        $address = "";
        if (count($parts) == 2) {
            $data = trim($parts[1]);

            $address = trim($this->extractUrl($parts[0]));

            // dev parse ping

            $ping = [
                "text" => $address . " " . $data,
                "minimum" => null,
                "average" => null,
                "maximum" => null,
                "standard_deviation" => null,
            ];
            return $ping;
        }

        return null;
    }

    /**
     *
     */
    public function readSubject()
    {
        $urls = $this->extractUrls($this->input);

if (strtolower($this->input) == 'pings') {

            $this->getPings();
$this->response .= "Saw pings. ";
return;

}

        $input = $this->assert($this->input);

        $urls = $this->extractUrls($input);

        // dev
        /*
        if ($urls) {
            $this->hostPing($urls);
        }
*/


        if ($this->hasText(strtolower($this->input), "digest")) {
            $this->getPings();
            if ($this->pings == true) {
                $this->response .= "Did not see pings. ";
                return;
            }
            $this->response .= count($this->pings) . " pings seen. ";
            return;
        }

        $ping = $this->extractPing($input);

        if ($ping != null) {
            $this->ping = $ping;

            $this->response .= $ping["text"];
        }
    }
}
