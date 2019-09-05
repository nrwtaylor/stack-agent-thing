<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

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

	function __construct(Thing $thing, $agent_command = null)
    {

//if ($agent_command == "variables place console") {exit();}
        // Setup Thing
        $this->thing = $thing;

        $this->start_time = $this->thing->elapsed_runtime();

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->identity = $this->from;

        // Setup Agent
        $this->agent = strtolower(get_class());

        $this->agent_name = "variables";
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';
        $this->agent_variables = array('variable', 'name', 'alpha', 'beta'); //Default variable set.
        $this->agent_variables = array();
        $this->max_variable_sets = 5;

        $this->agent_command = $agent_command;

        $this->verbosity = 1;
        $this->log_verbosity = 1;

        $this->agent_keywords = array('add', 'increment', 'equal', 'equals', '=', 'is', "&", "+", "-", "less", "plus", "subtract", "start", "init", "memory");

        $this->limit = 1e99;

        // Setup reporting
        $this->thing_report['thing'] = $this->thing->thing;

        if ($agent_command == null) {
            $this->thing->log( $this->agent_prefix . 'did not find an agent command. No action taken.', "WARNING" );
        }

        $this->variable_set_name = "identity";

        $this->agent_command = $agent_command;

        $this->nom_input = $agent_command . " " . $this->from . " " . $this->subject;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->node_list = array("start");

		$this->thing->log( $this->agent_prefix . 'running on Thing ' .  $this->thing->nuuid .  '.', 'INFORMATION' );

        $this->readInstruction();

        // Not sure this is limiting.
        $this->getVariables();

//if ($agent_command == "variables place console") {exit();}

        $this->nuuid = substr($this->variables_thing->uuid,0,4);

		$this->readText();

        $this->setVariables();
        if ($this->agent_command == null) {
    		$this->Respond();
        }

        // Commented out 4 Jul 2018
        // Toss in a refreshed.
        //$time_string = $this->thing->time();
        //$this->setVariable("refreshed_at", $time_string);

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', 'OPTIMIZE' );

        $this->thing_report['log'] = $this->thing->log;
	}

    function setVariables()
    {


        $this->thing->db->setFrom($this->identity);

        $refreshed_at = false;

        foreach ($this->agent_variables as $key=>$variable_name) {

            // Write the agent name (not "variables")
//            $this->variables_thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->name );
//            $this->thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->name );



//if ($this->agent_command == "variables place console") {exit();}


            // Intentionally write to the variable thing.  And the current thing.
            if (isset($variable_name)) {
                $this->variables_thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->variables_thing->$variable_name );
                $this->thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->variables_thing->$variable_name );

            }

            if ($variable_name == "refreshed_at") {$refreshed_at = true;}

        }

//if ($this->agent_command == "variables place console") {exit();}


        if ($refreshed_at == false) {
        // Toss in a refreshed.
            $time_string = $this->thing->time();
            $this->setVariable("refreshed_at", $time_string);
        }

    }




    function getAgent()
    {
        return;
    }

    function getVariables($variable_set_name = null)
    {
        $split_time = $this->thing->elapsed_runtime();

        if ($variable_set_name == null) {
            $variable_set_name= $this->variable_set_name;
        }

        $this->thing->log( $this->agent_prefix . 'got variable "' .  $variable_set_name . '".', 'INFORMATION' );

        // We will probably want a getThings at some point.

        $this->thing->db->setFrom($this->identity);

        // Returns variable sets managed by Variables.
        // Creates just one record per variable set.
        $thing_report = $this->thing->db->agentSearch("variables", $this->max_variable_sets); 
//if ($this->agent_command == "variables place console") {exit();}

        $things = $thing_report['things'];

        // When we have that list of Things, we check it for the tally we are looking for.
        // Review using $this->limit as search length limiter.  Might even just
        // limit search to N microseconds of search.

        $match_count =0;
        if ( $things == false  ) {

            // No tally found.
            $this->startVariables();

        } else {

            $this->thing->log( $this->agent_prefix . 'got ' . count($things) . ' recent variable sets.', "DEBUG" );

            foreach ($things as $thing) {
                // Check each of the Things.
                $this->variables_thing = new Thing($thing['uuid']);

                // Load the full variable set.
                // If we code this right it shouldn't be a penalty
                // over $this->getVariable();
//if ($this->agent_command == "variables place console") {exit();}

                if($this->getVariableSet() == false) {

                    $this->thing->log( $this->agent_prefix . 'got ' . $this->variables_thing->nuuid, "DEBUG" );

              //if($this->getVariableSet() != false) {

                    // Should echo the matching variable sets
                    $match_count += 1;
//if ($this->agent_command == "variables place console") {exit();}
 
                   $this->setVariables(); // Make sure thing and stack match.
                                            // Consider seeing if this is really needed.
//if ($this->agent_command == "variables place console") {exit();}

                    return;
                }
            }
            $this->startVariables();
            // So we get dropped out here with $this->variables_thing set
        }
        return;
	}

    function resetVariable() 
    {
        $this->setVariable("variable", 1);
        return;
    }

	function startVariables() 
    {
        $this->thing->log( 'Agent "Variables" started a variable set.', "DEBUG" );

        // Creat a new tally wheel counter
        $this->variables_thing = new Thing(null);
        $this->variables_thing->Create($this->identity, "variables", 's/ variables' );
        $this->variables_thing->flagGreen();

        foreach ($this->agent_variables as $key=>$variable_name) {

            //if ($variable_name == "name" {
            //    $this->setVariable($variable_name, $this->agent_name); // Reserved for the agent name.
            //} else {
                $this->setVariable($variable_name, null);
            //} 
        }



        // Not yet implemented/used?

        // And create a pointer to the next
        // variable which will allow
        // the creation of a data set.

        $thing = new Thing(null);
        $this->setVariable("next_uuid", $thing->uuid);

		return;
	}

    function addVariable($variable = null, $amount)
    {
        $this->{$variable . "_overflow_flag"} = false;

        if ($variable == null) {$variable = 'variable';}

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
            //$this->thing->flagRed();

            $this->thing->log('Variable overflow.', "ERROR");
            $this->function_message = "Variable overflow";
            // THIS IS WEHERE THE OWRK IS nrewtaylor 1635 18 Oct 2017

            // And in this case flag as true.
            $this->$variable = 1;
        } else {
            $this->thing->flagGreen();
        }

        // Store counts
        $this->setVariable($variable, $this->variables_thing->$variable);
        // $this->variables_thing->flagGreen();

        return $this->{$variable . "_overflow_flag"};
    }

    function getVariableset()
    {
        // Pulls in the full set from the db in one operation.
        // From a loaded Thing.


        if (!isset($this->variables_thing->account['stack'])) {
            // No stack balance available.
            return null;
        }

        $variables = $this->variables_thing->account['stack']->json->array_data;

        if (isset($variables[$this->variable_set_name])) {
            $this->context = "train";
            $t = $variables[$this->variable_set_name];

            $this->agent_variables = array();
            // Load to Thing variable for operations.
            foreach ($t as $name=>$variable) 
            {
                $this->variables_thing->$name = $variable;
                $this->agent_variables[] = $name;
            }

            return false;
        } else {
            return null;
        }

        return false;
    }

    function echoVariableset()
    {
        // Urgh :/

        echo "<br>Screened on: " . $this->variable_set_name . "<br>";
        echo "<br>nuuid " . substr($this->variables_thing->uuid,0,4) . "<br>";


        foreach ($this->agent_variables as $key=>$variable_name) 
        {
            echo $variable_name . " is " . $this->variables_thing->$variable_name . " ";
            echo "<br>";
        }
        echo "<br>";
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

        if ($variable == null) {$variable = 'variable';}

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->$variable = $this->variables_thing->json->readVariable( array($this->variable_set_name, $variable) );


//$this->thing->log("get " . $this->variables_thing->uuid . " " . $this->variable_set_name ." " . $variable . " " . $this->variables_thing->$variable. ".", DEBUG);


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

        if ($variable == null) {$variable = 'variable';}

        // Review why it would be unsset at this point.
        if (!isset($this->variables_thing)) {
            $this->startVariables();
        }

        $this->variables_thing->$variable = $value;

//      set_error_handler(array($this, 'warning_handler'), E_WARNING);
//set_exception_handler(array($this,'my_exception_handler'));
//try {
        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->json->writeVariable( array($this->variable_set_name, $variable), $value );
        // What are the options for dealing with variable overflow.
        // User will see this as the system not "remembering" things.

        // And that is okay to the extent that the stack erodes.
        // From an engineering perspective, we need to make the stack variables persstent in the face of random erosion.
        // Which is done via PERSISTENCE.

        // Here we are addressing a fundamental size limitation of any one thing to store all of an identitities variables
        // Especially when those variables have lots of unique identifiers.

if ($this->variables_thing->json->write_fail_count > 0) {
$this->thing->log("overflow " . $this->variables_thing->json->size_overflow . " write_fail_count " . $this->variables_thing->json->write_fail_count . ".");
$this->thing->log("set " . $this->variables_thing->uuid . " " .  $this->variable_set_name ." " . $variable . " " . $value);
}

//} catch (Exception $e) {
//    echo 'Caught exception: ',  $e->getMessage(), "\n";
//} catch (Error $e) {
//   echo 'Now you can catch me!';
//}

//        restore_error_handler();
//restore_exception_handler();


        // And save variable_set onto local Thing.
//try {
        $this->thing->db->setFrom($this->identity);
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array($this->variable_set_name, $variable), $value );
if ($this->variables_thing->json->write_fail_count > 0) {

$this->thing->log("overflow " . $this->thing->json->size_overflow . " write_fail_count " . $this->thing->json->write_fail_count . ".");
$this->thing->log("set " . $this->thing->uuid . " " .  $this->variable_set_name ." " . $variable . " " . $value);
}
//} catch (Exception $e) {
//    echo 'Caught exception: ',  $e->getMessage(), "\n";
//}
//} catch (Error $e) {
//   echo 'Now you can catch me!';
//}



        // bughunt 23 June 2018 if ($value == "usermanager") {exit();}

//        restore_error_handler();
//restore_exception_handler();


        return $this->variables_thing->$variable;
    }

    function incrementVariable($variable = null)
    {
        $this->addVariable('variable', 1);
        return;
	}

	public function Respond()
    {
		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 

        // $this->thing->log( $this->agent_prefix . ' ' .$this->variables_thing->variable . '.' );

		$this->sms_message = "VARIABLES SET IS ";
        $this->sms_message .= strtoupper($this->name);

        if ($this->verbosity >= 2) {
        $this->sms_message .= " | screened on " . $this->variable_set_name ;
        $this->sms_message .= " | nuuid " . substr($this->variables_thing->uuid,0,4) ;
        }

        $this->sms_message .= " | ";

        foreach ($this->agent_variables as $key=>$variable_name) {
            if (isset($variable_name)) {
                $this->sms_message .= " " . strtolower($variable_name) . " ";
                if (isset($this->variables_thing->$variable_name)) {
                    $this->sms_message .= $this->variables_thing->$variable_name;
                } else {
                    $this->sms_message .= "X";
                }
            }
            //if (isset($this->variables_thing->$agent_variable)) {
            //    $this->sms_message .= " | " . strtolower($agent_variable) . " " . $this->variables_thing->$agent_variable;
            //}
        }

        // = " . number_format($this->variables_thing->variable);
        //$this->sms_message .= " | name " . $this->variables_thing->name;

        //$this->sms_message .= " | nuuid " . substr($this->variables_thing->next_uuid, 0 ,4);

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }

        if ($this->verbosity >= 5) {
		    $this->sms_message .= ' | TEXT ?';

        }
		$this->thing_report['thing'] = $this->thing->thing;
		$this->thing_report['sms'] = $this->sms_message;


		// While we work on this
		$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);



		return $this->thing_report;
	}


    public function defaultCommand()
    {
        $this->thing->log( $this->agent_prefix  . 'default command set.' , "DEBUG" );

        $this->agent = "variables";
        $this->variable_set_name = "identity";

        $this->name = "identity";
        $this->identity = $this->from;
        return;
    }


    public function readInstruction()
    {
        if($this->agent_command == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
//        $this->limit = $pieces[1];
        $this->variable_set_name = $pieces[1];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];

        if (!isset($pieces[3])) {
            $this->index = 0;
        } else {
            $this->index = $pieces[4];
        }

        return;
    }

    public function extractNumber($input)
    {
        $matches = 0;
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
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
        foreach ($pieces as $key=>$piece) {
            foreach ($this->agent_variables as $variable_name) {
                if ($piece == $variable_name) {
                    return false;
                }
            }
        }
        return true; // Not found
    }

    public function extractVariable($input)
    {
        $matches = 0;
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key=>$piece) {

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
        //$this->thing->log( $this->agent_prefix . 'started reading the received text.' );
        $this->num_hits = 0;
        // No need to read text.  Any identity input to Tally
        // increments the tally.

        $keywords = $this->agent_keywords;

/*
        if ($this->agent_command == null) {
            return false;
        } else {


        }
*/
        //$this->input = $input;

        $haystack = strtolower($this->nom_input);
        $pieces = explode(" ", strtolower($this->nom_input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($this->nom_input == $this->agent) {
                //$this->readVariables();
                return;
            }
        }

        //$this->thing->log( $this->agent_prefix . 'extract variable and number.' );
        $this->extractVariable($this->subject);
        $this->extractNumber($this->subject);


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
/*
Turn this off Mar 17 2018.  Causes a conflict
with the start agent. And doesn't seem to be necessary
                        case 'start':
                            $needle = "start";
                            $this->thing->log( $this->agent_prefix . 'processing new variables.', "INFORMATION" );
                            $right_of_needle = ltrim(substr($this->nom_input, strpos($this->nom_input, $needle)+strlen($needle)));
//var_dump($right_of_needle);
                            $pairs = array();


                            preg_match_all('/[A-Za-z0-9\.]+(?: [A-Za-z0-9\.]+)?/',
                                $right_of_needle,$pairs);
                            $pairs = $pairs[0];
                            foreach ($pairs as $key=>$pair) {

                                $words = explode(" ", strtolower($pair));

                                $name = $words[0];
                                $value = $words[1];

                                $this->newVariable($name,$value);
                            }
                            return;
*/

                        case 'plus':
                        case 'add':
                        case '+':

                            if ( (isset($this->number)) and (isset($this->variable)) ) {
                                $this->thing->log( $this->agent_prefix . 'adding number to variable.',"INFORMATION" );
                                $this->addVariable($this->variable, $this->number);
                                return;
                            }
                            //return;
                            //break;

                        case 'minus':
                        case 'subtract':
                        case 'less':
                        case '-':

                            if ( (isset($this->number)) and (isset($this->variable)) ) {
                                $this->thing->log( $this->agent_prefix . 'adding number to variable.', "INFORMATION" );
                                $this->addVariable($this->variable, $this->number);
                                return;
                            }
                            //return;
                            //break;


                        case 'increment':
                            if ( (isset($this->variable)) ) {
                                $this->thing->log( $this->agent_prefix . 'incrementing variable.', "INFORMATION" );
                                $this->incrementVariable($this->variable);
                                return;
                            }
                            //return;
                            //break;

                        case 'equals':
                        case 'is':
                        case '=':
                            if ( (isset($this->number)) and (isset($this->variable)) ) {

                                $this->thing->log( $this->agent_prefix . 'setting ' . $this->variable . ' to ' . $this->number . '.', "INFORMATION" );

                                $this->setVariable($this->variable, $this->number);
                                return;
                            }
                            //return;
                            //break;

                        case 'add':
                        case "&":

                            if  (isset($this->variable) ) {

                                $this->thing->log( $this->agent_prefix . 'adding variable "' . $this->variable . '".', "INFORMATION" );
                                $right_of_is = ltrim(strrchr($this->nom_input," is "));

                                $this->setVariable($this->variable, $right_of_is);
                                return;
                            }

                        case 'memory':

//$t = $this->thing->db->length();
//var_dump($t);

                            return;



                        default:
                    }
                }
            }
        }

        $this->thing->log( $this->agent_prefix . ' did no operation.', "DEBUG" );


        return;
	}

    public function newVariable($name = null, $value = null)
    {
        if ($this->isVariable($name) == true) {
           $this->agent_variables[] = $name;
        }
        $this->setVariable($name, $value);
    }

}
