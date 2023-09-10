<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_DEPRECATED);
ini_set("display_errors", 1);

// TODO: Rebuild. Faster.

//#[\AllowDynamicProperties]
class Variables
{
    // So Variables manages a set of variables.
    // Providing basic mathematical and text variable
    // operations.

    // variables <variable set name> <identity> ie
    // a tally of 5 for mordok for variables@<mail_postfix>

    // Without an agent instruction, tally
    // return the calling identities self-tally.

    //   variables   / thing  /   $this->from

    public $nuuid;
    public $num_hits;
    public $variables_thing;
    public $index;
    public $name;

    public $train_agents;
    public $node_list;

    public $variable_set_name;

    public $limit;
    public $thing_report;

    public $agent_keywords;
    public $log_verbosity;

    public $agent_variables;
    public $verbosity;

    public $agent_command;
    public $nom_input;
    public $hour;
    public $minute;
    public $agent_name;

    public $agent_prefix;

    public $thing;
    public $start_time;
    public $uuid;
    public $to;
    public $from;
    public $subject;

    public $identity;
    public $response;
    public $agent;
    public $max_variable_sets;


    function __construct(Thing $thing, $agent_command = null)
    {
        // Setup Thing
        $this->thing = $thing;

        $this->start_time = $this->thing->elapsed_runtime();

        $this->uuid = $thing->uuid;

        // Review.
        // This is needed for null things.
        // Need to extend Variables with Agent.
        // And avoid recursive calls.

        $this->to = "null";
        if (isset($thing->to)) {
            $this->to = $thing->to;
        }
        $this->from = $thing->from;

        $this->subject = "merp";
        if (isset($thing->subject)) {
            $this->subject = $thing->subject;
        }
        $this->identity = $this->from;

        $this->response = "";

        // Setup Agent
        $this->agent = strtolower(get_class());

        $this->agent_name = "variables";
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';
        $this->agent_variables = ["variable", "name", "alpha", "beta"]; //Default variable set.
        $this->agent_variables = [];
        $this->max_variable_sets = 5;

        $this->agent_command = $agent_command;

        $this->verbosity = 1;
        $this->log_verbosity = 1;

        $this->agent_keywords = [
            "add",
            "increment",
            "equal",
            "equals",
            "=",
            "is",
            "&",
            "+",
            "-",
            "less",
            "plus",
            "subtract",
            "start",
            "init",
            "memory",
        ];

        $this->limit = 1e99;

        // Setup reporting
if (!isset($this->thing->thing)) {
$this->thing_report["thing"] = false;
} else {
        $this->thing_report["thing"] = $this->thing->thing;
}
        if ($agent_command == null) {
            $this->thing->log(
                    "did not find an agent command. No action taken.",
                "WARNING"
            );
        }

        $this->variable_set_name = "identity";

        $this->agent_command = $agent_command;

        $this->nom_input =
            $agent_command . " " . $this->from . " " . $this->subject;

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["start"];

        $this->thing->log(
                "running on Thing " .
                $this->thing->nuuid .
                ".",
            "INFORMATION"
        );

        $this->initVariables();

        $this->readInstruction();

        // Not sure this is limiting.
        $this->getVariables();

        $this->nuuid = substr($this->variables_thing->uuid, 0, 4);

        $this->readText();

        $this->setVariables();
        if ($this->agent_command == null) {
            $this->Respond();
        }

        $this->thing->log(
                "ran for " .
                number_format(
                    $this->thing->elapsed_runtime() - $this->start_time
                ) .
                "ms.",
            "OPTIMIZE"
        );

        $this->thing_report["log"] = $this->thing->log;
    }

    public function initVariables() {

        // Train variables have an associated headcode.
        $this->train_agents = [
            "destination",
            "bell",
            "horn",
            "A4",
            "stopwatch",
            "hey",
            "nod",
            "at",
            "event",
            "job",
            "frequency",
            "qr",
            "tone",
            "pain",
            "coordinate",
            "place",
            "rundate",
            "enddate",
            "amount",
            "fuel",
            "flag",
            "state",
            "quantity",
            "alias",
            "slug",
            "url",
            "negativetime",
            "runtime",
            "runat",
            "endat",
            "available",
            "state",
            "route",
            "consist",
        ];


    }
    function setVariables()
    {
        if (!isset($this->thing->db)) {
            $this->thing->log("Could not write to stack.");
            $this->response .= "Could not write to stack. ";
            return;
        }
        $this->thing->db->setFrom($this->identity);

        $refreshed_at = false;

        foreach ($this->agent_variables as $key => $variable_name) {

            // Intentionally write to the variable thing.  And the current thing.
            if (isset($variable_name)) {
                $this->variables_thing->Write(
                    [$this->variable_set_name, $variable_name],
                    $this->variables_thing->$variable_name
                );
                $this->thing->Write(
                    [$this->variable_set_name, $variable_name],
                    $this->variables_thing->$variable_name
                );
            }

            if ($variable_name == "refreshed_at") {
                $refreshed_at = true;
            }
        }

        if ($refreshed_at == false) {
            // Toss in a refreshed.
            $time_string = $this->thing->time();
            $this->setVariable("refreshed_at", $time_string);
        }
    }

    function getAgent()
    {
    }

    function getVariables($variable_set_name = null)
    {

if ((isset($this->thing->variables_thing)) and (isset($this->thing->variables_thing->db))) {
$this->variables_thing = $this->thing->variables_thing;
return;
}


        $split_time = $this->thing->elapsed_runtime();

        if ($variable_set_name == null) {
            $variable_set_name = $this->variable_set_name;
        }

        $this->thing->log(
            $this->agent_prefix . 'got variable "' . $variable_set_name . '".',
            "INFORMATION"
        );

        // We will probably want a getThings at some point.

        // Is there a database?
        $things = [null];
        if (isset($this->thing->db)) {
            $this->thing->db->setFrom($this->identity);

            // Returns variable sets managed by Variables.
            // Creates just one record per variable set.
            $thing_report = $this->thing->db->agentSearch(
                "variables",
                $this->max_variable_sets
            );

            $things = isset($thing_report["things"]) ? $thing_report['things'] : [];
        }
        // When we have that list of Things, we check it for the tally we are looking for.
        // Review using $this->limit as search length limiter.  Might even just
        // limit search to N microseconds of search.

        $match_count = 0;
        if ($things == false) {
            // No tally found.
            $this->startVariables();
        } else {
            $this->thing->log(
                $this->agent_prefix .
                    "got " .
                    count($things) .
                    " recent variable sets.",
                "DEBUG"
            );

            foreach ($things as $thing) {
                // Check each of the Things.
                //        $this->variables_thing = new Thing($thing['uuid']);

                // Load the full variable set.
                // If we code this right it shouldn't be a penalty
                // over $this->getVariable();
                if ($this->getVariableSet($thing) == false) {

                    // Should echo the matching variable sets
                    $match_count += 1;

                    $this->setVariables(); // Make sure thing and stack match.
                    // Consider seeing if this is really needed.

// dev store this in thing for memoization.
$this->thing->variables_thing = $this->variables_thing;
                    return;
                }
            }
            $this->startVariables();
            // So we get dropped out here with $this->variables_thing set
        }
$this->thing->variables_thing = $this->variables_thing;

    }

    function resetVariable()
    {
        $this->setVariable("variable", 1);
    }

    function startVariables()
    {
        $this->thing->log('Agent "Variables" started a variable set.', "DEBUG");

        // Creat a new tally wheel counter
        $this->variables_thing = new Thing(null);
        $this->variables_thing->Create(
            $this->identity,
            "variables",
            "s/ variables"
        );
        $this->variables_thing->flagGreen();

        foreach ($this->agent_variables as $key => $variable_name) {
            $this->setVariable($variable_name, null);
        }

        // Not yet implemented/used?

        // And create a pointer to the next
        // variable which will allow
        // the creation of a data set.

        $thing = new Thing(null);
        $this->setVariable("next_uuid", $thing->uuid);
    }

    function addVariable($variable = null, $amount = null)
    {
        $this->{$variable . "_overflow_flag"} = false;

        if ($variable == null) {
            $variable = "variable";
        }

        if (isset($this->variables_thing->$variable)) {
            $this->variables_thing->$variable += $amount;
        } else {
            $this->variables_thing->$variable = $amount;
        }

        // Then at this point we would call tally again for the next counter.
        if ($this->variables_thing->$variable > $this->limit) {
            $this->resetVariable();
            // Call next tallier, with a flag.
            $this->{$variable . "_overflow_flag"} = true; // true is the error flag

            $this->thing->log("Variable overflow.", "ERROR");
            $this->function_message = "Variable overflow";
            // THIS IS WHERE THE WORK IS nrwtaylor 1635 18 Oct 2017

            // And in this case flag as true.
            $this->$variable = 1;
        } else {
            $this->thing->flagGreen();
        }

        // Store counts
        $this->setVariable($variable, $this->variables_thing->$variable);

        return $this->{$variable . "_overflow_flag"};
    }

    function getVariableset($thing = null)
    {
        // Pulls in the full set from the db in one operation.
        // From a loaded Thing.

        $uuid = null;
        if (isset($thing["uuid"])) {
            $uuid = $thing["uuid"];
        }

        $this->variables_thing = new Thing($uuid);

        if (!isset($this->variables_thing->account["stack"])) {
            // No stack balance available.
            return null;
        }


        $variables = $this->variables_thing->account["stack"]->json->array_data;



        if (isset($variables[$this->variable_set_name])) {
            $this->context = "train";
            $t = $variables[$this->variable_set_name];

            $this->agent_variables = [];
            // Load to Thing variable for operations.
            foreach ($t as $name => $variable) {
                $this->variables_thing->$name = $variable;
                $this->agent_variables[] = $name;
            }
            return false;
        } else {
            return null;
        }

        return false;
    }

    public function makeVariableset()
    {
        // Urgh :/
        $t = "";
        $t .= "<br>Screened on: " . $this->variable_set_name . "<br>";
        $t .=
            "<br>nuuid " . substr($this->variables_thing->uuid, 0, 4) . "<br>";

        foreach ($this->agent_variables as $key => $variable_name) {
            $t .=
                $variable_name .
                " is " .
                $this->variables_thing->$variable_name .
                " ";
            $t .= "<br>";
        }
        $t .= "<br>";
        return $t;
    }

    function echoVariableset()
    {
        echo $this->makeVariableset();
    }

    function getVariable($variable = null)
    {
        if (!isset($this->thing->db)) {
            return true;
        }

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
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->$variable = $this->variables_thing->json->readVariable(
            [$this->variable_set_name, $variable]
        );

        // And then load it into the thing
        //        $this->$variable = $this->variables_thing->$variable;
        //        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    function setVariable($variable = null, $value = null)
    {
        if (!isset($this->thing->db)) {
            return true;
        }
        // Take a variable in the variables_thing and save
        // into the database.  Probably end
        // up coding setVariables, to
        // speed things up, but it isn't needed from
        // a logic perspective.

        if ($variable == null) {
            $variable = "variable";
        }

        // Review why it would be unsset at this point.
        if (!isset($this->variables_thing)) {
            $this->startVariables();
        }

        $this->variables_thing->$variable = $value;

        $this->variables_thing->db->setFrom($this->identity);

        try {
            $this->variables_thing->Write(
                [$this->variable_set_name, $variable],
                $value
            );
        } catch (Throwable $t) {
            //echo 'Caught throwable: ',  $t->getMessage(), "\n";
            // Executed only in PHP 7, will not match in PHP 5
        } catch (\OverflowException $e) {
            //echo 'Caught exception: ',  $e->getMessage(), "\n";

            $text = strtolower($this->subject);
            if ($text == "forget all" or $text == "forgetall") {
            } else {
                $this->thing_report["thing"] = $this->thing->thing;
                //                $this->thing_report['sms'] = $this->sms_message;
                $this->thing_report["sms"] =
                    "VARIABLES | 10,000 character stack variable space exhausted. Please text FORGET ALL to resolve.";

                $agent_message = new Message($this->thing, $this->thing_report);
                return;
            }
            // Executed only in PHP 5, will not be reached in PHP 7
        }

        // What are the options for dealing with variable overflow.
        // User will see this as the system not "remembering" things.

        // And that is okay to the extent that the stack erodes.
        // From an engineering perspective, we need to make the stack variables persstent in the face of random erosion.
        // Which is done via PERSISTENCE.

        // Here we are addressing a fundamental size limitation of any one thing to store all of an identitities variables
        // Especially when those variables have lots of unique identifiers.

        if ($this->variables_thing->json->write_fail_count > 0) {
            $this->thing->log(
                "overflow " .
                    $this->variables_thing->json->size_overflow .
                    " write_fail_count " .
                    $this->variables_thing->json->write_fail_count .
                    "."
            );
            $this->thing->log(
                "set " .
                    $this->variables_thing->uuid .
                    " " .
                    $this->variable_set_name .
                    " " .
                    $variable .
                    " " .
                    $value
            );
        }

        // And save variable_set onto local Thing.
        $this->thing->db->setFrom($this->identity);
        $this->thing->Write(
            [$this->variable_set_name, $variable],
            $value
        );

        if ($this->variables_thing->json->write_fail_count > 0) {
            $this->thing->log(
                "overflow " .
                    $this->thing->json->size_overflow .
                    " write_fail_count " .
                    $this->thing->json->write_fail_count .
                    "."
            );
            $this->thing->log(
                "set " .
                    $this->thing->uuid .
                    " " .
                    $this->variable_set_name .
                    " " .
                    $variable .
                    " " .
                    $value
            );
        }

        return $this->variables_thing->$variable;
    }

    function incrementVariable($variable = null)
    {
        $this->addVariable("variable", 1);
    }

    public function Respond()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        $this->sms_message = "VARIABLES SET IS ";
        $this->sms_message .= strtoupper($this->name);

        if ($this->verbosity >= 2) {
            $this->sms_message .= " | screened on " . $this->variable_set_name;
            $this->sms_message .=
                " | nuuid " . substr($this->variables_thing->uuid, 0, 4);
        }

        $this->sms_message .= " | ";

        foreach ($this->agent_variables as $key => $variable_name) {
            if (isset($variable_name)) {
                $this->sms_message .= " " . strtolower($variable_name) . " ";
                if (isset($this->variables_thing->$variable_name)) {
                    $this->sms_message .=
                        $this->variables_thing->$variable_name;
                } else {
                    $this->sms_message .= "X";
                }
            }
        }

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }

        if ($this->verbosity >= 5) {
            $this->sms_message .= " | TEXT ?";
        }
        $this->thing_report["thing"] = $this->thing->thing;
        $this->thing_report["sms"] = $this->sms_message;

        // While we work on this
        $this->thing_report["email"] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);

        return $this->thing_report;
    }

    public function defaultCommand()
    {
        $this->thing->log(
            "default command set.",
            "DEBUG"
        );

        $this->agent = "variables";
        $this->variable_set_name = "identity";

        $this->name = "identity";
        $this->identity = $this->from;
    }

    public function readInstruction()
    {
        if ($this->agent_command == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];

        // This is the agent name.
        $this->variable_set_name = $pieces[1];

        $headcode_flagging = "on";
        $variable_headcode = "";

        $train_agents = $this->train_agents;

        if ($headcode_flagging == "on") {
            // Is this a train agent.
            // One which has a headcode associated with it.
            if (in_array($pieces[1], $train_agents)) {
                $this->thing->json->setField("variables");
                $this->head_code = $this->thing->json->readVariable([
                    "headcode",
                    "head_code",
                ]);

                $variable_headcode = "_" . $this->head_code;
            }
        }

        $this->variable_set_name = $pieces[1] . $variable_headcode;

        $this->name = $pieces[1];
        $this->identity = $pieces[2];

        if (!isset($pieces[3])) {
            $this->index = 0;
        } else {
            $index = false;
            if (isset($pieces[4])) {
                $index = $pieces[4];

                $this->index = $pieces[4];
            }
        }
    }

    public function extractNumber($input)
    {
        $matches = 0;
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }
        if ($matches == 1) {
            $this->number = $number;
            $this->num_hits += 1;
            return $this->number;
        }
        return true;
    }

    public function isVariable($input)
    {
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key => $piece) {
            foreach ($this->agent_variables as $variable_name) {
                if ($piece == $variable_name) {
                    return false;
                }
            }
        }
        return true; // Not found
    }

    public function readSubject()
    {
    }

    public function extractVariable($input)
    {
        $matches = 0;
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key => $piece) {
            if ($this->isVariable($piece) == false) {
                $variable = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            $this->variable = $variable;
            $this->num_hits += 1;
        }
    }

    public function readText()
    {
        $this->num_hits = 0;
        // No need to read text.  Any identity input to Tally
        // increments the tally.

        $keywords = $this->agent_keywords;

        $haystack = strtolower($this->nom_input);
        $pieces = explode(" ", strtolower($this->nom_input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($this->nom_input == $this->agent) {
                return;
            }
        }

        $this->extractVariable($this->subject);
        $this->extractNumber($this->subject);

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {

                        case "plus":
                        case "add":
                        case "+":
                            if (
                                isset($this->number) and isset($this->variable)
                            ) {
                                $this->thing->log(
                                        "adding number to variable.",
                                    "INFORMATION"
                                );
                                $this->addVariable(
                                    $this->variable,
                                    $this->number
                                );
                                return;
                            }

                        case "minus":
                        case "subtract":
                        case "less":
                        case "-":
                            if (
                                isset($this->number) and isset($this->variable)
                            ) {
                                $this->thing->log(
                                        "adding number to variable.",
                                    "INFORMATION"
                                );
                                $this->addVariable(
                                    $this->variable,
                                    $this->number
                                );
                                return;
                            }

                        case "increment":
                            if (isset($this->variable)) {
                                $this->thing->log(
                                        "incrementing variable.",
                                    "INFORMATION"
                                );
                                $this->incrementVariable($this->variable);
                                return;
                            }

                        case "equals":
                        case "is":
                        case "=":
                            if (
                                isset($this->number) and isset($this->variable)
                            ) {
                                $this->thing->log(
                                        "setting " .
                                        $this->variable .
                                        " to " .
                                        $this->number .
                                        ".",
                                    "INFORMATION"
                                );

                                $this->setVariable(
                                    $this->variable,
                                    $this->number
                                );
                                return;
                            }

                        case "add":
                        case "&":
                            if (isset($this->variable)) {
                                $this->thing->log(
                                        'adding variable "' .
                                        $this->variable .
                                        '".',
                                    "INFORMATION"
                                );
                                $right_of_is = ltrim(
                                    strrchr($this->nom_input, " is ")
                                );

                                $this->setVariable(
                                    $this->variable,
                                    $right_of_is
                                );
                                return;
                            }

                        case "memory":

                            return;

                        default:
                    }
                }
            }
        }

        $this->thing->log("did no operation.", "DEBUG");
    }

    public function newVariable($name = null, $value = null)
    {
        if ($this->isVariable($name) == true) {
            $this->agent_variables[] = $name;
        }
        $this->setVariable($name, $value);
    }
}
