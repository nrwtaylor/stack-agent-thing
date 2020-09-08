<?php
namespace Nrwtaylor\StackAgentThing;

class Is extends Agent
{
    public function init()
    {
        $this->keywords = [];
    }

    public function respondResponse()
    {
        $this->cost = 0;

        // Thing stuff
        $this->thing->flagGreen();

        $this->thing_report['sms'] = $this->sms_message;

        $this->thing_report['message'] = $this->sms_message;

        // Make email
        //        $this->makeEmail();

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
        if (!isset($this->alias_thing)) {
            $this->sms_message = "IS | Not yet a Thing.";
            return;
        }

        if ($this->alias_thing->alias_id != null) {
            //            $this->sms_message = "IS | alias_id = " . $this->alias_thing->alias_id;
            $this->sms_message = "IS | " . $this->alias_thing->alias_id;

            return;
        } else {
            $this->sms_message = "IS | Seen as a thing.";
            return;
        }

        // Why did we get here?
        return true;
    }

    function makeEmail()
    {
        $this->email_message = "IS | ";
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $pieces = explode(" is ", strtolower($input));

        if (count($pieces) == 2) {
            if (strtolower($pieces[0]) == 'alias') {
                // A left and a right pairing and nothing else.
                // So we can substitute the word and pass it to Alias.
                $this->thing->log(
                    $this->agent_prefix .
                        'passed to Alias "' .
                        $this->subject .
                        '".',
                    "INFORMATION"
                );
                $this->alias_thing = new Alias($this->thing, 'alias');

                $this->thing->json->writeVariable(
                    ["is", "alias_id"],
                    $this->alias_thing->alias_id
                );
                return;
            }
        }

        $this->thing->json->writeVariable(["is", "alias_id"], true);

        return true;
    }
}
