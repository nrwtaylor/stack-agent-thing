<?php
namespace Nrwtaylor\StackAgentThing;

class Spike extends Agent
{
    public $var = "hello";

    public function init()
    {
    }

    private function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function readSubject()
    {
        $this->response .= "Said hello";

        $stuff_to_say = [
            "https://www.facebook.com/events/191905301472827/ This was July's Picnic in the Park.",
            "https://eregister.electionsbc.gov.bc.ca/ovr/welcome.aspx# Sign-up on-line to vote.",
            "https://www.facebook.com/gerald.peachey.146?ref=br_rs Spike's facebook page.",
            "https://vancouversun.com/news/local-news/vancouver-election-heres-whos-running-for-city-council-in-2018 3/4 of the way down.",
            "Gerald “Spike” Peachy is running as an independent candidate for Vancouver City Council. vancouversun.com",
            "Peachy is a Downtown Eastside resident and harm reduction support worker and educator. vancouversun.com",
            "fb.com/VoteSpike-for-City-Council Vote for Spike for City Council Facebook page.",
            "He hopes to advocate for better representation of Vancouver’s most marginalized community. vancouversun.com",
            "Having previously been homeless, Peachy hopes to use that experience at city hall to find ways to make the city more affordable and inclusive. vancouversun.com",
            "Peachy hopes to empower the vulnerable people in Vancouver to speak and participate in municipal government. vancouversun.com",
        ];

        $this->response = $stuff_to_say[array_rand($stuff_to_say)];

        if (strtolower($this->subject) != "spike") {
            // Tell folk how to vote.
            $this->response .=
                "https://eregister.electionsbc.gov.bc.ca/ovr/welcome.aspx# Sign-up on-line to vote.";
        }

        $this->sms_message =
            "SPIKE | Candidate for Vancouver City Council Oct. 20, 2018 muncipal election. " .
            $this->response .
            "";
        $this->message = $this->response;
        $this->keyword = "spike";

        $this->thing_report["keyword"] = $this->keyword;
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $this->message;

    }
}
