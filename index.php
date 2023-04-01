<?php /*/ Ядро /*/
//*/ 
require_once('vendor'.DIRECTORY_SEPARATOR.'autoload.php'); //*/
//*/ 
//*/ 
use HeadlessChromium\BrowserFactory; //*/
 //*/ 
 pa($browserFactory = new BrowserFactory()); //*/
$browserFactory = new BrowserFactory(); //*/
 //
 /*/ 
$browser = $browserFactory->createBrowser();
//
/* /

try {
 // creates a new page and navigate to an URL
    $page = $browser->createPage();
    pa($page);
    $page->navigate('http://ya.ru')->waitForNavigation();

 // get page title
    $pageTitle = $page->evaluate('document.title')->getReturnValue();
    pa($pageTitle);
 // screenshot - Say "Cheese"! 
    $page->screenshot()->saveToFile('bar.png');

 // pdf
    $page->pdf(['printBackground' => false])->saveToFile('bar.pdf');
} finally {
 // bye
    $browser-> close(); 
}
///*/
?>