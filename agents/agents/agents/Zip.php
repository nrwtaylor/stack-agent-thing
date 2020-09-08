<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Zip extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {

        $this->agent_name = "zip";
        $this->test= "Development code";

        $this->thing_report["info"] = "This is an operator with frequencies.";
        $this->thing_report["help"] = "Provides information useful to the Amateur Radio Service. Try HAM 146.480.";

    }


    /**
     *
     */
    function run() {

    }

    public function getZip($url) {
// dev

$zip_file = "/tmp/downloadfile.zip";

//$zip_resource = fopen($zipFile, "w");
$zip_resource = fopen($zip_file, "w");


$ch_start = curl_init();
curl_setopt($ch_start, CURLOPT_URL, $url);
curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
curl_setopt($ch_start, CURLOPT_HEADER, 0);
curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
$page = curl_exec($ch_start);

var_dump($page);

if(!$page)
{
 echo "Error :- ".curl_error($ch_start);
}
curl_close($ch_start);

$zip = new \ZipArchive;

$extractPath = "Download File Path";
if($zip->open($zipFile) != "true")
{
 echo "Error :- Unable to open the Zip File";
} 

$zip->extractTo($extractPath);
$zip->close();

}

function downloadUnzipGetContents($url) {

    $data = file_get_contents($url);

    $path = tempnam(sys_get_temp_dir(), 'prefix');

    $temp = fopen($path, 'w');
    fwrite($temp, $data);
    fseek($temp, 0);
    fclose($temp);

    $pathExtracted = tempnam(sys_get_temp_dir(), 'prefix');

    $filenameInsideZip = 'test.csv';
    copy("zip://".$path."#".$filenameInsideZip, $pathExtracted);

    $data = file_get_contents($pathExtracted);

    unlink($path);
    unlink($pathExtracted);

    return $data;
}

public function doZip($text = null) {

$this->link = trim($text);
//$this->downloadUnzipGetContents($this->link);
//$this->getZip($this->link);
}

    /**
     *
     * @return unknown
     */
    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "zip";

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is a stack agent to manage zip files.";
        $this->thing_report["help"] = "Provides access to functions to work with zip files. Try ZIP.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {

            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {
        if ((!isset($this->response)) or ($this->response == null)){$this->response = "Not found.";}
        //var_dump($this->response);
        $this->node_list = array("zip"=>array("zip"));
        $m = strtoupper("ZIP") . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "zip");
        $choices = $this->thing->choice->makeLinks('zip');
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $input= $this->input;
        //var_dump($this->input);
        $strip_words = array("zip");

        foreach ($strip_words as $i=>$strip_word) {

            $whatIWant = $input;
            if (($pos = strpos(strtolower($input), $strip_word. " is")) !== FALSE) {
                $whatIWant = substr(strtolower($input), $pos+strlen($strip_word . " is"));
            } elseif (($pos = strpos(strtolower($input), $strip_word)) !== FALSE) {
                $whatIWant = substr(strtolower($input), $pos+strlen($strip_word));
            }

            $input = $whatIWant;
        }

        //var_dump($input);
        $this->doZip($input);
        return false;
    }


}
