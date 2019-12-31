<?php
/**
 * Robot.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

// Looks like the only way to decide if the user is a bot is to look at the header.
// Look at the header.
// See if it gets a hit against known headers/header strings.
// If it does flag as a bot.

// Do not save the header.

// devstack - robots.txt reader

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Robot extends Agent {


    /**
     *
     * @param Thing   $thing
     */
    function init() {

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.


        if (isset($this->thing->container['api']['robot']['user_agent'])) {

            $user_agent = $this->thing->container['api']['robot']['user_agent'];
            ini_set('user_agent', $user_agent);
        }


        $this->node_list = array("start"=>array("acknowledge"));

        $this->current_time = $this->thing->json->time();

        $this->useragent = ini_get("user_agent");
        $url = rtrim($this->web_prefix. "/");

        $this->thing_report['help'] = 'Checks if you are a robot.';


    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function getHeader($text = null) {
        $this->hits = array();
        $this->hits_count = 0;

        try {
            $headers = apache_request_headers();
        }


        catch (\Throwable $t) {
            $this->thing->log("caught throwable.");
            // Executed only in PHP 7, will not match in PHP 5
            return true;
        }


        catch (\Exception $e) {
            $this->thing->log("caught exception");
            // Executed only in PHP 5, will not be reached in PHP 7
            return true;
        }




        if (isset($headers['User-Agent'])) {$request_user_agent = $headers['User-Agent'];
            $text = $request_user_agent;}
        //var_dump($text);

        $librex_agent = new Librex($this->thing, "librex");
        $librex_agent->getLibrex('robot/robot');
        $this->hits = $librex_agent->getHits($text);

        $this->hits_count = count($this->hits);

    }

    public function isRobot() {
        if ($this->hits > 1) {return true;}
        return false;
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
        $this->variables_agent = new Variables($this->thing, "variables robot " . $this->from);

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".");

        $this->counter = $this->counter + 1;

        $this->getHeader();

    }


    /**
     *
     */
    function makeTXT() {
        // https://developers.google.com/search/reference/robots_txt

        if ($this->hits_count == 0) {
            $txt = "# welcome human(?)\n"; } else {
            $txt = "# welcome robot\n";
        }

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
    public function makeSMS() {
        switch ($this->counter) {
        case 1:
            $sms = "ROBOT | You may read all end-points.  Please be respectful of resources. Read our Privacy Policy " . $this->web_prefix . "policy";
            break;

        case null;

        default:
            $sms = "ROBOT | You may read all end-points.  Please be respectful of resources. " . $this->web_prefix . "privacy";

        }

        if (isset($this->response)) {$sms = "ROBOT | " . $this->response;}

        $sms .= " | TEXT PRIVACY";

        //$sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }


    /**
     *
     */
    public function makeWeb() {

        $web = "<b>Robot Agent</b>";
        $web .= "<br><br>";

        if (($this->hits_count) == 0) {$web .= "You seem to be human.";} else {

            $web .= "Your header matches against the following known bot strings:<br>";

            foreach ($this->hits as $i=>$hit_text) {
                $web .= $hit_text . "<br>";
            }

        }

        $this->thing_report['web'] = $web;

    }


    /**
     *
     */
    public function makeEmail() {
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
     * @return unknown
     */
    public function respond() {
        // Thing actions
        $this->thing->flagGreen();

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];


        return $this->thing_report;
    }


    /**
     *
     */
    public function readSubject() {
        $link_agent = new Link($this->thing, $this->input);
        $t = $link_agent->extractLink($this->input);
        //var_dump($t);
        if ($t != null) {
            $this->test_link = $t;

            //$is_allowed_test1 = $this->robots_allowed($t);

            $is_allowed_test2 = $this->robots_allowed_curl($t);

            $this->test_allowed = $is_allowed_test2;

            if ($this->test_allowed) {
                $this->response .= $this->test_link . " is allowed. ";}
            else {

                $this->response .= $this->test_link . " is not allowed. ";


            }

            return;

        }
        $this->start();
        return;
    }


    /**
     *
     */
    function start() {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "robot start");
        $this->thing->log( $this->agent_prefix .'called the Usermanager to update user state to start.' );

        return;
    }


    /**
     *
     * @param unknown $url
     * @param unknown $useragent (optional)
     * @return unknown
     */
    function robots_allowed($url, $useragent=false) {
        // https://www.the-art-of-web.com/php/parse-robots/
        // parse url to retrieve host and path
        $parsed = parse_url($url);

        $agents = array(preg_quote('*'));
        if ($useragent) $agents[] = preg_quote($useragent);
        $agents = implode('|', $agents);

        // location of robots.txt file
        $robotstxt = file("http://{$parsed['host']}/robots.txt");

        // if there isn't a robots, then we're allowed in
        if (empty($robotstxt)) return true;

        $rules = array();
        $ruleApplies = false;
        foreach ($robotstxt as $line) {
            // skip blank lines
            if (!$line = trim($line)) continue;

            // following rules only apply if User-agent matches $useragent or '*'
            if (preg_match('#^\s*User-agent: (.*)#i', $line, $match)) {
                $ruleApplies = preg_match("#($agents)#i", $match[1]);
            }
            if ($ruleApplies && preg_match('#^\s*Disallow:(.*)#i', $line, $regs)) {
                // an empty rule implies full access - no further tests required
                if (!$regs[1]) return true;
                // add rules that apply to array for testing
                $rules[] = preg_quote(trim($regs[1]), '/');
            }
        }

        if (!isset($parsed['path'])) {return null;}

        foreach ($rules as $rule) {
            // check if page is disallowed to us
            if (preg_match("#^$rule#", $parsed['path'])) return false;
        }

        // page is not disallowed
        return true;
    }


    // https://www.the-art-of-web.com/php/parse-robots/

    // Original PHP code by Chirp Internet: www.chirp.com.au
    // Adapted to include 404 and Allow directive checking by Eric at LinkUp.com
    // Please acknowledge use of this code by including this header.


    /**
     *
     * @param unknown $url
     * @param unknown $useragent (optional)
     * @return unknown
     */
    function robots_allowed_curl($url, $useragent=false) {
        // parse url to retrieve host and path
        $parsed = parse_url($url);

        $agents = array(preg_quote('*'));
        if ($useragent) $agents[] = preg_quote($useragent, '/');
        $agents = implode('|', $agents);

        // location of robots.txt file, only pay attention to it if the server says it exists
        if (function_exists('curl_init')) {
            $handle = curl_init("http://{$parsed['host']}/robots.txt");
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                $robotstxt = explode("\n", $response);
            } else {
                $robotstxt = false;
            }
            curl_close($handle);
        } else {
            $robotstxt = @file("http://{$parsed['host']}/robots.txt");
        }

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
                continue;
            }
            if ($ruleApplies) {
                list($type, $rule) = explode(':', $line, 2);
                $type = trim(strtolower($type));
                // add rules that apply to array for testing
                $rules[] = array(
                    'type' => $type,
                    'match' => preg_quote(trim($rule), '/'),
                );
            }
        }

        $isAllowed = true;
        $currentStrength = 0;
        foreach ($rules as $rule) {
            // check if page hits on a rule
            if (preg_match("/^{$rule['match']}/", $parsed['path'])) {
                // prefer longer (more specific) rules and Allow trumps Disallow if rules same length
                $strength = strlen($rule['match']);
                if ($currentStrength < $strength) {
                    $currentStrength = $strength;
                    $isAllowed = ($rule['type'] == 'allow') ? true : false;
                } elseif ($currentStrength == $strength && $rule['type'] == 'allow') {
                    $currentStrength = $strength;
                    $isAllowed = true;
                }
            }
        }

        return $isAllowed;
    }



    /**
     *
     * @param unknown $url
     * @return unknown
     */
    function getRobots($url) {
        // devstack

        $robotsUrl = $url . "/robots.txt";
        $robot = null;
        //                $robot = new stdClass();

        //create an object
        $allRobots = [];
        $fh = fopen($robotsUrl, 'r');
        while (($line = fgets($fh)) != false) {
            //            echo $line . "<br>";
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
        //        echo "<pre>";
        //        var_dump($allRobots);
        //        echo "</pre>";
        return $allRobots;
    }


}
