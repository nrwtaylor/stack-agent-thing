<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Authenticategoogle extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->test = "Development code";

        $this->api_key = $this->thing->container["api"]["google"]["API key"];

        $this->client_id =
            $this->thing->container["api"]["google"]["client ID"];
        $this->client_secret =
            $this->thing->container["api"]["google"]["client secret"];

        $client = new Google_Client();
        $client->setDeveloperKey($this->api_key);

        $client->setAccessType("online"); // default: offline
        $client->setApplicationName("Stackr");
        $client->setClientId($this->client_id);
        $client->setClientSecret($this->client_secret);
        //$client->setRedirectUri($scriptUri);
        //$client->setDeveloperKey('INSERT HERE'); // API key

        $this->node_list = [
            "authenticate request" => [
                "authenticate verify" => ["authenticate request"],
            ],
        ];

        $this->response .= "Start state is ";
        $this->state = $thing->choice->load("token"); //this might cause problems

        $this->response .= "Start state is " . $this->state . ". ";

        $this->thing->account["thing"]->Debit(10);
    }
    public function set()
    {
        $this->thing->choice->Choose($this->state);
        $this->state = $thing->choice->load("token");
        //echo $this->thing->getState('usermanager');
        $this->response .= "End state is " . $this->state . ". ";
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = $this->thing->choice->makeLinks($this->state);

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
        if ($this->state == null) {
            echo "authenticate detected state null - run subject discriminator";

            switch ($this->subject) {
                case "authenticate request":
                    $this->create();
                    break;
                case "authenticate verify":
                    break;

                default:
                    $this->create();
            }
        }

        $this->state = $this->thing->choice->load("authenticate");

        // Will need to develop this to only only valid state changes.

        switch ($this->state) {
            case "authenticate request":
                $this->authenticateRequest();

                break;
            case "authenticate verify":
                //$this->kill();
                break;

            default:
                echo "not found";
        }

        $this->thing->choice->Create(
            "authenticate",
            $this->node_list,
            $this->state
        );

        return false;
    }

    function authenticateRequest()
    {
        $this->response .= "Send SMS? ";
        //$this->sendSMS();
        //return;
    }

    function create()
    {
        // devstack
        $ant_pheromone["stack"] = 4;

        if (rand(0, 5) + 1 <= $ant_pheromone["stack"]) {
            $this->thing->choice->Create(
                "token",
                $this->node_list,
                "authenticate request"
            );
        } else {
            $this->thing->choice->Create(
                "token",
                $this->node_list,
                "authenticate request"
            );
        }

        $this->thing->flagGreen();
    }
}
