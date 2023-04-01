# Chrome PHP

[![Latest Stable Version](https://poser.pugx.org/chrome-php/chrome/version)](https://packagist.org/packages/chrome-php/chrome)
[![License](https://poser.pugx.org/chrome-php/chrome/license)](https://packagist.org/packages/chrome-php/chrome)

Эта библиотека позволяет поиграться с chrome/chromium в headless режиме с PHP.

Может использоваться синхронно и асинхронно!


## Характеристики

- Откройте chrome браузер или другой на базе chromium из php
- Открывайте страницы и переходите по ним
- Делайте скриншоты
- Оцените javascript на странице
- Создавайте PDF слепки страниц
- Эмулируйте мышь
- Эмулируйте клавиатуру
- Всегда дружественный к IDE

Приятного просмотра!


## Требования

Требуется PHP 7.4-8.2 и исполняемый файл chrome / chromium 65+.

Обратите внимание, что библиотека тестируется только в Linux, но совместима с macOS и Windows.


## Установка

Библиотека может быть установлена с Composer и доступна на packagist
[chrome-php/chrome](https://packagist.org/packages/chrome-php/chrome):

```bash
$ composer require chrome-php/chrome
```


## Использование

Он использует простой и понятный API для запуска Chrome, открытия страниц, создания скриншотов, обхода веб-сайтов... и почти все, что вы можете сделать с Chrome как человек.
```php
use HeadlessChromium\BrowserFactory;

$browserFactory = new BrowserFactory();

// starts headless chrome
$browser = $browserFactory->createBrowser();

try {
    // creates a new page and navigate to an URL
    $page = $browser->createPage();
    $page->navigate('http://example.com')->waitForNavigation();

    // get page title
    $pageTitle = $page->evaluate('document.title')->getReturnValue();

    // screenshot - Say "Cheese"! 😄
    $page->screenshot()->saveToFile('/foo/bar.png');

    // pdf
    $page->pdf(['printBackground' => false])->saveToFile('/foo/bar.pdf');
} finally {
    // bye
    $browser->close();
}
```

### Использование другого исполняемого файла Chrome

При запуске фабрика будет искать переменную среды "CHROME_PATH" для использования в качестве исполняемого файла Chrome. Если переменная не найдена, она попытается угадать правильный путь к исполняемому файлу в соответствии с вашей операционной системой или использовать "chrome" по умолчанию.

Вы также можете явно настроить любой исполняемый файл по вашему выбору при создании нового объекта. Например ``"chromium-browser"``:

```php
use HeadlessChromium\BrowserFactory;

// replace default 'chrome' with 'chromium-browser'
$browserFactory = new BrowserFactory('chromium-browser');
```

### Отладка

Следующий пример отключает headless режим для облегчения отладки

```php
use HeadlessChromium\BrowserFactory;

$browserFactory = new BrowserFactory();

$browser = $browserFactory->createBrowser([
    'headless' => false, // disable headless mode
]);
```

Другие параметры отладки:

```php
[
    'connectionDelay' => 0.8,            // add 0.8 second of delay between each instruction sent to chrome,
    'debugLogger'     => 'php://stdout', // will enable verbose mode
]
```

О ``debugLogger``: это может быть любая строка ресурса, ресурс или объект, реализуемый 
``LoggerInterface`` из Psr\Log (such as [monolog](https://github.com/Seldaek/monolog)
or [apix/log](https://github.com/apix/log)).


## API

### Фабрика браузеров

Параметры, установленные непосредственно в `createBrowser` методе, будут использоваться только для создания одного браузера. Параметры по умолчанию будут проигнорированы.

```php
use HeadlessChromium\BrowserFactory;

$browserFactory = new BrowserFactory();
$browser = $browserFactory->createBrowser([
    'windowSize'   => [1920, 1000],
    'enableImages' => false,
]);

// this browser will be created without any options
$browser2 = $browserFactory->createBrowser();
```

Параметры, установленные с помощью методов `setOptions` и `addOptions` будут сохранены.

```php
$browserFactory->setOptions([
    'windowSize' => [1920, 1000],
]);

// both browser will have the same 'windowSize' option
$browser1 = $browserFactory->createBrowser();
$browser2 = $browserFactory->createBrowser();

$browserFactory->addOptions(['enableImages' => false]);

// this browser will have both the 'windowSize' and 'enableImages' options
$browser3 = $browserFactory->createBrowser();

$browserFactory->addOptions(['enableImages' => true]);

// this browser will have the previous 'windowSize', but 'enableImages' will be true
$browser4 = $browserFactory->createBrowser();
```

#### Доступные опции

Вот параметры, доступные для браузера factory:

| Название опции            | По умолчанию | Описание                                                                                                                     |
|---------------------------|--------------|------------------------------------------------------------------------------------------------------------------------------|
| `connectionDelay`         | `0`          | Задержка, применяемая между каждой операцией в целях отладки                                                                 |
| `customFlags`             | none         | Массив флагов для передачи в командную строку. Например: `['--option1', '--option2=someValue']`.                             |
| `debugLogger`             | `null`       | Строка (e.g "php://stdout"), или ресурс, или PSR-3 logger instance для печати отладочных сообщений.                          |
| `enableImages`            | `true`       | Переключает загрузку изображений.                                                                                            |
| `envVariables`            | none         | Массив переменных среды для передачи процессу (пример отображаемой переменной).                                              |
| `headers`                 | none         | Массив пользовательских HTTP-заголовков.                                                                                     |
| `headless`                | `true`       | Включить или отключить headless режим.                                                                                       |
| `ignoreCertificateErrors` | `false`      | Установите Chrome так, чтобы он игнорировал ошибки ssl.                                                                      |
| `keepAlive`               | `false`      | Установите значение `true` чтобы поддерживать экземпляр Chrome в рабочем состоянии при завершении работы скрипта.            |
| `noSandbox`               | `false`      | Включить режим без изолированной среды, полезный для запуска в контейнере docker.                                            |
| `noProxyServer`           | `false`      | Не используйте прокси-сервер, всегда устанавливайте прямые подключения. Переопределяет другие настройки прокси.              |
| `proxyBypassList`         | none         | Задает список хостов, для которых мы обходим настройки прокси и используем прямые подключения.                               |
| `proxyServer`             | none         | Прокси-сервер для использования. использование: `127.0.0.1:8080` (авторизация с использованием учетных данных не работает).  |
| `sendSyncDefaultTimeout`  | `5000`       | Время ожидания по умолчанию (мс) для отправки сообщений синхронизации.                                                       |
| `startupTimeout`          | `30`         | Максимальное время ожидания запуска Chrome в секундах.                                                                       |
| `userAgent`               | none         | Пользовательский агент для использования во всем браузере (альтернативный вариант см. в page API).                           |
| `userDataDir`             | none         | Каталог пользовательских данных Chrome (по умолчанию: временно создается новый пустой каталог).                              |
| `userCrashDumpsDir`       | none         | Каталог, в котором crashpad должен хранить дампы (crash reporter будет включен автоматически).                               |
| `windowSize`              | none         | Размер окна. использование: $width, $height - смотрите также Страницу::setViewport.                                          |


### Постоянный браузер

В этом примере показано, как совместно использовать один экземпляр Chrome для нескольких сценариев.

При первом запуске скрипта мы используем фабрику браузера для запуска Chrome, после чего сохраняем uri для подключения к этому браузеру в файловой системе.

Следующие вызовы скрипта будут считывать uri из этого файла, чтобы подключиться к экземпляру Chrome вместо создания нового. Если chrome был закрыт или произошел сбой, новый экземпляр запускается снова.

```php
use \HeadlessChromium\BrowserFactory;
use \HeadlessChromium\Exception\BrowserConnectionFailed;

// path to the file to store websocket's uri
$socket = \file_get_contents('/tmp/chrome-php-demo-socket');

try {
    $browser = BrowserFactory::connectToBrowser($socket);
} catch (BrowserConnectionFailed $e) {
    // The browser was probably closed, start it again
    $factory = new BrowserFactory();
    $browser = $factory->createBrowser([
        'keepAlive' => true,
    ]);

    // save the uri to be able to connect again to browser
    \file_put_contents($socketFile, $browser->getSocketUri(), LOCK_EX);
}
```

### API браузера

#### Создать новую страницу (tab)

```php
$page = $browser->createPage();
```

#### Получить открытые страницы (tabs)

```php
$pages = $browser->getPages();
```

#### Закрыть браузер

```php
$browser->close();
```

### Установите скрипт для оценки перед переходом на каждую страницу, созданную этим браузером

```php
$browser->setPagePreScript('// Simulate navigator permissions;
const originalQuery = window.navigator.permissions.query;
window.navigator.permissions.query = (parameters) => (
    parameters.name === 'notifications' ?
        Promise.resolve({ state: Notification.permission }) :
        originalQuery(parameters)
);');
```

### Page API

#### Navigate to an URL

```php
// navigate
$navigation = $page->navigate('http://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();
```

When using ``$navigation->waitForNavigation()`` you will wait for 30sec until the page event "loaded" is triggered.
You can change the timeout or the event to listen for:

```php
use HeadlessChromium\Page;

// wait 10secs for the event "DOMContentLoaded" to be triggered
$navigation->waitForNavigation(Page::DOM_CONTENT_LOADED, 10000);
```

Available events (in the order they trigger):

- ``Page::DOM_CONTENT_LOADED``: dom has completely loaded
- ``Page::LOAD``: (default) page and all resources are loaded
- ``Page::NETWORK_IDLE``: page has loaded, and no network activity has occurred for at least 500ms

When you want to wait for the page to navigate 2 main issues may occur.
First, the page is too long to load and second, the page you were waiting to be loaded has been replaced.
The good news is that you can handle those issues using a good old try-catch:

```php
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\NavigationExpired;

try {
    $navigation->waitForNavigation()
} catch (OperationTimedOut $e) {
    // too long to load
} catch (NavigationExpired $e) {
    // An other page was loaded
}
```

#### Evaluate script on the page

Once the page has completed the navigation you can evaluate arbitrary script on this page:

```php
// navigate
$navigation = $page->navigate('http://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();

// evaluate script in the browser
$evaluation = $page->evaluate('document.documentElement.innerHTML');

// wait for the value to return and get it
$value = $evaluation->getReturnValue();
```


Sometimes the script you evaluate will click a link or submit a form, in this case, the page will reload and you
will want to wait for the new page to reload.

You can achieve this by using ``$page->evaluate('some js that will reload the page')->waitForPageReload()``.
An example is available in [form-submit.php](./examples/form-submit.php)

#### Call a function

This is an alternative to ``evaluate`` that allows calling a given function with the given arguments in the page context:

```php
$evaluation = $page->callFunction(
    "function(a, b) {\n    window.foo = a + b;\n}",
    [1, 2]
);

$value = $evaluation->getReturnValue();
```

#### Add a script tag

That's useful if you want to add jQuery (or anything else) to the page:

```php
$page->addScriptTag([
    'content' => file_get_contents('path/to/jquery.js')
])->waitForResponse();

$page->evaluate('$(".my.element").html()');
```

You can also use an URL to feed the src attribute:

```php
$page->addScriptTag([
    'url' => 'https://code.jquery.com/jquery-3.3.1.min.js'
])->waitForResponse();

$page->evaluate('$(".my.element").html()');
```

#### Set the page HTML

You can manually inject html to a page using the ```setHtml``` method.

```php
// Basic
$page->setHtml('<p>text</p>');

// Specific timeout & event
$page->setHtml('<p>text</p>', 10000, Page::NETWORK_IDLE);
```

When a page's HTML is updated, we'll wait for the page to unload. You can specify how long to wait and which event to wait for through two optional parameters. This defaults to 3000ms and the "load" event.

Note that this method will not append to the current page HTML, it will completely replace it.

#### Get the page HTML

You can get the page HTML as a string using the ```getHtml``` method.

```php
$html = $page->getHtml();
```

### Add a script to evaluate upon page navigation

```php
$page->addPreScript('// Simulate navigator permissions;
const originalQuery = window.navigator.permissions.query;
window.navigator.permissions.query = (parameters) => (
    parameters.name === 'notifications' ?
        Promise.resolve({ state: Notification.permission }) :
        originalQuery(parameters)
);');
```

If your script needs the dom to be fully populated before it runs then you can use the option "onLoad":

```php
$page->addPreScript($script, ['onLoad' => true]);
```

#### Set viewport size

This feature allows changing the size of the viewport (emulation) for the current page without affecting the size of
all the browser's pages (see also option ``"windowSize"`` of [BrowserFactory::createBrowser](#options)).

```php
$width = 600;
$height = 300;
$page->setViewport($width, $height)
    ->await(); // wait for the operation to complete
```

#### Make a screenshot

```php
// navigate
$navigation = $page->navigate('http://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();

// take a screenshot
$screenshot = $page->screenshot([
    'format'  => 'jpeg',  // default to 'png' - possible values: 'png', 'jpeg',
    'quality' => 80,      // only if format is 'jpeg' - default 100
]);

// save the screenshot
$screenshot->saveToFile('/some/place/file.jpg');
```

**Screenshot an area on a page**

You can use the option "clip" to choose an area on a page for the screenshot

```php
use HeadlessChromium\Clip;

// navigate
$navigation = $page->navigate('http://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();

// create a rectangle by specifying to left corner coordinates + width and height
$x = 10;
$y = 10;
$width = 100;
$height = 100;
$clip = new Clip($x, $y, $width, $height);

// take the screenshot (in memory binaries)
$screenshot = $page->screenshot([
    'clip'  => $clip,
]);

// save the screenshot
$screenshot->saveToFile('/some/place/file.jpg');
```

**Full-page screenshot**

You can also take a screenshot for the full-page layout (not only the viewport) using ``$page->getFullPageClip`` with attribute ``captureBeyondViewport = true``

```php
// navigate
$navigation = $page->navigate('https://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();

$screenshot = $page->screenshot([
    'captureBeyondViewport' => true,
    'clip' => $page->getFullPageClip(),
    'format' => 'jpeg', // default to 'png' - possible values: 'png', 'jpeg',
]);

// save the screenshot
$screenshot->saveToFile('/some/place/file.jpg');
```

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
