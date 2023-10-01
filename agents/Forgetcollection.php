<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Forgetcollection extends Agent
{
    function init()
    {
        $this->node_list = ["start"];

        $this->help = "Try FORGET WEEK. Or FORGET TODAY. Or FORGET ALL CHOICE.";
        $this->thing_report['help'] = $this->help;
        $this->thing_report['info'] =
            "Makes collections of datagrams and forgets them.";

        $time_string = $this->thing->Read([
            "forgetcollection",
            "refreshed_at",
        ]);

        $this->forget_count = 0;

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["forgetcollection", "refreshed_at"],
                $time_string
            );
        }
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

                if ($thing_age_unit != $age_unit and $age_unit != 'all') {
                    continue;
                }
                if (isset($this->agent_tokens)) {
                    if (
                        !array_key_exists($thing['nom_to'], $this->agent_tokens)
                    ) {
                        continue;
                    }
                }

                $temp_thing = new Thing($thing['uuid']);
                $temp_thing->Forget();
                $count += 1;
            } else {
            }
        }

        $this->forget_count += $count;
    }

    public function makeSMS()
    {
        $selected_text = "";
        if (isset($this->selected_tokens)) {
            $selected_text = trim(
                implode(" ", array_keys($this->selected_tokens))
            );
        }

        $agents_text = "";
        if (isset($this->agent_tokens)) {
            $agents_text = trim(implode(" ", array_keys($this->agent_tokens)));
        }
        $sms = "FORGET COLLECTION ";
        $sms .= $selected_text . " ";
        $sms .= $agents_text . " ";
        $sms .=
            "| " .
            "Forgot " .
            $this->forget_count .
            " Things. " .
            $this->response .
            "";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
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
        $input = $this->input;

        //if (strtolower($input) =='forgetcollection all') {
        //    $forgetall_agent = new Forgetall($this->thing);
        //    $this->sms_message = $forgetall_agent->sms_message;
        //    return;
        //}

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
        ];

        $tokens = explode(" ", $input);

        if (count($tokens) == 1) {
            if ($input == 'forgetcollection') {
                $this->response .= $this->help;
                return;
            }
        }

        foreach ($tokens as $i => $token) {
            if (in_array(strtolower($token), $forget_tokens)) {
                $this->selected_tokens[$token] = true;
                $selected_token = $token;
                //break;
            }
        }

        $forget_agents = ['choice', 'null'];
        foreach ($tokens as $i => $token) {
            if (in_array(strtolower($token), $forget_agents)) {
                $this->agent_tokens[$token] = true;
                //break;
            }
        }

        if (!isset($selected_token)) {
            $this->response = "Incomplete forget request. ";
            return;
        }

        foreach ($this->selected_tokens as $selected_token => $a) {
            if ($selected_token == "today") {
                $this->doForgetcollection('second');
                $this->doForgetcollection('seconds');
                $this->doForgetcollection('minute');
                $this->doForgetcollection('minutes');
                $this->doForgetcollection('hour');
                $this->doForgetcollection('hours');
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
}
