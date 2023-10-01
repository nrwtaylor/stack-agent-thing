<?php
namespace Nrwtaylor\StackAgentThing;

class Git extends Agent
{
    public $var = 'hello';

    function init()
    {
        // Determine where the stack repository is.
        // dev
        $git_path = null;
        if (isset($GLOBALS['stack_path'])) {
            $git_path =
                $GLOBALS['stack_path'] . 'vendor/nrwtaylor/stack-agent-thing';
        }

        if ($git_path != null) {
            if (!file_exists($git_path . '/.git')) {
                $git_path = $this->settingsAgent(['git', 'path']);
            }
        }

        $this->git_path = $git_path;

//        $this->test();
    }

    public function test()
    {
        $this->thing->log($this->git_path);

        $hash = $this->headhashGit();
        $this->thing->log($hash);

        $line = $this->commitGit($hash, 'dev');
        $this->thing->log($line);

        $commit_nuuid = substr($hash, 0, 4);
        $this->thing->log($commit_nuuid);
    }

    public function run()
    {
        $this->runGit();
    }

    public function nuuidGit($hash)
    {
        $commit_nuuid = substr($hash, 0, 4);
        return $commit_nuuid;
    }

    public function headtagGit()
    {
        if ($this->git_path === null) {
            return true;
        }

        //$HEAD_hash = file_get_contents('../.git/refs/heads/master'); // or branch x
        $branch = 'dev';
        $HEAD_hash = file_get_contents(
            $this->git_path . '/.git/refs/heads/' . $branch
        ); // or branch x

        $files = glob($this->git_path . '/.git/refs/tags/*');
        $tag = false;
        foreach (array_reverse($files) as $file) {
            $contents = file_get_contents($file);

            if ($HEAD_hash === $contents) {
                $tag = basename($file);
                break;
            }
        }
        return $tag;
    }
    // commit
    // /codebase/stack-agent-thing/.git/logs/refs/heads

    public function headhashGit()
    {
        if ($this->git_path === null) {
            return true;
        }

        //$HEAD_hash = file_get_contents('../.git/refs/heads/master'); // or branch x
        $branch = 'dev';
        $HEAD_hash = file_get_contents(
            $this->git_path . '/.git/refs/heads/' . $branch
        ); // or branch x

        $HEAD_hash = trim($HEAD_hash);

        $log = file_get_contents(
            $this->git_path . '/.git/logs/refs/heads/' . $branch
        );

        $lines = explode(PHP_EOL, $log);

        foreach ($lines as $i => $line) {
            if (strpos($line, $HEAD_hash) !== false) {
                break;
            }
        }

        return $HEAD_hash;
    }

    public function commitGit($hash, $branch)
    {
        if ($this->git_path === null) {
            return true;
        }

        $log = file_get_contents(
            $this->git_path . '/.git/logs/refs/heads/' . $branch
        );

        $lines = explode(PHP_EOL, $log);
        $commit_line = false;
        foreach ($lines as $i => $line) {
            if (strpos($line, $hash) !== false) {
                $commit_line = $line;
                break;
            }
        }

        return $commit_line;
    }

    public function runGit()
    {
        if ($this->agent_input == null) {
            $response = "GIT | No response.";

            $this->git_message = $response;
        } else {
            $this->git_message = $this->agent_input;
        }
    }

    function makeSMS()
    {
        $this->node_list = ["git" => ["git"]];

        $sms = "GIT | " . $this->git_hash . " " . $this->response;

        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        $this->git_hash = $this->headhashGit();
        $this->response .= "Looked for git commit hash. ";
    }
}
