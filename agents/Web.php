<?php
/**
 * Web.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Web extends Agent
{
    /**
     *
     * @return unknown
     */
    function init()
    {
        /*
        if ($this->thing->thing != true) {


//            $this->thing->log ( 'Agent "Web" ran on a null Thing ' .  $thing->uuid .  '');
            $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";
            return $this->thing_report;
        }
*/
        $this->agent_version = 'redpanda';

        $this->node_list = [
            'web' => ['useful', 'useful?'],
            'link' => ['helpful', 'helpful?'],
        ];

        $this->initWeb();
    }

    public function initWeb()
    {
        $this->default_state = 'on';
        if (isset($this->thing->container['api']['web']['default_state'])) {
            $this->default_state =
                $this->thing->container['api']['web']['default_state'];
        }
        $this->state = $this->default_state;

        //$this->state = "off";

        $this->default_mode = 'local';
        if (isset($this->thing->container['api']['web']['default_mode'])) {
            $this->default_mode =
                $this->thing->container['api']['web']['default_mode'];
        }
        $this->mode = $this->default_mode;

        $this->url_get = "X";
        if (isset($this->thing->container['api']['web']['url_get'])) {
            $this->url_get = $this->thing->container['api']['web']['url_get'];
        }
        $this->mode = $this->default_mode;

        $this->url_post = "X";
        if (isset($this->thing->container['api']['web']['url_post'])) {
            $this->url_post = $this->thing->container['api']['web']['url_post'];
        }

        $this->url_prefix = "X";
        if (isset($this->thing->container['api']['web']['url_prefix'])) {
            $this->url_prefix =
                $this->thing->container['api']['web']['url_prefix'];
        }
    }

    public function get()
    {
        if ($this->from == null) {
            return true;
        }
        $this->variables = new Variables(
            $this->thing,
            "variables " . "web" . " " . $this->from
        );
        //$this->last_refreshed_at = $this->variables->getVariable("refreshed_at");

        // Don't need this as can access headcode variables at $this->headcode
        //$this->head_code = $this->headcode->getVariable("head_code");

        $state = $this->variables->getVariable("state");

        if ($state != false) {
            $this->state = $state;
        }
    }

    /**
     *
     * @param unknown $text (optional)
     */
    public function stackWeb($text = null)
    {
        $this->teststackWeb($text);
    }

    /**
     *
     * @param unknown $arr (optional)
     */
    public function devstackWeb($arr = null)
    {
        // devstack
        // Submit post datagrams to stack.

        $postdata = http_build_query($arr);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postdata,
            ],
        ];

        $context = stream_context_create($opts);
        $url = $this->url_post;
        $result = file_get_contents($url, false, $context);
        //var_dump($result);
    }

    /**
     *
     * @param unknown $text (optional)
     */
    public function teststackWeb($text = null)
    {
        // teststack obtain a UUID from stack.

        $text = "";
        $link = $this->url_get . $text;

        $uuid_agent = new Uuid($this->thing, "uuid");

        $stack_text = file_get_contents($link);

        $uuid_agent->extractUuid($stack_text);
        $uuids = array_unique($uuid_agent->uuids);
        $uuid = $uuids[0];

        $this->stack_link =
            $this->url_prefix .
            "/thing/" .
            $uuid .
            "/" .
            strtolower($this->prior_agent);
    }

    /**
     *
     */
    public function run()
    {
        //$this->doWeb();
    }

    public function doWeb()
    {
        if ($this->state == 'on') {
            $this->linkWeb();

            if ($this->mode == 'remote') {
                if (!isset($this->web)) {
                    $this->web = "test";
                }

                $this->stackWeb($this->web);
            }
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        if ($this->state == 'off') {
            $sms = "WEB | OFF. Try WEB ON.";
        }
        if ($this->state == 'on') {
            //        $sms = "WEB | " . $this->web_prefix . "thing/" . $this->link_uuid . "/" . strtolower($this->prior_agent);

            $sms = "WEB";

            if (isset($this->stack_link)) {
                $sms .= " | " . $this->stack_link;
            }

            $sms .= " | " . $this->response;
        }
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    public function set()
    {
        if (isset($this->variables)) {
            $this->variables->setVariable("state", $this->state);
            $this->variables->setVariable("refreshed_at", $this->current_time);
        }

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["web", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;

        $this->thing_report['message'] = $this->response;

        $this->thing_report['choices'] = $choices;
        $this->thing_report['info'] = 'This is the web agent.';
        $this->thing_report['help'] =
            'This agent takes an UUID and runs the Web agent on it.';
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function linkWeb($text = null)
    {
        //    function getLink($text = null) {
        $this->thing->log("called get web link.");
        $things = [];
        // See if a stack record exists.
        //$findagent_thing = new Findagent($this->thing, 'thing');

        $things = $this->getThings('thing');

        $this->max_index = 0;

        $match = 0;

        $link_uuids = [];

        foreach (array_reverse($things) as $uuid=>$thing) {
//            $this->thing->log(
//                $thing['task'] .
//                    " " .
//                    $thing['nom_to'] .
//                    " " .
//                    $thing['nom_from']
//            );

$nom_to = $thing->nom_to;;
            if ($nom_to == "usermanager") {
                continue;
            }

         //   $variables_json = $block_thing['variables'];
         //   $variables = $this->thing->json->jsontoArray($variables_json);
$variables = $thing->variables;
            if (isset($variables['message']['agent'])) {
                $this->prior_agent = $variables['message']['agent'];
                if (
                    in_array(strtolower($this->prior_agent), [
                        'web',
                        'pdf',
                        'txt',
                        'log',
                        'php',
                    ])
                ) {
                    continue;
                }

                $this->link_uuid = $uuid;

                $previous_thing = new Thing($this->link_uuid);

                break;
            }
        }

        $this->web_exists = true;
        // Testing with this removed
        //        $token_thing = new Tokenlimiter($previous_thing, "revoke tokens");
        //        $token_thing->revokeTokens(); // Because
        //        $agent_thing = new Agent($previous_thing,"agent");
        //        if (!isset($agent_thing->thing_report['web'] )) {$this->web_exists = false;}
        $previous_thing->silenceOn();
        $quiet_thing = new Quiet($previous_thing, "on");
        $agent_thing = new Agent($previous_thing, $this->prior_agent, 'agent');

        if (isset($agent_thing->thing_report['web'])) {
            $this->web = $agent_thing->thing_report['web'];
        }

        if (!isset($agent_thing->thing_report['web'])) {
            $this->web_exists = false;
        }

        $this->stack_link =
            $this->web_prefix .
            "thing/" .
            $this->link_uuid .
            "/" .
            strtolower($this->prior_agent);

        return $this->link_uuid;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = strtolower($this->input);
        $this->filtered_input = $this->assert($input);

        if ($input == "agent") {
            $this->response = true;
            return;
        }

        if ($input == "web") {
            $this->doWeb();
            $this->response .= "Requested web. ";
            return;
        }

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['on', 'off'];
        $input_agent->aliases['on'] = ['on'];
        $input_agent->aliases['off'] = ['off'];
        $response = $input_agent->discriminateInput($this->filtered_input, $discriminators);

        if ($response == "on") {
            $this->state = "on";
        }
        if ($response == "off") {
            $this->state = "off";
        }

        $this->doWeb();

        $this->defaultButtons();
        $status = true;
        $this->response = "Made a web link. ";
        return $status;
    }

    /**
     *
     */
    function makeWeb()
    {
        //return;

        $this->thing_report['web'] = $this->filtered_input;

        return;
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = ["web" => ["iching", "roll"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');

        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            'thing/' .
            $this->link_uuid .
            '/receipt.png">';
        $web .= "</a>";

        $web .= "<br>";

        $web .= $this->subject;
        $web .= "<br>";
        $web .= $this->sms_message;

        $web .= "<br><br>";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function defaultButtons()
    {
        if (rand(1, 6) <= 3) {
            $this->thing->choice->Create('web', $this->node_list, 'web');
        } else {
            $this->thing->choice->Create('web', $this->node_list, 'link');
        }

        $this->thing->flagGreen();
    }
}
