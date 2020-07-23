<?php
namespace Nrwtaylor\StackAgentThing;
//require_once '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/stackr.test/vendor/autoload.php';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

// Okay
// So we want to do this.
// https://developers.google.com/hangouts/chat/how-tos/rest-api

// Authentication using a service account is a prerequisite for using the Hangouts Chat REST API.

class Googleauthorize extends Agent
{
    function init()
    {
        $this->reset_flag = false;
        $this->agent_name = "Google Authorize";

        $this->thing_report['info'] = 'This is a Google OAuth manager.';

        $this->access_token =
            $this->thing->container['api']['google']['oauth_client'][
                'access_token'
            ];

        $this->node_list = ["start" => ["google authorize"]];
    }

    public function getClient()
    {
        // https://developers.google.com/api-client-library/php/auth/web-app
        $key_file_location =
            $this->thing->container['api']['google']['credentials'][
                'oauth-client'
            ]['key_file_location'];

        $this->client = new \Google_Client();
        $this->client->setApplicationName("Stackr");
        $this->client->setAuthConfig($key_file_location);
        //       $this->client->setScopes(['https://www.googleapis.com/auth/youtube.readonly']);

        $this->client->setScopes([
            'https://www.googleapis.com/auth/youtube.force-ssl',
        ]);

        $this->client->setAccessType('offline');

if (isset($this->access_token)) {

$accessToken = $this->access_token;

}


//        if ((!isset($this->access_token)) or ($this->reset_flag == true)) {

        // Request authorization from the user.

        $authUrl = $this->client->createAuthUrl();
        printf("Open this link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.

        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

//        }

        //$this->access_token = $accessToken;
        //*/

        $this->client->setAccessToken($accessToken);

    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['choices'] = false;
        $this->thing_report['help'] = 'In development.';
        $this->thing_report['log'] = $this->thing->log;
    }

    public function readSubject()
    {
        if ($this->input == "googleauthorize refresh") {
            $this->reset_flag = true;
        }
        $this->getClient();
    }
}
