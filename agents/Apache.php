<?php
namespace Nrwtaylor\StackAgentThing;

class Apache extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doApache();
    }

    public function parseApache($line)
    {
        // https://stackoverflow.com/questions/2221636/how-to-parse-apache-logs-using-a-regex-in-php

        // # Parses the NCSA Combined Log Format lines:
        $pattern =
            '/^([^ ]+) ([^ ]+) ([^ ]+) (\[[^\]]+\]) "(.*) (.*) (.*)" ([0-9\-]+) ([0-9\-]+) "(.*)" "(.*)"$/';

        if (preg_match($pattern, $line, $matches)) {
            //# puts each part of the match in a named variable

            list(
                $whole_match,
                $remote_host,
                $logname,
                $user,
                $date_time,
                $method,
                $request,
                $protocol,
                $status,
                $bytes,
                $referer,
                $user_agent,
            ) = $matches;
        }

        $date_time = str_replace(['[', ']'], '', $date_time);
        $old_date_timestamp = strtotime($date_time);
        $timestamp = date('Y-m-d H:i:s', $old_date_timestamp);

        $apache = [
            'user_agent' => $user_agent,
            'timestamp' => $timestamp,
        ];
        return $apache;
    }

    public function logApache()
    {
        // https://superuser.com/questions/1556791/when-calling-fopen-in-php-i-get-permission-denied-error-only-from-apache
        $files = [
            "/var/log/apache2/access.log",
            "/var/log/apache2/access.log.1",
        ];
        foreach ($files as $i => $file_name) {
            $contents = file_get_contents($file_name);

            $this->matches = [];
            $separator = "\r\n";

$line = strtok($contents, $separator);

            $parser_name = 'apache';
            $this->visits = [];

            while ($line !== false) {
              $apache = $this->parseApache($line);
                $this->visits[] = $apache;
                $line = strtok($separator);
            }
        }
    }

    public function doApache()
    {
        $this->logApache();

        $this->binned_visits = [];

        foreach ($this->visits as $i => $visit) {
            $timestamp = strtotime($visit['timestamp']);

            $age = strtotime($this->current_time) - $timestamp;

            $bin_name = intval($age / 60); // Bin minutes
            if (!isset($this->binned_visits[$bin_name])) {
                $this->binned_visits[$bin_name] = [];
            }

            $this->binned_visits[$bin_name]['count'] += 1;
        }

        if ($this->agent_input == null) {
            $t = "";
            $i = 0;
            foreach (
                array_reverse($this->binned_visits)
                as $bin_name => $variable
            ) {
                if (isset($variable['count'])) {
                    $t .= $bin_name . ' ' . $variable['count'] . ' / ';
                    $i += 1;
                    if ($i > 4) {
                        break;
                    }
                }
            }

            $response = "APACHE | " . $t;

            $this->apache_message = $response; // mewsage?
        } else {
            $this->apache_message = $this->agent_input;
        }
    }

    function makeSnippet()
    {
        $t = "";
        foreach ($this->binned_visits as $bin_name => $variable) {
            $count = $variable['count'];
            $t .= $bin_name . " " . $count . "<br>";
        }

        $this->snippet = $t;
        $this->thing_report['snippet'] = $t;
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "apache");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is Apache keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["apache" => ["apache", "dog"]];
        $this->sms_message = "" . $this->apache_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "apache");
        $choices = $this->thing->choice->makeLinks('apache');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }

    public function makeChart()
    {
        $this->numbers_history = $this->binned_visits;
        // if (!isset($this->numbers_history)) {$this->historyApache();}
        $t = "APACHE CHART\n";
        $points = [];

        // Defaults needed.
        $x_min = 1e99;
        $x_max = -1e99;

        $y_min = 1e99;
        $y_max = -1e99;

        foreach ($this->numbers_history as $i => $number_object) {
            //            $created_at = $number_object['created_at'];
            $created_at = $i;

            $number = $number_object['number'];

            $points[$created_at] = $number;

            if (!isset($x_min)) {
                $x_min = $created_at;
            }
            if (!isset($x_max)) {
                $x_max = $created_at;
            }

            if ($created_at < $x_min) {
                $x_min = $created_at;
            }
            if ($created_at > $x_max) {
                $x_max = $created_at;
            }

            if (!isset($y_min)) {
                $y_min = $number;
            }
            if (!isset($y_max)) {
                $y_max = $number;
            }

            if ($number < $y_min) {
                $y_min = $number;
            }
            if ($number > $y_max) {
                $y_max = $number;
            }
        }

        $this->chart_agent = new Chart(
            $this->thing,
            "chart apache " . $this->from
        );
        $this->chart_agent->points = $points;

        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;
        $this->chart_agent->x_max = strtotime($this->thing->time);

        if ($this->y_min_limit != false or $this->y_min_limit != null) {
            $y_min = $this->y_min_limit;
        }

        $this->chart_agent->y_min = $y_min;

        if ($this->y_max_limit != false or $this->y_max_limit != null) {
            $y_max = $this->y_max_limit;
        }
        $this->chart_agent->y_max = $y_max;

        $y_spread = 100;
        if (
            $this->chart_agent->y_min == false and
            $this->chart_agent->y_max === false
        ) {
            //
        } elseif (
            $this->chart_agent->y_min == false and
            is_numeric($this->chart_agent->y_max)
        ) {
            $y_spread = $y_max;
        } elseif (
            $this->chart_agent->y_max == false and
            is_numeric($this->chart_agent->y_min)
        ) {
            // test stack
            $y_spread = abs($this->chart_agent->y_min);
        } else {
            $y_spread = $this->chart_agent->y_max - $this->chart_agent->y_min;
            //            if ($y_spread == 0) {$y_spread = 100;}
        }
        if ($y_spread == 0) {
            $y_spread = 100;
        }

        $this->chart_agent->y_spread = $y_spread;
        $this->chart_agent->drawGraph();
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/number';

        $this->node_list = ["number" => ["number"]];

        $embedded = true;
        if (!$embedded) {
            $web = '<a href="' . $link . '">';
            $web .=
                '<img src= "' .
                $this->web_prefix .
                'thing/' .
                $this->uuid .
                '/number.png">';
            $web .= "</a>";
        } else {
            $web = '<a href="' . $link . '">';
            $web .= $this->image_embedded;
            $web .= "</a>";
        }
        $web .= "<br>";

        $web .= "number graph";

        $web .= "<br><br>";

        $this->web = $web;
        $this->thing_report['web'] = $web;
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            return true;
        }
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->thing_report['png'] = $this->chart_agent->thing_report['png'];
    }
}
