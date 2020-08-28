<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/*
print gearman_version() . "\n";

$thing = new Thing();
$t = new Manager($thing);
$s = $t->getStatus();
var_dump($s);
exit();
// Taken from https://stackoverflow.com/questions/2752431/any-way-to-access-gearman-administration
*/
class Manager extends Agent
{
    /**
     * @var string
     */
    public $host = "127.0.0.1";
    /**
     * @var int
     */
    public $port = 4730;

    public function init()
    {
        $host = "127.0.0.1";
        $port = 4730;

        if (!isset($host)) {
            $this->host = $host;
        }
        if (!isset($port)) {
            $this->port = $port;
        }

        $this->test = "Development code";

        $this->node_list = ["nuuid" => ["nuuid"]];

        $this->queue_engine_version = gearman_version();

        $s = $this->getStatus();
        $this->queued_jobs = $s['operations']['call_agent']['total'];
        $this->workers_running = $s['operations']['call_agent']['running'];
        $this->workers_connected =
            $s['operations']['call_agent']['connectedWorkers'];

        $this->y_max_limit = null;
        $this->y_min_limit = null;

        /*
// Fire off a test message via Gearman
        $arr = json_encode(array("to"=>"console", "from"=>"manager", "subject"=>"ping"));
        $client= new \GearmanClient();
        $client->addServer();
//        $client->doNormal("call_agent", $arr);
        $client->doHighBackground("call_agent", $arr);
//        var_dump($client);
$this->response = "Gearman snowflake worker started.";
*/
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "manager",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["manager", "refreshed_at"],
                $time_string
            );
        }
    }

    /**
     * @return array | null
     */
    public function getStatus()
    {
        $status = null;
        $handle = fsockopen(
            $this->host,
            $this->port,
            $errorNumber,
            $errorString,
            30
        );
        if ($handle != null) {
            fwrite($handle, "status\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if ($line == ".\n") {
                    break;
                }
                if (
                    preg_match(
                        "~^(.*)[ \t](\d+)[ \t](\d+)[ \t](\d+)~",
                        $line,
                        $matches
                    )
                ) {
                    $function = $matches[1];
                    $status['operations'][$function] = [
                        'function' => $function,
                        'total' => $matches[2],
                        'running' => $matches[3],
                        'connectedWorkers' => $matches[4],
                    ];
                }
            }
            fwrite($handle, "workers\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if ($line == ".\n") {
                    break;
                }
                // FD IP-ADDRESS CLIENT-ID : FUNCTION
                if (
                    preg_match(
                        "~^(\d+)[ \t](.*?)[ \t](.*?) : ?(.*)~",
                        $line,
                        $matches
                    )
                ) {
                    $fd = $matches[1];
                    $status['connections'][$fd] = [
                        'fd' => $fd,
                        'ip' => $matches[2],
                        'id' => $matches[3],
                        'function' => $matches[4],
                    ];
                }
            }
            fclose($handle);
        }

        return $status;
    }

    function getManager()
    {
        $this->queue_engine_version = gearman_version();

        $s = $this->getStatus();
        $this->queued_jobs = $s['operations']['call_agent']['total'];
        $this->workers_running = $s['operations']['call_agent']['running'];
        $this->workers_connected =
            $s['operations']['call_agent']['connectedWorkers'];
    }

    public function set()
    {
        $this->current_time = $this->thing->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "manager",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            //            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable(
                ["manager", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        //        $this->thing->json->setField("variables");
        //        $queue_time = $this->thing->json->readVariable( array("manager", "queued_jobs") );
        //        $run_time = $this->thing->json->readVariable( array("manager", "workers_running") );

        //        if ($this->queue_engine_version == false) {
        $this->getManager();

        $this->readSubject();

        $this->thing->json->writeVariable(
            ["manager", "queued_jobs"],
            $this->queued_jobs
        );
        $this->thing->json->writeVariable(
            ["manager", "workers_running"],
            $this->workers_running
        );
        $this->thing->json->writeVariable(
            ["manager", "workers_connected"],
            $this->workers_connected
        );
        //        }
    }

    function run()
    {
        // devstack
        // factor into own function

        $this->makeChart();
        $this->makeImage();
    }

    public function workersManager()
    {
        $this->points = [];
        $things = $this->getThings('manager');

        foreach (array_reverse($things) as $i => $thing) {
            if (!isset($thing->variables['manager']['queued_jobs'])) {
                continue;
            }
            //var_dump($thing->variables['manager']);
            $n = $thing->variables['manager']['queued_jobs'];

            $t = strtotime($thing->variables['manager']['refreshed_at']);

            if (!isset($this->x_min)) {
                $this->x_min = $t;
            }
            if (!isset($this->x_max)) {
                $this->x_max = $t;
            }

            if ($t < $this->x_min) {
                $this->x_min = $t;
            }
            if ($t > $this->x_max) {
                $this->x_max = $t;
            }

            if (!isset($this->y_min)) {
                $this->y_min = $n;
            }
            if (!isset($this->y_max)) {
                $this->y_max = $n;
            }

            if ($n < $this->y_min) {
                $this->y_min = $n;
            }
            if ($n > $this->y_max) {
                $this->y_max = $n;
            }

            $this->points[$t] = $n;
        }

        ksort($this->points);
    }

    public function makeChart()
    {
        if (!isset($this->points)) {
            $this->workersManager();
        }
        $t = "NUMBER CHART\n";
        //        $points = array();

        $x_min = $this->x_min;
        $x_max = $this->x_max;
        $y_min = $this->y_min;
        $y_max = $this->y_max;

        $this->chart_agent = new Chart(
            $this->thing,
            "chart manager " . $this->from
        );
        $this->chart_agent->points = $this->points;

        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;
        $this->chart_agent->x_max = strtotime($this->thing->time);

        if ($this->y_min_limit != false or $this->y_min_limit != null) {
            $y_min = $this->y_min_limit;
        }

        $this->chart_agent->y_min = $y_min;

        if ($this->y_max_limit != false or $this->y_max_limit != null) {
            $this->y_max = $this->y_max_limit;
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

    /**
     *
     */
    public function makeImage()
    {
        $this->image = $this->chart_agent->image;
    }

    /**
     *
     * @return unknown
     */
    public function makePNG()
    {
        if (!isset($this->image)) {
            return true;
        }
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->thing_report['png'] = $this->chart_agent->thing_report['png'];
    }

    function readSubject()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['keyword'] = 'manager';
        $this->thing_report['help'] = 'Checks the job queue.';
    }

    function makeSMS()
    {
        //        $this->getQueuetime();
        //        $rtime = $this->thing->elapsed_runtime() - $this->start_time;

        $this->node_list = ["manager" => ["managergraph"]];

        //echo $this->queued_jobs ." " . $this->workers_running . " of " . $this->workers_connected . " workers (" . $this->queue_engine_version . ")";

        $this->sms_message = "MANAGER";
        $this->sms_message .=
            " | queued jobs " . number_format($this->queued_jobs) . "";
        $this->sms_message .=
            " | workers running " . number_format($this->workers_running) . "";
        $this->sms_message .=
            " | workers connected " .
            number_format($this->workers_connected) .
            "";
        $this->sms_message .=
            " | queue version " . $this->queue_engine_version . "";

        $this->sms_message .= " | TEXT LATENCY";
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeChoices()
    {
        if ($this->from == "null@stackr.ca") {
            $this->thing->choice->Create(
                $this->agent_name,
                $this->node_list,
                "null"
            );
            $choices = $this->thing->choice->makeLinks("null");
        } else {
            $this->thing->choice->Create(
                $this->agent_name,
                $this->node_list,
                "manager"
            );
            $choices = $this->thing->choice->makeLinks('manager');
        }

        $this->thing_report['choices'] = $choices;
    }

    public function makeWeb()
    {
        //$this->getData();
        //        $this->drawGraph();
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/manager';

        $head = '
            <td>
            <table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">
            <tr>
            <td align="center" valign="top">
            <div padding: 5px; text-align: center">';

        $foot = "</td></div></td></tr></tbody></table></td></tr>";

        $web = "";
        //$web = '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        //$web .= "</a>";
        $web .= "<br>";
        $web .= "<p>";
        /*
        $web .= "<b>Agent Manager</b>";

        $web .= "<p>";
        $web .= '<table>';
        $web .= '<th>'.'age' . "</th><th>" . 'Queued jobs' . "</th>";
        foreach ($this->points as $tub_name => $tub_quantity) {
            $web .= '<tr>';
            $web .= '<th>'.$tub_name . "</th><th>" . $tub_quantity . "</th>";
            $web .= "</tr>";
        }

//            $web .= '<th>'.'Total' . "</th><th>" . $this->total_things . "</th>";

        $web .= '</table>';
        $web .= "<p>";
*/
        $web .= "<br><br>";

        $this->thing_report['web'] = $web;
    }
}
