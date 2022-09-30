<?php
include_once 'simple_html_dom.php';


//Функция получения данных;
//Рекурсивная функция, которая собирает json в один массив слов;
function getData($url = "", $mask = "", $count = 0, $i = 0)
{
  $context = stream_context_create(
    array(
      "http" => array(
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
      )
    )
  );
  // echo file_get_contents("https://www.google.com/", false, $context);

  // echo("ЗАПРОС ОТПРАВЛЕН");
  //Отправили запрос, получили ответ
  $response = file_get_contents($url, false, $context);
  $myJson = json_decode($response, true);


  if ($myJson["count"] && $myJson["count"] !== "-") {
    $count = $myJson["count"]; //Проверка на количество элементов в JSON; За раз выгружается 500
    var_dump("Записал в count: " . $count . "<br>\n");
  }


  // var_dump("Текущий размер переданного words: " . count($arr_words) . "<br>\n");
  var_dump("Текущая иттерация: " . $i . "<br>\n");
  var_dump("Текущая count: " . $count . "<br>\n");
  var_dump("Текущий url: " . $url . "<br>\n");
  var_dump("Текущий myJson: " . count($myJson["words"]) . "<br>\n");

  if ($count - 500 > 0) {
    $i++;
    if ($i > 5) {
      echo ("Запросов больше чем 5");
      return;
    }
    $count -= 500;
    var_dump("Текущая запрос: " . "https://poncy.ru/crossword/crossword-solve.jsn?mask=" . $mask . "&desc=&page=" . $i . "<br>\n");
    sleep(2);
    $array_from_recursion = getData("https://poncy.ru/crossword/next-result-page.json?" . $mask . "&desc=&page=" . $i, $mask, $count, $i);

    echo ("Размер текущего words до слияния в этой иттерации: " . count($myJson["words"]) . "<br>\n");
    echo ("Размер массива array_from_recursion до слияния в этой иттерации: " . count($array_from_recursion) . "<br>\n");

    $words = array_merge($myJson["words"], $array_from_recursion); //Сливаю два массива

    echo ("Размер words после слияния в этой иттерации: " . count($words) . "<br>\n");


    return $words;
  } else {
    echo ("Я сюда зашел и тут смотрю на myJson: " . count($myJson["words"]) . "<br>\n");
    echo ("Я сюда зашел и тут смотрю на myJson: " . implode(", ", $myJson["words"]) . "<br>\n");
    return $myJson["words"];
  }
};

//Рекурсивная функция, которая собирает json в один массив слов;
// $testABG = getData("https://poncy.ru/crossword/crossword-solve.jsn?mask=А------", "?mask=А------");
// $testABG = getData("https://poncy.ru/crossword/crossword-solve.jsn?mask=И------", "?mask=И------");
// $testABG = getData("https://poncy.ru/crossword/crossword-solve.jsn?mask=Щ------", "?mask=Щ------");
// echo ("Размер итогового JSON: " . count($testABG) . "<br>\n"); //1689 должно быть 
// file_put_contents("./data/testABG.json", json_encode($testABG)); //Возможная уязвимость, т.к. работа с путем

//Скачивает данные и сохраняет локально 
function cacheJSON($name, $url_withMask, $mask)
{
  $arr = getData($url_withMask, $mask);
  file_put_contents("./data/" . $name . ".json", json_encode($arr)); //Возможная уязвимость, т.к. работа с путем
}


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


//Находит нужный массив с данными
function findCurrentClaim($claim, $encryptedWord)
{
  switch ($encryptedWord[0]) {
    case 1:
      return $claim[0];
    case 2:
      return $claim[1];
    case 3:
      return $claim[2];
    case 4:
      return $claim[3];
    case 5:
      return $claim[4];
    case 6:
      return $claim[5];
  }
}

//Ищет слово по заданному шифру в заданном наборе букв; Передаем массив из 3 подмассивов на каждую букву; 
//Принимает конкретный массив на конкретный набор букв, Конкретный шифр слова, Массив из всех наборов букв 
//Возвращает массив подходящих по шифру слов с 3 массивов слов по одному Набору букв
function checkClaimWords($currentClaimWords, $encryptedWord, $claim)
{
  $resultWords = [];
  foreach ($currentClaimWords as $c => $currentLetterWords) {
    $result = implode(", ", findMatch($currentLetterWords, $claim, $encryptedWord));
    // $result = findMatch($currentLetterWords, $claim, $encryptedWord);
    if ($result !== "") {
      // echo ("Шифр: " . implode("", $encryptedWord) . "; ");
      // echo ("Результирующий массив: " . $result . "; ");
      // echo ("Текущий массив: " . $c . " Количество элементов: " . count($currentLetterWords) . "<br>\n");
      array_push($resultWords, $result);
    }
  }
  return $resultWords;
}

//Ищет совпадения в конкретном массиве слов с конкретным шифром; 
//Принимает конкретный массив слов на конкретную букву, Набор всех букв, Конкретный шифр слова
//Возвращает массив подходящих по шифру слов с текущего массива слов
function findMatch($words, $claim, $encryptedWord)
{
  $resultWords = [];
  // Начинаю обходить массив данных и ищу совпадения
  foreach ($words as $word => $currentWord) {
    //Привожу строку к массиву символов
    $letters = preg_split("//u", $currentWord, -1, PREG_SPLIT_NO_EMPTY);
    //Прохожу конкретное слово посивмольно и проверяю на вхождение в нужное множество
    foreach ($letters as $letter => $value) {
      //Проверка на 6 маску с неизвестной буквой
      if ($encryptedWord[(int)$letter] === "6") {
        if (!$letters[$letter + 1]) { //Проверка на тот случай, если 6 маска в конце слова
          array_push($resultWords, $currentWord);  //Записываем в результирующий массив
        }
        continue;
      }

      //Проверяем обычные буквы на вхождение в соответствуюущие множества
      if (strpos($claim[$encryptedWord[(int)$letter] - 1], $value) === false) {
        break;
      }

      //Если это была последняя буква, значит все остальные буквы попали в множества, значит записываем результат
      if (!$letters[$letter + 1]) {
        array_push($resultWords, $currentWord); //Записываем в результирующий массив
      }
    }
  }
  return $resultWords;
}

//Вывод результата;
function echoResult($result, $encryptedWord, $n = 0)
{
  $encryptedWordImploded = implode("", $encryptedWord);
  if (count($result) > 1) {
    echo ("Размер массива result: " . count($result) . "<br>\n");
    echo ("Слово №:" . ($n + 1) . " ");
    echo ("Возможные варианты: " . implode(", ", $result) . " Шифр: " . $encryptedWordImploded . "<br>\n");
    return true;
  } else {
    echo ("Слово №:" . ($n + 1) . " ");
    if (implode("", $result) !== "") {
      echo ("Результат: " . implode("", $result) . " Шифр: " . $encryptedWordImploded . "<br>\n");
      return true;
    } else {
      echo ("К сожалению, слово "  . " Шифр: " . $encryptedWordImploded .  " не найдено;" . "<br>\n");
      return false;
    }
  }
}

// function checkResult($result){
//   if (count($result) === 1) {
//     return true;
//   } else {
//     return false;
//   }
// }


//Транслит русских букв
function translit($value)
{
  $converter = array(
    'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
    'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
    'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
    'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
    'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
    'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
    'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

    'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
    'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
    'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
    'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
    'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
    'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
    'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
  );

  $value = strtr($value, $converter);
  return $value;
}

//-----------------------------

// Слова по горизонтали

//Получаю данные в JSON, обрабатываю их и привожу к массиву;
// $myJson = json_decode($data1, true);
// $words1 = $myJson["words"];

// $words1 = json_decode($data1, true);

//Массив заданных условием букв;
$claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ", "ЬЯ"];



//Скачиваю данные по конкретному набору букв; 

// Вариант 1

function getAllClaimData($claim, $encryptedWordLength = "0")
{
  foreach ($claim as $i => $currentClaim) {
    $currentClaim = preg_split("//u", $currentClaim, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($currentClaim as $j => $letter) {
      // https://poncy.ru/crossword/crossword-solve.jsn?mask=%D0%90---------
      $mask = $letter . "---------";
      $urlBase = "https://poncy.ru/crossword/crossword-solve.jsn?mask=";
      // $encryptedWordLength = "10";
      $name = "newData" . $encryptedWordLength  . "." . $i + 1 . "." . $j + 1;
      //Проверка на то, есть ли уже такой json
      if (file_get_contents("./data/" . $name . ".json") !== false) {
        echo ("Такой json уже есть, возьму его локально" . "<br>\n");
      } else {
        echo ("Загружаю json " . $name . " с сервера"  . "<br>\n");
        // cacheJSON($name, $urlBase . $mask, $mask); //Вот это обращение к внешнему серверу
      }
    }
  }
}

getAllClaimData($claim, "10");




//Вариант 2; Здесь я хотел по одному Набору букв делать один Массив слов; Т.е. 1 массив включает в себя слова на 3 буквы;
// $firstClaim = preg_split("//u", $claim[0], -1, PREG_SPLIT_NO_EMPTY);
// // foreach ($allLeters as $i => $letter) {
// $claimArr = [];
// foreach ($firstClaim as $i => $letter) {
//   // https://poncy.ru/crossword/crossword-solve.jsn?mask=%D0%90---------
//   $mask = "?mask=" . $letter . "---------";
//   $urlBase = "https://poncy.ru/crossword/crossword-solve.jsn";
//   $encryptedWordLength = "10";
//   $name = "newData" . $encryptedWordLength  . "." . $i + 1;

//   //Вариант 1; Проверка на то, есть ли уже такой json, чтобы не скачивать с сервера заново
//   if (file_get_contents("./data/" . $name . ".json") !== false) {
//     echo ("Такой json уже есть, возьму его локально" . "<br>\n");
//   } else {
//     echo ("Загружаю json с сервера"  . "<br>\n");
//     // cacheJSON($name, $urlBase . $mask, $mask); //Вот это обращение к внешнему серверу
//     // $claimArr = array_merge($claimArr, getData($urlBase . $mask, $mask)); //это я хотел собрать все данные по клейму в один массив;
//   }
//   // if (!$firstClaim[$i + 1]) {
//   // file_put_contents("./data/" . $name . ".json", json_encode($claimArr));
//   // }
// }



//



$words1[0] = json_decode(file_get_contents("./data/data1.1.json"), true)["words"];
$words1[1] = json_decode(file_get_contents("./data/data1.2.json"), true)["words"];
$words1[2] = json_decode(file_get_contents("./data/data1.3.json"), true)["words"];
$words2[0] = json_decode(file_get_contents("./data/data2.1.json"), true)["words"];
$words2[1] = json_decode(file_get_contents("./data/data2.2.json"), true)["words"];
$words2[2] = json_decode(file_get_contents("./data/data2.3.json"), true)["words"];
$words3[0] = json_decode(file_get_contents("./data/data3.1.json"), true)["words"];
$words3[1] = json_decode(file_get_contents("./data/data3.2.json"), true)["words"];
$words3[2] = json_decode(file_get_contents("./data/data3.3.json"), true)["words"];
$words4[0] = json_decode(file_get_contents("./data/data4.1.json"), true)["words"];
$words4[1] = json_decode(file_get_contents("./data/data4.2.json"), true)["words"];
$words4[2] = json_decode(file_get_contents("./data/data4.3.json"), true)["words"];
$words5[0] = json_decode(file_get_contents("./data/data5.1.json"), true)["words"];
$words5[1] = json_decode(file_get_contents("./data/data5.2.json"), true)["words"];
$words5[2] = json_decode(file_get_contents("./data/data5.3.json"), true)["words"];



//Массив шифров
$encryptedWords = [
  //Перевожу их в массив символов, чтобы можно было работать с масками
  $encryptedWord1 = preg_split("//u", "1153241526", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord2 = preg_split("//u", "1656335361", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord3 = preg_split("//u", "5424251322", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord4 = preg_split("//u", "3655516563", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord5 = preg_split("//u", "4213633456", -1, PREG_SPLIT_NO_EMPTY),
];





//Карта, которая связывает Наборы букв и Массивы слов, которые начинаются с этих букв
$claimMap = [
  $claim[0] => $words1,
  $claim[1] => $words2,
  $claim[2] => $words3,
  $claim[3] => $words4,
  $claim[4] => $words5
];


echo ("Слова по горизонтали:" . "<br>\n");

// Вызов функции
foreach ($encryptedWords as $e => $currentEncryptedWord) {
  $needClaim = findCurrentClaim($claim, $currentEncryptedWord);
  $result = checkClaimWords($claimMap[$needClaim], $currentEncryptedWord, $claim);
  // echo ("Результирующий массив: " . $result . "; " . "<br>\n");

  echoResult($result, $currentEncryptedWord, $e);
}








//Вертикальные слова

//Массивы в словами на нужные буквы
$verticalWords1[0] = json_decode(file_get_contents("./data/data5A.json"), true)["words"];
$verticalWords1[1] = json_decode(file_get_contents("./data/data5V.json"), true)["words"];
$verticalWords1[2] = json_decode(file_get_contents("./data/data5G.json"), true)["words"];
$verticalWords2[0] = json_decode(file_get_contents("./data/data5E.json"), true)["words"];
$verticalWords2[1] = json_decode(file_get_contents("./data/data5I.json"), true)["words"];
$verticalWords2[2] = json_decode(file_get_contents("./data/data5K.json"), true)["words"];
$verticalWords3[0] = json_decode(file_get_contents("./data/data5L.json"), true)["words"];
$verticalWords3[1] = json_decode(file_get_contents("./data/data5N.json"), true)["words"];
$verticalWords3[2] = json_decode(file_get_contents("./data/data5O.json"), true)["words"];
$verticalWords4[0] = json_decode(file_get_contents("./data/data5P.json"), true)["words"];
$verticalWords4[1] = json_decode(file_get_contents("./data/data5R.json"), true)["words"];
$verticalWords4[2] = json_decode(file_get_contents("./data/data5S.json"), true)["words"];
$verticalWords5[0] = json_decode(file_get_contents("./data/data5T.json"), true)["words"];
$verticalWords5[1] = json_decode(file_get_contents("./data/data5U.json"), true)["words"];
$verticalWords5[2] = json_decode(file_get_contents("./data/data5SH.json"), true)["words"];
$verticalWords6[0] = json_decode(file_get_contents("./data/data5YA.json"), true)["words"];

//

//Массив шифров
$verticalEncryptedWords = [
  //Перевожу их в массив символов, чтобы можно было работать с масками
  $encryptedWord1 = preg_split("//u", "11534", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord2 = preg_split("//u", "16462", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord3 = preg_split("//u", "55251", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord4 = preg_split("//u", "36453", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord5 = preg_split("//u", "23256", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord6 = preg_split("//u", "43513", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord7 = preg_split("//u", "15163", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord8 = preg_split("//u", "53354", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord9 = preg_split("//u", "26265", -1, PREG_SPLIT_NO_EMPTY),
  $encryptedWord10 = preg_split("//u", "61236", -1, PREG_SPLIT_NO_EMPTY)
];


//Карта, которая связывает Наборы букв и Массивы слов, которые начинаются с этих букв
$verticalClaimMap = [
  $claim[0] => $verticalWords1,
  $claim[1] => $verticalWords2,
  $claim[2] => $verticalWords3,
  $claim[3] => $verticalWords4,
  $claim[4] => $verticalWords5,
  $claim[5] => $verticalWords6
];


echo ("<br>\n");
echo ("Слова по вертикали:" . "<br>\n");

foreach ($verticalEncryptedWords as $e => $currentEncryptedWord) {
  $needClaim = findCurrentClaim($claim, $currentEncryptedWord);
  // echo ("Шифр: " . implode("", $currentEncryptedWord) . " Claim = " . $needClaim . "<br>\n");

  $result = checkClaimWords($verticalClaimMap[$needClaim], $currentEncryptedWord, $claim);
  // echo ("Слово №: " . ($e + 1) . " ");
  // echo ("Результирующий массив: " . $result . "; " . "<br>\n");
  echoResult($result, $currentEncryptedWord, $e);
}








//Результирующее слово

//шифр
$encryptedWordResult = preg_split("//u", "111222333", -1, PREG_SPLIT_NO_EMPTY);

//данные
$wordsR[0] = json_decode(file_get_contents("./data/dataResultC.json"), true)["words"];
$wordsR[1] = json_decode(file_get_contents("./data/dataResultK.json"), true)["words"];
$wordsR[2] = json_decode(file_get_contents("./data/dataResultU.json"), true)["words"];

//буквы
$claimNew = [$claim[1] . $claim[3] . $claim[4],  $claim[2] . $claim[3] . $claim[5], $claim[2] . $claim[3] . $claim[4]];


$finalResult = checkClaimWords($wordsR, $encryptedWordResult, $claimNew);

echo ("<br>\n");
echo ("Финальное слово: " . "<br>\n");
echoResult($finalResult, $encryptedWordResult);


// getAllClaimData((array)$claimNew[0], "9"); //Как бы я вывел результирующее слово


//Логика

//encryptedWords = масстив; зашифрованные слова
//encryptedWords[0] = первое код зашифрованного слова; 1153241526
//encryptedWords[0][0] = первый символ кода зашифрованного слова; 1
//words1 - массив данных под первый тип букв; АВГ
//words1[0] - массив слов начинающихся на букву А
//words2 - массив данных под второй тип букв; ЕИК
//words2[0] - массив слов начинающихся на букву Е

// var_dump("Результирующий массив: " . implode("", findMatch($words4[2], $claim, $encryptedWords[4])));
// var_dump("Результирующий массив: " . implode("", findMatch(Конкретный_Набор_БУКВ[Конректная_буква], Все_наборы_БУКВ, Массив_ШИФРОВ[Конкретный_ШИФР])));
// function checkClaimWords(Конкретный_набор_букв, Конкретный_ШИФР, Все_наборы_БУКВ)
// Нужно найти совпадение Первой_Буквы_ШИФРА и Конкретного_Набора_Букв







//Подход №2

// Исходные данные

// $POST = {
// Массив из зашифрованных слов
// Наборы букв
// Позиции букв для Результирующего слова; Шифр Результирующего зашифрованного слова
// }


//шифрованные слова + массив результирующих данных данных
$resultArr = [
  ["А", "1", "5", "О", "К", "4", "А", "5", "2", "6"],
  ["1", "6", "5", "6", "3", "3", "5", "3", "6", "1"],
  ["5", "4", "2", "П", "2", "5", "1", "Н", "2", "2"],
  ["3", "6", "5", "5", "5", "1", "6", "5", "6", "3"],
  ["Р", "2", "1", "3", "6", "3", "3", "4", "5", "6"],
];

//Зашифрованные слова
$encryptedWords = [
  ["1", "1", "5", "3", "2", "4", "1", "5", "2", "6"],
  ["1", "6", "5", "6", "3", "3", "5", "3", "6", "1"],
  ["5", "4", "2", "4", "2", "5", "1", "3", "2", "2"],
  ["3", "6", "5", "5", "5", "1", "6", "5", "6", "3"],
  ["4", "2", "1", "3", "6", "3", "3", "4", "5", "6"],
];


//Наборы букв
$claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ", "ЬЯ"];




$finalWordpart1 = [
  $resultArr[0][4], $resultArr[1][6], $resultArr[2][1]
];

$finalWordpart2 = [
  $resultArr[2][3], $resultArr[3][0], $resultArr[3][3]
];

$finalWordpart3 = [
  $resultArr[3][9], $resultArr[4][7], $resultArr[4][9]
];

$finalWord = [
  $finalWordpart1, $finalWordpart2, $finalWordpart3
];

$url = "https://poncy.ru/crossword/crossword-solve.jsn?mask=";


//------------------------------------------------------------------------------------------------------------------- 

// Функции


//Проверка на наличие известных букв в слове
function checkLetters($word)
{
  return preg_match('/[\p{L&}]/', $word) ? true : false;
}

//Генерация маски исходя из текущего состояния массива
function getMask($word)
{
  foreach ($word as $w => $letter) {
    if (checkLetters($letter) === false) {
      $word[$w] = "-";
    }
  }
  return implode("", $word);
}

//Создание маски для перебора
function createMask($letter, $count)
{
  $mask = "" . $letter;
  for ($i = 1; $i < $count; $i++) {
    $mask = $mask . "-";
  }
  return $mask;
}

//Находит разагдки для кроссворда; 
//Принимает Результирующий массив, Массив шифров, Набор букв, текущая иттерацию цикла, url адрес сайта с api
function solveCrossword($resultArr, $encryptedWords, $claim, $i, $url)
{
  $encryptedWordCurrent = $resultArr[$i];
  $encryptedWord = $encryptedWords[$i];
  if (checkLetters(implode("", $encryptedWordCurrent))) {
    //Если буквы есть в текущей строке, то делаю маску 
    echo ("Найден шифр с открытыми буквами: " . implode("", $encryptedWordCurrent) . "<br>\n");
    $mask = getMask($encryptedWordCurrent);
    echo ("Сгенерирована маска: " . $mask . "<br>\n");
    $words = getData($url . $mask, $mask); //отправка запроса на сервер
    // $words = json_decode(file_get_contents("./data/maskedJSON.json"), true); //чтение локально
    $result = findMatch($words, $claim, $encryptedWord);
  } else {
    //Если букв нет, то делаю перебор
    $needClaim = preg_split("//u", findCurrentClaim($claim, $encryptedWord), -1, PREG_SPLIT_NO_EMPTY);
    foreach ($needClaim as $k => $currentLetter) {
      $mask = createMask($currentLetter, count($encryptedWord));
      echo ("Маска: " . $mask . "<br>\n");
      $words = getData($url . $mask, $mask); //отправка запроса на сервер
      // $words = json_decode(file_get_contents("./data/maskedJSON.json"), true); //чтение локально
      $result = findMatch($words, $claim, $encryptedWord);
    }
  }

  // Отображаю результат
  echoResult($result, $encryptedWord, $i);
  //Если подошло только одно слово, то записываю его в результирующий массив
  if (count($result) === 1) {
    $result = preg_split("//u", $result[0], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($result as $r => $letter) {
      $resultArr[$i][$r] = $letter;
    }
  }
  return $resultArr;
}

//Возращает конкретное слово по вертикали из колонки $numberOfWord массива $arr
function getVerticalWord($numberOfWord, $arr)
{
  $result = [];
  for ($i = 0; $i < count($arr); $i++) {
    array_push($result, $arr[$i][$numberOfWord]);
  }
  return $result;
}

//Создает массивы Шифров и Вертикальных слов
function createVerticalArr($resultArr, $encryptedWords)
{
  $verticalEncryptedWords = [];
  $verticalWords = [];
  $countWords = count($resultArr[0]);
  for ($i = 0; $i < $countWords; $i++) {
    // echo ("Шифр: ");
    array_push($verticalEncryptedWords, getVerticalWord($i, $resultArr));
    // echo ("; Значение из массива: ");
    array_push($verticalWords, getVerticalWord($i, $encryptedWords));
    // echo ("<br>\n");
  }
  return ([$verticalWords, $verticalEncryptedWords]);
}

//Отобразить горизонтальные слова;
function viewHorizontalResult($resultArr)
{
  echo ("<br>\n");
  echo ("Слова по горизонтали: " . "<br>\n");
  for ($i = 0; $i < count($resultArr); $i++) {
    echo ("Слово №:" . $i + 1 . " ");
    for ($j = 0; $j < count($resultArr[$i]); $j++) {
      echo ($resultArr[$i][$j]);
    }
    echo ("<br>\n");
  }
  echo ("Горизонтальное отображение успешно завершено!" . "<br>\n");
};

//Отобразить вертикальные слова;
function viewVerticalResult($resultArr)
{
  echo ("<br>\n");
  echo ("Слова по горизонтали: " . "<br>\n");
  $countWords = count($resultArr[0]);
  for ($i = 0; $i < $countWords; $i++) {
    echo ("Слово №:" . $i + 1 . " ");
    for ($j = 0; $j < count($resultArr); $j++) {
      echo ($resultArr[$j][$i]);
    }
    echo ("<br>\n");
  }
  echo ("Вертикальное отображение успешно завершено!" . "<br>\n");
}

//Собирает Набор букв для Результирующего слова
function getFinalClaim($resultArr)
{
  $finalWordpart1 = [
    $resultArr[0][4], $resultArr[1][6], $resultArr[2][1]
  ];

  $finalWordpart2 = [
    $resultArr[2][3], $resultArr[3][0], $resultArr[3][3]
  ];

  $finalWordpart3 = [
    $resultArr[3][9], $resultArr[4][7], $resultArr[4][9]
  ];

  $finalClaim = [implode("", $finalWordpart1), implode("", $finalWordpart2), implode("", $finalWordpart3)];
  // echo (implode("", $finalClaim));
  return $finalClaim;
}

//-----------------------------------------------------------------

//Вызов

echo ("<br>\n");
echo ("Слова по горизонтали:" . "<br>\n");


// Слова по горизонтали
for ($i = 0; $i < count($resultArr); $i++) {
  $resultArr = solveCrossword($resultArr, $encryptedWords, $claim, $i, $url);
}

//Слова по вертикали
$verticalArr = createVerticalArr($resultArr, $encryptedWords);

for ($i = 0; $i < $countWords; $i++) {
  $resultArr = solveCrossword($veritcalArr[0], $verticalArr[1], $claim, $i, $url);
}


//Отображение результата на экране
viewHorizontalResult($resultArr);
viewVerticalResult($resultArr);
