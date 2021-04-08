<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Shuffle extends Agent
{
    public $var = 'hello';

    function init()
    {
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->node_list = ["start" => ["transit", "opt-in"]];
    }

    public function run()
    {
        //        $this->thing->shuffle();
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "shuffle",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["shuffle", "refreshed_at"],
                $time_string
            );
        }
        $this->last_refreshed_at = $time_string;
    }

    public function helpShuffle()
    {
        $this->thing_report['help'] =
            'Generates a new identifier for your Things.';
    }

    function allShuffle()
    {
        $this->shuffle_horizon = false;

        // Getting memory error from db looking
        // up balance for null
        if ($this->from == "null@" . $this->mail_postfix) {
            $this->response .= "Shuffle All requires an identity. ";

            return;
        }

        // devstack paged input
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        $this->total_things = count($things);

        $start_time = time();

        $count = 0;
        shuffle($things);

        $start_time = time();

        while (count($things) > 1) {
            $thing = array_pop($things);

            if ($this->thingShuffle($thing) != true) {
                $count += 1;
            }
        }

        $this->response .=
            "Completed request for this Identity. Shuffled " .
            $count .
            " Things.";
    }

    public function thingsShuffle()
    {
        // Get all users records

        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        $this->total_things = count($things);

        //$start_time = time();

        //shuffle($things);
        $things = array_reverse($things);
        return $things;
    }

    public function ageShuffle($age_unit = null)
    {
        if ($age_unit == null) {
            return true;
        }

        $things = $this->thingsShuffle();
        $count = 0;
        $start_time = time();

        while (count($things) > 1) {
            $thing = array_pop($things);

            if ($thing['uuid'] != $this->uuid) {
                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing['created_at']);

                $age_text = $this->thing->human_time($age);
                $thing_age_unit = explode(" ", $age_text)[1];

                if ($thing_age_unit != $age_unit) {
                    continue;
                }

                $this->thingShuffle($thing);
                //            $temp_thing = new Thing($thing['uuid']);
                //            $temp_thing->Forget();
                $count += 1;
            } else {
            }
        }

        if (!isset($this->shuffle_count)) {
            $this->shuffle_count = 0;
        }
        $this->shuffle_count += $count;
    }

    public function latestShuffle($number = null)
    {
        if ($number == null) {
            return true;
        }

        $things = $this->thingsShuffle();
        $count = 0;
        $start_time = time();

        while (count($things) > 1) {
            $thing = array_pop($things);

            if ($thing['uuid'] != $this->uuid) {
                $this->thingShuffle($thing);
                $count += 1;
                if ($count >= $number) {
                    break;
                }
            } else {
            }
        }

        if (!isset($this->shuffle_count)) {
            $this->shuffle_count = 0;
        }
        $this->shuffle_count += $count;
        $this->response .=
            "Shuffled the latest " . $this->shuffle_count . " Things. ";
    }

    private function weekShuffle()
    {
        $this->ageShuffle('week');
        $this->response .=
            "Shuffled " . $this->shuffle_count . " week old things. ";
    }

    private function dayShuffle()
    {
        $this->ageShuffle('day');
        $this->response .=
            "Shuffled " . $this->shuffle_count . " day old things. ";
    }

    private function hourShuffle()
    {
        $this->ageShuffle('hour');
        $this->response .=
            "Shuffled " . $this->shuffle_count . " hour old things. ";
    }

    private function thingShuffle($thing = null)
    {
        if ($thing == null) {
            $this->thing->shuffle();

            // And fix these pointers.  Now wrong.
            $this->uuid = $this->thing->uuid;
            $this->thing_report['thing'] = $this->thing->thing;
            return;
        }

        if ($thing != null) {
            if ($thing['uuid'] != $this->uuid) {
                $temp_thing = new Thing($thing['uuid']);
                $temp_thing->Shuffle();
            }
        }
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "shuffle"
        );
        $choices = $this->thing->choice->makeLinks('shuffle');
        $thing_report['choices'] = $choices;

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
    }

    public function makeSMS()
    {
        $this->sms_message = "SHUFFLE | " . $this->response;

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        $keywords = [
            'shuffle',
            'latest',
            'recent',
            'melt',
            'all',
            '?',
            'this',
            'day',
            'week',
            'hour',
        ];

        $input = strtolower($this->input);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if (is_string($input) and strlen($input) == 1) {
                // Test for single ? mark and call question()
                $this->message = "Single question mark received";
                $this->helpShuffle();
                if (!isset($this->response)) {
                    $this->response .= "This agent shuffles UUIDs";
                }
                return;
            }

            if ($input == 'shuffle') {
                $this->response .= "No action taken. ";
                return;
            }

            //$this->message = "Request not understood";
            //            $this->thing->shuffle();
            //            $this->response .=
            //                "This command will shuffle your stack. Text SHUFFLE ALL.";
            //            return;
        }

        // If there are more than one piece then look at order.

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'this':
                            $this->thing->shuffle();
                            $this->response .=
                                'Gave this thing a different unique identifier. ';
                        case '?':
                            if ($key + 1 > count($pieces)) {
                                $this->helpShuffle();
                                $this->response .= "Question mark at end";

                                return;
                            } else {
                                $this->helpShuffle();
                                $this->response .=
                                    "Saw a ? " . $this->thing_report['help'];
                                // Question mark was in the string somewhere.
                                // Not so useful right now.
                                return;
                            }
                            break;

                        case 'all':
                            $this->allShuffle();
                            return;
                        case 'recent':
                        case 'latest':
                            $number_agent = new Number($this->thing, "number");
                            $number_agent->extractNumber($input);
                            $number = 10;
                            if ($number_agent->number != false) {
                                $number = $number_agent->number;
                            }
                            $this->latestShuffle($number);
                            return;

                        case 'week':
                            $this->weekShuffle();
                            return;

                        case 'day':
                            $this->dayShuffle();
                            return;

                        case 'day':
                            $this->hourShuffle();
                            return;

                        default:
                    }
                }
            }
        }
        $this->response .= "No Things were shuffled. ";
    }
}
