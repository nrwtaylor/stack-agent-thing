<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

class Associations
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

        $this->agent_name = "associations";
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';
        $this->agent_associations = array('uuid'); //Default variable set.
        $this->agent_associations = array();
        $this->max_association_sets = 5;

        $this->agent_command = $agent_command;

        $this->verbosity = 1;
        $this->log_verbosity = 1;

        $this->current_time = $this->thing->time();

        $this->agent_keywords = array('associate', 'association', 'associations', 'add', 'equal', 'equals', '=', 'is', "&", "+", "-", "less", "plus", "subtract", "start", "init");

        $this->limit = 1e99;

        // Setup reporting
        $this->thing_report['thing'] = $this->thing->thing;

        if ($agent_command == null) {
            $this->thing->log( $this->agent_prefix . 'did not find an agent command. No action taken.', "WARNING" );
        }

        $this->association_set_name = "identity";

        $this->agent_command = $agent_command;

        $this->nom_input = $agent_command . " " . $this->from . " " . $this->subject;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->node_list = array("start");

		$this->thing->log( $this->agent_prefix . 'running on Thing ' .  $this->thing->nuuid .  '.', 'INFORMATION' );

        $this->signal = new Variables($this->thing, "variables signal " . $this->from);


        $this->readInstruction();

        // Not sure this is limiting.
        $this->getAssociations();

//if ($agent_command == "variables place console") {exit();}

        //$this->nuuid = substr($this->associations_thing->uuid,0,4);

		$this->readText();

        $this->setAssociations();
        if ($this->agent_command == null) {
    		$this->Respond();
        }

        // Commented out 4 Jul 2018
        // Toss in a refreshed.
        //$time_string = $this->thing->time();
        //$this->setVariable("refreshed_at", $time_string);

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', 'OPTIMIZE' );

        $this->thing_report['log'] = $this->thing->log;
		return;
	}

    function setAssociations()
    {
        //echo "variables.php Variable set name " . ($this->variable_set_name) . "\n";


        $this->thing->db->setFrom($this->identity);

        $refreshed_at = false;

        foreach ($this->agent_associations as $key=>$association_name) {

            // Write the agent name (not "variables")
//            $this->variables_thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->name );
//            $this->thing->json->writeVariable( array($this->variable_set_name, $variable_name), $this->name );



//if ($this->agent_command == "variables place console") {exit();}


            // Intentionally write to the variable thing.  And the current thing.
            if (isset($association_name)) {
//                $this->associations_thing->json->fallingwater( $this->associations_thing->$association_name );
                $this->thing->json->fallingwater( $association_name );
                $refreshed_at = true;
            }


        }

        //$this->setAssociation($association_name);



        if ($refreshed_at == true) {

            $this->signal->setVariable("refreshed_at", $this->current_time);

            // Figure out how and if to keep track of the last update to the associations list

        // Toss in a refreshed.
//            $time_string = $this->thing->time();
//            $this->thing->setVariables(array("associations","refreshed_at"), $time_string);
        }

    }




    function getAgent()
    {
        return;
    }

    function getAssociations($association_set_name = null)
    {
        // This will pull up all the Things for a given user
        // which have the uuid in the associations field.

        $split_time = $this->thing->elapsed_runtime();

        if ($association_set_name == null) {
            return true;
            //$association_set_name= $this->association_set_name;
        }

        $this->thing->log( $this->agent_prefix . 'got assocation "' .  $association_set_name . '".', 'INFORMATION' );

        // We will probably want a getThings at some point.

        $this->thing->db->setFrom($this->identity);

        // Returns variable sets managed by Associations.
        // Creates just one record per variable set.
        $thing_report = $this->thing->db->associationSearch($association_set_name, $this->max_association_sets); 

        $things = $thing_report['things'];
        // When we have that list of Things, we check it for the tally we are looking for.
        // Review using $this->limit as search length limiter.  Might even just
        // limit search to N microseconds of search.

        $match_count =0;
        $this->associations_list = array();
        $this->thing_objects = array();
        if ( $things == false  ) {
            return true;
            // No tally found.
            //$this->startAssociations();

        } else {

            $this->thing->log( $this->agent_prefix . 'got ' . count($things) . ' recent association sets.', "DEBUG" );

            foreach ($things as $thing_object) {
                // Check each of the Things.
                //$this->associations_thing = new Thing($thing['uuid']);
                $this->thing_objects[] = $thing_object;

                $uuid = $thing_object['uuid'];

                $this->associations_list[] = array('uuid' => $thing_object['uuid'], 'agent' => $thing_object['nom_to']);
                //var_dump($uuid);
                $this->setAssociation($uuid);



            }

        }





        return;
	}


    function addAssociation($association)
    {

        $this->thing->log("associated " . $association . " with the Thing.");

        $this->setAssociation($association);

        // Handled as a Thing function
        $this->thing->associate($association);

        return false;
    }


    function getAssociation($association = null)
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

        if ($association == null) {$association = 'association';}

        $this->associations_thing->db->setFrom($this->identity);
        $this->associations_thing->json->setField("associations");
        $this->associations_thing->$association = $this->associations_thing->json->readVariable( array($this->association_set_name, $association) );

        // And then load it into the thing
//        $this->$variable = $this->variables_thing->$variable;
//        $this->variables_thing->flagGreen();

        return $this->associations_thing->$association;
    }

    function setAssociation($association_name)
    {

        if (!isset($this->agent_associations)) {$this->agent_associations = array();}

        $this->agent_associations[] = $association_name;

        return $this->agent_associations;

    }

	public function Respond()
    {
		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 

        // $this->thing->log( $this->agent_prefix . ' ' .$this->variables_thing->variable . '.' );

		$this->sms_message = "ASSOCIATIONS SET FOR ";
        $this->sms_message .= strtoupper($this->association_name);

var_dump($this->associations_list);
        if ($this->verbosity >= 2) {
        $this->sms_message .= " | screened on " . $this->association_set_name ;
        $this->sms_message .= " | nuuid " . substr($this->associations_thing->uuid,0,4) ;
        }

        $this->sms_message .= " | ";

        foreach ($this->agent_associations as $key=>$association_name) {
            if (isset($variable_name)) {
                $this->sms_message .= " " . strtolower($association_name) . " ";
                if (isset($this->associations_thing->$association_name)) {
                    $this->sms_message .= $this->associations_thing->$association_name;
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

        $this->agent = "associations";
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
        $this->association_set_name = $pieces[1];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];

        if (!isset($pieces[3])) {
            $this->index = 0;
        } else {
            if (isset($pieces[4])) {
                $this->index = $pieces[4];
            }
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

    public function extractHexadecimals($input)
    {
        $this->hexadecimals = array();
        $matches = 0;
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            if (ctype_xdigit($piece)) {
                $hexadecimal = $piece;
                $this->hexadecimals[] = $piece;
                $matches += 1;
            }
        }

        if ($matches >= 1) {
            $this->num_hits += count($this->hexadecimals);
            return $this->hexadecimals;
        }
//        if ($matches == 1) {
//            $this->hexadecimal = $hexadecimal;
//            $this->num_hits += 1;
//            return $this->hexadecimal;
//        }
        return true;
    }

    public function extractHexadecimal($input)
    {
        $matches = 0;
        $this->extractHexadecimals($input);

        foreach ($this->hexadecimals as $key=>$hexadecimal) {
            if (strlen($hexadecimal) >= 4) {
                $big_hexadecimal = $hexadecimal;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            $this->hexadecimal = $big_hexadecimal;
            $this->num_hits += 1;
            return $this->hexadecimal;
        }
        return true;
    }


    public function isAssociation($input)
    {
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key=>$piece) {
            foreach ($this->agent_associations as $association_name) {
                if ($piece == $association_name) {
                    return false;
                }
            }
        }
        return true; // Not found
    }

    public function extractAssociation($input)
    {
        $matches = 0;
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key=>$piece) {

            if ($this->isAssociation($piece) == false) {
                $association = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            $this->association = $association;
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
//        $this->extractAssociation($this->subject);
        $uuid_agent = new Uuid($this->thing, "uuid");
        $association_name = $uuid_agent->extractUuid($this->subject);

        // So couldn't find a UUID
        if ($association_name == false) {
            $association_name = $this->extractHexadecimal($this->subject);
            if (is_bool($association_name)) {
                // True = no  hexadecimal found.
                $association_name = "X";
            }
        }

        $this->association_name = $association_name;


//$this->from = "console";
//$association_name = "08935ef2-db6f-4a56-8ca1-d5f0fab415c5";

//$association_name = $this->hexadecimal;

$this->getAssociations($association_name);


$this->extractAssociation($this->subject);

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'is':
                        case '=':
                        case 'plus':
                        case 'add':
                        case '+':

                            if ( (isset($this->number)) and (isset($this->association)) ) {
                                $this->thing->log( $this->agent_prefix . 'adding number to association.',"INFORMATION" );
                                $this->addAssociation($this->association);
                                return;
                            }
                            //return;
                            //break;

                        case 'remove':
                        case 'delete':
                        case '-':
                            // Not implemented.  But recognized.
                                //$this->setAssociation($this->association, $this->number);
                                return;
                            //}
                            //return;
                            //break;
/*
                        case 'is':

                            if  (isset($this->variable) ) {

                                $this->thing->log( $this->agent_prefix . 'setting ' . $this->variable . ' to ' . $this->number . '.', "INFORMATION" );
                                //$right_of_is = ltrim(strrchr($this->nom_input," is "));

                                $needle = "is";
                                $this->thing->log( $this->agent_prefix . 'processing new variables.' );
                                $right_of_needle = ltrim(substr($this->nom_input,strpos($this->nom_input, $needle)+strlen($needle)));
//echo $this->nom_input;
//echo "<br>";
//echo $this->variable;
//var_dump($right_of_needle);
//exit();

                                $this->setVariable($this->variable, $right_of_needle);
                                return;
                            }
*/



                        default:
                    }
                }
            }
        }

        $this->thing->log( $this->agent_prefix . ' did no operation.', "DEBUG" );


        return;
	}

    public function newAssociation($name = null, $value = null)
    {
        if ($this->isAssociation($name) == true) {
           $this->agent_associations[] = $name;
        }
        $this->setAssociation($name, $value);
    }
}
?>
