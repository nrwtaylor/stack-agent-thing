<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Tally
{
    // So Tally just increments a variable and keeps going past 0.
    // limit:5 => 1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2 ...
    // And that is what this does.

    // If an Agent gives it a command, it will set up the
    // parameters of the Tally, which by default are:
    //   tally /  5   / mordok  /  tally@<mail_postfix>

    //   tally <tally_limit> <agent> <identity> ie
    // a tally of 5 for mordok for tally@<mail_postfix>

    // Without an agent instruction, tally
    // return the calling identities self-tally.

    //   tally /  5   / thing  /   $this->from

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
        $this->thing_report['thing'] = $this->thing->thing;

        if ($agent_command == null) {
            $this->thing->log('Agent "Tally" did not find an agent command.');
        }

        $this->agent_command = $agent_command;
        $this->nom_input =
            $agent_command . " " . $this->from . " " . $this->subject;

        $this->readInput();
        $this->thing->log(
            'Agent "Tally" settings are: ' .
                $this->agent .
                ' ' .
                $this->name .
                ' ' .
                $this->identity .
                ' ' .
                $this->limit .
                "."
        );

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        //$this->sqlresponse = null;

        $this->node_list = array("start");

        $this->thing->log(
            '<pre> ' .
                $this->agent_prefix .
                ' running on Thing ' .
                $this->thing->nuuid .
                ' </pre>'
        );

        $this->overflow = null;

        // Load the variables into $this->variables_thing
        $this->getVariables();

        // Add 1 to the tally
        $this->addTally();

        // Make sure the variables are written to the db.
        $this->setVariables();

        // Then respond to the Identity.
        if ($this->agent_command == null) {
            $this->Respond();
        }

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log('Agent "Tally" ran for ' . $milliseconds . 'ms.');

        $this->thing_report['log'] = $this->thing->log;
        return;
    }

    function getAgent()
    {
        return;
    }

    function setVariables()
    {
        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->json->writeVariable(
            array("tally", "variable"),
            $this->variables_thing->variable
        );
        $this->variables_thing->json->writeVariable(
            array("tally", "name"),
            $this->variables_thing->name
        );
        $this->variables_thing->json->writeVariable(
            array("tally", "limit"),
            $this->variables_thing->limit
        );
        $this->variables_thing->json->writeVariable(
            array("tally", "next_uuid"),
            $this->variables_thing->next_uuid
        );

        // Save to local Thing too.  But note
        // this isn't pulled up by an agent search.
        // So just provides a backup in case the main record gets
        // forgetton.
        $this->thing->db->setFrom($this->identity);
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            array("tally", "variable"),
            $this->variables_thing->variable
        );
        $this->thing->json->writeVariable(
            array("tally", "name"),
            $this->variables_thing->name
        );
        $this->thing->json->writeVariable(
            array("tally", "limit"),
            $this->variables_thing->limit
        );

        $this->thing->json->writeVariable(
            array("tally", "next_uuid"),
            $this->variables_thing->next_uuid
        );
    }

    function getFlag()
    {
        $this->flag_thing = new Flag($this->thing, 'flag');
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }

    function setFlag($colour)
    {
        $this->flag_thing = new Flag($this->thing, 'flag ' . $colour);
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }

    function getVariables()
    {
        // So this returns the last 3 tally Things.
        // which should be enough.  One should be enough.
        // But this just provides some resiliency.

        $this->thing->log('Agent "Tally" requested the variables.');

        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch(
            $this->agent,
            $this->limit
        );
        $things = $thing_report['things'];

        // When we have that list of Things, we check it for the tally we are looking for.
        // Review using $this->limit as search length limiter.  Might even just
        // limit search to N microseconds of search.
        if ($things == false) {
            //            $this->thing->log( 'Agent "Tally" getVariables did not find any Tally Things.' );

            // No tally found.
            $this->startVariables();
        } else {
            $this->thing->log(
                'Agent "Tally" got ' . count($things) . ' recent Tally Things.'
            );

            foreach ($things as $thing) {
                // Check each of the three Things.
                $this->variables_thing = new Thing($thing['uuid']);

                $this->getVariable('variable');
                $this->getVariable('name');
                $this->getVariable('limit');
                $this->getVariable('next_uuid');

                $this->thing->log(
                    'Agent "Tally" (' .
                        $this->variables_thing->variable .
                        ' ' .
                        $this->variables_thing->name .
                        ' ' .
                        $this->variables_thing->next_uuid .
                        ').'
                );

                if ($this->name == $this->variables_thing->name) {
                    $this->thing->log(
                        'Agent "Tally" loaded the tally variable: ' .
                            $this->variables_thing->variable .
                            '.'
                    );
                    $this->thing->log(
                        'Agent "Tally" loaded the tally name: ' .
                            $this->variables_thing->name .
                            '.'
                    );
                    $this->thing->log(
                        'Agent "Tally" next counter pointer is: ' .
                            substr($this->variables_thing->next_uuid, 0, 4) .
                            "."
                    );

                    return;
                }

                //                 $this->thing->log( 'Agent "Tally" did not find a match for ' . $this->name ."." );
                // So couldn't find a matching variable.  Reset.
                //$this->resetVariable();
            }
            $this->startVariables();
            // So we get dropped out here with $this->variables_thing set
        }
    }

    function resetVariable()
    {
        //        $this->thing->log( 'Agent "Tally" reset the tally variable.' );

        //$this->variable = 1;

        $this->setVariable("variable", 0);
        //        $this->setVariable("name", $this->name);

        // If there is no pointer to the next "wheel";
        // then we need to create one.  But it should be constant.
        // Creating a tally chain.
        // Mordok > f56e > 1cde > 9a6e4 > etc
        //       if (!isset($this->next_uuid)) {
        //           $thing = new Thing(null);
        //           $this->setVariable("next_uuid", $thing->uuid);
        //       }
    }

    function startVariables()
    {
        $this->thing->log('Agent "Tally" started a tally.');

        // Creat a new tally wheel counter
        $this->variables_thing = new Thing(null);
        $this->variables_thing->Create($this->identity, "tally", 's/ tally');
        $this->variables_thing->flagGreen();

        $this->setVariable("variable", 0);
        $this->setVariable("name", $this->name);
        $this->setVariable("limit", $this->limit);

        // And create the next wheel
        // The uuid is unique, so we can bank
        // it until we create the next wheel on the 'click'.
        $thing = new Thing(null);
        //$thing->Create($this->identity, "tally", 's/ tally' );

        $this->setVariable("next_uuid", $thing->uuid);

        //        $this->setVariable("next_uuid", $this->variables_thing->uuid);

        return;
    }

    function addVariable($variable = null, $amount = null)
    {
        $this->{$variable . "_overflow_flag"} = false;

        if ($variable == null) {
            $variable = 'variable';
        }

        $this->variables_thing->$variable += $amount;

        // Then at this point we would call tally again for the next counter.
        if ($this->variables_thing->$variable > $this->limit) {
            $this->resetVariable();
            $this->{$variable . "_overflow_flag"} = true; // true is the error flag

            $this->thing->log('Variable overflow.');
            $this->function_message = "Variable overflow";

            $this->overflow = false;
            $this->setFlag("red");
        } else {
            $this->setFlag("green");
            $this->thing->flagGreen();
        }

        // Store counts
        $this->setVariable($variable, $this->variables_thing->$variable);
        //        $this->variables_thing->flagGreen();

        return $this->{$variable . "_overflow_flag"};
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
            $variable = 'variable';
        }

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->$variable = $this->variables_thing->json->readVariable(
            array($this->agent, $variable)
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
            $variable = 'variable';
        }
        //        if (!isset($this->variables_thing)) { $this->variables_thing = $this->thing;}

        $this->variables_thing->$variable = $value;

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->json->writeVariable(
            array($this->agent, $variable),
            $value
        );

        //        $this->$variable = $value;
        //        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    function incrementVariable($variable = null)
    {
        $this->addVariable('variable', 1);
        return;
    }

    function addTally()
    {
        $this->incrementVariable('variable');

        if ($this->variable_overflow_flag) {
            $this->function_message = "CLICK";

            // There are two ways to call the next wheel.
            $tally_thing = new Thing($this->variables_thing->next_uuid);

            // Create the wheel on the fly if it hasn't yet been created..
            if ($tally_thing->thing == false) {
                $this->thing->log(
                    'Agent "Tally" created a new wheel ' .
                        $this->variables_thing->next_uuid .
                        '.'
                );

                $tally_thing->Create($this->identity, "tally", 's/ tally');
                $tally_thing->flagGreen();
            }
            // EXPLORE this slow way once tally is working
            // and once function can be checked via tallycounter.
            //   if ($tally_thing == false) {
            //       $tally_thing->Create($this->identity, "tally", 's/ ' . $this->agent_command );
            //   }
            //tally /  5   / mordok  /  tally@<mail_postfix>
            //   $tally_thing->flagRed();

            // Or call a instant update by
            $command =
                $this->agent .
                " " .
                $this->limit .
                " " .
                $this->variables_thing->next_uuid .
                " " .
                $this->identity;
            $report = new Tally($tally_thing, $command);
        }
    }

    public function Respond()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        $this->thing->log(
            'Agent "Tally" variable is ' .
                $this->variables_thing->variable .
                '.'
        );

        $this->sms_message =
            "TALLY = " . number_format($this->variables_thing->variable);
        $this->sms_message .= " | name " . $this->variables_thing->name;

        $this->sms_message .=
            " | nuuid " . substr($this->variables_thing->next_uuid, 0, 4);

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);

        return $this->thing_report;
    }

    public function defaultCommand()
    {
        $this->agent = "tally";
        $this->limit = 4; // ie 5 digit counter
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
        $this->limit = $pieces[1];
        $this->name = $pieces[2];
        $this->identity = $pieces[3];

        //if (!isset($pieces[4])) {
        //    $this->index = 0;
        //} else {
        //    $this->index = $pieces[4];
        //}

        $this->thing->log(
            'Agent "Tally" read the instruction and got ' .
                $this->agent .
                ' ' .
                $this->limit .
                ' ' .
                $this->name .
                ' ' .
                $this->identity .
                "."
        );
    }

    public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
    }

    public function readInput()
    {
        $this->readInstruction();
        $this->readText();
    }
}
