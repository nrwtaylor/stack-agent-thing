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

        $this->variable_agent = "statistics";
        $this->variable_name = "number";

        $this->node_list = ["start"];

        $this->count = 0;
        $this->sum = 0;
        $this->sum_squared = 0;
        $this->sum_squared_difference = 0;
        $this->minimum = null;
        $this->maximum = null;
    }

    public function get()
    {
        if (!isset($this->statistics_thing)) {
            $this->statistics_thing = $this->thing;
        }
    }

    public function getStatistics()
    {
        // devstack
        // no need to load last statistics.
        // calculate

        $this->thing->db->setFrom($this->from);
        //        $thing_report = $this->thing->db->agentSearch($this->variable_agent, 3);

        $thing_report = $this->thing->db->agentSearch('statistics', 3);

        $things = $thing_report['things'];

        $this->sms_message = "";
        $reset = false;

        if ($things == false) {
            // No age information store found.
            $this->initStatistics();
        } else {
            foreach ($things as $thing) {
                $uuid = $thing['uuid'];

                $variables_json = $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);
                if (!isset($variables['statistics'])) {
                    continue;
                }

                throw new \Exception("devstack");
                /*
                $this->age = $thing->json->readVariable([
                $this->count = $thing->json->readVariable([
                $this->sum = floatval(
                $this->sum_squared = floatval(
                $this->sum_squared_difference = floatval(
                $this->earliest_seen = strtotime(
*/
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

                $this->initStatistics();
            }
        }
    }

    function getBalance()
    {
    }

    function initStatistics()
    {
        $this->response .= "Reset stream stats. ";
        $this->count = 0;
        $this->sum = 0;
        $this->sum_squared = 0;
        $this->sum_squared_difference = 0;
        $this->minimum = null;
        $this->maximum = null;

        $this->statistics_thing = new Thing(null);
        $this->statistics_thing->Create(
            $this->from,
            'statistics',
            's/ channel . ' . $this->variable_agent . " " . $this->variable_name
        );
        $this->statistics_thing->flagGreen();
    }

    function calcStatistics()
    {
        $variable_agent = $this->variable_agent;
        $variable_name = $this->variable_name;

        $things = $this->getThings('baseline');

        if ($things == null) {
            return false;
        }

        $this->total_things = count($things);

        $this->sample_count = 0;
        $start_time = time();
        $count_zeros = 0;
        $proportion = 1.0;
        shuffle($things);
        while (count($things) > 0) {
            //		        shuffle($things);
            $thing = array_pop($things);

            if (!isset($thing->uuid)) {continue;}

            $uuid = $thing->uuid;

            $variables = $thing->variables;
            $created_at = $thing->created_at;

            if (
                !isset($this->earliest_seen) or
                $created_at < $this->earliest_seen or
                $this->earliest_seen == false
            ) {
                $this->earliest_seen = $created_at;

                //     $this->number = $variables[$variable_agent][$variable_name];
            }

            if (!isset($variables[$variable_agent][$variable_name])) {
                continue;
            }

            if (
                !isset($this->latest_seen) or
                $created_at > $this->latest_seen or
                $this->latest_seen == false
            ) {
                $this->latest_seen = $created_at;

                if (is_numeric($variables[$variable_agent][$variable_name])) {
                    $this->number = $variables[$variable_agent][$variable_name];
                }
            }

            //            if (!isset($variables[$variable_agent][$variable_name])) {
            //                continue;
            //            }

            $number = $variables[$variable_agent][$variable_name];
            if (strtolower($number) == 'x') {
                continue;
            }
            if (strtolower($number) == 'z') {
                continue;
            }
            if (strtolower($number) === true) {
                continue;
            }
            if (strtolower($number) == false) {
                continue;
            }
            if (strtolower($number) == null) {
                continue;
            }

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
                $this->thing->log("Sampled for more than 2s");
                // timed out
                break;
            }

            if ($this->sample_count > $this->total_things * $proportion) {
                // 20% should be enough for sampling
                break;
            }

            //       }

            if (is_numeric($number)) {
                if ($this->minimum == null) {
                    $this->minimum = $number;
                }
                if ($this->maximum == null) {
                    $this->maximum = $number;
                }

                if ($number < $this->minimum) {
                    $this->minimum = $number;
                }
                if ($number > $this->maximum) {
                    $this->maximum = $number;
                }
            }
        }
        // Calculate the mean
        if ($this->count > 0) {
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
        }
        if ($count_zeros > 0) {
            $this->response .= "Counted " . $count_zeros . " zeros. ";
        }
        if (!isset($this->mean)) {
            $this->mean = "X";
        }
        return $this->mean;
    }

    public function set()
    {
        /*
//return;
$statistics = array($this->variable_name =>
array(
"mean"=>$this->mean,
"count"=>$this->count,
"sum"=>$this->sum,
"sum_squared"=>floatval($this->sum_squared),
"sum_squared_difference"=>floatval($this->sum_squared_difference),
"earliest_seen"=>$this->earliest_seen,
"minimum"=>$this->minimum,
"maximum"=>$this->maximum
));
*/
        $statistics = $this->statistics;
        $this->statistics_thing->json->writeVariable(["statistics"], $statistics);

        return;
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

        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "minimum"],
            $this->minimum
        );

        $this->statistics_thing->json->writeVariable(
            ["statistics", $this->variable_name, "maximum"],
            $this->maximum
        );

        $this->statistics_thing->flagGreen();
    }

    public function makeSMS()
    {
        $sms = "STATISTICS " . $this->variable_name . " ";

        if ($this->count != 0) {
            $sms .= "MEAN is " . number_format($this->mean);
            $sms .= "| ";
            //            $this->sms_message;
            $sms .= "SD " . number_format($this->standard_deviation) . " | ";
            $sms .=
                number_format($this->sample_count) .
                " Things sampled from " .
                number_format($this->total_things) .
                " in " .
                $this->calc_time .
                "s ";
        }
        $sms .= "COUNT " . number_format($this->count) . "";

        if (false) {
            $sms .= "sum " . number_format($this->sum) . "";
            $sms .= "sum squared " . number_format($this->sum_squared) . "";
            $sms .=
                "sum squared difference " .
                number_format($this->sum_squared_difference);
        }

        $sms .= " |";
        $sms .= " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);
    }

    public function makeStatistics()
    {
        if ($this->count == 0) {
            $statistics = [
                $this->variable_agent => [
                    $this->variable_name => [],
                ],
            ];
            $this->statistics = $statistics;
            return;
        }
        $number = "X";
        if (isset($this->number)) {
            $number = $this->number;
        }

        //return;
        $statistics = [
            $this->variable_agent => [
                $this->variable_name => [
                    "mean" => $this->mean,
                    "count" => $this->count,
                    "sum" => $this->sum,
                    "sum_squared" => floatval($this->sum_squared),
                    "sum_squared_difference" => floatval(
                        $this->sum_squared_difference
                    ),
                    "earliest_seen" => $this->earliest_seen,
                    "latest_seen" => $this->latest_seen,
                    "minimum" => $this->minimum,
                    "maximum" => $this->maximum,
                    "number" => $number,
                ],
            ],
        ];

        $this->statistics = $statistics;
    }

    public function run()
    {
        //return;
        $this->calcStatistics();
        $this->makeStatistics();
    }

    public function readSubject()
    {
        //return;
        $input = strtolower($this->input);

        if ($input == 'statistics') {
            $this->response .= "Saw statistics. ";

            return;
        }

        $filtered_input = $this->assert($input);

        $tokens = explode(" ", $filtered_input);

        $this->variable_agent = $tokens[0];

        if (!isset($tokens[1])) {
            $this->response .= "Did not see a variable name. ";
            // Unexpected.
            $this->variable_name = "number";
            return;
        }

        $this->variable_name = $tokens[1];

        $this->response .=
            "Using " .
            $this->variable_agent .
            " and " .
            $this->variable_name .
            ". ";

        $this->getStatistics();
    }
}
