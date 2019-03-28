<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Search extends Agent
{
    public $var = 'hello';

    public function run()
    {
        $input = $this->subject;

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "search is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("search is"));
        } elseif (($pos = strpos(strtolower($input), "search")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("search"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $search_agent = new Contextualweb($this->thing, $filtered_input);
        $this->thing_report = $search_agent->thing_report;
    }

	public function respond()
    {
        $this->thing_report['info'] = 'Search uses the Contextual Web API.' ;
        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This is an agent which understands what search is. And will try to do one.';
	}

    public function readSubject()
    {
        // Blank
	}

}
