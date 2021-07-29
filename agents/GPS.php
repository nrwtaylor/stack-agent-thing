<?php
namespace Nrwtaylor\StackAgentThing;

// GPS conversion algorithm
// dev need authorative list of GPS leap seconds.
// https://www.andrews.edu/~tzs/timeconv/timealgorithm.html

// review https://www.ietf.org/timezones/data/leap-seconds.list
// review https://www.usno.navy.mil/USNO/time/master-clock/leap-seconds
// not found http://maia.usno.navy.mil/ser7/tai-utc.dat list of historical leap seconds
// https://kb.meinbergglobal.com/kb/time_sync/ntp/configuration/ntp_leap_second_file
// ftp://tycho.usno.navy.mil/pub/ntp/leap-seconds.list

// dev cache local leap seconds file
// dev implement GPS time to unix converter
// test

class GPS extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doGPS();
    }

// Define GPS leap seconds
   function getleaps() {
      $leaps = array(46828800, 78364801, 109900802, 173059203, 252028804, 315187205, 346723206, 393984007, 425520008, 457056009, 504489610, 551750411, 599184012, 820108813, 914803214, 1025136015, 1119744016, 1167264017);
      return $leaps;
   }

// Test to see if a GPS second is a leap second
   function isleap($gpsTime) {
      $isLeap = FALSE;
      $leaps = getleaps();
      $lenLeaps = count($leaps);
      for ($i = 0; $i < $lenLeaps; $i++) {
         if ($gpsTime == $leaps[$i]) {
            $isLeap = TRUE;
         }
      }
      return $isLeap;
   }

// Count number of leap seconds that have passed
   function countleaps($gpsTime, $dirFlag){
      $leaps = getleaps();
      $lenLeaps = count($leaps);
      $nleaps = 0;  // number of leap seconds prior to gpsTime
      for ($i = 0; $i < $lenLeaps; $i++) {
         if (!strcmp('unix2gps', $dirFlag)) {
            if ($gpsTime >= $leaps[$i] - $i) {
               $nleaps++;
            }
         } elseif (!strcmp('gps2unix', $dirFlag)) {
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
   function unix2gps($unixTime){
      // Add offset in seconds
      if (fmod($unixTime, 1) != 0) {
         $unixTime = $unixTime - 0.5;
         $isLeap = 1;
      } else {
         $isLeap = 0;
      }
      $gpsTime = $unixTime - 315964800;
      $nleaps = countleaps($gpsTime, 'unix2gps');
      $gpsTime = $gpsTime + $nleaps + $isLeap;
      return $gpsTime;
   }

// Convert GPS Time to Unix Time
   function gps2unix($gpsTime){
     // Add offset in seconds
     $unixTime = $gpsTime + 315964800;
     $nleaps = countleaps($gpsTime, 'gps2unix');
     $unixTime = $unixTime - $nleaps;
     if (isleap($gpsTime)) {
        $unixTime = $unixTime + 0.5;
     }
     return $unixTime;
   }

    public function doGPS()
    {
        if ($this->agent_input == null) {
            $response = "GPS | " . "No response.";

            $this->gps_message = $response; // mewsage?
        } else {
            $this->gps_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a handler for GPS things.";
        $this->thing_report["help"] = "This is processing GPS signals.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
        $this->node_list = array("gps" => array("gnss", "global positioning system", "satellites", "location"));
        $this->sms_message = "" . $this->gps_message;
        $this->thing_report['sms'] = $this->gps_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "gps");
        $choices = $this->thing->choice->makeLinks('gps');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
