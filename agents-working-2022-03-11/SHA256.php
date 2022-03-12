<?php
namespace Nrwtaylor\StackAgentThing;

class SHA256 extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doCat();
    }

    function isShA256($sha256 = '')
    {
        return strlen($sha256) == 64 && ctype_xdigit($sha256);
    }

    public function readSubject()
    {
        return false;
    }
}
