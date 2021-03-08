<p align="center">
    <a href="https://travis-ci.org/moosend/website-tracking-php"><img src="https://travis-ci.org/moosend/website-tracking-php.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/moosend/website-tracking"><img src="https://poser.pugx.org/moosend/tracker/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/moosend/website-tracking"><img src="https://poser.pugx.org/moosend/tracker/license.svg" alt="License"></a>
</p>

## Moosend tracking library

#### Pulling dependencies
This project uses [composer](https://getcomposer.org/) for auto-loading and managing dependencies.

To install all dependencies run : `composer install` from your terminal / cmd, if everything goes fine you should see **/vendor** directory on root of this directory.

#### Running tests
This project relies on [phpspec](http://www.phpspec.net/en/latest/), an unit testing and BDD toolset. To run all tests type this on your terminal / cmd
	
~~~~
composer test
~~~~

#### Initialisation
Before you dive in with sending events you have to create an instance of Tracker first and perform initialisation. This is very important as it wont send any data to the server without a proper initialisation. The init. phase deals with some Cookies that determines if current user is a new visitor or a returned one.

~~~~
$trackerFactory = new Moosend\TrackerFactory();
$tracker = $trackerFactory->create($siteId, $requestUseragent, $requestIpAddress);

$tracker->init('site-id');
~~~~

There is another alternative, by using the function called `track()` which creates the instance for you.

~~~~
$tracker = track($siteId, $requestUseragent, $requestIpAddress);
~~~~

You have to make sure that **vendor/autoload.php** is included somewhere on your code base in order to make this work.

#### Sending events

~~~~
//identify
$tracker->identify('some@mail.com', 'John Doe', ['favourite-color' => 'blue']); //returns GuzzleHttp\Psr7\Response

//page view
$tracker->pageView('http://example.com');

//add to order
$tracker->addToOrder('itemCode', 'itemPrice', 'itemUrl', 'itemName', 'itemImage', $props);

//order completed
$order = $tracker->createOrder();

$order->addProduct('itemCode', 'itemPrice', 'itemUrl', 'itemName', 'itemImage', $props);
//add as many products as you want before tracking and order completed event
$order->addProduct('itemCode', 'itemPrice', 'itemUrl', 'itemName', 'itemImage', $props);

$tracker->orderCompleted($order);
~~~~

#### Add Subscription Forms
In order to use Moosend subscription forms feature, you have to append our JS library into your HTML body, preferably in HEAD

~~~~
//example how to embed JS snippet
<head>
    <?php echo $tracker->addSubscriptionForms($siteId); ?>
</head>
~~~~
