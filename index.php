<?php ///*/ Ядро ///*/
///*/ 
require_once('vendor'.DIRECTORY_SEPARATOR.'autoload.php'); ///*/
///*/
use HeadlessChromium\BrowserFactory; ///*/
///*/ 
pa($browserFactory = new BrowserFactory('google-chrome')); ///*/
///*/ pa(
$browser = $browserFactory->createBrowser(); ///*/); 
///*/
try {
 ///*/ creates a new page and navigate to an URL
    $page = $browser->createPage(); $page->navigate('https://yandex.ru/')->waitForNavigation(); ///*/
 ///*/ get page $pageTitle = $page->evaluate('document.title')->getReturnValue();
    $pageBody = $page->evaluate('document.documentElement.innerHTML')->getReturnValue(); pa($pageBody); ///*/
 ///*/ screenshot - Say "Cheese"! 
    $page->screenshot()->saveToFile('/var/www/www-root/data/www/flosman.ru/bar.png'); ///*/
 ///*/ pdf $page->pdf(['printBackground' => false])->saveToFile('bar.pdf'); ///*/
} finally {///*/ pa(['bye']); ///*/
    $browser-> close(); 
}
///*/
?>