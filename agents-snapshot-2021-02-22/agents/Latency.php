<?php
namespace Nrwtaylor\StackAgentThing;

class Latency extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->queue_time = $this->thing->elapsed_runtime();

        // So I could call
        $this->test = false;
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->getLatency();

        $this->node_list = [
            "latency" => ["latency", "tallygraph"],
            "null" => [],
        ];
    }

    public function get()
    {
        $this->thing->Get();
        $this->current_state = $this->thing->getState('usermanager');
    }

    public function makeMessage()
    {
        $m = "Time queued was " . number_format($this->queue_time) . "ms. ";
        $rtime = $this->thing->elapsed_runtime() - $this->start_time;
        $m .= "Job ran for " . number_format($rtime) . "ms. ";
        $m .=
            "Total elapsed time is " .
            number_format($this->thing->elapsed_runtime()) .
            "ms.";

        $this->message = $m;
        $this->thing_report['message'] = $m;
    }

    public function makeSMS()
    {
        $this->getQueuetime();
        $rtime = $this->thing->elapsed_runtime() - $this->start_time;

        $this->node_list = ["latency" => ["latencygraph"]];

        $this->sms_message = "LATENCY";
        $this->sms_message .=
            " | qtime " . number_format($this->queue_time) . "ms";
        $this->sms_message .= " | rtime " . number_format($rtime) . "ms";
        $this->sms_message .=
            " | etime " . number_format($this->thing->elapsed_runtime()) . "ms";

        $this->sms_message .= " | TEXT PING";
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
                "latency"
            );
            $choices = $this->thing->choice->makeLinks('latency');
        }

        $this->thing_report['choices'] = $choices;
        return;
    }

    private function getRuntime()
    {
        $this->run_time = $this->thing->elapsed_runtime() - $this->start_time;
    }

    private function getQueuetime()
    {
        if (!isset($this->queue_time)) {
            $this->queue_time = $this->start_time;
        }
    }

    public function set()
    {
        $this->current_time = $this->thing->json->time();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "latency",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["latency", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->thing->json->setField("variables");
        $queue_time = $this->thing->json->readVariable([
            "latency",
            "queue_time",
        ]);
        $run_time = $this->thing->json->readVariable(["latency", "run_time"]);

        if ($queue_time == false and $run_time == false) {
            $this->getLatency();

            $this->readSubject();

            $this->thing->json->writeVariable(
                ["latency", "queue_time"],
                $this->queue_time
            );
            $this->thing->json->writeVariable(
                ["latency", "run_time"],
                $this->run_time
            );
        }
    }

    function getLatency()
    {
        $this->getQueuetime();
        $this->getRuntime();
        $this->elapsed_time = $this->queue_time + $this->run_time;
    }

    function isNumeric($number = null)
    {
        return is_numeric($number);
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        //$to = $this->thing->from;
        //$from = "latency";

        //		$subject = 's/pingback '. $this->current_state;

        //		$message = 'Latency checker.';

        //		$received_at = strtotime($this->thing->thing->created_at);

        //        $ago = $this->thing->human_time ( time() - $received_at );

        $this->makeChoices();

        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['keyword'] = 'pingback';
        $this->thing_report['help'] =
            'Latency is how long the message is in the stack queue.';
    }

    public function readSubject()
    {
    }
}
