<?php

// $opts = array(
//   'http' =>
//   array(
//     'method'  => 'GET',
//     'header'  => "Content-Type: text/xml\r\n" .
//       "Authorization: Basic " . base64_encode("$https_user:$https_password") . "\r\n",
//     'content' => $body,
//     'timeout' => 60
//   )
// );

// $context  = stream_context_create($opts);
// $url = 'https://poncy.ru/crossword/?mask=%D0%B3----' . $https_server;
// $result = file_get_contents($url, false, $context, -1, 40000);

// echo $result;

include_once 'simple_html_dom.php';


// https://poncy.ru/crossword/crossword-solve.jsn?mask=%D0%90---------
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=1
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=2

// $url = 'https://poncy.ru/crossword/';
// $req = '?mask=%D0%B3----';
// $req = '?mask=А----';

$url = 'https://poncy.ru/crossword/crossword-solve.jsn';
$req = '?mask=%D0%90---------&desc=';

$words = $url . $req;

// echo $words;

// Вариант 1 (работает)

// var_dump($url . $req);
// $response = file_get_contents($url . $req);
// $response = file_get_contents($url . $req);
// $response = file_get_contents("./result.php");
$response = file_get_contents("./php.json");
// file_put_contents("php.json", $response);
// var_dump($response);
$myJson = json_decode($response, true);
// var_dump($myJson->{"words"});
// var_dump($myJson["words"]);

$words = $myJson["words"];
var_dump($words[1]);
// var_dump(gettype($myJson));



$html = str_get_html($response);

// Пример массива
$arr = [1, 2, 3, 4, 5];
var_dump($arr[0]);
// foreach ($arr as $value) {
//   var_dump($value);
// }

// $count = 0;
// foreach ($html->find('.result-item') as $post) {
//   echo ($post);
//   $count++;
// }
// echo $count;


// foreach ($html->find('.col-md-12') as $post) {
//   $img = $post->find('.result-item');
//   echo $img;
// };

//94.181.130.8



//https://www.youtube.com/watch?v=bQ25t9Qa4G8
// : SimpleHTMLDOM или DOMDocument 
// https://github.com/paquettg/php-html-parser
//https://simplehtmldom.sourceforge.io/docs/1.9/api/api/
//https://www.php.net/manual/ru/function.file-get-contents.php



//План
// 1. Получить массив
// 2. Получить строку этого массива 
// 3. Взять символ этой строки и проверить входит ли он в нужное множество
// 3.1 Если входит, то все ок, переходим к следущей букве, если нет, то переходим к следующей строке
// 3.2 Если следующей буквы нет, то записываем строку в массив результатов 
