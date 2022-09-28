<?php
include_once 'simple_html_dom.php';


// https://poncy.ru/crossword/crossword-solve.jsn?mask=%D0%90---------
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=1
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=2

// $url = 'https://poncy.ru/crossword/';
// $req = '?mask=%D0%B3----';
// $req = '?mask=А----';

$url = 'https://poncy.ru/crossword/crossword-solve.jsn';
$req = '?mask=%D0%90---------&desc=';

$wordsURL = $url . $req;
// echo $wordsURL;

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
// var_dump($words[0]);
$current_word = $words[0];
// var_dump(gettype($myJson));

// $html = str_get_html($response);


//Полученные данные
$data = [$data1, $data2, $data3, $data4, $data5];
// $data1 = file_get_contents("https://poncy.ru/crossword/crossword-solve.jsn");
$data1 = file_get_contents("./php.json");
$data2 = file_get_contents("./php2.json");
$data3 = file_get_contents("./php3.json");
$data4 = file_get_contents("./php4.json");
$data5 = file_get_contents("./php5.json");

file_put_contents("data1.json", $data1); //сохраняю себе


//Обрабатываю полученные данные и привожу к массиву
$myJson = json_decode($data1, true);
$words1 = $myJson["words"];


// Начинаю обходить массив данных и ищу совпадения
foreach ($words1 as $word => $letters) {
  //Привожу строку к массиву символов
  var_dump($letters . "<br>\n");
  $arrStr = preg_split("//u", $letters, -1, PREG_SPLIT_NO_EMPTY);

  foreach ($arrStr as $letter => $value) {
    var_dump("Текущий элемент: " . $value . " Вот это char =" . $letter . "<br>\n");
    if (!$arrStr[$char + 1]) {
      var_dump("последний элемент");
    }
  }
}



//Массив загаданных слов
$encryptedWords = ["1153241526", "1656335361", "5424251322", "3655516563", "4213633456"];

//Массив заданных условием букв;
$claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ"];
// var_dump($claim[0]);

//Берем первую букву;
$first_character = mb_substr($current_word, 0, 1);
// var_dump(mb_strlen($current_word));

//Проверка на вхождение символа в строку
if (strpos($claim[0], $first_character) !== false) {
  // var_dump($first_character . " входит в " . $claim[0]);
};



//Условие на проверку зашифрованного слова полное;


$firstEncryptedWord = $encryptedWords[0];
var_dump($firstEncryptedWord);
$arrIntStr = preg_split("//u", $firstEncryptedWord, -1, PREG_SPLIT_NO_EMPTY);
$firstEncryptedWordResult = [];

foreach ($arrIntStr as $i => $value) {
  var_dump((int)$value - 1);
}


//Беру массив данных по первому слову
// foreach ($)












//Добавить элемент в массив
// Способ 1
// $myArray[] = [1, 2, 3];
// array_push($myArray, 4, 5);
// array_push($myArray, [4, 5]);
// Способ 2
// $myArray[] = [4, 5];
// $myArray[] = 6;
// var_dump($myArray);

// Приведение типов
//$myTest = "123";//Строка
//int($myTest); //Число


//Перевод строки в массив символов
$arrStr = preg_split("//u", $current_word, -1, PREG_SPLIT_NO_EMPTY);
// var_dump($arrStr . "<br>\n");
// var_dump($current_word . "<br>\n");
// $test = strlen($arrStr);
$test = count($arrStr);
// var_dump($test . "<br>\n");
var_dump("<br>\n");
var_dump("<br>\n");
var_dump("<br>\n");


//Перебор массива
foreach ($arrStr as $char => $v) {
  var_dump("Текущий элемент: " . $v . " Вот это char =" . $char);
  var_dump("Следующий элемент: " . $arrStr[$char + 1] . " и номер у него char = " . $char + 1 . "<br>\n");
  var_dump($char);
  if (!$arrStr[$char + 1]) {
    var_dump("последний элемент");
  }
}
 


// Пример массива
// $arr = [1, 2, 3, 4, 5];
// var_dump($arr[0]);
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

//https://dwweb.ru/page/php/function/053_razbit_stroku_po_simvolam_php.html#paragraph_2

/* 
foreach($input as $key => $value) {
    $ret .= "$value";
    if (next($input)==true) $ret .= ",";
}

https://translated.turbopages.org/proxy_u/en-ru.ru.678cac81-63340895-097ad36d-74722d776562/https/stackoverflow.com/questions/665135/find-the-last-element-of-an-array-while-using-a-foreach-loop-in-php
*/