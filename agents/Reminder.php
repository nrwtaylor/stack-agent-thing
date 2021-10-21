<?php
namespace Nrwtaylor\StackAgentThing;
// Call regularly from cron
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Reminder extends Agent
{
    function init()
    {
        $this->agent_version = "redpanda";

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = [
            "feedback" => ["useful" => ["credit 100", "credit 250"]],
            "not helpful" => ["wrong place", "wrong time"],
            "feedback2" => ["awesome", "not so awesome"],
        ];
    }

    function get()
    {
        $time_string = $this->thing->Read([
            "reminder",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["reminder", "refreshed_at"],
                $time_string
            );
        }

        $this->reminder_ids = $this->thing->Read([
            "reminder",
            "uuids",
        ]);
    }

    function set()
    {
        if ($this->reminder_ids == false) {
            $this->thing->log("set Reminders.");
            $this->setReminders();
        }
    }

    function setReminders()
    {
        $thingreport = $this->thing->db->reminder(
            $this->from,
            ["s/", "stack record"],
            ["ant", "email", "transit", "translink"]
        );
        $things = $thingreport["thing"];
        $this->reminder_ids = [];

        if (count($things) == 0) {
            $this->reminder_ids = null;
        } else {
            foreach ($things as $thing) {
                $this->reminder_ids[] = $thing["uuid"];
            }
        }

        $this->thing->Write(
            ["reminder", "uuids"],
            $this->reminder_ids
        );
    }

    public function respond()
    {
        // Thing actions

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(
            ["reminder", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );

        $this->thing->flagGreen();

        $choices = $this->thing->choice->makeLinks("feedback");

        // Compose email

        $subject =
            "Three things from your stack on a " .
            date("l") .
            " in " .
            date("F");

        $uuid = $this->uuid;
        $sqlresponse = $this->sqlresponse;

        $url = $this->web_prefix . "api/redpanda/thing/" . $uuid . "/random";

        //		$thingreport = $this->thing->db->userRecords($this->from,30);

        $thingreport = $this->thing->db->reminder(
            $this->from,
            ["s/", "stack record"],
            ["ant", "email", "transit", "translink"]
        );
        $things = $thingreport["thing"];

        $this->ranked_things = [];

        foreach ($this->reminder_ids as $uuid) {
            $temp_thing = new Thing($uuid);
            $haystack = strtolower($temp_thing->to . $temp_thing->subject);

            if (isset($temp_thing->account)) {
                $rank_score = $temp_thing->account["thing"]->balance["amount"];
            } else {
                $rank_score = null;
            }

            $this->ranked_things[] = [
                "name" => $temp_thing->uuid,
                "likes" => $rank_score,
            ];
        }

        $things = $this->get_flavors_by_likes(30);

        $message =
            "So here are three things you put on the stack.  That's what you wanted.<br>";
        //$message .= "<ul>";
        $i = 0;

        $subjects = [];

        foreach ($things as $ranked_thing) {
            $thing = new Thing($ranked_thing["name"]);

            if (isset($thing->account)) {
                $message .=
                    "<li>" .
                    $thing->account["thing"]->balance["amount"] .
                    " | " .
                    $thing->subject .
                    " ";
            } else {
                $message .= "<li>" . "null" . " | " . $thing->subject . " ";
            }
            $message .=
                '<a href="' .
                $this->web_prefix .
                "thing/" .
                $thing->uuid .
                '/forget">Forget</a>';
            $message .=
                ' | <a href="' .
                $this->web_prefix .
                "thing/" .
                $thing->uuid .
                '/remember">Remember</a>';
            $message .= "</li>";

            $subjects[] = $thing->subject;
        }
        $message .= "</ul>";
        $message .= "<br><br>";

        $max_sms_length = 150;

        $length_budgets = [];
        $total_chars = 0;
        foreach ($subjects as $subject) {
            $chars = strlen($subject) + 1;
            $total_chars += $chars;
        }

        $this->sms_message = "REMINDER | ";

        foreach ($subjects as $subject) {
            $char_budget = intval(
                ((strlen($subject) + 1) / $total_chars) * $max_sms_length
            );

            $this->sms_message .= substr($subject, 0, $char_budget) . "/";
        }

        $this->sms_message .= " | REPLY ?";

        $this->thing->log('<pre> Agent "Reminder" email sent.</pre>');

        $this->thing_report = [
            "thing" => $this->thing->thing,
            "choices" => $choices,
            "info" => "This is a reminder.",
            "help" =>
                "This is probably stuff you want to remember.  Or forget.",
        ];

        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $message;
        $this->thing_report["message"] = $message;

        $message_thing = new Message($this->thing, $this->thing_report);
        //$thing_report['info'] = 'SMS sent';

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["sms"] = $this->sms_message;

        return $this->thing_report;
    }

    // https://teamtreehouse.com/community/how-to-retrieve-highest-4-values-from-an-associative-array-in-php

    //function get_all_flavors() {

    //$flavors = array(
    //    array("name" => "Vanilla", "likes" => 312),
    //    array("name" => "Cookie Dough", "likes" => 976),
    //    array("name" => "Peppermint", "likes" => 12),
    //    array("name" => "Cake Batter", "likes" => 598),
    //    array("name" => "Avocado Chocolate", "likes" => 6),
    //    array("name" => "Jalapeno So Spicy", "likes" => 3),
    //);

    //return $flavors;

    //}

    function get_flavors_by_likes($number)
    {
        //$all = $this->get_all_flavors();
        $all = $this->ranked_things;
        $total_flavors = count($all);
        $position = 0;

        $popular = $all;
        usort($popular, function ($a, $b) {
            return $b["likes"] - $a["likes"];
        });

        return array_slice($popular, 0, $number);
    }

    public function readSubject()
    {
        $this->start();

        $status = true;
        return $status;
    }

    function start()
    {
        if (rand(0, 5) <= 3) {
            $this->thing->choice->Create(
                "reminder",
                $this->node_list,
                "feedback"
            );
        } else {
            $this->thing->choice->Create(
                "reminder",
                $this->node_list,
                "feedback2"
            );
        }
        $this->thing->flagGreen();
    }
}
