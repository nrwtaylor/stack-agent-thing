<?php
namespace Nrwtaylor\StackAgentThing;

class Shopify extends Agent {
	

	public $var = 'hello';


public function init() {

		$this->domain = $this->thing->container['api']['shopify']['domain'];
        $this->api_key = $this->thing->container['api']['shopify']['apiKey'];
        $this->app_id = $this->thing->container['api']['shopify']['appId'];

		$this->retain_for = 24; // Retain for at least 24 hours.

		}


	function shopifyButtons() {


$html = '<div id="product-component-266443766d9"></div>
<script type="text/javascript">
/*<![CDATA[*/

(function () {
  var scriptURL = "https://sdks.shopifycdn.com/buy-button/latest/buy-button-storefront.min.js";
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
    var script = document.createElement("script");
    script.async = true;
    script.src = scriptURL;
    (document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(script);
    script.onload = ShopifyBuyInit;
  }

  function ShopifyBuyInit() {
    var client = ShopifyBuy.buildClient({
      domain: ' . $this->domain . ',
      apiKey: ' . $this->api_key . ',
      appId: '. $this->app_id . ',
    });

    ShopifyBuy.UI.onReady(client).then(function (ui) {
      ui.createComponent("product", {
        id: [9550854155],
        node: document.getElementById("product-component-266443766d9"),
        moneyFormat: "%24%7B%7Bamount%7D%7D",
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
';


	}


// -----------------------

	public function respondResponse() {


		$this->thing->flagGreen();


                $message_thing = new Message($this->thing, $this->thing_report);
                //$thing_report['info'] = 'SMS sent';


		$this->thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;


	}



	public function readSubject() {



		//mail("nick@wildnomad.com","watson.php readSubject() run" ,"Test message");
		//echo "Hello";
		$this->response = "Shopify says hello";

		$this->sms_message = "SHOPIFY | Says hello | REPLY QUESTION";
		$this->message = "Shopify says hello";
		$this->keyword = "shopify";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
                $this->thing_report['message'] = $this->message;
		$this->thing_report['email'] = $this->message;

//		return $this->response;

	}

}
