<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Forgetcollection extends Agent
{
    function init()
    {
        $this->node_list = ["start"];

        $this->thing_report['help'] = "Try FORGET DAYS. Or FORGET TODAY.";
        $this->thing_report['info'] =
            "Makes collections of datagrams and forgets them.";

        $this->thing->log(
            'Agent "Forget Collection" running on Thing ' . $this->uuid . '.'
        );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "forgetcollection",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["forgetcollection", "refreshed_at"],
                $time_string
            );
        }

        $this->sms_message = "";
    }

    function doForgetcollection($age_unit = null)
    {
        if ($age_unit == null) {
            return true;
        }

        // Get all users records

        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        $this->total_things = count($things);

        $start_time = time();

        $count = 0;
        shuffle($things);

        $start_time = time();

        while (count($things) > 1) {
            $thing = array_pop($things);

            if ($thing['uuid'] != $this->uuid) {
                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing['created_at']);

                $age_text = $this->thing->human_time($age);
                $thing_age_unit = explode(" ", $age_text)[1];

                if ($thing_age_unit != $age_unit) {
                    continue;
                }

                $temp_thing = new Thing($thing['uuid']);
                $temp_thing->Forget();
                $count += 1;
            } else {
            }
        }

        $this->sms_message .=
            "Completed request for this Identity. Forgot " .
            $count .
            " Things.";
    }

    public function makeSMS()
    {
        if ($this->sms_message == "") {
            $this->sms_message =
                "FORGET COLLECTION | " . $this->sms_message . " | TEXT PRIVACY";
        }
        //      $this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeEMail()
    {
        $this->thing_report['email'] = $this->sms_message;
    }

    public function respondResponse()
    {
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    public function readSubject()
    {
        $forget_tokens = [
            "all",
            "today",
            "now",
            "second",
            "seconds",
            "minute",
            "minutes",
            "hour",
            "hours",
            "day",
            "days",
            "week",
            "weeks",
            "month",
            "months",
            "year",
            "years",
            "everything",
        ];
        $tokens = explode(" ", $this->subject);
        foreach ($tokens as $i => $token) {
            if (in_array(strtolower($token), $forget_tokens)) {
                $selected_token = $token;
                break;
            }
        }

        if (!isset($selected_token)) {
            $this->sms_message .= "Incomplete forget request. ";
        }

        if ($selected_token == "everything") {
            $forgetall_agent = new Forgetall($this->thing);
            $this->sms_message = $forgetall_agent->sms_message;

            return;
        }

        if ($selected_token == "all") {
            $forgetall_agent = new Forgetall($this->thing);
            $this->sms_message = $forgetall_agent->sms_message;
            return;
        }

        if ($selected_token == "today") {
            $this->doForgetcollection('day');
            return;
        }

        if ($selected_token == "now") {
            $this->doForgetcollection('minute');
            return;
        }

        // And forget this ...
        $this->doForgetcollection($selected_token);
    }
}
