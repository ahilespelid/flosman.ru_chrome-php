# flosman.ru_chrome-php
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

#### Переход по URL

```php
// navigate
$navigation = $page->navigate('http://example.com');

// wait for the page to be loaded
$navigation->waitForNavigation();
```

При использовании ``$navigation->waitForNavigation()`` вы будете ждать в течение 30 секунд, пока не сработает событие страницы "загружено".
Вы можете изменить время ожидания или событие для прослушивания:

```php
use HeadlessChromium\Page;

// wait 10secs for the event "DOMContentLoaded" to be triggered
$navigation->waitForNavigation(Page::DOM_CONTENT_LOADED, 10000);
```

Доступные события (в порядке их запуска):

- ``Page::DOM_CONTENT_LOADED``: DOM когда польностью загрузился
- ``Page::LOAD``: (по умолчанию) страница отрендирелась со всеми ресурсами
- ``Page::NETWORK_IDLE``: страница загружена, и в течение как минимум 500 мс не происходило никакой сетевой активности

Когда вы хотите дождаться перехода по странице, могут возникнуть 2 основные проблемы.
Во-первых, страница слишком длинная для загрузки, а во-вторых, страница, загрузки которой вы ожидали, была заменена.
Хорошая новость заключается в том, что вы можете справиться с этими проблемами, используя старый добрый try-catch:

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

#### Создайте скрипт на странице

После завершения загрузки страницы, вы можете инициировать произвольный скрипт на этой странице:

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

Иногда скрипт, который вы оцениваете, нажимает на ссылку или отправляет форму, в этом случае страница перезагружается, и вы
захотите дождаться перезагрузки новой страницы.

Вы можете дождаться загрузки с помощью ``$page->evaluate('здесь скрипт с перезагрузкой')->waitForPageReload()``.
Пример доступен в [form-submit.php](./examples/form-submit.php)

#### Вызов функций

Эта альтернатива ``evaluate``, которая позволяет вызывать заданную функцию с заданными аргументами в контексте страницы:

```php
$evaluation = $page->callFunction(
    "function(a, b) {\n    window.foo = a + b;\n}",
    [1, 2]
);

$value = $evaluation->getReturnValue();
```

#### Добавьте тег скрипта

Это полезно, если вы хотите добавить jQuery (или что-либо еще) на страницу:

```php
$page->addScriptTag([
    'content' => file_get_contents('path/to/jquery.js')
])->waitForResponse();

$page->evaluate('$(".my.element").html()');
```

Вы также можете использовать URL-адрес для передачи атрибута src:

```php
$page->addScriptTag([
    'url' => 'https://code.jquery.com/jquery-3.3.1.min.js'
])->waitForResponse();

$page->evaluate('$(".my.element").html()');
```

#### Добавьте HTML на страницу

Вы можете в ручную внести HTML с помощью метода ```setHtml```.

```php
// Basic
$page->setHtml('<p>text</p>');

// Specific timeout & event
$page->setHtml('<p>text</p>', 10000, Page::NETWORK_IDLE);
```

Когда HTML-код страницы обновляется, мы будем ждать загрузки страницы. Вы можете указать, как долго ждать и какого события ожидать, с помощью двух необязательных параметров. По умолчанию это значение равно 3000 мс и событию "загрузка".

Обратите внимание, что этот метод не будет добавляться к текущей странице HTML, он полностью заменит ее.

#### Взять HTML со страницы

Вы можете использовать этот метод ```getHtml```, для выбора HTML со страницы.

```php
$html = $page->getHtml();
```

### Добавьте скрипт для оценки при навигации по странице

```php
$page->addPreScript('// Simulate navigator permissions;
const originalQuery = window.navigator.permissions.query;
window.navigator.permissions.query = (parameters) => (
    parameters.name === 'notifications' ?
        Promise.resolve({ state: Notification.permission }) :
        originalQuery(parameters)
);');
```

Если вашему скрипту необходимо, чтобы DOM был полностью загружен перед его запуском, вы можете использовать опцию "onLoad".:

```php
$page->addPreScript($script, ['onLoad' => true]);
```

#### Установка размера viewport

Эта функция позволяет изменять размер области просмотра (эмуляция) для текущей страницы, не влияя на размер
всех страниц браузера (см. также опцию ``"windowSize"`` от [BrowserFactory::createBrowser](#options)).

```php
$width = 600;
$height = 300;
$page->setViewport($width, $height)
    ->await(); // wait for the operation to complete
```

#### Сделать screenshot

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

**Screenshot области страницы**

Вы можете использовать опцию "обрезать", чтобы выбрать область на странице для screenshot

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

**Полноразмерный screenshot**

Вы также можете сделать снимок экрана для макета всей страницы (не только viewport) используя ``$page->getFullPageClip`` с атрибутом ``captureBeyondViewport = true``

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