Moosend tracking library

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
$cookie = new Moosend\Cookie();
$payload = new Moosend\Payload(new Moosend\Cookie(), new Sinergi\BrowserDetector\Language());
$client = new Client([
    'base_uri' => Moosend\API::ENDPOINT
]);

$tracker = new Moosend\Tracker($cookie, $payload, $client);

$tracker->init('site-id');
~~~~

There is another alternative, by using the function called `track()` which creates the instance for you.

~~~~
$tracker = track();
~~~~

You have to make sure that **index.php** is included somewhere on your code base in order to make this work.

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
$order->addProduct('itemCode', 'itemPrice', 'itemUrl', 'itemName', 'itemImage', $props);

$tracker->orderCompleted($order);
~~~~