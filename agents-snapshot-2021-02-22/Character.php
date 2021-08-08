<?php
/**
 * Character.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Character extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @return unknown
     */
    //function __construct(Thing $thing) {
    public function init()
    {
        $this->character_thing = $this->thing;

        $this->retain_for = 48; // Retain for at least 48 hours.

        $this->node_list = ['character'];

        if ($this->response == true) {
            $this->thing_report['info'] = 'No character response created.';
            $this->thing_report['help'] =
                'This is the character manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';
            $this->thing_report['num_hits'] = $this->num_hits;

            $this->thing->flagGreen();

            $this->thing->log('<pre> Agent "Group" completed</pre>');

            return $this->thing_report;
        }
    }

    public function get()
    {
        //$this->getCharacters();

        // If this return true then no existing characters found.

        $this->getCharacter(); // Should load up current
    }

    public function set()
    {
        $this->characterSet();
    }

    /**
     *
     * @return unknown
     */
    public function nullAction()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["character", "action"],
            'null'
        );

        $this->message = "CHARACTER | Request not understood. | TEXT SYNTAX";
        $this->sms_message =
            "CHARACTER | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    /**
     *
     */
    function characterSet()
    {
        $this->thing->log(
            '<pre> Agent "Character" called characterSet()</pre>'
        );

        // Store counts
        //  echo $this->from;
        //exit();

        //if (!isset ( $this->character_thing ) ) {
        // $this->character_thing = $this->thing;
        //}

        $this->character_thing->db->setFrom($this->from);

        $this->character_thing->json->setField("variables");

        $this->character_thing->json->writeVariable(
            ["character", "name"],
            $this->name
        );

        $this->character_thing->json->writeVariable(
            ["character", "strength"],
            $this->strength
        );
        $this->character_thing->json->writeVariable(
            ["character", "dexterity"],
            $this->dexterity
        );
        $this->character_thing->json->writeVariable(
            ["character", "constitution"],
            $this->constitution
        );
        $this->character_thing->json->writeVariable(
            ["character", "intelligence"],
            $this->intelligence
        );
        $this->character_thing->json->writeVariable(
            ["character", "wisdom"],
            $this->wisdom
        );
        $this->character_thing->json->writeVariable(
            ["character", "charisma"],
            $this->charisma
        );

        //$this->age_thing->json->writeVariable( array("character", "earliest_seen"), $this->earliest_seen   );

        $this->character_thing->flagGreen();
    }

    /**
     *
     * @return unknown
     */
    function getCharacters()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "character",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            // Then this Thing has no character information
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["character", "refreshed_at"],
                $time_string
            );
        }

        $things = false;
        if (isset($this->thing->db)) {
        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch('character', 99);
        $things = $thing_report['things'];
        }
        $this->sms_message = "";
        $reset = false;

        if (!isset($this->characters)) {
            $this->characters = [];
        }
        if (!isset($this->seen_characters)) {
            $this->seen_characters = [];
        }

        if ($things == false) {
            // No character information store found.
            $this->resetCharacter();
        } else {
            $this->characters = [];
            $this->seen_characters = [];
            foreach ($things as $thing) {
//var_dump($thing);
  //              $variables = $thing->variables;


                $uuid = $thing['uuid'];

                $variables_json = $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);



                //$thing = new Thing($thing['uuid']);

                //$this->character_uuid = $thing->uuid;
                $this->character_uuid = $uuid;


            $this->name = "X";
            if (isset($variables['character']['name'])) {
                $this->name = $variables["character"]['name'];
            }



                if (in_array($this->name, $this->seen_characters)) {
                    continue;
                } else {
                    $this->seen_characters[] = $this->name;
                }

          //      $this->strength = $thing->json->readVariable([
            //        "character",
              //      "strength",
                //]);


            $this->strength = "X";
            if (isset($variables['character']['strength'])) {
                $this->strength = $variables["character"]['strength'];
            }

            $this->dexterity = "X";
            if (isset($variables['character']['dexterity'])) {
                $this->dexterity = $variables["character"]['dexterity'];
            }

            $this->constitution = "X";
            if (isset($variables['character']['constitution'])) {
                $this->constitution = $variables["character"]['constitution'];
            }

            $this->intelligence = "X";
            if (isset($variables['character']['intelligence'])) {
                $this->intelligence = $variables["character"]['intelligence'];
            }

            $this->wisdom = "X";
            if (isset($variables['character']['wisdom'])) {
                $this->wisdom = $variables["character"]['wisdom'];
            }

            $this->charisma = "X";
            if (isset($variables['character']['charisma'])) {
                $this->charisma = $variables["character"]['charisma'];
            }


                $character = [
                    "name" => $this->name,
                    "strength" => $this->strength,
                    "dexterity" => $this->dexterity,
                    "constitution" => $this->constitution,
                    "intelligence" => $this->intelligence,
                    "wisdom" => $this->wisdom,
                    "charisma" => $this->charisma,
                ];

                if (
                    $this->name == false or
                    $this->strength == false or
                    $this->dexterity == false or
                    $this->constitution == false or
                    $this->intelligence == false or
                    $this->wisdom == false or
                    $this->charisma == false
                ) {
                    $this->thing->log(
                        'Agent "Character" found no existing character information'
                    );
                    //     $this->thing->log ( "No character info found.  Created a random character.");
                    //                                        $this->randomCharacter();
                } else {
                    $this->age = $thing->thing->created_at;

                    $character = [
                        "name" => $this->name,
                        "uuid" => $this->character_uuid,
                        "strength" => $this->strength,
                        "dexterity" => $this->dexterity,
                        "constitution" => $this->constitution,
                        "intelligence" => $this->intelligence,
                        "wisdom" => $this->wisdom,
                        "charisma" => $this->charisma,
                        "age" => $this->age,
                    ];

                    // Successfully loaded most recent character Thing
                    // and stored the other characters in an array
                    $this->characters[] = $character;
                    //$this->character_thing = $thing;
                    //$this->age = $thing->thing->created_at;
                    //return;
                }
            }
            if (!isset($this->characters)) {
                $this->characters = [];
            }
            //exit();
            //   $this->characters = array_unique($this->characters);

            if (count($this->characters) == 0) {
                return true;
            }
        }

        return $this->characters;
    }

    /**
     *
     */
    function characterReport()
    {
        $this->sms_message = "CHARACTER";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

        $this->sms_message .= $this->name . ' | ';

        $this->sms_message .= "STR " . $this->strength . ' ';
        $this->sms_message .= "DEX " . $this->dexterity . ' ';
        $this->sms_message .= "CON " . $this->constitution . ' ';
        $this->sms_message .= "INT " . $this->intelligence . ' ';
        $this->sms_message .= "WIS " . $this->wisdom . ' ';
        $this->sms_message .= "CHA " . $this->charisma . ' | ';

        $this->sms_message .= "TEXT HELP";
    }

    /**
     *
     */
    function characterList()
    {
        $this->sms_message = "CHARACTER > LIST";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

        //                $this->sms_message .= $this->name . ' | ';

        if (count($this->characters) == 0) {
            $this->sms_message .=
                "No characters found. | TEXT NEW RANDOM CHARACTER";
        } else {
            //$this->sms_message .= $this->name . ' | ';
            $count = 0;
            foreach ($this->characters as $character) {
                $count += 1;
                $this->sms_message .= "" . $character['name'] . '';
                $this->sms_message .= ' | ';

                if (
                    strlen(
                        $this->sms_message .
                            (count($this->characters) - $count) .
                            " more found. | TEXT HELP"
                    ) > 159
                ) {
                    $this->sms_message =
                        $this->sms_message .
                        (count($this->characters) - $count) .
                        " more found. | ";
                    break;
                }
            }

            $this->sms_message .= "TEXT HELP";
        }
        return;
    }

    /**
     *
     */
    function characterSyntax()
    {
        $this->sms_message = "CHARACTER > SYNTAX";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

        $this->sms_message .= '<qualifier> CHARACTER <command> | ';

        $this->sms_message .= "TEXT [ CHARACTER REPORT | HELP ]";
    }

    /**
     *
     */
    function characterInfo()
    {
        $this->sms_message = "CHARACTER > INFORMATION";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

        $this->sms_message .= "The current character is " . $this->name . ' | ';
        $this->sms_message .= "The character age is " . $this->age . ' | ';

        $this->sms_message .= "TEXT [ CHARACTER REPORT | HELP ]";
    }

    /**
     *
     */
    function characterHelp()
    {
        $this->sms_message = "CHARACTER | HELP";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

        $this->sms_message .=
            "Characters are Things which you can talk to.  There are player characters, and non-player characters.";

        $this->sms_message .= " | ";

        $this->sms_message .= "TEXT [ NEW RANDOM CHARACTER | WHATIS ]";
    }

    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        //$this->thing_report = array('thing' => $this->thing->thing);

        // Thing actions
        $this->thing->flagGreen();

        //  $this->readSubject();

        //  $this->characterReport();

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';

        // Generate email response.

        $to = $this->thing->from;

        // Testing
        // $to = 'redpanda.stack@gmail.com';

        $from = "character";

        // $message = $this->readSubject();

        //$message = "Thank you for your request.<p><ul>" . ucwords(strtolower($response)) . '</ul>' . $this->error . " <br>";

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks('start');
        $this->thing_report['choices'] = $choices;

        // Need to refactor email to create a preview of the sent email in the $thing_report['email']
        // For now this attempts to send both an email and text.

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = 'Character development.';

        return $this->thing_report;
    }

    /**
     *
     */
    function resetCharacter()
    {
        $this->thing->log(
            '<pre> Agent "Character" called resetCharacter()</pre>'
        );

        $this->sms_message = "Empty character created. | ";
        $this->count = 0;

        $this->name = 'nonname';

        $this->strength = 0;
        $this->dexterity = 0;
        $this->constitution = 0;
        $this->intelligence = 0;
        $this->wisdom = 0;
        $this->charisma = 0;

        $this->character_thing = new Thing(null);
        $this->character_thing->Create(
            $this->from,
            'character',
            's/ reset character'
        );

        $this->characterSet();

        $this->character_thing->flagGreen();

        return;
    }

    /**
     *
     * @param unknown $name (optional)
     */
    function nameCharacter($name = null)
    {
        $this->thing->log(
            '<pre> Agent "Character" name applies to ' . $this->name . '</pre>'
        );

        $this->sms_message =
            'CHARACTER | "' .
            $this->name .
            '" renamed "' .
            $name .
            '". | TEXT ' .
            strtoupper($name);
        $this->count = 0;

        if ($name == null) {
            $this->name = "Norman the Barbarian";
        } else {
            $this->name = $name;
        }

        //$this->character_thing = $this->thing;

        $this->characterSet();
        //$this->character_thing = $this->thing;

        //$this->character_thing = new Thing(null);
        //$this->character_thing->Create($this->from , 'character', 's/ reset character');
        //$this->character_thing->flagGreen();

        return;
    }

    /**
     *
     */
    function randomCharacter()
    {
        $this->thing->log(
            '<pre> Agent "Character" called randomCharacter()</pre>'
        );

        $this->sms_message = "Random character. | ";
        $this->count = 0;

        $nominals = [
            'Abrielle',
            'Adair',
            'Adara',
            'Adriel',
            'Aiyana',
            'Alissa',
            'Alixandra',
            'Altair',
            'Amara',
            'Anatola',
            'Anya',
            'Arcadia',
            'Ariadne',
            'Arianwen',
            'Aurelia',
            'Aurelian',
            'Aurelius',
            'Avalon',
            'Acalia',
            'Alaire',
            'Auristela',
            'Bastian',
            'Breena',
            'Brielle',
            'Briallan',
            'Briseis',
            'Cambria',
            'Cara',
            'Carys',
            'Caspian',
            'Cassia',
            'Cassiel',
            'Cassiopeia',
            'Cassius',
            'Chaniel',
            'Cora',
            'Corbin',
            'Cyprian',
            'Daire',
            'Darius',
            'Destin',
            'Drake',
            'Drystan',
            'Dagen',
            'Devlin',
            'Devlyn',
            'Eira',
            'Eirian',
            'Elysia',
            'Eoin',
            'Evadne',
            'Eliron',
            'Evanth',
            'Fineas',
            'Finian',
            'Fyodor',
            'Gareth',
            'Gavriel',
            'Griffin',
            'Guinevere',
            'Gaerwn',
            'Ginerva',
            'Hadriel',
            'Hannelore',
            'Hermione',
            'Hesperos',
            'Iagan',
            'Ianthe',
            'Ignacia',
            'Ignatius',
            'Iseult',
            'Isolde',
            'Jessalyn',
            'Kara',
            'Kerensa',
            'Korbin',
            'Kyler',
            'Kyra',
            'Katriel',
            'Kyrielle',
            'Leala',
            'Leila',
            'Lilith',
            'Liora',
            'Lucien',
            'Lyra',
            'Leira',
            'Liriene',
            'Liron',
            'Maia',
            'Marius',
            'Mathieu',
            'Mireille',
            'Mireya',
            'Maylea',
            'Meira',
            'Mordok',
            'Natania',
            'Nerys',
            'Nuriel',
            'Nyssa',
            'Neirin',
            'Nyfain',
            'Oisin',
            'Oralie',
            'Orion',
            'Orpheus',
            'Ozara',
            'Oleisa',
            'Orinthea',
            'Peregrine',
            'Persephone',
            'Perseus',
            'Petronela',
            'Phelan',
            'Pryderi',
            'Pyralia',
            'Pyralis',
            'Qadira',
            'Quintessa',
            'Quinevere',
            'Raisa',
            'Remus',
            'Rhyan',
            'Rhydderch',
            'Riona',
            'Renfrew',
            'Saoirse',
            'Sarai',
            'Sebastian',
            'Seraphim',
            'Seraphina',
            'Sirius',
            'Sorcha',
            'Saira',
            'Sarielle',
            'Serian',
            'Séverin',
            'Tavish',
            'Tearlach',
            'Terra',
            'Thalia',
            'Thaniel',
            'Theia',
            'Torian',
            'Torin',
            'Tressa',
            'Tristana',
            'Uriela',
            'Urien',
            'Ulyssia',
            'Vanora',
            'Vespera',
            'Vasilis',
            'Xanthus',
            'Xara',
            'Xylia',
            'Yadira',
            'Yseult',
            'Yakira',
            'Yeira',
            'Yeriel',
            'Yestin',
            'Zaira',
            'Zephyr',
            'Zora',
            'Zorion',
            'Zaniel',
            'ZarekAbrielle',
        ];

        $adjectives = [
            'brave',
            'magnificient',
            'luminous',
            'stupendous',
            'quixotic',
            'horrible',
            'great',
            'blonde',
            'artificial',
            'kinetic',
            'normal',
            'awful',
            'terrible',
            'small',
            'tiny',
            'fantastic',
            'fascinating',
            'hilarious',
            'deafening',
        ];

        $adjective = $adjectives[array_rand($adjectives)];
        $nominal = $nominals[array_rand($nominals)];

        $this->name = ucwords($nominal) . " the " . ucwords($adjective);

        $this->strength = $this->rollAbility();
        $this->dexterity = $this->rollAbility();
        $this->constitution = $this->rollAbility();
        $this->intelligence = $this->rollAbility();
        $this->wisdom = $this->rollAbility();
        $this->charisma = $this->rollAbility();

        //$this->character_thing = $this->thing;

        $this->character_thing = new Thing(null);
        $this->character_thing->Create(
            $this->from,
            'character',
            's/ new random character'
        );

        $this->characterSet();

        $this->character_thing->flagGreen();

        return;
    }

    /**
     *
     * @param unknown $search_name (optional)
     * @return unknown
     */
    function getCharacter($search_name = null)
    {
        $this->thing->log(
            '<pre> Agent "Character" called getCharacter()</pre>'
        );

        if (!isset($this->characters)) {$this->getCharacters();}


        if ($search_name == null) {
            if (count($this->characters) == 0) {
                return true;
            }

            $character = $this->characters[0];

            //       $min_distance = $distance;

            $this->name = $character["name"];
            $this->character_uuid = $character["uuid"];
            $this->strength = $character["strength"];
            $this->dexterity = $character["dexterity"];
            $this->constitution = $character["constitution"];
            $this->intelligence = $character["intelligence"];
            $this->wisdom = $character["wisdom"];
            $this->charisma = $character["charisma"];
            $this->age = $character["age"];
            return;
        }

        $min_distance = 255;
        $found_flag = false;
        foreach ($this->characters as $character) {
            $distance = levenshtein($character['name'], $search_name);

            if ($distance < $min_distance) {
                $found_flag = true;
                $min_distance = $distance;
                $this->name = $character["name"];
                $this->character_uuid = $character["uuid"];
                $this->strength = $character["strength"];
                $this->dexterity = $character["dexterity"];
                $this->constitution = $character["constitution"];
                $this->intelligence = $character["intelligence"];
                $this->wisdom = $character["wisdom"];
                $this->charisma = $character["charisma"];
                $this->age = $character["age"];
            }

            $this->thing->log(
                '<pre> Agent "Character" processing ' .
                    $character['name'] .
                    ' ' .
                    $distance .
                    '</pre>'
            );
        }

        // This is an internal function.  Doesn't set sms/message.  That
        // is the job of the character<command> functions.

        if (!$found_flag) {
            //                      $this->sms_message .= "";
            //                      $this->sms_message .= " | No character found.";
            //                $this->thingreport['characters'] = false;

            $this->character_thing = $this->thing;

            $this->thing->log(
                '<pre> Agent "Character" got no character result. </pre>'
            );

            //      $group = "meep";
        } else {
            //                        $this->sms_message .= " | This is " . $this->name . " Commands: TBD";
            $this->character_thing = new Thing($this->character_uuid);
            $this->thing->log(
                '<pre> Agent "Character" got character' .
                    $this->name .
                    ' is Levenshtein closest.</pre>'
            );

            //                  $this->thingreport['character'] = $characters;
        }

        //$this->sms_message = " | ". strtoupper( $this->group_id ) . " | " .$this->sms_message;

        $this->thing->log(
            '<pre> Agent "Character" found ' .
                implode(" ", $characters) .
                '</pre>'
        );
    }

    /**
     *
     * @return unknown
     */
    function rollAbility()
    {
        $arr = ['a', 'b', 'c', 'd'];

        $minimum = 6;
        $sum = 0;

        foreach ($arr as $item) {
            $roll = rand(1, 6);
            if ($roll < $minimum) {
                $minimum = $roll;
            }
            $sum = $sum + $roll;
        }

        $score = $sum - $minimum;

        return $score;
    }

    /**
     *
     * @param unknown $phrase
     */
    private function nextWord($phrase)
    {
    }

    // TODO
    public function extractCharacters($text = null) {

if (!isset($this->characters)) {

        $this->getCharacters();


}

    }
    // TODO
    public function extractCharacter($text = null) {

        $this->extractCharacters($text);

    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->thing->log('started to read "' . $this->subject . '"');

        $this->num_hits = 0;

        //$input = strtolower($this->subject);
        $input = $this->input;

        if (strtolower($input) == "character") {return;}

//        $this->getCharacters();
        $this->extractCharacter($input);

        $pieces = explode(" ", strtolower($input));

        $this->characterList();

        $matches = [];
        foreach ($pieces as $key => $piece) {
            $common_words = ['the'];
            foreach ($this->characters as $character) {
                $character_array = explode(" ", strtolower($character['name']));

                foreach ($character_array as $character_piece) {
                    if ($character_piece == $piece) {
                        if (!in_array($piece, $common_words)) {
                            $matches[] = $character;
                            $this->num_hits += 1;
                        }
                    }
                }
            }
        }

        $matches = array_unique($matches);

        if (count($matches) == 1) {
            $this->thing->log('$this->name input ' . $this->name . '');
            $this->getCharacter($matches[0]['name']);
            $this->thing->log(
                'Agent "Character" $this->name output ' . $this->name . ''
            );
        }

        if (count($matches) == 0) {
            $this->thing->log('did not match any characters');
            $this->nullAction();
            $this->response = "Did not find any Characters.";
        }

        //$this->response = null;
        //http://speednetwork14.adk2x.com/ul_cb/imp?p=70459751&ap=1304&ct=html&u=http%3A%2F%2Fwww.angelfire.com%2Fme3%2FPrOzaCc%2Fdndkeywords.html&r=https%3A%2F%2Fwww.google.ca%2F&iss=0&f=1&popunderPrivateSize=800x600&ci=3

        $keywords = [
            'character',
            'race',
            'gender',
            'ability',
            'modifier',
            'class',
            'skill',
            'strength',
            'dexterity',
            'constituion',
            'intelligence',
            'wisdom',
            'charisma',
            'new',
            'random',
            'skill',
            'check',
            'stats',
            'str',
            'dex',
            'con',
            'int',
            'wis',
            'cha',
            'dm',
            'gm',
            'pc',
            'npc',
            'exp',
            'xp',
            'armour class',
            'armor class',
            'ac',
            'dual-class',
            'multi-class',
            'hit points',
            'infravision',
            'name',
            'list',
        ];

        //
        //    Strength, measuring physical power
        //    Dexterity, measuring agility
        //    Constitution, measuring endurance
        //    Intelligence, measuring reasoning and memory
        //    Wisdom, measuring perception and insight
        //    Charisma, measuring force of personality
        //
        // https://roll20.net/compendium/dnd5e/Ability%20Scores#content

        // Strength
        //    Athletics

        //Dexterity
        //    Acrobatics
        //    Sleight of Hand
        //    Stealth

        //Intelligence
        //    Arcana
        //    History
        //    Investigation
        //    Nature
        //    Religion

        //Wisdom
        //    Animal Handling
        //    Insight
        //    Medicine
        //    Perception
        //    Survival

        //Charisma
        //    Deception
        //    Intimidation
        //    Performance
        //    Persuasion

        // https://roll20.net/compendium/dnd5e/Ability%20Scores#content

        $input = strtolower($this->subject);

        $prior_uuid = null;

        // So at this point we have the latest/current character loaded.

        // Check if this is a call to the current character

        //                                if (strpos(strtolower($this->subject), strtolower($this->name)) !== false) {

        //       $this->characterReport($this->name);
        //       $this->num_hits += 1;

        //       return $this->response;
        // }

        // Or is the subject in the character name?

        //if (strpos(strtolower($this->name), strtolower($this->subject )) !== false) {
        // If nothing else use this.
        //       $this->characterReport($this->name);
        //       $this->num_hits += 1;

        //       return $this->response;
        // }

        // So now at this point we know the subject doesn't match the character name.

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            $this->thing->log(
                '<pre>Agent "Character" checking keywords.</pre>'
            );
            $input = $this->subject;
        }

        if ($this->subject == 'character') {
            // If this is a four-digit number
            $this->characterReport();
            return;
        }

        if (count($matches) == 0) {
            $this->nullAction();
            $this->thing->log(
                'did not respond to any keywords and there are no character matches.'
            );
        }

        if (count($matches) == 1) {
            $this->characterReport();
            $this->thing->log(
                '<pre> Agent "Character" did not respond to any keywords and there was one character match.</pre>'
            );
        }

        $this->thing->log(
            '<pre> Agent "Character" did not respond to any keywords</pre>'
        );

        //$this->characterList();

        //                return "Request not understood";

        //         }

        //      $this->thing->log('<pre>Agent "Character" is reading multiple words.</pre>');

        foreach ($pieces as $key => $piece) {
            //$this->thing->log($piece);
            //exit();
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    //$this->thing->log("Matched command piece" . $piece . $command);

                    switch ($piece) {
                        case 'name':
                            if ($key + 1 > count($pieces)) {
                                $this->thing->log(
                                    '<pre>Agent "Character" matched "name" as last word.</pre>'
                                );

                                //echo "last word is stop";
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->thing->log(
                                    '<pre>Agent "Character" matched "name" in "' .
                                        $this->subject .
                                        '".</pre>'
                                );

                                //echo "next word is:";
                                //var_dump($pieces[$index+1]);
                                $check = $pieces[$key + 1]; // Have this return up to four words
                                if ($check == "is" or $check == "character") {
                                    $adjust = 1;
                                } else {
                                    $adjust = 0;
                                }

                                $slice = array_slice(
                                    $pieces,
                                    $key + 1 + $adjust
                                );
                                $name = implode($slice, " ");
                                $this->response = $this->nameCharacter($name);
                                return $this->response;
                            }
                            break;

                        case 'new':
                            //$this->thing->log("new found");

                            if ($key + 1 > count($pieces)) {
                                // Last word is new
                                $this->thing->log(
                                    '<pre>Agent "Character" matched "new" as last word.</pre>'
                                );

                                $this->resetCharacter();
                                return "Character reset";
                            } else {
                                $this->thing->log(
                                    '<pre>Agent "Character" matched "new".</pre>'
                                );

                                if (strpos($input, 'random') !== false) {
                                    $this->thing->log(
                                        '<pre>Agent "Character" matched "new" with "random".</pre>'
                                    );
                                    //$this->thing->log("new followed by random");
                                    $this->randomCharacter();
                                    $this->characterReport();
                                    return "Random character created";
                                }

                                switch ($pieces[$key + 1]) {
                                    case 'random':
                                        $this->thing->log(
                                            '<pre>Agent "Character" matched "new" followed by "random".</pre>'
                                        );

                                        //$this->thing->log("new followed by random");
                                        $this->randomCharacter();
                                        return "Random character created";
                                }
                            }

                            $this->thing->log(
                                '<pre>Agent "Character" matched "new" but nothing else.</pre>'
                            );

                            $this->resetCharacter();
                            return "Character reset";
                            //echo 'bus';
                            break;

                        case 'list':
                            $this->characterList();

                            //echo $this->sms_message;
                            //exit();
                            $this->thing->log(
                                '<pre>Agent "Character" matched "list".</pre>'
                            );

                            return "Character list created";
                        case 'info':
                            $this->thing->log(
                                '<pre>Agent "Character" matched "report".</pre>'
                            );
                            $this->characterInformation($this->name);
                            return "Character report created";

                        case 'information':
                            $this->thing->log(
                                '<pre>Agent "Character" matched "report".</pre>'
                            );
                            $this->characterInformation($this->name);
                            return "Character report created";

                        case 'report':
                            $this->thing->log(
                                '<pre>Agent "Character" matched "report".</pre>'
                            );
                            $this->characterReport($this->name);
                            return "Character report created";
                            //echo 'bus';
                            break;

                        default:
                            $this->thing->log(
                                '<pre>Agent "Character" did not multiple-match.</pre>'
                            );
                        //$this->characterInfo();

                        //echo 'default';
                    }
                }
            }
        }

        $this->thing->log(
            '<pre>Agent "Character" did not match anything in the subject "' .
                $this->subject .
                '".</pre>'
        );

        // $this->thing->log("Agent Character did not match.");
        //$this->characterHelp();

        return "Message not understood";
    }
}
