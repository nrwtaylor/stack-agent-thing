<?php
namespace Nrwtaylor\StackAgentThing;

// GPS conversion algorithm
// dev need authorative list of GPS leap seconds.
// https://www.andrews.edu/~tzs/timeconv/timealgorithm.html

// Experimental. Use at your own risk. Not for navigation.

// review https://www.ietf.org/timezones/data/leap-seconds.list
// review https://www.usno.navy.mil/USNO/time/master-clock/leap-seconds
// not found http://maia.usno.navy.mil/ser7/tai-utc.dat list of historical leap seconds
// https://kb.meinbergglobal.com/kb/time_sync/ntp/configuration/ntp_leap_second_file
// ftp://tycho.usno.navy.mil/pub/ntp/leap-seconds.list

/*
November 20, 2038. “GPS devices that use only the C/A code and its legacy navigation
message (LNAV) can expect the next rollover on November 20, 2038.”
https://www.gps.gov/support/user/rollover/

2137. “Newer devices can avoid that event [November 20, 2038 GPS rollover] by using
the new civil signals being added to GPS. Those signals use a modernized civil navigation
message (CNAV) with a 13-bit week number that won't roll over until 2137.”
*/

// dev implement GPS time to unix converter
// test

// https://hpiers.obspm.fr/iers/bul/bulc/ntp/leap-seconds.list

class GPS extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->gps_unix_offset = 315964800;
        $this->gps_dtai_offset = 20;

        $this->leapsecondsGPS();
    }

    function run()
    {
        $this->doGPS();
    }

    // Define GPS leap seconds
    public function leapsecondsGPS()
    {
        $leaps = [];
        $leap_seconds = $this->loadLeapseconds();
        foreach ($leap_seconds as $i => $leap_second) {
            $leap =
                $leap_second["timestamp"] -
                $this->gps_unix_offset -
                $this->gps_dtai_offset +
                $leap_second["dtai"];
            if ($leap < 0) {
                continue;
            }
            $leaps[] = $leap;
        }

        // https://www.andrews.edu/~tzs/timeconv/timealgorithm.html
        // Above link to code provides a list of GPS leaps.
        // Check the above code to see that it returns the same numbers.

        $leaps_check = [
            46828800,
            78364801,
            109900802,
            173059203,
            252028804,
            315187205,
            346723206,
            393984007,
            425520008,
            457056009,
            504489610,
            551750411,
            599184012,
            820108813,
            914803214,
            1025136015,
            1119744016,
            1167264017,
        ];

        foreach ($leaps as $i => $leap) {
            if ($leaps_check[$i] == $leap) {
                continue;
            }
            return true;
        }
        $this->gps_leapseconds = $leaps;
        $this->response .= "Got GPS leap seconds. ";

        return $leaps;
    }

    // Test to see if a GPS second is a leap second
    function isleapGPS($gpsTime)
    {
        $isLeap = false;
        $leaps = getleaps();
        $lenLeaps = count($leaps);
        for ($i = 0; $i < $lenLeaps; $i++) {
            if ($gpsTime == $leaps[$i]) {
                $isLeap = true;
            }
        }
        return $isLeap;
    }

    // Count number of leap seconds that have passed
    function countleapsGPS($gpsTime, $dirFlag)
    {
        //$leaps = getleaps();
        $leaps = $this->gps_leapsecond;
        $lenLeaps = count($leaps);
        $nleaps = 0; // number of leap seconds prior to gpsTime
        for ($i = 0; $i < $lenLeaps; $i++) {
            if (!strcmp("unix2gps", $dirFlag)) {
                if ($gpsTime >= $leaps[$i] - $i) {
                    $nleaps++;
                }
            } elseif (!strcmp("gps2unix", $dirFlag)) {
                if ($gpsTime >= $leaps[$i]) {
                    $nleaps++;
                }
            } else {
                echo "ERROR Invalid Flag!";
            }
        }
        return $nleaps;
    }

    // Convert Unix Time to GPS Time
    function unix2gpsGPS($unixTime)
    {
        // Add offset in seconds
        if (fmod($unixTime, 1) != 0) {
            $unixTime = $unixTime - 0.5;
            $isLeap = 1;
        } else {
            $isLeap = 0;
        }
        $gpsTime = $unixTime - 315964800;
        $nleaps = countleapsGPS($gpsTime, "unix2gps");
        $gpsTime = $gpsTime + $nleaps + $isLeap;
        return $gpsTime;
    }

    // Convert GPS Time to Unix Time
    function gps2unixGPS($gpsTime)
    {
        // Add offset in seconds
        $unixTime = $gpsTime + 315964800;
        $nleaps = countleapsGPS($gpsTime, "gps2unix");
        $unixTime = $unixTime - $nleaps;
        if (isleapGPS($gpsTime)) {
            $unixTime = $unixTime + 0.5;
        }
        return $unixTime;
    }

    public function doGPS()
    {
        if ($this->agent_input == null) {
            $response = "GPS | " . $this->response;

            $this->gps_message = $response; // mewsage?
        } else {
            $this->gps_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is a handler for GPS things.";
        $this->thing_report["help"] = "This is processing GPS signals.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = [
            "gps" => [
                "gnss",
                "global positioning system",
                "satellites",
                "location",
            ],
        ];
        $this->sms_message = "" . $this->gps_message;
        $this->thing_report["sms"] = $this->gps_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "gps");
        $choices = $this->thing->choice->makeLinks("gps");
        $this->thing_report["choices"] = $choices;
    }

    public function nmeaGPS($text)
    {
        $nmea_array = $this->parseNMEA($text);
        if (
            isset($nmea_array["current_latitude"]) and
            isset($nmea_array["current_latitude_north_south"])
        ) {
            $this->latitude =
                $nmea_array["current_latitude"] .
                strtoupper($nmea_array["current_latitude_north_south"]);
        }
        if (
            isset($nmea_array["current_longitude"]) and
            isset($nmea_array["current_longitude_east_west"])
        ) {
            $this->longitude =
                $nmea_array["current_longitude"] .
                strtoupper($nmea_array["current_longitude_east_west"]);
        }

        if (isset($this->latitude) and isset($this->longitude)) {
            $this->response .=
                "Saw position " .
                $this->latitude .
                " " .
                $this->longitude .
                ". ";
        }
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert($input);

        $numbers = $this->extractNumbers($filtered_input);
        if ($numbers !== null) {
            $this->response .= "Saw numbers " . implode(" ", $numbers) . ". ";
        }
        $this->nmeaGPS($filtered_input);
        return false;
    }
}
