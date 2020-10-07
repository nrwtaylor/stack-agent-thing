<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Payment extends Agent
{
    public function init()
    {
        // So I could call
        $name = 'red token';
        if (isset($this->thing->container['api']['payment'])) {
            $items = $this->thing->container['api']['payment'];
        }

        $this->items = $items;

        if (count($this->items) != 1) {
            $this->response = true;
        }

        $item = array_pop($this->items);

        //$this->link = $item['link'];
        $this->link =
            $this->web_prefix . 'thing/' . $this->thing->uuid . '/payment';

        $this->price = $item['price'];
        $this->text = $item['text'];

        $this->node_list = ["payment" => ["forget"]];
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';
    }

    public function itemPayment($item = null)
    {
        if (isset($this->item) and $item == null) {
            return $this->item;
        }
        if ($item != null) {
            $this->item = $item;
            return $this->item;
        }

        $item_agent = new Item($this->thing, "item");
        $this->item = $item_agent->item;

        return $this->item;
    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables payment " . $this->from
        );
        $this->current_time = $this->thing->time();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "payment",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["payment", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);
    }

    public function set()
    {
    }

    public function readPayment($text = null)
    {
        if ($text == null) {
            return true;
        }

        if (strtolower($text) == "payment") {
            return false;
        }

        return $text;
    }

    public function getPayment()
    {
    }

    public function newPayment()
    {
    }

    public function imaginePayment()
    {
        new Thought($this->thing, "payment");
    }

    public function makeSMS()
    {
        $link = $this->link;
        $text = $this->text;
        $link_text = $link . " " . $text . " ";

        $sms = "PAYMENT | " . $link_text . "" . $this->response;

        //        }

        //$sms .= " | id " . $this->id;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    private function svgPayment($rule)
    {
        $suits = [
            "emergency" => "H",
            "priority" => "C",
            "routine" => "S",
            "welfare" => "D",
        ];
    }

    public function makeWeb()
    {
        $link = $this->link;
        $text = $this->text;
        $html_link = '<a href="' . $link . '">' . $text . '</a>';

        $web = "";
        $web .= "<p>";
        $web .= "Click on the link below to send payment.";
        $web .= "<p>";

        //$web .= "<p>";
        //$web .= $html_link;
        $web .= "<p>";
        if (!isset($this->snippet)) {
            $this->makeSnippet();
        }
        $web .= $this->snippet;
        $this->thing_report['web'] = $web;
    }

    public function makeSnippet()
    {
        //        $link = $this->link;
        //        $text = $this->text;
        //        $html_link = '<a href="' . $link . '">' . $text . '</a>';

        //        $web = $html_link;

//        if (!isset($this->item)) {
//            $this->itemPayment();
//        }

        $stripe_agent = new Stripe($this->thing, "stripe");
        //$stripe_agent->itemStripe($this->item);
        $stripe_agent->makeSnippet();
        $web = $stripe_agent->snippet;

        //if (!isset($this->item)) {
        //    $this->itemPayment();
       // }
$item_agent = new Item($this->thing,"item");
$this->item = $item_agent->item;

        $item_web = "<div>";
        $item_web .= "Item: ";
        $item_web .= $this->item['text'] . " ";
        $item_web .= $this->item['price'] . " ";
        $item_web .= "</div>";
        //$web .= $item_web;

        $this->snippet = $item_web . $web;
        $this->thing_report['snippet'] = $web;
    }

    public function makeEmail()
    {
        $message = "It is still playing.\n\n";

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "rule"
        );
        $this->choices = $this->thing->choice->makeLinks('rule');
        $this->thing_report['choices'] = $this->choices;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            $this->agent_prefix . 'responding to the word payment.';
    }

    public function readSubject()
    {
    }

    public function payment()
    {
        $this->getPayment();
    }
}
