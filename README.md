#### Print as PDF

```php
// navigate
$navigation = $page->navigate('http://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();

$options = [
    'landscape'           => true,             // default to false
    'printBackground'     => true,             // default to false
    'displayHeaderFooter' => true,             // default to false
    'preferCSSPageSize'   => true,             // default to false (reads parameters directly from @page)
    'marginTop'           => 0.0,              // defaults to ~0.4 (must be a float, value in inches)
    'marginBottom'        => 1.4,              // defaults to ~0.4 (must be a float, value in inches)
    'marginLeft'          => 5.0,              // defaults to ~0.4 (must be a float, value in inches)
    'marginRight'         => 1.0,              // defaults to ~0.4 (must be a float, value in inches)
    'paperWidth'          => 6.0,              // defaults to 8.5 (must be a float, value in inches)
    'paperHeight'         => 6.0,              // defaults to 11.0 (must be a float, value in inches)
    'headerTemplate'      => '<div>foo</div>', // see details above
    'footerTemplate'      => '<div>foo</div>', // see details above
    'scale'               => 1.2,              // defaults to 1.0 (must be a float)
];

// print as pdf (in memory binaries)
$pdf = $page->pdf($options);

// save the pdf
$pdf->saveToFile('/some/place/file.pdf');

// or directly output pdf without saving
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename=filename.pdf');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

echo base64_decode($pdf->getBase64());
```

Options `headerTemplate` and `footerTemplate`:

Should be valid HTML markup with the following classes used to inject printing values into them:
- date: formatted print date
- title: document title
- url: document location
- pageNumber: current page number
- totalPages: total pages in the document

### Save downloads

You can set the path to save downloaded files.

```php
// After creating a page.
$page->setDownloadPath('/path/to/save/downloaded/files');
```

### Mouse API

The mouse API is dependent on the page instance and allows you to control the mouse's moves, clicks and scroll.

```php
$page->mouse()
    ->move(10, 20)                             // Moves mouse to position x=10; y=20
    ->click()                                  // left-click on position set above
    ->move(100, 200, ['steps' => 5])           // move mouse to x=100; y=200 in 5 equal steps
    ->click(['button' => Mouse::BUTTON_RIGHT]; // right-click on position set above

// given the last click was on a link, the next step will wait
// for the page to load after the link was clicked
$page->waitForReload();
```

You can emulate the mouse wheel to scroll up and down in a page, frame, or element.

```php
$page->mouse()
    ->scrollDown(100) // scroll down 100px
    ->scrollUp(50);   // scroll up 50px
```

#### Finding elements

The `find` method will search for elements using [querySelector](https://developer.mozilla.org/docs/Web/API/Document/querySelector) and move the cursor to a random position over it.

```php
try {
    $page->mouse()->find('#a')->click(); // find and click at an element with id "a"

    $page->mouse()->find('.a', 10); // find the 10th or last element with class "a"
} catch (ElementNotFoundException $exception) {
    // element not found
}
```

This method will attempt to scroll right and down to bring the element to the visible screen. If the element is inside an internal scrollable section, try moving the mouse to inside that section first.

### Keyboard API

The keyboard API is dependent on the page instance and allows you to type like a real user.

```php
$page->keyboard()
    ->typeRawKey('Tab') // type a raw key, such as Tab
    ->typeText('bar');  // type the text "bar"
```

To impersonate a real user you may want to add a delay between each keystroke using the ```setKeyInterval``` method:

```php
$page->keyboard()->setKeyInterval(10); // sets a delay of 10 milliseconds between keystrokes
```

#### Key combinations

The methods `press`, `type`, and `release` can be used to send key combinations such as `ctrl + v`.

```php
// ctrl + a to select all text
$page->keyboard()
    ->press('control') // key names are case insensitive and trimmed
        ->type('a')    // press and release
    ->release('Control');

// ctrl + c to copy and ctrl + v to paste it twice
$page->keyboard()
    ->press('Ctrl') // alias for Control
        ->type('c')
        ->type('V') // upper and lower cases should behave the same way
    ->release();    // release all
```

You can press the same key several times in sequence, this is the equivalent to a user pressing and holding the key. The release event, however, will be sent only once per key.

#### Key aliases

| Key     | Aliases                  |
|---------|--------------------------|
| Control | `Control`, `Ctrl`, `Ctr` |
| Alt     | `Alt`, `AltGr`, `Alt Gr` |
| Meta    | `Meta`, `Command`, `Cmd` |
| Shift   | `Shift`                  |

### Cookie API

You can set and get cookies for a page:

#### Set Cookie

```php
use HeadlessChromium\Cookies\Cookie;

$page = $browser->createPage();

// example 1: set cookies for a given domain

$page->setCookies([
    Cookie::create('name', 'value', [
        'domain' => 'example.com',
        'expires' => time() + 3600 // expires in 1 hour
    ])
])->await();


// example 2: set cookies for the current page

$page->navigate('http://example.com')->waitForNavigation();

$page->setCookies([
    Cookie::create('name', 'value', ['expires'])
])->await();
```

#### Get Cookies

```php
use HeadlessChromium\Cookies\Cookie;

$page = $browser->createPage();

// example 1: get all cookies for the browser

$cookies = $page->getAllCookies();

// example 2: get cookies for the current page

$page->navigate('http://example.com')->waitForNavigation();
$cookies = $page->getCookies();

// filter cookies with name == 'foo'
$cookiesFoo = $cookies->filterBy('name', 'foo');

// find first cookie with name == 'bar'
$cookieBar = $cookies->findOneBy('name', 'bar');
if ($cookieBar) {
    // do something
}
```

### Set user agent

You can set up a user-agent per page:

```php
$page->setUserAgent('my user-agent');
```

See also BrowserFactory option ``userAgent`` to set up it for the whole browser.


Advanced usage
--------------

The library ships with tools that hide all the communication logic but you can use the tools used internally to
communicate directly with chrome debug protocol.

Example:

```php
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Communication\Message;

// chrome devtools URI
$webSocketUri = 'ws://127.0.0.1:9222/devtools/browser/xxx';

// create a connection
$connection = new Connection($webSocketUri);
$connection->connect();

// send method "Target.activateTarget"
$responseReader = $connection->sendMessage(new Message('Target.activateTarget', ['targetId' => 'xxx']));

// wait up to 1000ms for a response
$response = $responseReader->waitForResponse(1000);
```

### Create a session and send a message to the target

```php
// given a target id
$targetId = 'yyy';

// create a session for this target (attachToTarget)
$session = $connection->createSession($targetId);

// send message to this target (Target.sendMessageToTarget)
$response = $session->sendMessageSync(new Message('Page.reload'));
```

### Debugging

You can ease the debugging by setting a delay before each operation is made:

```php
  $connection->setConnectionDelay(500); // wait for 500ms between each operation to ease debugging
```

### Browser (standalone)

```php
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Browser;

// chrome devtools URI
$webSocketUri = 'ws://127.0.0.1:9222/devtools/browser/xxx';

// create connection given a WebSocket URI
$connection = new Connection($webSocketUri);
$connection->connect();

// create browser
$browser = new Browser($connection);
```

### Interacting with DOM

Find one element on a page by CSS selector:

```php
$page = $browser->createPage();
$page->navigate('http://example.com')->waitForNavigation();

$elem = $page->dom()->querySelector('#index_email');
```

Find all elements inside another element by CSS selector:

```php
$elem = $page->dom()->querySelector('#index_email');
$elem->querySelectorAll('a.link');
```

Find all elements on a page by XPath selector:

```php
$page = $browser->createPage();
$page->navigate('http://example.com')->waitForNavigation();

$elem = $page->dom()->search('//div/*/a');
```

You can send out a text to an element or click on it:

```php
$elem->click();
$elem->sendKeys('Sample text');
```

You can upload file to file from the input:

```php
$elem->sendFile('/path/to/file');
```

You can get element text or attribute:

```php
$text = $elem->getText();
$attr = $elem->getAttribute('class');
```


## Contributing

See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for contribution details.


## License

This project is licensed under the [The MIT License (MIT)](LICENSE).

# flosman.ru-chrome-php-
