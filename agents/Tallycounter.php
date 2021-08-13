<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set("display_errors", 1);

class Tallycounter
{
    // So Tallycounter tallies up.  It follows
    // the uuid chain and calculates the count.

    // If an Agent gives it a command, it will set up the
    // parameters of the Tally, which by default are:
    //   tallycounter / mordok  /  tally@stackr.ca

    //   tallycounter  <agent> <identity> ie
    // a tallycounter for mordok for tally@stackr.ca

    // Without an agent instruction, tallycounter
    // return the calling identities self-count.

    //   tallycounter / thing  /   $this->from

    function __construct(Thing $thing, $agent_command = null)
    {
        $this->start_time = microtime(true);

        // Setup Thing
        $this->thing = $thing;
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        // Setup logging
        $this->thing_report["thing"] = $this->thing->thing;

        //Testing
        //$agent_command = "tallycounter binary tally@stackr.ca";

        if ($agent_command == null) {
            $this->thing->log('Agent "Tally" did not find an agent command.');
        }

        $this->agent_command = $agent_command;

        $this->nom_input =
            $agent_command . " " . $this->from . " " . $this->subject;

        $this->readInput();

        $this->thing->log(
            $this->agent_prefix .
                "settings are: " .
                $this->agent .
                " " .
                $this->name .
                " " .
                $this->identity .
                "."
        );

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->current_time = $this->thing->time();

        $this->node_list = ["tallycounter"];

        $this->thing->log(
            "<pre> " .
                $this->agent_prefix .
                " running on Thing " .
                $this->thing->nuuid .
                " </pre>",
            "INFORMATION"
        );

        $this->getAgent();

        $this->Respond();

        $this->set();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log(
            'Agent "Tallycounter" ran for ' . $milliseconds . "ms.",
            "OPTIMIZE"
        );

        $this->thing_report["log"] = $this->thing->log;
        return;
    }

    function set()
    {
        $this->thing->Write(
            ["tallycounter", "count"],
            $this->count
        );

        $this->thing->Write(
            ["tallycounter", "display"],
            $this->display
        );

        $this->thing->Write(
            ["tallycounter", "refreshed_at"],
            $this->thing->time()
        );
    }

    function get()
    {
        return;
    }

    function getTallycounter()
    {
        $this->getVariables("tally");
        // which will match the name

        $uuid = $this->variables_thing->uuid;

        //if (!isset($this->variables_thing->next_uuid)) {
        //    $this->variables_thing->next_uuid = null;
        //}
        $next_uuid = $this->variables_thing->next_uuid;

        // Now there is a

        $index = 0;
        $count = 0;
        $display = "";

        $split_time = $this->thing->elapsed_runtime();

        foreach ($this->counter_uuids as $uuid) {
            //$uuid = $next_uuid;
            $thing = new Thing($uuid);

            $thing->db->setFrom($this->identity);

            $variable = $thing->Read(["tally", "variable"]);
            $limit = $thing->Read(["tally", "limit"]);
            $name = $thing->Read(["tally", "name"]);
            $next_uuid = $thing->Read(["tally", "next_uuid"]);

            $count = $count + pow($limit, $index) * $variable;
            $display = $variable . "/" . $display;
            $index += 1;
        }

        $this->count = $count;
        $this->display = $display;
    }

    function getAgent()
    {
        // Tallycounter
        $this->getTallycounter();
    }

    function getVariables($agent = null)
    {
        if ($agent == null) {
            $agent = $this->agent;
        }

        $this->variables_horizon = 99;
        $this->variables_agent = $agent; // Allows getVariables to pull in a different agents variables.
        // Here we only need to save the count.
        // But need to inspect Tally

        //        $this->variables_agent = $agent;

        // So this returns the last 3 tally Things.
        // which should be enough.  One should be enough.
        // But this just provides some resiliency.

        $this->thing->log(
            'Agent "Tallycounter" requested the variables.',
            "DEBUG"
        );

        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch(
            $this->variables_agent,
            $this->variables_horizon
        );
        $things = $thing_report["things"];

        if ($things == false) {
            $this->startVariables();
            return;
        }

        $this->thing->log(
            'Agent "Tallycounter" got ' .
                count($things) .
                " recent Tally Things.",
            "INFORMATION"
        );

        $this->counter_uuids = [];

        foreach ($things as $thing) {
            // Check each of the three Things.

            $uuid = $thing["uuid"];
            $variables_json = $thing["variables"];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables["tally"])) {
                if (isset($variables["tally"]["variable"])) {
                    $variable = $variables["tally"]["variable"];
                }
                if (isset($variables["tally"]["name"])) {
                    $name = $variables["tally"]["name"];
                }
                if (isset($variables["tally"]["next_uuid"])) {
                    $next_uuid = $variables["tally"]["next_uuid"];
                }
            }

            if ($this->name == $name) {
                //$next_uuid = $uuid;
                $this->counter_uuids[] = $uuid;

                //               $this->thing->log( 'Agent "Tallycounter" loaded the tallycounter variable: ' . $this->variables_thing->variable . '.','INFORMATION' );
                //               $this->thing->log( 'Agent "Tallycounter" loaded the tallycounter name: ' . $this->variables_thing->name . '.','INFORMATION' );
                //               $this->thing->log( 'Agent "Tallycounter" next counter pointer is: ' . substr($this->variables_thing->next_uuid,0,4) . "." ,'DEBUG');

                break;
            }
        }

        $match_uuid = $next_uuid;

        $split_time = $this->thing->elapsed_runtime();
        $index = 0;

        while (true) {
            foreach ($things as $thing) {
                // Check each of the three Things.
                $this->variables_thing = new Thing($thing["uuid"]);

                $uuid = $thing["uuid"];
                $variable = $this->getVariable("variable");
                $name = $this->getVariable("name");
                $next_uuid = $this->getVariable("next_uuid");
                /*
                $uuid = $thing['uuid'];
                $variables_json= $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);
                if (isset($variables['tally'])) {
                    if(isset($variables['tally']['variable'])) {$variable = $variables['tally']['variable'];}
                    if(isset($variables['tally']['name'])) {$name = $variables['tally']['name'];}
                    if(isset($variables['tally']['next_uuid'])) {$next_uuid = $variables['tally']['next_uuid'];}

                }
*/

                if ($name == $match_uuid) {
                    $this->counter_uuids[] = $uuid;
                    break;
                }
            }
            /*
            $this->variables_thing = new Thing($thing['uuid']);
*/
            $match_uuid = $next_uuid;

            $index += 1;

            $max_time = 1000 * 0.05; //ms
            if ($this->thing->elapsed_runtime() - $split_time > $max_time) {
                break;
            }
        }

        return;
    }

    function startVariables()
    {
        $this->thing->log('Agent "Tallycounter" started a count.');

        if (!isset($this->variables_thing)) {
            $this->variables_thing = $this->thing;
        }

        $this->setVariable("variable", 0);
        $this->setVariable("name", $this->name);
    }

    function getVariable($variable = null)
    {
        // Pulls variable from the database
        // and sets variables thing on the current record.
        // so shouldn't need to adjust the $this-> set
        // of variables and can refactor that out.

        // All variables should be callable by
        // $this->variables_thing.

        // The only Thing variable of use is $this->from
        // which is used to set the identity for
        // self-tallies.  (Thing and Agent are the
        // only two role descriptions.)

        if ($variable == null) {
            $variable = "variable";
        }

        $this->variables_thing->db->setFrom($this->identity);

        $this->variables_thing->$variable = $this->variables_thing->Read(
            [$this->variables_agent, $variable]
        );

        // And then load it into the thing
        //        $this->$variable = $this->variables_thing->$variable;
        //        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    function setVariable($variable = null, $value)
    {
        // Take a variable in the variables_thing and save
        // into the database.  Probably end
        // up coding setVariables, to
        // speed things up, but it isn't needed from
        // a logic perspective.

        if ($variable == null) {
            $variable = "variable";
        }
        //        if (!isset($this->variables_thing)) { $this->variables_thing = $this->thing;}

        $this->variables_thing->$variable = $value;

        $this->variables_thing->db->setFrom($this->identity);

        $this->variables_thing->Write(
            [$this->variables_agent, $variable],
            $value
        );

        //        $this->$variable = $value;
        //        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    public function Respond()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        $this->thing->log(
            'Agent "Tallycounter" variable is ' .
                $this->variables_thing->variable .
                "."
        );

        $this->sms_message = "TALLY COUNTER  = " . number_format($this->count);

        $this->sms_message .= " | " . $this->display;
        $this->sms_message .= " | " . $this->name;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
        $this->sms_message .= " | TEXT ?";

        $this->thing_report["thing"] = $this->thing->thing;
        $this->thing_report["sms"] = $this->sms_message;

        // While we work on this
        $this->thing_report["email"] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);

        return $this->thing_report;
    }

    public function defaultCommand()
    {
        $this->agent = "tallycounter";
        //$this->limit = 5;
        $this->name = "thing";
        $this->identity = $this->from;
        return;
    }

    public function readInstruction()
    {
        if ($this->agent_command == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];

        return;
    }

    public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
        return;
    }

    public function readInput()
    {
        $this->readInstruction();
        $this->readText();
        return;
    }
}

?>
