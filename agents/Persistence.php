<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Persistence extends Agent
{
    public function init()
    {
        $this->persist_for = $this->thing->container['stack']['persist_for'];
        $this->aliases = ["destroy" => ["delete"]];
    }

    public function set()
    {
        $this->thing->Write(
            ["persistence", "persist_to"],
            $this->thing->time($this->persist_to)
        );
    }

    public function run()
    {
        // Before doing anything else
        $this->remember_status = $this->thing->Read([
            "remember",
            "status",
        ]);

        if ($this->remember_status == true) {
            $this->thing->log(
                'found a record flagged for Remember.'
            );
            //$this->setRemember();
        } else {
            // TODO Refactor all this agent in new style
            $this->created_at = null;
            if (isset($thing->thing->created_at)) {
                $this->created_at = strtotime($thing->thing->created_at);
            }
            $dteStart = time();

            // Provide for translation to stack time unit
            if ($this->persist_for['unit'] == 'hours') {
                $age = $this->persist_for['amount'] * (60 * 60);
            }

            if ($this->persist_for['unit'] == 'days') {
                $age = $this->persist_for['amount'] * (24 * 60 * 60);
            }

            $this->persist_to = $dteStart + $age;

            $this->thing->json->setField("variables");
            $variables = $this->thing->json->read();
            $this->refreshed_at = 0;
            if ($variables != false) {
                foreach ($variables as $key => $variable) {
                    if (isset($variable['refreshed_at'])) {
                        $dte = strtotime($variable['refreshed_at']);

                        if ($dte > $this->refreshed_at) {
                            $this->refreshed_at = $dte;
                        }
                    }
                }
            }

            if ($this->refreshed_at == 0) {
                $this->refreshed_at = $this->created_at;
            }

            $this->time_remaining = $this->persist_to - $this->refreshed_at;
        }
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        // Thing actions

        $this->thing->Write(
            ["persistence", "persist_to"],
            $this->thing->time($this->persist_to)
        );

        $this->thing->flagGreen();

//        $from = $this->from;
//        $to = $this->to;

//        $subject = $this->subject;

        // Now passed by Thing object
//        $uuid = $this->uuid;
//        $sqlresponse = "yes";

//        $message = "Thank you $from this was PERSISTENCE";

        $this->sms_message = "PERSISTENCE | ";
        $this->sms_message .=
            "Thing " .
            $this->thing->nuuid .
            " will persist for " .
            $this->thing->human_time($this->time_remaining) .
            ".";
        $this->sms_message .= ' | TEXT PRIVACY';

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $this->thing_report['choices'] = false;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

//        $this->makeWeb();

//        $this->thing_report['thing'] = $this->thing->thing;

    }

    public function makeWeb()
    {
        $w = '<b>Persistence Agent</b><br>';
        $w .=
            "This agent sets how long a Thing is on the stack after the last refresh.<br>";
        $w .=
            "persist for " .
            $this->persist_for['amount'] .
            " " .
            $this->persist_for['unit'] .
            "<br>";
        $w .=
            "created at " .
            strtoupper(date('Y M d D H:i', $this->created_at)) .
            "<br>";
        $w .=
            "refreshed at " .
            strtoupper(date('Y M d D H:i', $this->refreshed_at)) .
            "<br>";
        $w .=
            "persist to " .
            strtoupper(date('Y M d D H:i', $this->persist_to)) .
            "<br>";
        $w .=
            "time remaining is " .
            $this->thing->human_time($this->time_remaining) .
            "<br>";
        // $w.= "<br>" . $this->age;
        // $w .= $this->retain_to;
        // $w .= ' | TEXT ?';

        $this->web_message = $w;
        $this->thing_report['web'] = $w;
    }

    public function readSubject()
    {
    }
}
