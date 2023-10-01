<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

// Okay
// So we want to do this.
// https://developers.google.com/hangouts/chat/how-tos/rest-api

// Authentication using a service account is a prerequisite for using the Hangouts Chat REST API.

// devstack web authorization.
// Much work needed.

class Googleauthorize extends Agent
{
    function init()
    {
        $this->reset_flag = false;
        $this->agent_name = "Google Authorize";

        $this->thing_report["info"] = "This is a Google OAuth manager.";

        $this->access_token =
            $this->thing->container["api"]["google"]["oauth_client"][
                "access_token"
            ];

        $this->key_file_location =
            $this->thing->container["api"]["google"]["credentials"][
                "oauth-client"
            ]["key_file_location"];

        $this->node_list = ["start" => ["google authorize"]];
    }

    public function get()
    {
        $this->getChannel();
        $this->getCode();
        //        $this->getClient();
    }

    public function getCode()
    {
        if (!isset($this->code)) {
            if (isset($_GET["code"])) {
                $t = $_GET["code"];
                $this->code = $t;
            }
        }
    }

    public function run()
    {
        $this->runClient();
    }

    public function runClient()
    {
        if (!file_exists($this->key_file_location)) {
            $this->response .= "Key file not found. ";
            return true;
        }
        $this->client = new \Google_Client();
        $this->client->setApplicationName("Stackr");
        $this->client->setAuthConfig($this->key_file_location);

        // $client->setScopes(array('https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/userinfo.profile'));

        $this->client->setScopes([
            "https://www.googleapis.com/auth/userinfo.profile",
        ]);

        $redirect_uri = $this->web_prefix . "googleauthorize";

        if (isset($this->code)) {
            $token = $this->client->fetchAccessTokenWithAuthCode($this->code);

            if (isset($token["error"])) {
                $this->response .= $token["error"] . " ";
            }

            if (isset($token["error_description"])) {
                $this->response .= $token["error_description"] . " ";
            }

            // store in the session also
            $_SESSION["id_token_token"] = $token;

            // redirect back to the example
            header(
                "Location: " . filter_var($redirect_uri, FILTER_SANITIZE_URL)
            );
            return;
        }

        $access_type = "online";
        if ($this->channel_name == "console") {
            $access_type = "offline";
        }

        $this->client->setAccessType($access_type);

        $this->client->setRedirectUri($redirect_uri);

        if (isset($this->access_token)) {
            $accessToken = $this->access_token;
        }

        //        if ((!isset($this->access_token)) or ($this->reset_flag == true)) {

        // Request authorization from the user.

        $authUrl = $this->client->createAuthUrl();

        $this->authUrl = $authUrl;

        if ($this->channel_name == "console") {
            printf("Open this link in your browser:\n%s\n", $authUrl);
            print "Enter verification code: ";
            $authCode = trim(fgets(STDIN));
        }

        // Exchange authorization code for an access token.
        if (isset($authCode)) {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode(
                $authCode
            );
            $this->client->setAccessToken($accessToken);
        }
    }

    public function makeSMS()
    {
        $sms = strtoupper($this->agent_name);
        $sms .= " " . $this->response;

        if (isset($this->authUrl)) {
            $sms .= $this->authUrl . " ";
        } 
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeWeb()
    {
        $web = "Authorizes the Service.";
        if (isset($this->authUrl)) {
            $web .= "Click here to authorize our Service: ";

            //$authUrl = filter_var($this->authUrl, FILTER_SANITIZE_URL);
            $authUrl = $this->authUrl;

            $link = "<a href=" . $authUrl . ">" . $authUrl . "</a>";
            //$authUrl = filter_var($this->authUrl, FILTER_SANITIZE_URL);

            //$web .= '<pre>';
            //$web .= $this->authUrl;
            //$web .= '</pre>';

            //$web .= "<a href=\"'. $this->authUrl. "\">Google Authorize</a>";
            $web .= "<div>" . $link . "</div>";
            $web .= $this->response;
        }

        //$web = "merp";
        $this->thing_report["web"] = $web;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["choices"] = false;
        $this->thing_report["help"] = "In development.";
        $this->thing_report["log"] = $this->thing->log;
    }

    public function getChannel()
    {
        if (!isset($this->channel_name)) {
            $channel_agent = new Channel($this->thing, "channel");
            $this->channel_name = $channel_agent->channel_name;
        }
    }

    public function readSubject()
    {
        $filtered_input = $this->assert($this->input, "googleauthorize", false);
        $pieces = explode(" ", $filtered_input);
        if ($filtered_input !== "" and count($pieces) == 1) {
            $this->code = $filtered_input;
        }
        $this->getChannel();
        if ($this->input == "googleauthorize refresh") {
            $this->reset_flag = true;
        }
        //$this->getClient();
    }
}
