<?php
namespace Nrwtaylor\StackAgentThing;

// So this is difference to Contact.
// But uses the information provided to the Contact Agent.
// To report on unique contacts in the from channel.
// In a defined period.

class Contactcounter extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "This is seeing how many contacts this channel has had.";
        $this->thing_report["help"] =
            'Text CONTACT <text>. Then text CONTACT COUNTER.';

        // Start with 14 days.
        $this->contactcounter_horizon = 14 * 24 * 60 * 60;
    }

    public function get()
    {
        $this->getContacts();
        $this->getTransit();
    }

    public function getContacts()
    {
        $contacts_list = [];

        $findagent_thing = new Findagent($this->thing, 'contact');

        $things = $findagent_thing->thing_report['things'];

        $count = count($things);

        $this->thing->log(
            'Agent "Contactcounter" found ' .
                count($findagent_thing->thing_report['things']) .
                " Contact Things."
        );

        $contact_agent = new Contact($this->thing, "contact");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $text = $contact_agent->readContact($thing_object['task']);
                if ($text == "") {
                    continue;
                }
                if ($text == "tracking") {
                    continue;
                }

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object['created_at']);

                if ($age > $this->contactcounter_horizon) {
                    continue;
                }

                if (!isset($contacts_list[$text])) {
                    $contacts_list[$text] = 0;
                }
                $contacts_list[$text] = $contacts_list[$text] + 1;
            }
        }

        $this->unique_count = count($contacts_list);
    }

    public function getTransit()
    {
        $transit_count = 0;
        $translink_count = 0;

        $findagent_thing = new Findagent($this->thing, 'transit');
        $count = count($findagent_thing->thing_report['things']);

        $this->thing->log(
            'Agent "Contactcounter" found ' .
                count($findagent_thing->thing_report['things']) .
                " Contact Things."
        );

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object['created_at']);

                if ($age > $this->contactcounter_horizon) {
                    continue;
                }

                if (isset($variables['transit'])) {
                    $transit_count += 1;
                }

                if (isset($variables['message']['agent'])) {
                    if ($variables['message']['agent'] == 'Translink') {
                        $translink_count += 1;
                    }
                }
            }
        }

        $this->transit_count = $transit_count;
        $this->translink_count = $translink_count;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = [
            "contact" => ["privacy", "terms of use", "warranty"],
        ];
        $sms = "CONTACT COUNTER | ";
        $sms .= $this->response;
        $sms .=
            "Counted " .
            $this->unique_count .
            " unique contacts in " .
            $this->thing->human_time($this->contactcounter_horizon) .
            ". ";
        $sms .= "Saw " . $this->translink_count . " Translink tags. ";
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "contactcounter");
        $choices = $this->thing->choice->makeLinks('contactcounter');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}

