<?php
namespace Nrwtaylor\StackAgentThing;

// Collects channel rules.
// And figures out how to present them.

class Etiquette extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "Etiquette is a collection of channel specific rules.";
        $this->thing_report["help"] = htmlspecialchars(
            "Text RULE <text>. Then text ETIQUETTE."
        );

        $this->rules_list = [];
        $this->rule_tag_count = 0;
        $this->rule_message_count = 0;
        $this->unique_count = 0;

        $this->state = "off";
        if (isset($this->thing->container['api']['etiquette']['state'])) {
            $this->state = $this->thing->container['api']['etiquette']['state'];
        }

        if ($this->state == "off") {
            $this->response .= strtoupper($this->state) . ". ";
        }

        // Start with 365 days.
        $this->etiquette_horizon = 365 * 24 * 60 * 60;

        $this->channel_id = "not read";
        $this->channel_name = "not read";
    }

    public function get()
    {
        if ($this->state == "off") {
            return;
        }

        $this->getRules();
        $this->getRule();
        $this->getChannel();
    }

    public function getChannel()
    {
        $channel_agent = new Channel($this->thing, "channel");

        $this->channel_sms = "No message.";
        if (isset($channel_agent->thing_report['sms'])) {
            $this->channel_sms = $channel_agent->thing_report['sms'];
        }

        if (isset($channel->channel_name)) {
            $this->channel_name = $channel->channel_name;
        }

        $t = $channel_agent->readFrom();
        if (isset($t->channel_id)) {
            $this->channel_id = $t->channel_id;
        }
    }

    public function getRules()
    {
        $rules_list = [];

        $this->rules_list = [];
        $this->unique_count = 0;

        $findagent_thing = new Findagent($this->thing, 'rule');
        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }
        $count = count($findagent_thing->thing_report['things']);

        $this->thing->log(
            'Agent "Etiquette" found ' .
                count($findagent_thing->thing_report['things']) .
                " Rule Things."
        );

        $rule_agent = new Rule($this->thing, "rule");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $response = $rule_agent->readRule($thing_object['task']);

                // This can be refactered I think with a call to the empty thing function.
                //if ($response == false) {continue;}
                //if ($response == true) {continue;}
                //if ($response == null) {continue;}
                if ($response == "") {
                    continue;
                }

                $text = $response;

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object['created_at']);

                if ($age > $this->etiquette_horizon) {
                    continue;
                }
                $link = $this->web_prefix . 'thing/' . $uuid . '/rule';

                $rule = [
                    'title' => $thing_object['task'],
                    'url' => $link,
                    'age' => $age,
                ];
                $rules_list[] = $rule;
                //                if (!isset($rules_list[$text])) {
                //                    $rules_list[$text] = 0;
                //                }
                //                $rules_list[$text] = $rules_list[$text] + 1;
            }
        }
        $this->rules_list = $rules_list;
        $this->unique_count = count($rules_list);
    }

    public function getRule()
    {
        $rule_tag_count = 0;
        $rule_message_count = 0;

        $this->rule_tag_count = 0;
        $this->rule_message_count = 0;

        $findagent_thing = new Findagent($this->thing, 'rule');

        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }

        $count = count($findagent_thing->thing_report['things']);

        $this->thing->log(
            'Agent "Etiquette" found ' .
                count($findagent_thing->thing_report['things']) .
                " Rule Things."
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

                if ($age > $this->etiquette_horizon) {
                    continue;
                }

                if (isset($variables['rule'])) {
                    $rule_tag_count += 1;
                }

                if (isset($variables['message']['agent'])) {
                    if ($variables['message']['agent'] == 'Rule') {
                        $rule_message_count += 1;
                    }
                }
            }
        }

        $this->rule_tag_count = $rule_tag_count;
        $this->rule_message_count = $rule_message_count;
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

    function makeWeb()
    {
        $web = "<b>Etiquette Agent</b><br>";
        $web .= "<p>";
        $web .= "<p>";
        $web .= "<b>COLLECTED RULES</b><br><p>";

        $rules = "No channel rules found.";
        if ($this->state == "on") {
            if (count($this->rules_list) > 0) {
                $rules = "<ul>";
                foreach ($this->rules_list as $i => $rule) {
                    $link = $rule['url'];

                    $web_link = '<a href="' . $link . '" target="_blank">';
                    $web_link .= '[rule]';
                    $web_link .= "</a>";

                    $rules .=
                        "<li>" .
                        htmlspecialchars($rule['title']) .
                        " " .
                        $web_link .
                        " " .
                        $this->thing->human_time($rule['age']) .
                        " old<br>";
                }
                $rules .= "</ul>";
            }
        }

        $web .= $rules;

        $web .= "<p>";

        if ($this->channel_name != 'not read') {
            $web .= "<b>CHANNEL</b><br>";

            $web .= "<p>";
            $web .= $this->channel_sms . "<br>";

            $web .= "Channel name is " . $this->channel_name . "<br>";
            $web .= "Channel identity is " . $this->channel_id . "<br>";

            $web .= "<p>";
        }

        $web .= "<b>HELP</b><br>";

        $web .= "<p>";

        $web .= $this->thing_report['help'] . "<p>";

        $web .= "<p>";
        $web .=
            "Text RULE WEAR SUN SCREEN or RULE IS WEAR SUN PROTECTION to the channel to associate that rule with the channel. ";

        if (count($this->rules_list) > 0) {
            $web .=
                "The RULE access keys provided allow deletion of specific channel rules. A new link can be generated at any time by texting ETIQUETTE.<br>";
        }

        $web .= "<p>";

        $web .= "<b>META</b><br>";
        $web .= "<p>";

        $web .= $this->to . "<br>";
        $web .= substr(hash("sha256", $this->from), 0, 4) . "<br>";

        $web .= "Counted " . $this->rule_tag_count . " rule tags.<br>";
        $web .= "Counted " . $this->rule_message_count . " rule messages.<br>";
        $this->thing_report['web'] = $web;
    }

    function makeSMS()
    {
        $this->node_list = [
            "rule" => ["privacy", "terms of use", "warranty"],
        ];
        $sms = "ETIQUETTE | ";
        $sms .= $this->response;

        if ($this->state == "off") {
            $this->sms_message = $sms;
            $this->thing_report['sms'] = $sms;
        }

        $sms .= "Saw " . $this->unique_count . " rules. ";

        $link =
            $this->web_prefix . 'thing/' . $this->thing->uuid . '/etiquette';

        $outro = "TEXT WEB";
        foreach ($this->rules_list as $i => $rule) {
            $sms .= $rule['title'] . " / ";

            if (strlen($sms . $link) > 140) {
                $sms .= "[ ... ] ";
                $sms .= $link;
                $outro = "";
                break;
            }
        }

        $sms .= $outro;

        //        $sms .= $link;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "etiquette");
        $choices = $this->thing->choice->makeLinks('etiquette');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
