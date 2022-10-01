<?php

ini_set('max_execution_time', 600);

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

  //Отправили запрос, получили ответ
  $response = file_get_contents($url, false, $context);
  $myJson = json_decode($response, true);

  if ($myJson["count"] && $myJson["count"] !== "-") {
    $count = $myJson["count"];
  }

  if ($count - 500 > 0) {
    $i++;
    $count -= 500;
    usleep(300000); // защита от бана; ждать 0.3 секунды
    // usleep(1000000); // защита от бана; ждать 1 секунду
    $array_from_recursion = getData("https://poncy.ru/crossword/next-result-page.json?mask=" . $mask . "&desc=&page=" . $i, $mask, $count, $i);
    $words = array_merge($myJson["words"], $array_from_recursion); //Сливаю два массива
    return $words;
  } else {
    return $myJson["words"];
  }
};



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
//Возвращает массив результатов
function solveCrossword($resultArr, $encryptedWords, $claim, $i, $url)
{
  $encryptedWordCurrent = $resultArr[$i];
  $encryptedWord = $encryptedWords[$i];

  if (checkLetters(implode("", $encryptedWordCurrent))) {
    //Если буквы есть в текущей строке, то делаю маску 
    $mask = getMask($encryptedWordCurrent);
    // sleep(1); //защита от бана
    // ждать 0.2 секунды
    usleep(200000);
    // $words = getData($url . $mask, $mask); //отправка запроса на сервер
    $words = json_decode(file_get_contents("./data/maskedJSON.json"), true); //чтение локально
    // $words = json_decode(file_get_contents("./data/withMASK.json"), true); //чтение локально

    // $name = $i;
    // file_put_contents("./data/" . $name . ".json", json_encode($words));
    $result = findMatch($words, $claim, $encryptedWord);
    // Отображаю результат
    echoResult($result, $encryptedWord, $i);
    //Если подошло только одно слово, то записываю его в результирующий массив
    if (count($result) === 1) {
      $result = preg_split("//u", $result[0], -1, PREG_SPLIT_NO_EMPTY);
      foreach ($result as $r => $letter) {
        $resultArr[$i][$r] = $letter;
      }
    }
  } else {
    //Если букв нет, то делаю перебор
    $needClaim = preg_split("//u", findCurrentClaim($claim, $encryptedWord), -1, PREG_SPLIT_NO_EMPTY);
    foreach ($needClaim as $k => $currentLetter) {
      $mask = createMask($currentLetter, count($encryptedWord));
      sleep(1); //защита от бана
      // ждать 0.8 секунды
      // usleep(800000);
      // $words = getData($url . $mask, $mask); //отправка запроса на сервер
      $words = json_decode(file_get_contents("./data/withoutMASK.json"), true); //чтение локально

      // $name = $i + 100;
      // file_put_contents("./data/" . $name . ".json", json_encode($words));
      $result = findMatch($words, $claim, $encryptedWord);
      // Отображаю результат
      echoResult($result, $encryptedWord, $i);
      //Если подошло только одно слово, то записываю его в результирующий массив
      if (count($result) === 1) {
        $result = preg_split("//u", $result[0], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($result as $r => $letter) {
          $resultArr[$i][$r] = $letter;
        }
      }
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
    array_push($verticalEncryptedWords, getVerticalWord($i, $encryptedWords));
    array_push($verticalWords, getVerticalWord($i, $resultArr));
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
    $resultArr[0][4], $resultArr[3][3], $resultArr[4][7]
  ];

  $finalWordpart2 = [
    $resultArr[2][3], $resultArr[3][9], $resultArr[4][9]

  ];

  $finalWordpart3 = [
    $resultArr[1][6], $resultArr[2][1], $resultArr[3][0]
  ];

  $finalClaim = [implode("", $finalWordpart1), implode("", $finalWordpart2), implode("", $finalWordpart3)];
  return $finalClaim;
}

//Разгадывает финальное слово
function getFinalWord($finalClaim, $finalEncryptedWord, $url)
{
  $currentClaim = preg_split("//u", $finalClaim[0], -1, PREG_SPLIT_NO_EMPTY);
  foreach ($currentClaim as $k => $currentLetter) {
    $mask = createMask($currentLetter, count($finalEncryptedWord));
    usleep(200000); //защита от бана; ждать 0.2 секунды
    // $words = getData($url . $mask, $mask); //отправка запроса на сервер
    $words = json_decode(file_get_contents("./data/resultWord.json"), true); //чтение локально
    $result = findMatch($words, $finalClaim, $finalEncryptedWord);
    echoResult($result, $finalEncryptedWord);
  }
  echo ("<br>\n");
  echo ("Финальное слово: " . "<br>\n");
  echoResult($result, $finalEncryptedWord);
}

//------------------------------------------------------------------------------------------------------------------- 


// Исходные данные

// $POST = {
// Массив из зашифрованных слов
// Наборы букв
// Позиции букв для Результирующего слова; Шифр Результирующего зашифрованного слова
// }

//Массив результирующих данных 
$resultArr = [
  ["1", "1", "5", "3", "2", "4", "1", "5", "2", "6"],
  ["1", "6", "5", "6", "3", "3", "5", "3", "6", "1"],
  ["5", "4", "2", "4", "2", "5", "1", "3", "2", "2"],
  ["3", "6", "5", "5", "5", "1", "6", "5", "6", "3"],
  ["4", "2", "1", "3", "6", "3", "3", "4", "5", "6"],
];

// $resultArr = [
//   ["А", "1", "5", "О", "К", "4", "А", "5", "2", "6"],
//   ["1", "6", "5", "6", "3", "3", "Т", "3", "6", "1"],
//   ["5", "Р", "2", "П", "2", "5", "1", "Н", "2", "2"],
//   ["О", "6", "5", "У", "5", "1", "6", "5", "6", "Л"],
//   ["Р", "2", "1", "3", "6", "3", "3", "С", "5", "Ь"],
// ];

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

//url api
$url = "https://poncy.ru/crossword/crossword-solve.json?mask=";

//------------------------------------------------------------------------------------------------------------------- 

//Вызов функций

// Слова по горизонтали
echo ("<br>\n");
echo ("Слова по горизонтали:" . "<br>\n");

$horizontalResultArr = $resultArr;
$countWords = count($resultArr);

for ($i = 0; $i < $countWords; $i++) {
  $horizontalResultArr = solveCrossword($horizontalResultArr, $encryptedWords, $claim, $i, $url);
}


//Слова по вертикали
echo ("<br>\n");
echo ("Слова по вертикали:" . "<br>\n");

$verticalArr = createVerticalArr($horizontalResultArr, $encryptedWords);
$verticalResultArr = $verticalArr[0];
$verticalEncryptedWords = $verticalArr[1];
$countWords = count($resultArr[0]);

for ($i = 0; $i < $countWords; $i++) {
  $verticalResultArr = solveCrossword($verticalResultArr, $verticalEncryptedWords, $claim, $i, $url);
}


//Отображение результата на экране

//horizontal
viewHorizontalResult($horizontalResultArr);
viewVerticalResult($horizontalResultArr);

//vertical
viewHorizontalResult($verticalResultArr);
viewVerticalResult($verticalResultArr);


//Слияние вертикального и горизонтального массивов
$resultArr = mergeVerticalHorizontal($verticalResultArr, $horizontalResultArr);
//horizontal
viewHorizontalResult($resultArr);
viewVerticalResult($resultArr);

function mergeVerticalHorizontal($verticalArr, $horizontalArr)
{
  echo ("<br>\n");
  echo ("Слияние вертикального и горизонтального массивов: " . "<br>\n");
  $countWords = count($verticalArr[0]);
  for ($i = 0; $i < $countWords; $i++) { //0 .. 5
    echo ("Слово №:" . $i + 1 . " ");
    echo ("<br>\n");
    for ($j = 0; $j < count($verticalArr); $j++) { // 0 .. 10
      // echo ("ДО" . $verticalArr[$j][$i] . " = " . $horizontalArr[$i][$j] . "<br\n");
      // echo ($horizontalArr[$i][$j] . " <= Горизонтальный элемент" . "<br\n");
      echo "TEST0 " . " <br>\n";
      echo "TEST1 " . $verticalArr[$j][$i] . " <br>\n";
      echo "TEST2 " . $horizontalArr[$i][$j] . " <br>\n";
      $horizontalArr[$i][$j] = $verticalArr[$j][$i];
      // echo ("ПОСЛЕ: " . $verticalArr[$j][$i] . " = " . $horizontalArr[$i][$j] .  "<br\n");
      echo "TEST3 " . " <br>\n";
      echo "TEST4 " . $verticalArr[$j][$i] . " <br>\n";
      echo "TEST5 " . $horizontalArr[$i][$j] . " <br>\n";
    }
    echo ("<br>\n");
  }
  echo ("Слияние успешно завершено!" . "<br>\n");
  return $horizontalArr;
}


//Результирующее слово
echo ("<br>\n");
echo ("Результирующее слово:" . "<br>\n");

$finalClaim = getFinalClaim($resultArr);
$finalEncryptedWord = ["1", "1", "1", "2", "2", "2", "3", "3", "3"];

getFinalWord($finalClaim, $finalEncryptedWord, $url);
