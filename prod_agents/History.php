<?php
/**
 * History.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class History extends Agent
{
    public $var = 'hello';

    /**
     */
    function init()
    {
        $this->event_horizon = 60 * 60 * 24;
    }

    function variablesHistory($variable_set = null)
    {
        $variable_set_name = key($variable_set);

        $things = $this->getThings($variable_set_name);

        $this->variables_history = [];

        if ($things === true) {
            return;
        }
        if ($things === null) {
            return;
        }

        foreach ($things as $uuid => $thing) {
            $variables = $thing->variables;
            if (isset($variables[$variable_set_name])) {
                $number = "X";

                foreach (
                    $variable_set[$variable_set_name]
                    as $i => $variable_name
                ) {
                    if (isset($variables[$variable_set_name][$variable_name])) {
                        ${$variable_name} =
                            $variables[$variable_set_name][$variable_name];
                    }
                }
            }

            $age = strtotime($this->current_time) - strtotime($refreshed_at);
            if ($age > $this->event_horizon) {
                continue;
            }

            foreach (
                $variable_set[$variable_set_name]
                as $i => $variable_name
            ) {
                if (!is_numeric(${$variable_name})) {
                    continue;
                }
            }

            $variable_history = [];
            foreach (
                $variable_set[$variable_set_name]
                as $i => $variable_name
            ) {
                $variable_history[$variable_name] = ${$variable_name};
            }
            $variable_history['uuid'] = $uuid;
            $variable_history['timestamp'] = $refreshed_at;
            $variable_history['created_at'] = strtotime($refreshed_at);

            $this->variables_history[] = $variable_history;
        }

        $refreshed_at = [];
        foreach ($this->variables_history as $key => $row) {
            $refreshed_at[$key] = $row['timestamp'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->variables_history);
    }

    public function get()
    {
    }

    public function set()
    {
    }

    public function make()
    {
    }

    public function read($text = null)
    {
    }
}
