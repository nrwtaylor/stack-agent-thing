<?php
namespace Nrwtaylor\StackAgentThing;

class Course extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_course = false;
        $this->default_units = "degrees";
        $this->default_type = "true";

        if (isset($this->thing->container["stack"]["course"])) {
            $this->default_course =
                $this->thing->container["stack"]["course"]["course"];
            $this->default_units =
                $this->thing->container["stack"]["course"]["units"];
            $this->default_units =
                $this->thing->container["stack"]["course"]["type"];

        }
    }

    function get()
    {
        $this->course_agent = new Variables(
            $this->thing,
            "variables course " . $this->from
        );

        $course = $this->course_agent->getVariable("course");

        if (is_numeric($course)) {
            $this->course = $course;
        } else {
            $this->course = $this->default_course;
        }

        $this->refreshed_at = $this->course_agent->getVariable("refreshed_at");
    }

    function set()
    {
        $this->course_agent->setVariable("course", $this->course);
        $this->course_agent->setVariable("refreshed_at", $this->current_time);
    }

    function run()
    {
        $this->doCourse();
    }

    public function doCourse()
    {
        if ($this->agent_input == null) {
            $array = ["observation", "polaris", "sun"];
            $k = array_rand($array);
            $v = $array[$k];

            if (!is_numeric($this->course)) {
                $response = "No course available. ";
            }

            //            if ($this->course !== false) {
            //                $response = "Course is " . $this->course .". ";
            //            }

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        //return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["course" => ["longitude", "time"]];

        $course_text = "";
        if (is_numeric($this->course)) {
            $course_text = $this->course . " ";
        }

        $sms =
            "COURSE " .
            $course_text .
            "| " .
            $this->message .
            " " .
            $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function extractCourse($text = null)
    {
        if ($text == null) {
            return true;
        }
        if ($text == "null") {
            return true;
        }

        $tokens = explode(" ", trim($text));
        $course = false;
        $courses = [];

        // dev todo
        // Recognize text courses
        /*
        foreach ($tokens as $i=>$token) {
            $sign = +1;
            $last_character = strtolower(substr(trim($text), -1));
            $text_token = $token;
            if (($last_character == "n") or ($last_character == "s")) {

                if ($last_character == "n") {$sign = +1;}
                if ($last_character == "s") {$sign = -1;}
                $text_token = mb_substr($token, 0, -1);
            }

            if (is_numeric($text_token)) {$courses[] = $sign * $text_token;}

        }
*/

        $nmea_response = $this->readNMEA($text);

        if (isset($nmea_response["true_course"])) {
            $course = $nmea_response["true_course"];
            $courses[] = $course;
        }

        if (count($courses) == 1) {
            $course = $courses[0];
        }

        return $course;
    }

    public function readSubject()
    {
        $input = $this->input;
        $this->extractCourse($input);
        return false;
    }
}
