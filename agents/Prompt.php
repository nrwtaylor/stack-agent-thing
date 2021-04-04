<?php

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Prompt extends Agent
{
    function init()
    {

        // So I could call
        $this->test = false;
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = [
            'start' => [
                'no time or date',
                'time only',
                'date only',
                'time and date',
            ],
        ];

        $this->node_list = [
            "start" => [
                "scheduling" => ["waiting" => ["reminding" => ["learning"]]],
            ],
        ];
        $this->requested_state = "on";
    }

    public function get()
    {
        // devstack
        //$this->thing->Get();

        $this->current_state = $this->thing->getState('prompt');
        //$this->getSubject();
    }

    public function run()
    {
        $this->doPrompt();
    }

    public function doPrompt()
    {
        //    public function respondResponse()
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        $thing_report = true;

        // Generate email response.

        // The getSubject has come up with the best assessment
        // of what the current_state is and what the request_state is.
        if ($this->test) {
        }
        if ($this->state_change) {
            switch ($this->requested_state) {
                case 'opt-in':
                    break;

                case 'opt-out':
                    break;

                case null:
                    // Tested case
                    // Web view of
                    // thing/<34 char>/usermanager

                    $this->thing->choice->Create(
                        'start',
                        $this->node_list,
                        "start"
                    );

                    // And then use the same tool to make the opt-out and credit 'temporary choices'
                    // as buttons.  Allows for other keywords to be injected.
                    $choices = $this->thing->choice->makeLinks('start');

                    // 1999-11-30 - No date?
                    // 2000 - response to ' June 23rd'?
                    // Relative date found - Weekday?  Tomorrow? 1 day.
                    // [am pm signal is lost - worth running an extractor.  Make an extractor agent]

                    //                              $test_message = $choices['url'];

                    $subject = $this->subject . ' - processed';

                    $message =
                        "We received a request for a prompt from " .
                        $this->short_name .
                        "<br>";

                    $date_array = $this->extractDate();
                    $this->date = $date_array;

                    $date_string = date(
                        'Y-m-d H:i:s',
                        mktime(
                            $date_array['hour'],
                            $date_array['minute'],
                            $date_array['second'],
                            $date_array['month'],
                            $date_array['day'],
                            $date_array['year']
                        )
                    );

                    $message .= $date_string . "<br>";

                    //$message .= $this->time_scale . "<br>";

                    if (isset($date_array['relative'])) {
                        // date("Y-m-d H:i:s", $date_arra)

                        $relative_date =
                            $date_array['relative']['year'] .
                            '-' .
                            $date_array['relative']['month'] .
                            '-' .
                            $date_array['relative']['day'] .
                            ' ' .
                            $date_array['relative']['hour'] .
                            ':' .
                            $date_array['relative']['minute'];

                        $message .= "relative date found " . $relative_date;
                        $this->duration = $date_array['relative'];
                    }

                    $date =
                        $date_array['year'] .
                        '-' .
                        $date_array['month'] .
                        '-' .
                        $date_array['day'] .
                        ' ' .
                        $date_array['hour'] .
                        ':' .
                        $date_array['minute'];

                    $message .= "<br>";

                    $message .= "<br>";
                    $message .= $date;
                    $message .= " 
                                        <br>
                                        Keep on stacking.

                                        ";

                    $thing_report = [
                        'thing' => $this->thing->thing,
                        'choices' => $choices,
                        'info' =>
                            'This is the opt-in agent responding to a valid opt-in request.',
                        'help' =>
                            'Reads dates in the subject and tries to best schedule stuff',
                    ];

                    // Associated email?

                    $thing = new Thing(null);
                    $thing->Create($this->from, $this->agent_name, $subject);

                    $message_thing = new Message($thing, $thing_report);
                    $thing_report['info'] =
                        $message_thing->thing_report['info'];

                    $this->defaultPrompt();
                    break;

                default:
                    $this->thing->log('default chose newuser');
                    $this->newuserPrompt();
                    break;
            }
        }

        // NOTE THAT IT IS REALLY EASY TO CREATE A NEW AGENT TO 'PASS' THE
        // THING TO.
        //			$temp_thing = new Optout($this->thing);
        //
        //			// Whereas in the new scheme it would look like this.
        //			$this->thing->choice->Choose("opt-out");
        //			return;

        // aka ... Fast | Slow?

        if (isset($agent)) {
            $thing_report = $agent->thing_report;
        } else {
            $thing_report = ['thing' => false];
        }
        return $thing_report;
    }

    public function makeSMS()
    {
        $sms = "PROMPT | " . $this->response;
        $this->thing_report['sms'] = $sms;
    }

    public function defaultPrompt()
    {
        $this->response .= "> ";
    }

    public function newuserPrompt()
    {
        $this->response .= "Try GLOSSARY > ";
    }

    public function readSubject()
    {
        //$this->node_list = array("start"=>array("scheduling"=>array("waiting"=>
        //                                      array("reminding"=>array("learning"))));

        // What do we know at this point?
        // We know the nom_from.
        // We have the message.
        // And we know this was directed towards usermanager (or close).

        // So starting with nom_from.
        // Two conditions, we either know the nom_from, or we don't.

        //$status = false;

        $this->state_change = false;

        $input = strtolower($this->to . " " . $this->subject);

        // First see what we have on record for this alias.
        // Need to decide whether this is a stack call, or whether to create
        // a Thing here.

        // If it is a new User we will need a Thing.
        // If it is an opted-out user, we will need to log a request

        $this->current_state = $this->thing->getState($this->agent_name);

        switch ($this->current_state) {
            case 'start':
                $this->time_scale = $this->discriminateInput($input, [
                    'minutes',
                    'days',
                ]);

                $this->requested_state = $this->time_scale;

                //				$this->requested_state = $this->discriminateInput($input, array('opt-in', 'opt-out'));
                //				$this->thing->choice->Choose($this->requested_state);

                //				if ($this->requested_state != $this->current_state) {$this->state_change = true;}

                break;

            case 'scheduling':
                $this->requested_state = $this->discriminateInput($input, [
                    'opt-in',
                    'opt-out',
                ]);
                $this->thing->choice->Choose($this->requested_state);

                if ($this->requested_state != $this->current_state) {
                    $this->state_change = true;
                }

                break;

            case 'waiting':
                $this->requested_state = $this->discriminateInput($input, [
                    'opt-in',
                    'opt-out',
                ]);

                $this->thing->choice->Choose($this->requested_state);

                if ($this->requested_state != $this->current_state) {
                    $this->state_change = true;
                }

                break;

            case 'reminding':
                //$this->state_change = true;
                //$this->thing->choice->Choose("new user");
                break;

            case 'learning':
                //$this->state_change = true;
                //$this->thing->choice->Choose("new");
                break;

            default:
                $this->thing->json->setField("settings");
                $this->thing->json->writeVariable(
                    ["prompt", "received_at"],
                    $this->thing->json->time()
                );

                //$date_string = date('Y-m-d H:i:s', mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year']));

                //$this->date;
                //$this->duration;

                $this->thing->json->setField("variables");
                $this->thing->json->writeVariable(
                    ["prompt", "trigger_at"],
                    $this->thing->json->time()
                );

                $this->state_change = true;
                $this->thing->choice->Choose("start");

                $this->extractDate();
        }
    }

    function extractDate()
    {
        //	$test_text = "I was born on 26 march 1975 at 23:56:2";
        //$test_text = "in 15 minutes remind me";
        $test_text = "remind me at 2pm in 2 days";
        $test_text = $this->subject;

        $a = date_parse($test_text);

        return $a;
    }

    function discriminateInput($input, $discriminators = null)
    {
        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ['minutes', 'hours'];
        }

        $aliases = [];

        $aliases['minutes'] = ['m', 'mins', 'mns', 'minits'];
        $aliases['hours'] = ['hours', 'h', 'hr', 'hrs', 'hsr'];

        $words = explode(" ", $input);

        $count = [];

        $total_count = 0;
        // Set counts to 1.  Bayes thing...
        foreach ($discriminators as $discriminator) {
            $count[$discriminator] = 1;
            $total_count = $total_count + 1;
        }
        // ...and the total count.

        foreach ($words as $word) {
            foreach ($discriminators as $discriminator) {
                if ($word == $discriminator) {
                    $count[$discriminator] = $count[$discriminator] + 1;
                    $total_count = $total_count + 1;
                }

                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                    }
                }
            }
        }

        // Set total sum of all values to 1.

        $normalized = [];
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key => $value) {
            if (isset($max)) {
                $delta = $max - $value;
                break;
            }
            if (!isset($max)) {
                $max = $value;
                $selected_discriminator = $key;
            }
        }

        if ($delta >= $minimum_discrimination) {
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }
}
