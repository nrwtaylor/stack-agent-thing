<?php
namespace Nrwtaylor\StackAgentThing;

class Travelogue extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->path = null;
        if (isset($this->thing->container['stack']['path'])) {
            $this->path = $this->thing->container['stack']['path'];
        }

        $this->initTravelogue();
    }

    public function initTravelogue()
    {
    }

    public function run()
    {
        $this->runTravelogue();
    }

    public function test()
    {
    }

    public function runTravelogue()
    {
        $interlink = $this->interlink;

        $this->text = $interlink['text'];
        $this->prior_uuid = $interlink['prior_uuid'];
        $this->posterior_uuid = $interlink['posterior_uuid'];

        $this->slugs = false;
        if (isset($interlink['slugs'])) {
            $this->slugs = $interlink['slugs'];
        }
        if ($this->agent_input == null) {
            $response = "Travelogue.";

            $this->travelogue_message = $response; // mewsage?
        } else {
            $this->travelogue_message = $this->agent_input;
        }
    }

    public function makeMessage()
    {
        $message = "Travelogue message.";

        $this->sms_message = $message;
        $this->thing_report['message'] = $message;
    }

    public function makeWeb()
    {
        $date_flag = false;

        $url_agent = new Url($this->thing, "url");

        $web = "";
        $web .= "<div>";

        // TODO pre-process date extraction
        if ($date_flag === true) {
            $dateline_agent = new Dateline($this->thing, "dateline");

            // TODO Natural (human) date reconstruction.
            $date = $dateline_agent->extractDateline($this->text);
            $web .= $date['dateline'];
            $web .= "<p><p>";
        }
        $web .= $url_agent->restoreUrl($this->text) . "</div>";

        $web .= "<p>";
        if (is_array($this->slugs)) {
            $web .= "<div>";
            $web .= "<b>LINKS</b><p>";
            $arr = [];
            foreach ($this->slugs as $slug => $uuid_slugs) {
                foreach ($uuid_slugs as $uuid_slug => $j) {
                    $uuid = $uuid_slug;

                    //$uuid = key($uuid_slug);
                    $arr[$uuid][] = $slug;
                }
            }
            foreach ($arr as $uuid => $slugs) {
                $link = $this->linkTravelogue($uuid, "link");
                $web .= $link . " ";

                $t = implode(" ", $slugs);
                $web .= $this->interlinkTravelogue($uuid)['text'] . "<br>";
            }
            $web .= "</div>";
        }

        $interlink_prior = $this->interlinkTravelogue($this->prior_uuid);
        if ($interlink_prior !== false) {
            $web .= "<div>";
            $web .=
                $this->linkTravelogue($this->prior_uuid, "previous") .
                " " .
                $url_agent->restoreUrl($interlink_prior['text']) .
                "\n";
            $web .= "</div>";
        }

        $interlink_posterior = $this->interlinkTravelogue(
            $this->posterior_uuid
        );
        if ($interlink_posterior !== false) {
            $web .= "<p>";
            $web .= "<div>";
            $web .=
                $this->linkTravelogue($this->posterior_uuid, "next") .
                " " .
                $url_agent->restoreUrl($interlink_posterior['text']) .
                "\n";
            $web .= "</div>";
        }

        $this->thing_report['web'] = $web;
    }

    public function linkTravelogue($uuid, $text = null)
    {
        if ($text == null) {
            $text = 'link';
        }

        return '<a href="' .
            $this->web_prefix .
            'thing/' .
            $uuid .
            '/travelogue">' .
            $text .
            '</a>';
    }

    public function interlinkTravelogue($uuid)
    {
        $interlink = $this->getMemory($uuid);
        return $interlink;
    }

    public function txtTravelogue()
    {
        $url_agent = new Url($this->thing, "url");
        $txt = "";

        $txt .= "Travelogue uuid: " . $this->travelogue_uuid . "\n";
        $txt .= "Text: " . $this->text . "\n";

        if (is_array($this->slugs)) {
            $arr = [];
            foreach ($this->slugs as $slug => $uuid_slug) {
                $uuid = key($uuid_slug);
                $arr[$uuid][] = $slug;
            }

            foreach ($arr as $uuid => $slugs) {
                $txt .= "Other links: " . $uuid . " " . " ";
                $t = implode(" ", $slugs);
                $txt .= $t . "\n";
            }
            $txt .= "\n";
        }

        $interlink_prior = $this->interlinkTravelogue($this->prior_uuid);
        if ($interlink_prior !== false) {
            $txt .=
                "Previous: " .
                $this->prior_uuid .
                " " .
                $interlink_prior['text'] .
                "\n";
            $txt .= "\n";
        }

        $interlink_posterior = $this->interlinkTravelogue(
            $this->posterior_uuid
        );
        if ($interlink_posterior !== false) {
            $txt .=
                "Next: " .
                $this->posterior_uuid .
                " " .
                $interlink_posterior['text'] .
                "\n";
            $txt .= "\n";
        }

        $this->txt = $txt;
    }

    public function makeTXT()
    {
        $this->txtTravelogue();
        $this->thing_report['txt'] = $this->txt;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This navigates interlinks between blocks of text.";
        $this->thing_report["help"] = "This is about links between things.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    function makeSMS()
    {
        $this->node_list = ["travelogue" => ["travelogue"]];
        $sms =
            "TRAVELOGUE | " . $this->travelogue_message . " " . $this->response;
        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;

        $uuid_agent = new Uuid($this->thing, "uuid");
        $this->travelogue_uuid = $uuid_agent->extractUuid($input);

        if ($this->travelogue_uuid == false) {
            $this->travelogue_uuid = $this->uuid;
        }

        $t = $this->getMemory($this->travelogue_uuid);

        if ($t === false) {
            $file = $this->path . 'test.php';

            if (file_exists($file)) {
                include $file;
                $this->interlinks = $interlinks;
                $this->response .= "Loaded interlink file. ";

                foreach ($this->interlinks as $uuid => $interlink) {
                    $this->setMemory($uuid, $interlink);
                }
            }
            if ($this->travelogue_uuid === false) {
                $this->travelogue_uuid = array_key_first($interlinks);
            }

            $t = $this->getMemory($this->travelogue_uuid);
        }
        $this->interlink = $t;
    }
}
