<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set("display_errors", 1);

// devstack Shuffeall, comes from Forgetall, leads to Stack Balance.

class Shuffleall extends Agent
{
    public function init()
    {
        $this->node_list = ["start", "opt-in"];
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "shuffleall",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["shuffleall", "refreshed_at"],
                $time_string
            );
        }
    }

    public function set()
    {
    }

    public function run()
    {
        $this->ShuffleAll();
        $this->thing->Shuffle();
    }

    function ShuffleAll()
    {
        // Getting memory error from db looking
        // up balance for null
        if ($this->from == "null" . $this->mail_postfix) {
            $this->response .= "Shuffle All requires an identity. ";

            return;
        }

        // devstack paged input
        // Get all users records

        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(""); // Designed to accept null as $this->uuid.

        $things = $thingreport["thing"];

        $this->total_things = count($things);

        $start_time = time();

        $count = 0;
        shuffle($things);

        $start_time = time();

        while (count($things) > 1) {
            $thing = array_pop($things);

            if ($thing["uuid"] != $this->uuid) {
                $temp_thing = new Thing($thing["uuid"]);
                $temp_thing->Shuffle();

                $count += 1;
            } else {
            }
        }

        $this->response .=
            "Completed request for this Identity. Shuffled " .
            $count .
            " Things. ";
    }

    public function respondResponse()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        $this->sms_message =
            "SHUFFLE ALL | " . $this->response . " | TEXT FORGET ALL";

        // Will it pass this forward?
        // Must do to report on outcome.
        // devstack could create a null Thing.

        // This would retain an image of the Thing in the response.  This
        // is clearly not the intent of someone requesting FORGET ALL.

        // So return false
        $this->thing_report["thing"] = $this->thing->thing;
        $this->thing_report["sms"] = $this->sms_message;

        // While we work on this
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
    }
}
