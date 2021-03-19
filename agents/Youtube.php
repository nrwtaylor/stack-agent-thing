<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack

class Youtube extends Agent
{
    // This does Youtube Search via Google's API.

    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always

        $this->keywords = ['youtube', 'search', 'video'];

        $this->api_key =
            $this->thing->container['api']['google']['youtube']['api_key'];

        $this->thing_report['help'] =
            'This provides video search via the Youtube API. Try YOUTUBE MIND THE GAP.';

        $this->thing_report['info'] =
            'This provides video  search via the Youtube API.';
    }

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "youtube" . " " . $this->from
        );


        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'loaded ' . $this->counter . ".",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;
    }

    public function getYoutube($text = null)
    {
        // For testing to save quota
        $this->getVideos();

        if (isset($this->items[0]['id'])) {
            $this->video_id = $this->items[0]['id'];
        }

        if (!isset($this->video_id)) {
            return false;
        }

        $transcript = $this->transcriptYoutube($this->video_id);

        $caption_id = $this->captionidYoutube($this->video_id);
        if ($caption_id === true) {
            $this->response .= "No caption id found. ";
            return;
        }

        // test
        $this->response .= "Caption id is " . $caption_id . ". ";
        //$caption = $this->captionYoutube($caption_id);
    }

    public function getVideos()
    {
        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }

        $keywords = urlencode($keywords);

        if (!isset($this->search_words)) {
            $keywords = "youtube";
        } else {
            $keywords = urlencode($this->search_words);
        }

        if (!isset($this->items_count)) {
            $this->items_count = 0;
        }

        $data_source =
            "https://www.googleapis.com/youtube/v3/search?key=" .
            $this->api_key .
            "&part=snippet" .
            "&q=" .
            $keywords .
            "&maxResults=50";

        $json_data = $this->getApi($data_source);

        $items = $this->parseYoutube($json_data);

        if (!is_array($items)) {
            return true;
        }

        $this->items = $items;
        $this->items_count = count($this->items);
    }

    public function captionidYoutube($video_id = null)
    {
        if ($video_id == null) {
            return true;
        }

        $data_source =
            "https://www.googleapis.com/youtube/v3/captions?key=" .
            $this->api_key .
            "&part=id" .
            "&videoId=" .
            $video_id;

        $data = file_get_contents($data_source);

        $json_data = json_decode($data, true);

        if (!isset($json_data['items'][0]['id'])) {
            return true;
        }

        $caption_id = $json_data['items'][0]['id'];

        return $caption_id;
    }

    public function captionYoutube($caption_id = null)
    {
        $data_source =
            'https://www.googleapis.com/youtube/v3/captions/id?id=' .
            $caption_id .
            '' .
            "&key=" .
            $this->api_key;

        $googleauthorize_agent = new Googleauthorize(
            $this->thing,
            'googleauthorize'
        );

        $http = $googleauthorize_agent->client->authorize();
        //$response = $http->request(
        // 'GET',
        // 'https://www.googleapis.com/youtube/v3/captions/id=' .
        //            $caption_id);

        $response = $http->request(
            'GET',
            '/youtube/v3/captions/id=' . $caption_id
        );

return true;

        /*
// returns a Guzzle HTTP Client
$httpClient = $googleauthorize_agent->client->authorize();

$response = $httpClient->get($data_source);

var_dump($response);
exit();

*/

        /*
        var_dump($googleauthorize_agent->access_token);

        $options = [
            "http" => [
                "method" => "GET",
                "header" =>
                    "Authorization: Bearer " .
                    $googleauthorize_agent->access_token .
                    "\r\n" .
                    "Accept: application/json\r\n",
            ],
        ];

        $context = stream_context_create($options);

        $data = file_get_contents($data_source, false, $context);
var_dump($data);
exit();
*/
        $youtube = new \Google_Service_YouTube($googleauthorize_agent->client);

        $captions = $youtube->captions->listCaptions("snippet", $video_id);

        var_dump($captions);
        echo "merp";

        exit();

        return false;
    }

    function getApi($data_source = null)
    {
        if ($data_source == null) {
            return true;
        }

        $data = @file_get_contents($data_source);
        //var_dump($data);
        if ($data == false) {
            $this->response .= "Could not ask Youtube.";
            //            $this->items_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, true);

        return $json_data;
    }

    public function parseYoutube($array)
    {
        $items = [];

        $total_results = $array['pageInfo']['totalResults'];
        $results_per_page = $array['pageInfo']['resultsPerPage'];
        // devstac
        if (!isset($array['items'])) {
            return true;
        }
        foreach ($array['items'] as $i => $item) {
            if (!isset($item['id']['videoId'])) {
                continue;
            }
            $id = $item['id']['videoId'];
            $link = "https://www.youtube.com/watch?v=" . $id;
            $kind = $item['id']['kind'];

            $snippet = $item['snippet'];
            $title = $snippet['title'];
            $description = $snippet['description'];

            $created_at = $snippet['publishTime'];

            $image_urls = [];
            foreach ($snippet['thumbnails'] as $j => $image_thumbnail) {
                $image_urls[] = $image_thumbnail['url'];
            }
            $item = [
                "id" => $id,
                "title" => $title,
                "description" => $description,
                "created_at" => $created_at,
                "link" => $link,
                "image_urls" => $image_urls,
            ];

            $items[] = $item;
        }

        return $items;
    }

    public function transcriptYoutube($video_id = null)
    {
        $data_source =
            "http://video.google.com/timedtext?lang=en&v=" . $video_id;

        //        $data = file_get_contents($data_source);
        //        $json_data = json_decode($data, true);
    }

    public function getLink($ref = null)
    {
        // Give it the message returned from the API service
        $this->link = "https://www.youtube.com/search?q=" . $ref;
        return $this->link;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->flag = "green";

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        //        $this->thing_report['help'] =
        //            'This provides web search via the Youtube API.';

        $this->thingreportYoutube();
    }

    public function textYoutube($item)
    {
        //        $text = $item['title'];

        $link = $item['link'];
        $html_link = '<a href="' . $link . '">';
        //        $web .= $this->html_image;
        $html_link .= "youtube";
        $html_link .= "</a>";

        $text = $item['title'] . " " . $html_link;
        return $text;
    }

    public function makeWeb()
    {
        $html = "<b>YOUTUBE AGENT</b>";
        //        $html .= "<p><b>Youtube Defintitions</b>";
        $html .= "<p>";

        if (!isset($this->items)) {
            $html .= "<br>No definitions found on Youtube.";
        } else {
            foreach ($this->items as $id => $item) {
                $item_html = $this->textYoutube($item);
                $html .= "<br>" . $item_html;
            }
        }

        $this->html_message = $html;
    }

    public function makeSMS()
    {
        $sms = "YOUTUBE";
        $items_count = 0;
        if (isset($this->items_count)) {
            $items_count = $this->items_count;
        }

        switch ($this->items_count) {
            case 0:
                $sms .= " | No definitions found.";
                break;
            case 1:
                $sms .=
                    " | " .
                    $this->items[0]['title'] .
                    " " .
                    $this->items[0]['link'];
                break;
            default:
                foreach ($this->items as $i => $item) {
                    $sms .= " / " . $item['title'] . " " . $item['link'];
                    if ($i > 5) {
                        $sms .= " [ TEXT WEB for more items ] ";
                        break;
                    }
                }
        }

        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
    }

    public function makeMessage()
    {
        if (!isset($this->items)) {
            $this->getYoutube();
        }

        $message = "Youtube";

        switch ($this->items_count) {
            case 0:
                $message .= " did not find any definitions.";
                break;
            case 1:
                $message .= ' found, "' . $this->definitions[0] . '"';
                break;
            default:
                foreach ($this->items as $item) {
                    $message .= " / " . $item['title'] . "  " . $item['link'];
                }
        }

        $this->message = $message;
    }

    private function thingreportYoutube()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
        $this->response = null;

        $this->num_hits = 0;

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'youtube') {
                //$this->search_words = null;
                $this->response .= "Asked Youtube about nothing. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "youtube is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("youtube is")
            );
        } elseif (($pos = strpos(strtolower($input), "youtube")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("youtube"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->getYoutube();

            $this->response .=
                'Asked Youtube about "' . $this->search_words . '".';
            return false;
        }

        $this->response .= "Message not understood";
        return true;
    }
}
