<?php

//echo "Watson says hi<br>";

require_once '/var/www/html/stackr.ca/agents/message.php';

class Shopify {
	

	public $var = 'hello';


    function __construct(Thing $thing) {



		// create container and configure it
		$settings = require '/var/www/html/stackr.ca/src/settings.php';
		$this->container = new \Slim\Container($settings);
		// create app instance
		$app = new \Slim\App($this->container);
		$this->container = $app->getContainer();
		$this->test= "Development code";


		$this->container['api'] = function ($c) {
			$db = $c['settings']['api'];
			return $db;
			};

		$this->api_key = $this->container['api']['watson'];



//		$thingy = $thing->thing;
		$this->thing = $thing;

                $this->thing_report['thing'] = $this->thing->thing;


		$this->retain_for = 24; // Retain for at least 24 hours.

	        $this->uuid = $thing->uuid;
        	$this->to = $thing->to;
        	$this->from = $thing->from;
        	$this->subject = $thing->subject;
		
		$this->sqlresponse = null;

		$this->thing->log ( '<pre> Agent "Shopify" running on Thing ' . $this->uuid . '</pre>' );
		$this->thing->log ( '<pre> Agent "Shopify" received this Thing "' .  $this->subject .  '"</pre>' );

		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.
		$this->readSubject();
		
		$this->respond();

		$this->thing->log( '<pre> Agent "Shopify" completed</pre>' );


		return;

		}


	function shopifyButtons() {


$html = "<div id='product-component-266443766d9'></div>
<script type="text/javascript">
/*<![CDATA[*/

(function () {
  var scriptURL = 'https://sdks.shopifycdn.com/buy-button/latest/buy-button-storefront.min.js';
  if (window.ShopifyBuy) {
    if (window.ShopifyBuy.UI) {
      ShopifyBuyInit();
    } else {
      loadScript();
    }
  } else {
    loadScript();
  }

  function loadScript() {
    var script = document.createElement('script');
    script.async = true;
    script.src = scriptURL;
    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(script);
    script.onload = ShopifyBuyInit;
  }

  function ShopifyBuyInit() {
    var client = ShopifyBuy.buildClient({
      domain: 'stackr.myshopify.com',
      apiKey: '8d1289e28622764c04a76baea097dda3',
      appId: '6',
    });

    ShopifyBuy.UI.onReady(client).then(function (ui) {
      ui.createComponent('product', {
        id: [9550854155],
        node: document.getElementById('product-component-266443766d9'),
        moneyFormat: '%24%7B%7Bamount%7D%7D',
        options: {
  "product": {
    "buttonDestination": "checkout",
    "variantId": "all",
    "contents": {
      "imgWithCarousel": false,
      "variantTitle": false,
      "description": false,
      "buttonWithQuantity": false,
      "quantity": false
    },
    "text": {
      "button": "BUY NOW"
    },
    "styles": {
      "product": {
        "text-align": "left",
        "@media (min-width: 601px)": {
          "max-width": "calc(25% - 20px)",
          "margin-left": "20px",
          "margin-bottom": "50px"
        }
      }
    }
  },
  "cart": {
    "contents": {
      "button": true
    },
    "styles": {
      "footer": {
        "background-color": "#ffffff"
      }
    }
  },
  "modalProduct": {
    "contents": {
      "img": false,
      "imgWithCarousel": true,
      "variantTitle": false,
      "buttonWithQuantity": true,
      "button": false,
      "quantity": false
    },
    "styles": {
      "product": {
        "@media (min-width: 601px)": {
          "max-width": "100%",
          "margin-left": "0px",
          "margin-bottom": "0px"
        }
      }
    }
  },
  "productSet": {
    "styles": {
      "products": {
        "@media (min-width: 601px)": {
          "margin-left": "-20px"
        }
      }
    }
  }
}
      });
    });
  }
})();
/*]]>*/
</script>
";


	}


// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "shopify";

		
		//echo "foo" .'<br>';				
		// Create a new Thing to keep track of
		// this response.
		//$thing = new Thing(null);
		//$thing->Create($from, $to, $this->subject);



//		$email = new Email($thing);

                $message_thing = new Message($this->thing, $this->thing_report);
                //$thing_report['info'] = 'SMS sent';


		$this->thing_report['info'] = $message_thing->thing_report['info'] ;

	
	//	$message = $this->readSubject();

	
		//$thing_report = array("agent"=>$from, "thing"=>$this->thing);

		return $this->thing_report;


	}



	public function readSubject() {



		//mail("nick@wildnomad.com","watson.php readSubject() run" ,"Test message");
		//echo "Hello";
		$this->response = "Shopify says hello";

		$this->sms_message = "WATSON | Says hello | REPLY QUESTION";
		$this->message = "Watson says hello";
		$this->keyword = "watson";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
                $this->thing_report['message'] = $this->message;
		$this->thing_report['email'] = $this->message;


		
		return $this->response;

	
	}






}




return;
