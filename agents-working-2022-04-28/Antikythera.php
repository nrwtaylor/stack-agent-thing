<?php
namespace Nrwtaylor\StackAgentThing;

/*
(87 BC.)? 
Manufacture of “Antikythera mechanism …
generally referred to as the first known analogue computer”.
“sophisticated mechanical astronomical calculator
with its functions pre-determined”
https://en.m.wikipedia.org/wiki/Antikythera_mechanism

"used to predict astronomical positions and eclipses for calendar and astrological purposes decades in advance."

https://www.britannica.com/topic/Antikythera-mechanism Edmunds

Input:
"It is believed" ...
"each revolution of the main gear wheel corresponding to one solar year." 

Get current time.

Output(s):
"On the front of the mechanism is a large dial with pointers for
showing the position of the Sun and the Moon in the zodiac
and a half-silvered ball for displaying lunar phases."

Show sun zodiac symbol. Moon zodiac symbol. Lunar phase.

"Two large dials are on the back of the mechanism. The large upper
dial has a five-turn spiral slot with a moving pointer to show the
235 lunations, or synodic months, in the Metonic cycle. 
This cycle is almost exactly 19 years long and is useful in regulating calendars."

"The large lower dial has a four-turn spiral with symbols
to show months in which there was a likelihood of a solar
or lunar eclipse, based on the 18.2-year saros eclipse cycle."

" ... may originally have been a display of planetary
positions, most likely on the front face, but nearly
all the relevant parts are missing."

TODO: Sun.

So let's develop a tool inspired by this mechanism.

Starting with making an observation ... that it is noon or nautical twilight.
And using that to do a Lunar Distances type calculation to get longitude
offset from a meridian.

Antikythera ... Nautical Almanac?

First with reference to a prime meridian.
Then a reference to a set meridian.

ANTIKYTHERA NOON
ANTIKYTHERA | Longitude is XXXXX.

ANTIKYTHERA NAUTICAL TWIGHT
ANTIKYTHERA | Longitude is XXXXX.

I think it is known that it does not have an escapement mechanism.
Hence thinking it is performing an almanac function.
So it can produce an estimate of longitude based on
a known astronomical observation.

*/

class Antikythera extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function initAntikythera() {

        $this->thing_report["info"] =
            "This emulates something a basic Antikythera astronomical tool. Or at least provides some astronomical tools.";
        $this->thing_report["help"] = "Try ANTIKYTHERA NOON. Or ANTIKYTHERA NAUTICAL TWILIGHT.";
    }

    function run()
    {
        $this->runAntikythera();
    }

    public function runAntikythera()
    {
        if ($this->agent_input == null) {
            $v = $this->messageAntikythera();
            $response = $v;
            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    public function messageAntikythera() {

        $message = "No message. What does this thing do?";
        return $message;

    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
        $this->node_list = array("antikythera" => array("transit", "nautical twilight"));

        $sms = "ANTIKYTHERA | " . $this->message;

        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        // TODO Listen for NOON. Or NAUTICAL TWILIGHT.
    }
}
