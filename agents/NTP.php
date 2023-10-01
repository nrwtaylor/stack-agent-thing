<?php
/**
 * Cat.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class NTP extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init()
    {
        $this->agent_name = "ntp";
        $this->test = "Development code";
        $this->thing_report["info"] =
            "This connects to an authorative time server.";
        $this->thing_report["help"] = "Get the time. Text CLOCKTIME.";
        $this->epoch_convert = 2208988800;
    }

    /**
     *
     * @return unknown
     */

    public function respondResponse()
    {
       // $this->thing->flagGreen();

       // $to = $this->thing->from;
       // $from = "ntp";

       // $this->makeSMS();
       // $this->makeChoices();

        //$this->thing_report["info"] = "This is a ntp in a park.";
        //$this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text RANGER.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->node_list = ["ntp" => ["ntp"]];
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report["sms"] = $m;
    }

    /* Query a time server (C) 1999-09-29, Ralf D. Kloth (QRQ.software) <ralf at qrq.de> */
    function query_time_server($timeserver, $socket)
    {
        $fp = fsockopen($timeserver, $socket, $err, $errstr, 5);
        # parameters: server, socket, error code, error text, timeout
        if ($fp) {
            fputs($fp, "\n");
            $timevalue = fread($fp, 49);
            fclose($fp); # close the connection
        } else {
            $timevalue = " ";
        }

        $ret = [];
        $ret[] = $timevalue;
        $ret[] = $err; # error code
        $ret[] = $errstr; # error text
        return $ret;
    }

    // function query_time_server

    /**
     *
     */
    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function timestampNTP($ntp = null)
    {
        if ($ntp == null) {
            return true;
        }
        return $ntp - $this->epoch_convert;
    }

    function doNTP($text = null)
    {
        // If we didn't receive the command NTP ...
        if (strtolower($this->input) != "ntp") {
            $this->ntp_message = $this->ntp_response;
            $this->response = $this->ntp_response;
            return;
        }

        // "None" agent command received.
        if ($this->agent_input == null) {
            $token_thing = new Tokenlimiter($this->thing, "ntp");

            $dev_overide = null;
            if (
                $token_thing->thing_report["token"] == "ntp" or
                $dev_overide == true
            ) {
                // From example
                $timeserver = "ntp.pads.ufrj.br";
                // Dev neither of these two are working.
                $timeserver = "time.nrc.ca";
                $timeserver = "time.chu.nrc.ca";
                // This is an older protocol version.
                $timeserver = "time4.nrc.ca";

                $timercvd = $this->query_time_server($timeserver, 37);

                $this->time_zone = "America/Vancouver";

                //if no error from query_time_server
                if (!$timercvd[1]) {
                    $timevalue = bin2hex($timercvd[0]);
                    $timevalue = abs(
                        HexDec("7fffffff") -
                            HexDec($timevalue) -
                            HexDec("7fffffff")
                    );
                    $tmestamp = $timevalue - 2208988800; # convert to UNIX epoch time stamp
                    $epoch = $tmestamp;
                    //    $datum = date("Y-m-d H:i:s",$tmestamp - date("Z",$tmestamp)); /* incl time zone offset */

                    //    $d = date("Y-m-d H:i:s",$tmestamp - date("Z",$tmestamp)); /* incl time zone offset */

                    //$datum = $dt = new \DateTime($tmestamp, new \DateTimeZone("UTC"));
                    $datum = new \DateTime("@$epoch", new \DateTimeZone("UTC"));

                    //$datum->setTimezone($tz);

                    //                $dt = new \DateTime($prediction['date'], new \DateTimeZone("UTC"));

                    $datum->setTimezone(new \DateTimeZone($this->time_zone));

                    $m = "Time check from time server " . $timeserver . ". ";

                    $m =
                        "In the timezone " .
                        $this->time_zone .
                        ", it is " .
                        $datum->format("l") .
                        " " .
                        $datum->format("d/m/Y, H:i:s") .
                        ". ";
                } else {
                    $m = "Unfortunately, the time server $timeserver could not be reached at this time. ";
                    $m .= "$timercvd[1] $timercvd[2].\n";
                }

                $this->response = $m;

                // Bear goes back to sleep.
                $this->bear_message = $this->response;
            }
        } else {
            $this->bear_message = $this->agent_input;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //        $this->doNTP($this->input);

        return false;
    }
}
