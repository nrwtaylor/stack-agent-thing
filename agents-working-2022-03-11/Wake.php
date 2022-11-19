<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Wake extends Agent
{
    public function init()
    {
        $this->agent_version = 'redpanda';

        $this->node_list = ["start" => ["sleep" => ["wake" => ["sleep"]]]];

        // Set up reminders

        $this->initWake();

        $this->wake_time = $this->thing->Read([
            "wake",
            "wake_time",
        ]);

        if ($this->wake_time == false) {
            $this->setWaketime();
        }
    }

    public function initWake()
    {
        $time_string = $this->thing->Read([
            "wake",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["wake", "refreshed_at"],
                $time_string
            );
        }
    }

    public function get()
    {
        $this->wake_time = $this->thing->Read([
            "wake",
            "wake_time",
        ]);
    }

    public function set()
    {
        if ($this->wake_time == false) {
            $this->setWaketime();
        }
        if ($this->wake_time == false) {
            $this->thing->log('<pre> Agent "Wake" state </pre>');
            $this->setWaketime();
        }
    }

    function setWaketime()
    {
        $this->thing->Write(
            ["wake", "wake_time"],
            $this->wake_time
        );

        $this->thing->Write(["wake", "state"], 'sleep');
    }

    public function wake()
    {
        $thingreport = $this->thing->db->reminder(
            $this->from,
            ['s/', 'stack record'],
            ['ant', 'email', 'transit', 'translink']
        );
        $things = $thingreport['thing'];

        $this->ranked_things = [];

        foreach ($things as $i => $thing) {
            $uuid = $thing['uuid'];

            $temp_thing = new Thing($uuid);

            $haystack = strtolower($temp_thing->to . $temp_thing->subject);

            if (isset($temp_thing->account)) {
                $rank_score = $temp_thing->account['thing']->balance['amount'];
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
            $thing = new Thing($ranked_thing['name']);

            if (isset($thing->account)) {
                $message .=
                    '<li>' .
                    $thing->account['thing']->balance['amount'] .
                    ' | ' .
                    $thing->subject .
                    ' ';
            } else {
                $message .= '<li>' . 'null' . ' | ' . $thing->subject . ' ';
            }
            $message .=
                '<a href="' .
                $this->web_prefix .
                'thing/' .
                $thing->uuid .
                '/forget">Forget</a>';
            $message .=
                ' | <a href="' .
                $this->web_prefix .
                'thing/' .
                $thing->uuid .
                '/remember">Remember</a>';
            $message .= "</li>";

            $subjects[] = $thing->subject;

        }
        $message .= "</ul>";
        $message .= '<br><br>';

        $this->subjects = $subjects;
    }

    public function run()
    {
        $this->wake();
    }

    public function makeSMS()
    {
        $max_sms_length = 150;

        $length_budgets = [];
        $total_chars = 0;
        foreach ($this->subjects as $subject) {
            $chars = strlen($subject) + 1;
            $total_chars += $chars;
        }

        $sms = "WAKE | ";

        foreach ($this->subjects as $subject) {
            $char_budget = intval(
                ((strlen($subject) + 1) / $total_chars) * $max_sms_length
            );

            $sms .= substr($subject, 0, $char_budget) . ' / ';
        }

        $sms .= " | REPLY ?";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = $this->thing->choice->makeLinks('feedback');

        $subject =
            "Three things from your stack on a " .
            date("l") .
            ' in ' .
            date("F");

        $uuid = $this->uuid;
        $sqlresponse = $this->sqlresponse;

        $url = $this->web_prefix . "api/redpanda/thing/" . $uuid . "/random";
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
            return $b['likes'] - $a['likes'];
        });

        return array_slice($popular, 0, $number);
    }

    public function readSubject()
    {
    }
}
