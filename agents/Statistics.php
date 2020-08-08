<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set('display_errors', 1);


class Statistics extends Agent
{
    function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["start"];

        $this->variable_name = "age" . "_" . "number";
        $this->variable_agent = "age";

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "statistics",
            $this->variable_name,
            "refreshed_at",
        ]);

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }

        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch($this->variable_agent, 3);
        $things = $thing_report['things'];

        $this->sms_message = "";
        $reset = false;

        if ($things == false) {
            // No age information store found.
            $this->resetCounts();
        } else {
            foreach ($things as $thing) {
                $thing = new Thing($thing['uuid']);
                //		var_dump($thing);

                $thing->json->setField("variables");
                $this->age = $thing->json->readVariable([
                    "statistics",
                    $this->variable_name,
                    "mean",
                ]);
                $this->count = $thing->json->readVariable([
                    "statistics",
                    $this->variable_name,
                    "count",
                ]);
                $this->sum = floatval(
                    $thing->json->readVariable([
                        "statistics",
                        $this->variable_name,
                        "sum",
                    ])
                );
                $this->sum_squared = floatval(
                    $thing->json->readVariable([
                        "statistics",
                        $this->variable_name,
                        "sum_squared",
                    ])
                );
                $this->sum_squared_difference = floatval(
                    $thing->json->readVariable([
                        "statistics",
                        $this->variable_name,
                        "sum_squared_difference",
                    ])
                );

                $this->earliest_seen = strtotime(
                    $thing->json->readVariable([
                        "statistics",
                        $this->variable_name,
                        "earliest_seen",
                    ])
                );

                if (
                    $this->age == false or
                    $this->count == false or
                    $this->sum == false or
                    $this->sum_squared == false or
                    $this->sum_squared_difference == false
                ) {
                } else {
                    // Successfully loaded an age Thing

                    $this->statistics_thing = $thing;
                    break;
                }

                $this->resetCounts();
            }
        }
    }

    function getBalance()
    {
    }

    function resetCounts()
    {
        $this->sms_message = "Reset stream stats. | ";
        $this->count = 0;
        $this->sum = 0;
        $this->sum_squared = 0;
        $this->sum_squared_difference = 0;

        $this->statistics_thing = new Thing(null);
        $this->statistics_thing->Create(
            $this->from,
            'statistics',
            's/ channel . ' . $this->variable_name
        );
        $this->statistics_thing->flagGreen();
    }

    function stackAge()
    {
        // Calculate streamed adhoc sample statistics
        // Like calculating stream statistics.
        // Keep track of counts.  And sums.  And squares of sums.
        // And sums of differences of squares.

        // Get all users records
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        foreach ($things as $thing) {
            $created_at = $thing['created_at'];
        }
        //echo "meep";
        //	exit();

        $this->total_things = count($things);
        $this->sum = $this->sum;

        $this->sample_count = 0;
        $this->count = $this->count;

        $start_time = time();
$count_zeros = 0;
        $variables = [];
        shuffle($things);
        while ($this->total_things > 0) {
            //		        shuffle($things);
            $thing = array_pop($things);

            $uuid = $thing['uuid'];

            $variables_json = $thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

$created_at = strtotime($thing['created_at']);


            if ( (!isset($this->earliest_seen)) or 
                $created_at < $this->earliest_seen or
                $this->earliest_seen == false
            ) {
                $this->earliest_seen = $created_at;
            }

            $time_now = time();

            $number = $time_now - $created_at; //age
            $numbers[] = $number;

            if ($number == 0) {
            $count_zeros += 1;
                continue;
            }

            $this->sample_count += 1;

            $this->count += 1;
            $this->sum += $number;
            $this->sum_squared += $number * $number;

            if (time() - $start_time > 2) {
                // timed out
                break;
            }

            if ($this->sample_count > $this->total_things / 20) {
                // 5% should be enough for sampling
                break;
            }
        }

        // Calculate the mean
        $this->mean = $this->sum / $this->count;

        // Calculate the sum squared difference
        $this->sum_squared_difference = $this->sum_squared_difference;

        foreach ($numbers as $number) {
            $squared_difference =
                ($number - $this->mean) * ($number - $this->mean);
            $this->sum_squared_difference += $squared_difference;
        }

        // Calculate the variance.  Precursor to standard deviation.
        $this->variance = $this->sum_squared_difference / $this->count;

        // Calculation the standard deviation.
        $this->standard_deviation = sqrt($this->variance);

        $end_time = time();
        $this->calc_time = $end_time - $start_time;

        // Store counts
        $this->statistics_thing->db->setFrom($this->from);

        $this->statistics_thing->json->setField("variables");
        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "mean"],
            $this->mean
        );
        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "count"],
            $this->count
        );
        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "sum"],
            $this->sum
        );
        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "sum_squared"],
            floatval($this->sum_squared)
        );
        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "sum_squared_difference"],
            floatval($this->sum_squared_difference)
        );

        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "earliest_seen"],
            $this->earliest_seen
        );

        $this->statistics_thing->flagGreen();

if ($count_zeros > 0) {
$this->response .= "Counted " . $count_zeros . " zeros. ";
}
        return $this->mean;
    }

    public function makeSMS()
    {
        $this->sms_message =
            "STATISTICS " . $this->variable_name . " MEAN is " .
            number_format($this->mean) .
            " | " .
            $this->sms_message;
        $this->sms_message .=
            "SD " . number_format($this->standard_deviation) . " | ";
        $this->sms_message .=
            number_format($this->sample_count) .
            " Things sampled from " .
            number_format($this->total_things) .
            " in " .
            $this->calc_time .
            "s | ";
        $this->sms_message .= "COUNT " . number_format($this->count) . " | ";

        if (false) {
            $this->sms_message .= "SUM " . number_format($this->sum) . " | ";
            $this->sms_message .=
                "SUM SQUARED " . number_format($this->sum_squared) . " | ";
            $this->sms_message .=
                "SUM SQUARED DIFFERENCE " .
                number_format($this->sum_squared_difference) .
                " | ";
        }

$this->sms_message .= " "  . $this->response;

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);
    }

public function run() {

$this->stackAge();

}

    public function readSubject()
    {
    }
}
