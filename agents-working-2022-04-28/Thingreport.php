<?php
namespace Nrwtaylor\StackAgentThing;

class Thingreport extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
    }

    public function makeThingreport()
    {
        // dev review Agent and build array
        $arr = ['sms', 'web', 'choices', 'png', 'json', 'link','txt','help','info','message'];

        foreach ($arr as $index => $channel) {
            $value = null;

            if (isset($this->thing_report[$channel])) {
                continue;
            }

            // No thing report for the channel.
            // Look to see if there is a channel message.
            $value = null;
            if (isset($this->{$channel . "_message"})) {
                $value = $this->{$channel . "_message"};
            }

            if (($channel == 'message') and (isset($this->message))) {
                $value = $this->message;
            }

            $this->thing_report[$channel] = $value;
        }
    }

    // Avoid loop.

    public function make()
    {
    }
    public function response()
    {
    }

    public function makeSMS()
    {
        $sms = "THINGREPORT | ";
        $sms .= "Built thing report. ";
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        return false;
    }
}
