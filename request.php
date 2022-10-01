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
    usleep(500000); // защита от бана; ждать 0.3 секунды
    // usleep(1000000); // защита от бана; ждать 1 секунду
    $array_from_recursion = getData("https://poncy.ru/crossword/next-result-page.json?mask=" . $mask . "&desc=&page=" . $i, $mask, $count, $i);
    $words = array_merge($myJson["words"], $array_from_recursion); //Сливаю два массива
    return $words;
  } else {
    return $myJson["words"];
  }
};

//Функция проверки на локальное хранение;
//Возвращает массив данных по определенной маске
function checkCache($name, $mask, $url = "https://poncy.ru/crossword/crossword-solve.json?mask=", $sleepTime = 200000)
{
  //Кэширование; Сделать функцию
  if (file_exists("./data/" . $name)) {
    $words = json_decode(file_get_contents("./data/" . $name), true); //чтение локально
  } else {
    // ждать 0.2 секунды, защита от бана сервера
    usleep($sleepTime);
    $words = getData($url . $mask, $mask); //отправка запроса на сервер
    file_put_contents("./data/" . $name, json_encode($words));
  }
  return $words;
}

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

//Добавить слово в массив возможных вариантов ответов
function addVariant($result, $answersArr)
{
  if ($result !== "") {
    foreach ($result as $q => $answer) {
      array_push($answersArr, $answer);
    }
  }
  return $answersArr;
}

//Записать ответ в результирующий массив
function addResult($answersArr, $resultArr, $i)
{
  if (count($answersArr) === 1) {
    // echo ("Записываю результат:" . $answersArr[0] . "<br>\n");
    $result = preg_split("//u", $answersArr[0], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($result as $r => $letter) {
      $resultArr[$i][$r] = $letter;
    }
  }
  return $resultArr;
}

//Находит разагдки для кроссворда; 
//Принимает Результирующий массив, Массив шифров, Набор букв, текущая иттерацию цикла, url адрес сайта с api
//Возвращает массив результатов
function solveCrossword($resultArr, $encryptedWords, $claim, $i, $url = "https://poncy.ru/crossword/crossword-solve.json?mask=")
{
  $answersArr = [];
  $encryptedWordCurrent = $resultArr[$i];
  $encryptedWord = $encryptedWords[$i];

  if (checkLetters(implode("", $encryptedWordCurrent))) {
    //Если буквы есть в текущей строке, то делаю маску 
    $mask = getMask($encryptedWordCurrent);
    $name = count($encryptedWordCurrent) . $mask . ".json";

    $words = checkCache($name, $mask);
    $result = findMatch($words, $claim, $encryptedWord);
    $answersArr = addVariant($result, $answersArr);
  } else {
    //Если букв нет, то делаю перебор
    $needClaim = preg_split("//u", findCurrentClaim($claim, $encryptedWord), -1, PREG_SPLIT_NO_EMPTY);
    foreach ($needClaim as $k => $currentLetter) {
      $mask = createMask($currentLetter, count($encryptedWord));
      $name = count($encryptedWordCurrent) . $mask . ".json";

      $words = checkCache($name, $mask, $url, 800000);
      $result = findMatch($words, $claim, $encryptedWord);
      $answersArr = addVariant($result, $answersArr);
    }
  }


  // echo ("ITOGO count answerArr: " . count($answersArr) . "<br>\n");
  // echo ("ITOGO implode answersArr: " . implode(", ", $answersArr) . "<br>\n");
  // //АВТОКРАТИЯ "\u0410\u041b\u042c\u041a\u0410\u041d\u0410\u0414\u0420\u0415"

  // foreach ($answersArr as $o => $answer) {
  //   // echo ("ITOGO result: " . count($answer) . "<br>\n");
  //   // echo ("ITOGO result: " . implode(", ", $answer) . "<br>\n");
  //   echo ("ITOGO current №" . $o .  ": " . $answer . "<br>\n");
  // }


  // Отображаю результат
  // echoResult($result, $encryptedWord, $i);
  echoResult($answersArr, $encryptedWord, $i);
  //Если подошло только одно слово, то записываю его в результирующий массив
  $resultArr = addResult($answersArr, $resultArr, $i);

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
  echo ("Слова по вертикали: " . "<br>\n");
  $countWords = count($resultArr[0]);
  for ($i = 0; $i < $countWords; $i++) {
    echo ("Слово №:" . $i + 1);
    for ($j = 0; $j < count($resultArr); $j++) {
      echo ($resultArr[$j][$i]);
    }
    echo ("<br>\n");
  }
  echo ("Вертикальное отображение успешно завершено!" . "<br>\n");
}

//Слияние вертикального и горизонтального массивов
//Возвращает горизонтальный массив
function mergeVerticalHorizontal($verticalArr, $horizontalArr)
{
  echo ("<br>\n");
  $countWords = count($verticalArr[0]);
  for ($i = 0; $i < $countWords; $i++) { //0 .. 5

    for ($j = 0; $j < count($verticalArr); $j++) { // 0 .. 10
      $horizontalArr[$i][$j] = $verticalArr[$j][$i];
    }
  }
  echo ("Слияние вертикального и горизонтального массивов успешно завершено!" . "<br>\n");
  return $horizontalArr;
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
function getFinalWord($finalClaim, $finalEncryptedWord, $url = "https://poncy.ru/crossword/crossword-solve.json?mask=")
{
  $currentClaim = preg_split("//u", $finalClaim[0], -1, PREG_SPLIT_NO_EMPTY);
  $resultArr = [];
  $result = [];
  foreach ($currentClaim as $k => $currentLetter) {
    $mask = createMask($currentLetter, count($finalEncryptedWord));
    $name = count($finalEncryptedWord) . $mask . ".json";

    //Получаю данные локально или скачиваю
    $words = checkCache($name, $mask);

    $result = implode("", findMatch($words, $finalClaim, $finalEncryptedWord));
    if ($result !== "") {
      array_push($resultArr, $result);
    }
  }
  echoResult($resultArr, $finalEncryptedWord);
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
  ["3", "6", "5", "У", "5", "1", "6", "5", "6", "3"],
  ["4", "2", "1", "3", "6", "3", "3", "4", "5", "6"],
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
// viewHorizontalResult($horizontalResultArr);
// viewVerticalResult($horizontalResultArr);

//vertical
// viewHorizontalResult($verticalResultArr);
// viewVerticalResult($verticalResultArr);



//Слияние вертикального и горизонтального массивов
$resultArr = mergeVerticalHorizontal($verticalResultArr, $horizontalResultArr);
//horizontal
viewHorizontalResult($resultArr);
viewVerticalResult($resultArr);




//Результирующее слово


$finalClaim = getFinalClaim($resultArr);
$finalEncryptedWord = ["1", "1", "1", "2", "2", "2", "3", "3", "3"];


echo ("<br>\n");
echo ("Финальное слово: " . "<br>\n");
getFinalWord($finalClaim, $finalEncryptedWord);
