<?php
include_once 'simple_html_dom.php';



function getData($url, $mask, $jsonName)
{
  $response = file_get_contents($url . $mask);
  file_put_contents("./data/" . $jsonName, $response); //Возможная уязвимость, т.к. работа с путем
  $myJson = json_decode($response, true);
  // if ($myJson["count"]) {
  //   $count = $myJson["count"]; //Проверка на количество элементов в JSON; За раз выгружается 500
  // }
  return $myJson["words"];
};



// Отправляю запрос за получение данных

// https://poncy.ru/crossword/crossword-solve.jsn?mask=%D0%90---------
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=1
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=2

// $url = 'https://poncy.ru/crossword/';
// $url = "https://poncy.ru/crossword/crossword-solve.jsn?mask="
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

if ($myJson["count"]) {
  $count = $myJson["count"];
  var_dump($count);
}

$words = $myJson["words"];
// var_dump($words[0]);
$current_word = $words[0];
// var_dump(gettype($myJson));

// $html = str_get_html($response);

//--------------------------------------------------------------



//Полученные данные
$data = [$data1, $data2, $data3, $data4, $data5];
// $data1 = file_get_contents("https://poncy.ru/crossword/crossword-solve.jsn");
$data1 = file_get_contents("./data1.json");
$data1_test = file_get_contents("./data1_test.json");
$data2 = file_get_contents("./php2.json");
$data3 = file_get_contents("./php3.json");
$data4 = file_get_contents("./php4.json");
$data5 = file_get_contents("./php5.json");

// file_put_contents("data1.json", $data1); //сохраняю себе

//---------------------------------------------------------------------------------

//Обрабатываю полученные данные и привожу к массиву
$myJson = json_decode($data1, true);
$words1 = $myJson["words"];


//Слияние нескольких массивов; Загрузка и выгрузка JSON
$words2 = array_merge($words2, $words1); //Соединяет два массива и больше, если надо
file_put_contents("./merge.json", json_encode($words2)); //Сохраняю массив к себе; Как бы кэш свой такой;
$merge_test = file_get_contents("./merge.json"); //Загружаю из json ранее сохраненные данные 
$words1_merge = json_decode($merge_test, true); // Готовый к работе объединенный массив



//Массив загаданных слов
// $encryptedWords = ["1153241526", "1656335361", "5424251322", "3655516563", "4213633456"];
//Перевожу их в массив символов
// $encryptedWords = [
//   preg_split("//u", "1153241526", -1, PREG_SPLIT_NO_EMPTY),
//   preg_split("//u", "1656335361", -1, PREG_SPLIT_NO_EMPTY),
//   preg_split("//u", "5424251322", -1, PREG_SPLIT_NO_EMPTY),
//   preg_split("//u", "3655516563", -1, PREG_SPLIT_NO_EMPTY),
//   preg_split("//u", "4213633456", -1, PREG_SPLIT_NO_EMPTY),
// ];

$encryptedWords = preg_split("//u", "1153241526", -1, PREG_SPLIT_NO_EMPTY);



// var_dump($encryptedWords);

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

// $firstEncryptedWord = $encryptedWords[0];
// var_dump($firstEncryptedWord);
// $arrIntStr = preg_split("//u", $firstEncryptedWord, -1, PREG_SPLIT_NO_EMPTY);
// $firstEncryptedWordResult = [];

// foreach ($arrIntStr as $i => $value) {
//   var_dump((int)$value - 1);
// }



//TEST
var_dump("Это claim1 = " . $claim[0] . "<br>\n");
var_dump("Это encryptedWords = " . $encryptedWords . "<br>\n");
var_dump("Это encryptedWords[0] = " . $encryptedWords[0] . "<br>\n");

$resultWords = [];



// Начинаю обходить массив данных и ищу совпадения
foreach ($words1 as $word => $letters) {
  //Привожу строку к массиву символов
  var_dump($letters . "<br>\n");
  var_dump("1153241526" . "<br>\n");
  $arrStr = preg_split("//u", $letters, -1, PREG_SPLIT_NO_EMPTY);
  //Прохожу конкретное слово посивмольно и проверяю на вхождение в нужное множество
  foreach ($arrStr as $letter => $value) {

    //Проверка на 6 маску с неизвестной буквой
    if ($encryptedWords[(int)$letter] === "6") {
      var_dump("ПОДХОДИТ по маске 6 = " .  $encryptedWords[(int)$letter] . "<br>\n");
      //Проверка на тот случай, если 6 маска в конце слова
      if (!$arrStr[$letter + 1]) {
        //Записываем в результирующий массив
        array_push($resultWords, $letters);
        var_dump("СЛОВО " . $letters . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ" . "<br>\n");
        var_dump("последний элемент" . "<br>\n");
      }
      continue;
    }
    if (strpos($claim[$encryptedWords[(int)$letter] - 1], $value) !== false) {
      var_dump("ПОДХОДИТ");
      var_dump("Маска: " . $encryptedWords[(int)$letter]);
      var_dump("Буквы: " . $claim[$encryptedWords[(int)$letter] - 1]);
      var_dump("Текущий элемент: " . $value . " ; letter =" . $letter . "; входит в " . $claim[$encryptedWords[(int)$letter] - 1] . "<br>\n");
    } else {
      var_dump("НЕТ ");
      var_dump("Маска: " . $encryptedWords[(int)$letter]);
      var_dump("Буквы: " . $claim[$encryptedWords[(int)$letter] - 1]);
      var_dump("Текущий элемент: " . $value . "; letter =" . $letter . "; не входит в " . $claim[$encryptedWords[(int)$letter] - 1] . "<br>\n");
      break;
    }
    if (!$arrStr[$letter + 1]) {
      //Записываем в результирующий массив
      array_push($resultWords, $letters);
      var_dump("СЛОВО " . $letters . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ" . "<br>\n");
      var_dump("последний элемент" . "<br>\n");
    }
  }
}

// array_push($resultWords, "kek");
var_dump("Результирующий массив: " . $resultWords);
foreach ($resultWords as $i => $v) {
  var_dump($v);
}







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


// Слияние массивов и JSON'ов;

//Обрабатываю полученные данные и привожу к массиву
// $myJson = json_decode($data1, true);
// $words1 = $myJson["words"];

// $myJson = json_decode($data1_test, true);
// $words2 = $myJson["words"];

// var_dump("Размер words1 " . count($words1));
// var_dump("Размер words2 " . count($words2));
// $words2 = array_merge($words2, $words1);
// var_dump("Слитый массив words2 " . $words2 . " Его размер: " . count($words2));

// // $myObj["words"] = $words2;
// // $myObj["count"] = 167;
// // file_put_contents("./merge.json", json_encode($myObj));
// file_put_contents("./merge.json", json_encode($words2));

// $merge_test = file_get_contents("./merge.json");
// $myNewJson = json_decode($merge_test, true);
// // $words1_test = $myNewJson["words"];
// $words1_test = json_decode($merge_test, true);

// var_dump("МОЙ НОВЫЙ МАССИВ: " . count($words1_test));



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