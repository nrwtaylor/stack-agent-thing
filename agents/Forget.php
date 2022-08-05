<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set("display_errors", 1);

class Forget extends Agent
{
    public function init()
    {
        $this->node_list = ["start"];

        // Hold the uuid through the forgetting.
        $this->uuid = $this->thing->uuid;

        $this->thing->log(
            'Agent "Forget" running on Thing ' . $this->uuid . "."
        );
    }

    public function run()
    {
        $this->Forget();

    }

    public function get()
    {
        // Should never happen if Forget is working.

        $time_string = $this->thing->Read([
            "forget",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["forget", "refreshed_at"],
                $time_string
            );
        }
    }

    public function set()
    {
    }

    function Forget()
    {
        $this->thing->Forget();
    }

    public function respondResponse()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and stopping this train.
        $this->thing->flagRed();


        // So return false
        $this->thing_report["thing"] = false;

        // While we work on this
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function makeSMS() {
        $this->sms_message =
            "FORGOT | Thing " . $this->uuid . ". " . $this->response . " | TEXT PRIVACY";
        $this->thing_report["sms"] = $this->sms_message;

    }

    public function makeMessage() {
        $this->thing_report['message'] = "Forgot Thing " . $this->uuid . ". " . $this->response;
    }

    public function makeInfo() {
        $this->info = "This is the command to FORGET a THING.";
        $this->thing_report['info'] = $this->info;
    }

    public function makeHelp() {
        $this->help = "Once a THING is forgotten it is gone.";
        $this->thing_report['help'] = $this->help;

    }



    public function makeResponse() {
       $this->response = "It is gone. ";
       $this->thing_report['response'] = $this->response;
    }

    public function makeWeb()
    {
        $web = "";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = ["forget" => ["privacy", "terms-and-conditions"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');

        $web = "Forgot Thing " . $this->uuid . ". It is gone.<br>";

        $ago = $this->thing->human_time(time() - $this->created_at);
        $web .= "It was instantiated " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    public function readSubject()
    {
    }
}
