<?php
/**
 * Csv.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Csv extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
    }

    public function respondResponse() {}

    public function parseCsv($line, $field_names = null)
    {
        if ($field_names == null) {
            $field_names = $this->field_names;
        }

        $field_values = explode(",", $line);
        $i = 0;
        $arr = [];

        foreach ($field_names as $field_name) {
            if (!isset($field_values[$i])) {
                $field_values[$i] = null;
            }
            $arr[$field_name] = $field_values[$i];
            $i += 1;
        }
        return $arr;
    }

    public function readSubject() {}

}
