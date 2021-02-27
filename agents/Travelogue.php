<?php
namespace Nrwtaylor\StackAgentThing;

class Travelogue extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->file_name = $this->resource_path . "interlink/test.php";

        $this->initTravelogue();
    }

    public function initTravelogue()
    {
    	$this->thing_report['help'] = 'Try INTERLINK interlink/test.php. Or INTERLINK <uuid>.';
        $this->thing_report['info'] = 'Navigates a set of UUIDs with prior and posterior links.';

    }

    public function run()
    {
    }

    public function test()
    {
    }

    public function readTravelogue($travelogue = null)
    {
        if ($travelogue == null) {
        }

        $this->text = null;
        $this->prior_uuid = null;
        $this->posterior_uuid = null;
        $this->slugs = [];

        if (!$this->isTravelogue($travelogue)) {
            return true;
        }

        $this->text = $travelogue["text"];
        $this->prior_uuid = $travelogue["prior_uuid"];
        $this->posterior_uuid = $travelogue["posterior_uuid"];

        $this->slugs = [];
        if (isset($travelogue["slugs"])) {
            $this->slugs = $travelogue["slugs"];
        }

        if ($this->agent_input == null) {
            $this->travelogue_message = "Read travelogue.";
        } else {
            $this->travelogue_message = $this->agent_input;
        }
    }

    public function isTravelogue($travelogue)
    {
        if (!isset($travelogue["text"])) {
            return false;
        }
        if (!isset($travelogue["prior_uuid"])) {
            return false;
        }

        if (
            !isset($travelogue["posterior_uuid"]) and
            $travelogue["posterior_uuid"] == null
        ) {
            $this->response .= "Did not see a posterior reference . ";
            return true;
        }

        $this->response .= "Saw a travelogue. ";
        return true;
    }

    public function makeMessage()
    {
        $message = "Travelogue message.";

        $this->sms_message = $message;
        $this->thing_report["message"] = $message;
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
            $web .= $date["dateline"];
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
                $web .= '<div style="margin-bottom: 16px">';
                $link = $this->linkTravelogue($uuid, "link");
                $web .= $link . " ";
                //$t = $this->slugtextTravelogue($slugs);
                $t = implode(" ", $slugs);
                $web .= " [" . $t . "] ";
                $text = $this->interlinkTravelogue($uuid)["text"];
                $text = $url_agent->restoreUrl($text);
                $web .= $text . "</div>";
            }
            $web .= "</div>";
        }

        $interlink_prior = $this->interlinkTravelogue($this->prior_uuid);
        if ($interlink_prior !== false) {
            $web .= "<div>";
            $web .=
                $this->linkTravelogue($this->prior_uuid, "previous") .
                " " .
                $url_agent->restoreUrl($interlink_prior["text"]) .
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
                $url_agent->restoreUrl($interlink_posterior["text"]) .
                "\n";
            $web .= "</div>";
        }

        $this->thing_report["web"] = $web;
    }

    public function linkTravelogue($uuid, $text = null)
    {
        if ($text == null) {
            $text = "link";
        }

        return '<a href="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/travelogue">' .
            $text .
            "</a>";
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
                $interlink_prior["text"] .
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
                $interlink_posterior["text"] .
                "\n";
            $txt .= "\n";
        }

        $this->txt = $txt;
    }

    public function makeLink()
    {
        $uuid = $this->travelogue_uuid;

        if ($this->travelogue_uuid === false) {
            $uuid = $this->prior_uuid;
        }

        $this->link = $this->web_prefix . "thing/" . $uuid . "/travelogue";
        $this->thing_report["link"] = $this->link;
    }

    public function makeTXT()
    {
        $this->txtTravelogue();
        $this->thing_report["txt"] = $this->txt;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    function makeSMS()
    {
        $this->node_list = ["travelogue" => ["travelogue"]];
        $link = "";

        $sms =
            "TRAVELOGUE | " .
            $this->travelogue_message .
            " " .
            $this->response .
            " " .
            $link;
        $this->sms_message = "" . $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        $input = $this->assert($this->input, "travelogue", false);
        $uuid_agent = new Uuid($this->thing, "uuid");
        $this->travelogue_uuid = $uuid_agent->extractUuid($input);

        if (!isset($this->thing->created_at)) {
            $input = $this->thing->uuid;
        }
        $t = $this->loadResource($input);

        // Expect a single travelogue entry back.
        // Try reading it.
        $this->readTravelogue($t);
    }
}
