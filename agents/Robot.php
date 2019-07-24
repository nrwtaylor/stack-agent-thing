<?php
/**
 * Robot.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;
//require '/var/www/stackr.test/vendor/autoload.php';

use webignition\RobotsTxt\File\Parser;
use webignition\RobotsTxt\Inspector\Inspector;

// devstack only reads the current web_prefix currently

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);


class Robot extends Agent {


    /**
     *
     */
    function init()
        {

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        $user_agent = $this->thing->container['api']['robot']['user_agent'];
        $user_agent = "reader stackr.ca";
        ini_set('user_agent', $user_agent);

        $this->node_list = array("start"=>array("acknowledge"));

        $this->variables_agent = new Variables($this->thing, "variables robot " . $this->from);

        $this->useragent = ini_get("user_agent");
        $url = rtrim($this->web_prefix. "/");

    }


    /**
     *
     * @param unknown $url_robots_txt (optional)
     */
    function readTxt($url_robots_txt = null) {


        $this->is_allowed = $this->robots_allowed($this->search_url, "Stackr (stackr.ca)");
return;

// devstack explore
        $parser = new Parser();
        //$parser = \webignition\RobotsTxt\File\Parser();
        //$robots_txt = file_get_contents('');

        if ($url_robots_txt == null) {$url_robots_txt = $this->url_robots_txt;}

        //        $robots_txt = file_get_contents('https://www.vancouverconventioncentre.com/robots.txt');
        //        $this->robots_txt = file($url_robots_txt);

        $this->robots_txt = @file_get_contents($url_robots_txt);

        var_dump($this->robots_txt);
//exit();
        if ($this->robots_txt == false) {
            // failed to read site
            echo "failed to read site";
            $this->is_allowed = null;
            return;
        }


        $this->thing->log($url_robots_txt);


        $parser->setSource($this->robots_txt);

        $robotsTxtFile = $parser->getFile();

        $this->inspector = new Inspector($parser->getFile());
        $this->inspector->setUserAgent('stackr');


        // http://feeds.justshows.net/sitemap/vancouver/?

        $this->is_allowed = $this->robots_allowed($this->search_url, "Stackr (stackr.ca)");
        var_dump($this->is_allowed);
        //var_dump($this->search_url);
        //        $this->is_allowed = $this->inspector->isAllowed($this->search_url);
        //var_dump($this->is_allowed);
        //exit();

    }


    /**
     *
     */
    function set() {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);
    }


    /**
     *
     */
    function get() {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".");

        $this->counter = $this->counter + 1;
    }


    /**
     *
     */
    function makeTXT() {
        // https://developers.google.com/search/reference/robots_txt

        $txt = "# welcome robot\n";

        $jarvis = new Jarvis($this->thing, "jarvis");
        $txt .= "# " . $jarvis->sms_message . "\n";

        $txt .= 'User-agent: *';
        $txt .= "\n";

        $txt .= 'Disallow:';
        $txt .= "\n";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     */
    private function makeWeb() {

        $html = $this->robots_txt;

        $this->html = $html;
        $this->thing_report['web'] = $html;

    }


    /**
     *
     */
    private function makeSMS() {


        switch ($this->counter) {
            //         case 1:
            //             $sms = "ROBOT | You may read all end-points.  Please be respectful of resources. Read our Privacy Policy " . $this->web_prefix . "policy";
            //             break;

        case null;

        default:
            $sms = "ROBOT | You may read all end-points.  Please be respectful of resources. " . $this->web_prefix . "privacy";
            if ($this->search_url == "") {
                $sms = "ROBOT | You may read all end-points.  Please be respectful of resources. " . $this->web_prefix . "privacy";
            } else {
                $sms = "ROBOT | Read that " . $this->search_url . " is";
                if ($this->is_allowed) {$sms .= " allowed.";} else {$sms .= " not allowed.";}
            }
        }

        //$sms .= " | TEXT PRIVACY";

        //$sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }


    /**
     *
     */
    private function makeEmail() {
        switch ($this->counter) {
        case 1:
            $subject = "Hello Robot";
            $message = "Email access is in limited beta. Please be respectful of resources.
                    <br>
                    Keep on stacking.

                    ";
            break;
        case 2:
            $subject = "Robot";
            $message = "No robots please.\n\n";
            break;
        case null;
        default:
            $message = "ROBOT | Acknowledged. " . $this->web_prefix ."privacy";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;

    }


    /**
     *
     */
    private function makeChoices() {
        $choices = $this->thing->choice->makeLinks('start');

        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     */
    public function respond() {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeSMS();
        $this->makeEmail();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->makeTxt();
        $this->thing_report['help'] = $this->agent_prefix  .'responding to a message from a robot.';

        $this->makeWeb();

        return;
    }


    /**
     *
     */
    public function readSubject() {
        if ($this->agent_input == null) {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }
echo "robot readsubjec tinput " . $input . "\n";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "robot is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("robot is"));
        } elseif (($pos = strpos(strtolower($input), "robot")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("robot"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $this->search_url = $filtered_input;
        echo "search_url ". $this->search_url ."\n";
        $this->makeLink($filtered_input);

echo "robots url " . $this->url_robots_txt . "\n";

        $this->readTxt();
echo "end of readsubject/n";
        // var_dump($this->robots_allowed($this->robots_txt));
        //exit();
//        $this->start();
        return;
    }


    /**
     *
    
    function start() {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "robot start");
        $this->thing->log( $this->agent_prefix .'called the Usermanager to update user state to start.' );

        return;
    }
*/

    /**
     *
     * @param unknown $url
     */
    function makeLink($url) {
        // https://www.the-art-of-web.com/php/parse-robots/
        $this->search_url = $url;
        // https://www.the-art-of-web.com/php/parse-robots/
        // parse url to retrieve host and path

        $parsed = parse_url($url);

        if (!isset($parsed['scheme'])) {$scheme = "http";} else {$scheme = $parsed['scheme'];}

        //$robotstxt = "{$parsed['scheme']}://{$parsed['host']}/robots.txt";
        $robotstxt = $scheme . "://{$parsed['host']}/robots.txt";
        $this->url_robots_txt = $robotstxt;
    }


    /**
     *
     * @param unknown $url
     * @param unknown $useragent (optional)
     * @return unknown
     */
    function robots_allowed($url, $useragent=false) {
        // parse url to retrieve host and path
        $parsed = parse_url($url);

        $agents = array(preg_quote('*'));
        if ($useragent) $agents[] = preg_quote($useragent);
        $agents = implode('|', $agents);

        // location of robots.txt file
        $robotstxt = @file("http://{$parsed['host']}/robots.txt");

        // if there isn't a robots, then we're allowed in
        if (empty($robotstxt)) return true;

        $rules = array();
        $ruleApplies = false;
        foreach ($robotstxt as $line) {
            // skip blank lines
            if (!$line = trim($line)) continue;

            // following rules only apply if User-agent matches $useragent or '*'
            if (preg_match('/^\s*User-agent: (.*)/i', $line, $match)) {
                $ruleApplies = preg_match("/($agents)/i", $match[1]);
            }
            if ($ruleApplies && preg_match('/^\s*Disallow:(.*)/i', $line, $regs)) {
                // an empty rule implies full access - no further tests required
                if (!$regs[1]) return true;
                // add rules that apply to array for testing
                $rules[] = preg_quote(trim($regs[1]), '/');
            }
        }

        if (!isset($parsed['path'])) {
            $path = null;
        } else {
            $path = $parsed['path'];
        }

        var_dump($path);

        foreach ($rules as $rule) {
            // check if page is disallowed to us
            if (preg_match("/^$rule/", $path)) return false;
        }

        // page is not disallowed
        return true;
    }





    /**
     *
     * @param unknown $url
     */
    function getRobots($url) {
        $robotsUrl = $url . "/robots.txt";
        $robot = null;
        //create an object
        $allRobots = [];
        $fh = fopen($robotsUrl, 'r');
        while (($line = fgets($fh)) != false) {
            echo $line . "<br>";
            if (preg_match("/user-agent.*/i", $line) ) {
                if ($robot != null) {
                    array_push($allRobots, $robot);
                }

                $robot = new stdClass();
                $robot->userAgent = [];
                $robot->userAgent = explode(':', $line, 2)[1];
                $robot->disAllow = [];
                $robot->allow = [];
            }
            if (preg_match("/disallow.*/i", $line)) {
                array_push($robot->disAllow, explode(':', $line, 2)[1]);
            }
            else if (preg_match("/^allow.*/i", $line)) {
                array_push($robot->allow, explode(':', $line, 2)[1]);
            }
        }

        if ($robot != null) {
            array_push($allRobots, $robot);
        }

        //Lazy way of outputting. Loop through for prettier output.
        echo "<pre>";
        var_dump($allRobots);
        echo "</pre>";
    }


}
