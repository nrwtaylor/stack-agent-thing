<?php
namespace Nrwtaylor\StackAgentThing;

class Pheromone extends Agent
{
    public $var = 'hello';

    // See getEnergy() for dev

    public function init()
    {
        $this->retain_for = 24; // Retain for at least 24 hours.
        $this->state = "dev";

        $this->value_unit_name = "units";
        $this->time_unit_name = "ms";

        $this->add_value = 1;
        $this->max_value = 100;

        $this->acceleration_factor = 0;
        if (
            isset(
                $this->thing->container['api']['pheromone'][
                    'acceleration_factor'
                ]
            )
        ) {
            $this->acceleration_factor =
                $this->thing->container['api']['pheromone'][
                    'acceleration_factor'
                ];
        }

        $this->velocity_factor = 0;
        if (
            isset(
                $this->thing->container['api']['pheromone']['velocity_factor']
            )
        ) {
            $this->acceleration_factor =
                $this->thing->container['api']['pheromone']['velocity_factor'];
        }
    }

    public function run()
    {
        $this->doPheromone();
    }

    function set()
    {
        $this->pheromone_thing->Write(
            ["pheromone", "value"],
            $this->value
        );

        $time_string = $this->pheromone_thing->time();
        $this->pheromone_thing->Write(
            ["pheromone", "refreshed_at"],
            $time_string
        );

        $time_string = $this->thing->time();
        $this->thing->Write(
            ["pheromone", "refreshed_at"],
            $time_string
        );

        $pheromone_timestamp = $this->pheromone_thing->microtime(); // Trial microtime.
        $this->pheromone_thing->Write(
            ["pheromone", "timestamp"],
            $pheromone_timestamp
        );
    }

    public function get()
    {
        if (!isset($this->pheromone_thing)) {
            $this->pheromone_thing = $this->thing;
        }

        //if (!isset($this->pheromone_thing)) {return true;}

        $this->getPheromone();
    }

    function getPheromone()
    {
        $time_string = $this->pheromone_thing->Read([
            "pheromone",
            "refreshed_at",
        ]);

        $micro_timestamp = $this->pheromone_thing->Read([
            "pheromone",
            "timestamp",
        ]);

        // Keep second level timestamp because ...
        // not all stacks are capable of microtime.
        if ($time_string == false) {
            $time_string = $this->pheromone_thing->time();
            $this->pheromone_thing->Write(
                ["pheromone", "refreshed_at"],
                $time_string
            );
        }

        // And in microtime code for Pheromone.
        if ($micro_timestamp == false) {
            $micro_timestamp = $this->pheromone_thing->microtime();
            $this->pheromone_thing->Write(
                ["pheromone", "timestamp"],
                $micro_timestamp
            );
        }

        // If it has already been processed ...
        $this->last_timestamp = $micro_timestamp;

        $this->value = $this->pheromone_thing->Read([
            "pheromone",
            "value",
        ]);
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function doPheromone()
    {
        $value = $this->updatePheromone();
        $this->value = $value;

        $this->addPheromone($this->add_value);
    }

    private function updatePheromone()
    {
        $this->velocity_factor = -1e-9;
        $this->acceleration_factor = -1e-6;

        $tick_time = $this->getTime(); //s but test

        $this->value_acceleration =
            ($this->acceleration_factor * $tick_time * $tick_time) / 2;

        $this->value_velocity = $this->velocity_factor * $tick_time;

        $value =
            $this->value_acceleration + $this->value_velocity + $this->value;
        return $value;
    }

    private function addPheromone($amount = null)
    {
        // Distance down the path.  Only number that matters.
        // For now.

        if (!isset($this->value) or $this->value == false) {
            $this->value = 0;
        }

        if ($this->value < 0) {
            $this->value = 0;
        }

        $this->value += $amount;
        if ($this->value > $this->max_value) {
            $this->value = $this->max_value;
        } // Saturated.
    }

    public function getTime()
    {
        if (isset($this->elapsed_clock)) {
            return $this->elapsed_clock;
        }
        // Only do this once.
        // Can't have calculations based on different timestamps.
        if (!isset($this->current_timestamp)) {
            $this->current_timestamp = $this->pheromone_thing->microtime();
        }

        $this->elapsed_clock = $this->microtime_diff(
            $this->last_timestamp,
            $this->current_timestamp
        );
        return $this->elapsed_clock;
    }

    // https://gist.github.com/hadl/5721816
    function microtime_diff($start, $end)
    {
        // Lots of testing needed on this :/

        list($start_date, $start_clock, $start_usec) = explode(" ", $start);
        list($end_date, $end_clock, $end_usec) = explode(" ", $end);

        $diff_date = strtotime($end_date) - strtotime($start_date);

        $diff_clock = strtotime($end_clock) - strtotime($start_clock);

        $diff_usec = floatval($end_usec) - floatval($start_usec);

        return floatval($diff_date) + floatval($diff_clock) + $diff_usec;
    }

    public function readSubject()
    {
        $input = $this->input;

        $uuid_agent = new Uuid($this->thing, "uuid");

        $this->pheromone_thing = $this->thing;
        if ($this->uuid != $uuid_agent->uuid) {
            $thing = new Thing($uuid_agent->uuid);
            $this->pheromone_thing = $thing;

            $this->response .= "Found a uuid and loaded that thing. ";
            $this->getPheromone();
        }

        $this->message =
            "Saw " .
            $this->value .
            " " .
            $this->value_unit_name .
            " of Pheromone.";
        $this->keyword = "pheromone";

        $this->thing_report['keyword'] = $this->keyword;
        $this->thing_report['email'] = $this->message;
    }

    public function makeSMS()
    {
        $link =
            $this->web_prefix .
            'thing/' .
            $this->pheromone_thing->uuid .
            '/pheromone';

        $sms = "PHEROMONE " . $this->value . "m";

        $sms .= " | " . $link . " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeWeb()
    {
        $link =
            $this->web_prefix .
            'thing/' .
            $this->pheromone_thing->uuid .
            '/pheromone';
        $html = "";
        //$html = "<b>PHEROMONE</b>";
        $html .= "<p><b>Pheromone Variables</b>";

        $html .=
            "<br>Pheromone value is " . $this->value . $this->value_unit_name;

        $html .=
            "<br>Elapsed time between pheromone additions " .
            $this->elapsed_clock * 1e3 .
            $this->time_unit_name;

        $html .= "<br>Last pheromone time " . $this->last_timestamp;

        $html .= "<p><b>Pherome adder reader link</b>";
        $html .= "<br>";

        $html .= '<a href="' . $link . '">';
        //        $web .= $this->html_image;
        $html .= $link;

        $html .= "</a>";
        $html .= "<br>";
        $html .= "<br>";
        $html .= 'Pheromone says, "';
        $html .= $this->sms_message . '"';

        $this->web_message = $html;
        $this->thing_report['web'] = $this->web_message;
    }
}
