<?php
include_once 'simple_html_dom.php';


//Функция получения данных;
function getData($url = "", $mask = "", $count = 0, $arr_words = [], $i = 0)
{
  //Отправили запрос, получили ответ
  $response = file_get_contents($url);
  $myJson = json_decode($response, true);

  if ($myJson["count"]) {
    $count = $myJson["count"]; //Проверка на количество элементов в JSON; За раз выгружается 500
    var_dump("Записал в count: " . $count . "<br>\n");
  }

  $words  = array_merge($arr_words, $myJson["words"]); //Сливаю два массива
  var_dump("Текущий размер words: " . count($words) . "<br>\n");
  var_dump("Текущая иттерация: " . $i . "<br>\n");
  var_dump("Текущая count: " . $count . "<br>\n");

  if ($count - 500 > 0) {
    $i++;
    if ($i > 10) {
      return;
    }
    $count -= 500;
    var_dump("Текущая запрос: " . "https://poncy.ru/crossword/next-result-page.json" . $mask . "&desc=&page=" . $i . "<br>\n");
    sleep(5);
    getData("https://poncy.ru/crossword/next-result-page.json" . $mask . "&desc=&page=" . $i, $mask, $count, $words, $i);
  } else {
    return $words;
  }
};


//Тест функции загрузки данных; Забанило в итоге;
// $testABG = getData("https://poncy.ru/crossword/crossword-solve.jsn?mask=А---------", "?mask=A---------");
// file_put_contents("./data/testABG.json", $testABG); //Возможная уязвимость, т.к. работа с путем

// file_put_contents("./data/" . $jsonName, $response); //Возможная уязвимость, т.к. работа с путем; После формирования данных кэшируем их у себя;


//--------------------------------------------------------------------------


// Запросы Отправляю запрос за получение данных

// https://poncy.ru/crossword/crossword-solve.jsn?mask=%D0%90---------
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=1
// https://poncy.ru/crossword/next-result-page.json?mask=%D0%90---------&desc=&page=2

// $url = 'https://poncy.ru/crossword/';
// $url = "https://poncy.ru/crossword/crossword-solve.jsn?mask="
// $req = '?mask=%D0%B3----';
// $req = '?mask=А----';

// $url = 'https://poncy.ru/crossword/crossword-solve.jsn';
// $req = '?mask=%D0%90---------&desc=';

//--------------------------------------------------------------------------



//.





//---------------------------------------------------------------------------------
//Получаю данные в JSON, обрабатываю их и привожу к массиву;
// $myJson = json_decode($data1, true);
// $words1 = $myJson["words"];

// $words1 = json_decode($data1, true);
$words1[0] = json_decode(file_get_contents("./data/data1.1.json"), true);
$words1[1] = json_decode(file_get_contents("./data/data1.2.json"), true);
$words1[2] = json_decode(file_get_contents("./data/data1.3.json"), true);
$words2[0] = json_decode(file_get_contents("./data/data2.1.json"), true);
$words2[1] = json_decode(file_get_contents("./data/data2.2.json"), true);
$words2[2] = json_decode(file_get_contents("./data/data2.3.json"), true);
$words3[0] = json_decode(file_get_contents("./data/data3.1.json"), true);
$words3[1] = json_decode(file_get_contents("./data/data3.2.json"), true);
$words3[2] = json_decode(file_get_contents("./data/data3.3.json"), true);
$words4[0] = json_decode(file_get_contents("./data/data4.1.json"), true);
$words4[1] = json_decode(file_get_contents("./data/data4.2.json"), true);
$words4[2] = json_decode(file_get_contents("./data/data4.3.json"), true);
$words5[0] = json_decode(file_get_contents("./data/data5.1.json"), true);
$words5[1] = json_decode(file_get_contents("./data/data5.2.json"), true);
$words5[2] = json_decode(file_get_contents("./data/data5.3.json"), true);

$words = [$words1, $words2, $words3, $words4, $words5];



//Массив загаданных слов
// $encryptedWords = ["1153241526", "1656335361", "5424251322", "3655516563", "4213633456"];

//Перевожу их в массив символов, чтобы можно было работать с масками
$encryptedWords = [
  $word1 = preg_split("//u", "1153241526", -1, PREG_SPLIT_NO_EMPTY),
  $word2 = preg_split("//u", "1656335361", -1, PREG_SPLIT_NO_EMPTY),
  $word3 = preg_split("//u", "5424251322", -1, PREG_SPLIT_NO_EMPTY),
  $word4 = preg_split("//u", "3655516563", -1, PREG_SPLIT_NO_EMPTY),
  $word5 = preg_split("//u", "4213633456", -1, PREG_SPLIT_NO_EMPTY),
];

// $crypt = implode("", $encryptedWords[0]); // Собирает всю строку из массива симоволов
// $encryptedWords = preg_split("//u", "1153241526", -1, PREG_SPLIT_NO_EMPTY); // Разбирает всю строку на массив символов






//Массив заданных условием букв;
$claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ"];
// var_dump($claim[0]);




//TEST
var_dump("Это claim = " . $claim[0] . "<br>\n");
var_dump("Это encryptedWords = " . $encryptedWords . "<br>\n");
var_dump("Это encryptedWords[0] = " . $encryptedWords[0][0] . "<br>\n");

$resultWords = [[], [], [], [], []];


// foreach ($encryptedWords as $t => $encryptedWord) {
// var_dump("Иттерация: " . $t . "; Шифрованное слово: " . implode("", $encryptedWord) . "<br>\n");
// findMatches($encryptedWord[0], $encryptedWord);
// foreach (${$words . [$encryptedWord[0]]} as $c => $currentDataLetter) {
// var_dump($currentDataLetter);
// var_dump($c);

// }

findMatches("1", $encryptedWord[0]);


function findMatches($claimType, $encryptedWord = [])
{
  $claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ"];
  switch ($claimType) {
    case 1: {
        $resultWords = [];
        var_dump("claimType, говорит что равен 1; Правда? " . $claimType . "<br>\n");

        $words1[0] = (json_decode(file_get_contents("./data/data1.1.json"), true))["words"];
        $words1[1] = (json_decode(file_get_contents("./data/data1.2.json"), true))["words"];
        $words1[2] = (json_decode(file_get_contents("./data/data1.3.json"), true))["words"];

        foreach ($words1 as $i => $currentDataLetter) {
          foreach ($currentDataLetter as $word => $currentWord) {
            $currentLetters = preg_split("//u", $currentWord, -1, PREG_SPLIT_NO_EMPTY);
            var_dump("Текущее слово: " . $currentWord . "<br>\n");
            foreach ($currentLetters as $letter => $value) {

              //Проверка на 6 маску с неизвестной буквой
              if ($encryptedWord[(int)$letter] === "6") {
                var_dump("ПОДХОДИТ по маске 6 = " .  $encryptedWord[(int)$letter] . "<br>\n");
                //Проверка на тот случай, если 6 маска в конце слова
                if (!$currentLetters[$letter + 1]) {
                  //Записываем в результирующий массив
                  array_push($resultWords, $currentWord);
                  var_dump("СЛОВО " . $currentWord . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ" . "<br>\n");
                  var_dump("последний элемент" . "<br>\n");
                }
                continue;
              }

              if (strpos($claim[$encryptedWord[(int)$letter] - 1], $value) !== false) {
                var_dump("ПОДХОДИТ");
                var_dump("Маска: " . $encryptedWord[(int)$letter]);
                var_dump("Буквы: " . $claim[$encryptedWord[(int)$letter] - 1]);
                var_dump("Текущий элемент: " . $value . " ; letter =" . $letter . "; входит в " . $claim[$encryptedWord[(int)$letter] - 1] . "<br>\n");
              } else {
                var_dump("НЕТ ");
                var_dump("Маска: " . $encryptedWord[(int)$letter]);
                var_dump("Буквы: " . $claim[$encryptedWord[(int)$letter] - 1]);
                var_dump("Текущий элемент: " . $value . "; letter =" . $letter . "; не входит в " . $claim[$encryptedWord[(int)$letter] - 1] . "<br>\n");
                break;
              }

              if (!$currentLetters[$letter + 1]) {
                //Записываем в результирующий массив
                array_push($resultWords[0], $currentWord);
                var_dump("СЛОВО " . $currentWord . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ №" . $i . "<br>\n");
                var_dump("последний элемент" . "<br>\n");
              }
            }
          }
        }

        var_dump("Результирующий массив №" . count($resultWords));

        foreach ($resultWords as $e => $arr) {
          var_dump("Результирующий массив №" . $e + 1 . ": " . $arr);
          foreach ($arr as $j => $v) {
            var_dump($v);
          }
          var_dump("<br>\n");
        }
        break;
        return;
      }
    case 2:
      var_dump("claimType, говорит что равен 2; Правда? " . $claimType . "<br>\n");
      break;
    case 3:
      var_dump("claimType, говорит что равен 3; Правда? " . $claimType . "<br>\n");
      break;
    case 4:
      var_dump("claimType, говорит что равен 4; Правда? " . $claimType . "<br>\n");
      break;
    case 5:
      var_dump("claimType, говорит что равен 5; Правда? " . $claimType . "<br>\n");
      break;
  }
  return;
}

// for ($i = 0; $i < 6; $i++) {
//   // Начинаю обходить массив данных и ищу совпадения
//   foreach ($words[$i] as $word => $currentWord) {
//     //Привожу строку к массиву символов
//     var_dump($currentWord . "<br>\n");
//     $crypt = implode("", $encryptedWords[$i]);
//     var_dump($crypt . "<br>\n");
//     $currentLetters = preg_split("//u", $currentWord, -1, PREG_SPLIT_NO_EMPTY);
//     //Прохожу конкретное слово посивмольно и проверяю на вхождение в нужное множество
//     foreach ($currentLetters as $letter => $value) {
//       //Проверка на 6 маску с неизвестной буквой
//       if ($encryptedWords[$i][(int)$letter] === "6") {
//         var_dump("ПОДХОДИТ по маске 6 = " .  $encryptedWords[$i][(int)$letter] . "<br>\n");
//         //Проверка на тот случай, если 6 маска в конце слова
//         if (!$currentLetters[$letter + 1]) {
//           //Записываем в результирующий массив
//           array_push($resultWords, $currentWord);
//           var_dump("СЛОВО " . $currentWord . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ" . "<br>\n");
//           var_dump("последний элемент" . "<br>\n");
//         }
//         continue;
//       }
//       if (strpos($claim[$encryptedWords[$i][(int)$letter] - 1], $value) !== false) {
//         var_dump("ПОДХОДИТ");
//         var_dump("Маска: " . $encryptedWords[$i][(int)$letter]);
//         var_dump("Буквы: " . $claim[$encryptedWords[$i][(int)$letter] - 1]);
//         var_dump("Текущий элемент: " . $value . " ; letter =" . $letter . "; входит в " . $claim[$encryptedWords[$i][(int)$letter] - 1] . "<br>\n");
//       } else {
//         var_dump("НЕТ ");
//         var_dump("Маска: " . $encryptedWords[$i][(int)$letter]);
//         var_dump("Буквы: " . $claim[$encryptedWords[$i][(int)$letter] - 1]);
//         var_dump("Текущий элемент: " . $value . "; letter =" . $letter . "; не входит в " . $claim[$encryptedWords[$i][(int)$letter] - 1] . "<br>\n");
//         break;
//       }
//       if (!$currentLetters[$letter + 1]) {
//         //Записываем в результирующий массив
//         array_push($resultWords[$i], $currentWord);
//         var_dump("СЛОВО " . $currentWord . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ №" . $i . "<br>\n");
//         var_dump("последний элемент" . "<br>\n");
//       }
//     }
//   }
//   // array_push($resultWords, "kek");
//   var_dump("Результирующий массив №" . $i . ": " . $resultWords[$i]);
//   foreach ($resultWords[$i] as $j => $v) {
//     var_dump($v);
//   }
//   var_dump("<br>\n");
// }

// foreach ($resultWords as $e => $arr) {
//   var_dump("Результирующий массив №" . $e + 1 . ": " . $arr);
//   foreach ($arr as $j => $v) {
//     var_dump($v);
//   }
//   var_dump("<br>\n");
// }















// for ($i = 0; $i < 6; $i++) {
//   // Начинаю обходить массив данных и ищу совпадения
//   foreach ($words[$i] as $word => $currentWord) {
//     //Привожу строку к массиву символов
//     var_dump($currentWord . "<br>\n");
//     $crypt = implode("", $encryptedWords[$i]);
//     var_dump($crypt . "<br>\n");
//     $currentLetters = preg_split("//u", $currentWord, -1, PREG_SPLIT_NO_EMPTY);
//     //Прохожу конкретное слово посивмольно и проверяю на вхождение в нужное множество
//     foreach ($currentLetters as $letter => $value) {
//       //Проверка на 6 маску с неизвестной буквой
//       if ($encryptedWords[$i][(int)$letter] === "6") {
//         var_dump("ПОДХОДИТ по маске 6 = " .  $encryptedWords[$i][(int)$letter] . "<br>\n");
//         //Проверка на тот случай, если 6 маска в конце слова
//         if (!$currentLetters[$letter + 1]) {
//           //Записываем в результирующий массив
//           array_push($resultWords, $currentWord);
//           var_dump("СЛОВО " . $currentWord . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ" . "<br>\n");
//           var_dump("последний элемент" . "<br>\n");
//         }
//         continue;
//       }
//       if (strpos($claim[$encryptedWords[$i][(int)$letter] - 1], $value) !== false) {
//         var_dump("ПОДХОДИТ");
//         var_dump("Маска: " . $encryptedWords[$i][(int)$letter]);
//         var_dump("Буквы: " . $claim[$encryptedWords[$i][(int)$letter] - 1]);
//         var_dump("Текущий элемент: " . $value . " ; letter =" . $letter . "; входит в " . $claim[$encryptedWords[$i][(int)$letter] - 1] . "<br>\n");
//       } else {
//         var_dump("НЕТ ");
//         var_dump("Маска: " . $encryptedWords[$i][(int)$letter]);
//         var_dump("Буквы: " . $claim[$encryptedWords[$i][(int)$letter] - 1]);
//         var_dump("Текущий элемент: " . $value . "; letter =" . $letter . "; не входит в " . $claim[$encryptedWords[$i][(int)$letter] - 1] . "<br>\n");
//         break;
//       }
//       if (!$currentLetters[$letter + 1]) {
//         //Записываем в результирующий массив
//         array_push($resultWords[$i], $currentWord);
//         var_dump("СЛОВО " . $currentWord . " ДОБАВЛЕНО В РЕЗУЛЬТИРУЮЩИЙ МАССИВ №" . $i . "<br>\n");
//         var_dump("последний элемент" . "<br>\n");
//       }
//     }
//   }
//   // array_push($resultWords, "kek");
//   var_dump("Результирующий массив №" . $i . ": " . $resultWords[$i]);
//   foreach ($resultWords[$i] as $j => $v) {
//     var_dump($v);
//   }
//   var_dump("<br>\n");
// }

// foreach ($resultWords as $e => $arr) {
//   var_dump("Результирующий массив №" . $e + 1 . ": " . $arr);
//   foreach ($arr as $j => $v) {
//     var_dump($v);
//   }
//   var_dump("<br>\n");
// }
























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


// //Перевод строки в массив символов
// $arrStr = preg_split("//u", $current_word, -1, PREG_SPLIT_NO_EMPTY);
// // var_dump($arrStr . "<br>\n");
// // var_dump($current_word . "<br>\n");
// // $test = strlen($arrStr);
// $test = count($arrStr);
// // var_dump($test . "<br>\n");
// var_dump("<br>\n");
// var_dump("<br>\n");
// var_dump("<br>\n");


// //Перебор массива
// foreach ($arrStr as $char => $v) {
//   var_dump("Текущий элемент: " . $v . " Вот это char =" . $char);
//   var_dump("Следующий элемент: " . $arrStr[$char + 1] . " и номер у него char = " . $char + 1 . "<br>\n");
//   var_dump($char);
//   if (!$arrStr[$char + 1]) {
//     var_dump("последний элемент");
//   }
// }


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

//Из переменных сделать имя новой переменной
// $i15 = 15;
// $a = "data" . $i15;
// $$a = "test";
// var_dump("ВОООООООООООООООООООООООТ ЗДЕСЬ! " . $data15);



// Подготовил исходные данные;

// function dataMerge($dataName = [], $data1, $data2, $data3)
// {
//   $data_temp1 = file_get_contents("./data/" . $data1);
//   $data_temp2 = file_get_contents("./data/" . $data2);
//   $data_temp3 = file_get_contents("./data/" . $data3);
//   $JSON_temp1 = json_decode($data_temp1, true);
//   $JSON_temp2 = json_decode($data_temp2, true);
//   $JSON_temp3 = json_decode($data_temp3, true);
//   var_dump("Было символов в массиве 1: " . count($JSON_temp1["words"]));
//   var_dump("Было символов в массиве 2: " . count($JSON_temp2["words"]));
//   var_dump("Было символов в массиве 3: " . count($JSON_temp3["words"]));
//   $dataName  = array_merge($JSON_temp1["words"], $JSON_temp2["words"], $JSON_temp3["words"]); //Сливаю массивы в один
//   var_dump("Было символов в массиве " . $dataName . ": " . count($dataName) . "<br>\n");
//   return $dataName;
// }

// $dataTest = dataMerge($d, "data1.1.json", "data1.2.json", "data1.3.json");
// file_put_contents("data1.json", json_encode($dataTest)); //сохраняю себе
// $dataTest = dataMerge($d, "data2.1.json", "data2.2.json", "data2.3.json");
// file_put_contents("data2.json", json_encode($dataTest)); //сохраняю себе
// $dataTest = dataMerge($d, "data3.1.json", "data3.2.json", "data3.3.json");
// file_put_contents("data3.json", json_encode($dataTest)); //сохраняю себе
// $dataTest = dataMerge($d, "data4.1.json", "data4.2.json", "data4.3.json");
// file_put_contents("data4.json", json_encode($dataTest)); //сохраняю себе
// $dataTest = dataMerge($d, "data5.1.json", "data5.2.json", "data5.3.json");
// file_put_contents("data5.json", json_encode($dataTest)); //сохраняю себе

//Слияние нескольких массивов; Загрузка и выгрузка JSON
// $words2 = array_merge($words2, $words1); //Соединяет два массива и больше, если надо
// file_put_contents("./merge.json", json_encode($words2)); //Сохраняю массив к себе; Как бы кэш свой такой;
// $merge_test = file_get_contents("./merge.json"); //Загружаю из json ранее сохраненные данные 
// $words1_merge = json_decode($merge_test, true); // Готовый к работе объединенный массив
