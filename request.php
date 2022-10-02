<?php

ini_set('max_execution_time', 600);

//Функция получения данных;
//Рекурсивная функция, которая собирает json в один массив слов;
function getData($url = "", $mask = "", $count = 0, $i = 0)
{

  //Меняю контекст, чтобы сервер не банил;
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
    usleep(500000); // защита от бана; ждать 0.5 секунды
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
    usleep($sleepTime); // ждать 0.2 секунды, защита от бана сервера
    $words = getData($url . $mask, $mask); //отправка запроса на сервер
    file_put_contents("./data/" . $name, json_encode($words));
  }
  return $words;
}

//Находит нужный массив с данными по шифру
//Возвращает Набор букв
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


// Находит набор букв по букве
// Возвращает набор букв
function findClaimByLetter($claim, $letter)
{
  foreach ($claim as $i => $currentClaim) {
    if (strpos($currentClaim, $letter) !== false) {
      return $currentClaim;
    }
  }
}

//Найти шифр по известному набору букв
//Вощвращает цифру шифра
function findCurrentEncrypt($currentClaim, $claim)
{
  switch ($currentClaim) {
    case $claim[0]:
      return "1";
    case $claim[1]:
      return "2";
    case $claim[2]:
      return "3";
    case $claim[3]:
      return "4";
    case  $claim[4]:
      return "5";
    case $claim[5]:
      return "6";
  }
}

//Создает шифр по слову
//Возвращает полный шифр на слово $word
function createEncrtyptedWord($word, $claim)
{
  $encryptedWord = [];
  foreach ($word as $i => $letter) {
    if (checkDigits($letter)) {
      array_push($encryptedWord, $letter);
      continue;
    }
    $currentClaim = findClaimByLetter($claim, $letter);
    array_push($encryptedWord, findCurrentEncrypt($currentClaim, $claim));
  }

  return $encryptedWord;
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
    echo ("Слово №:" . ($n + 1) . " ");
    echo ("Возможные варианты: " . implode(", ", $result) . " Шифр: " . $encryptedWordImploded . "<br>\n");
    return true;
  } else {
    echo ("Слово №:" . ($n + 1) . " ");
    if (implode("", $result) !== "") {
      echo ("Результат: " . implode("", $result) . " Шифр: " . $encryptedWordImploded . "<br>\n");
      return true;
    } else {
      echo ("К сожалению, слово не найдено;" . " Шифр: " . $encryptedWordImploded . "<br>\n");
      return false;
    }
  }
}

//Проверка на наличие известных букв в слове
function checkLetters($word)
{
  return preg_match('/[\p{L&}]/', $word) ? true : false;
}

//Проверка на вхождение цифр в строку/букву
function checkDigits($word)
{
  return preg_match('/[\d]/', $word) ? true : false;
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
  for ($i = 0; $i < count($resultArr); $i++) {
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


    // Отображаю результат
    echoResult($answersArr, $encryptedWord, $i);
    $resultArr = addResult($answersArr, $resultArr, $i);
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
  echo ("Слова по вертикали: " . "<br>\n");
  $countWords = count($resultArr[0]);
  for ($i = 0; $i < $countWords; $i++) {
    echo ("Слово №:" .  $i + 1 . " ");
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

//Выявляю черные поля
//Возвращает результирующий массив
function fillBlackFields($resultArr)
{
  for ($i = 0; $i < count($resultArr); $i++) {
    for ($j = 0; $j < count($resultArr[$i]); $j++) {
      if ($resultArr[$i][$j] == "6") {
        if (checkBlackCell($resultArr, $i, $j, count($resultArr), count($resultArr[$i]))) {
          $resultArr[$i][$j] = ".";
        }
      }
    }
  }
  echo ("<br>\n");
  echo ("Все черные блоки обнаружены!" . "<br>\n");
  echo ("Изменения внесены в результирующий массив." . "<br>\n");
  echo ("<br>\n");

  return $resultArr;
}

//Проверяет можно ли ячейку сделать черной;
//Возвращает результирующий массив
function checkBlackCell($resultArr, $row, $column, $maxi, $maxj)
{
  $i = $row;
  $j = $column;
  $a = $resultArr;

  if ($i + 1 < $maxi) {
    if (checkLetters($a[$i + 1][$j]) === false) {
      return false;
    }
  }

  if ($i - 1 >= 0) {
    if (checkLetters($a[$i - 1][$j]) === false) {
      return false;
    }
  }

  if ($j + 1 < $maxj) {
    if (checkLetters($a[$i][$j + 1]) === false) {
      return false;
    }
  }

  if ($j - 1 >= 0) {
    if (checkLetters($a[$i][$j - 1]) === false) {
      return false;
    }
  }

  return true;
}

//Находит оставшиеся неразгаданые пробелы и заполняет их
//Возвращает результирующий массив
function fillGaps($arr, $claim)
{
  foreach ($arr as $h => $line) {
    if (checkDigits(implode("", $line))) {
      $myMap = createMap($line);
      $answersArr = checkMap($myMap, $claim);
      $arr = writeWord($arr, $answersArr, $h, $myMap,  count($line));
    }
  }
  return $arr;
}


//Формируем карту соответствий; Проходимся по строке и записываем возможные слова;
//Слово => Номер символа в строке где нашли;
//Возвращает массив;  Слово => Номер элемента в строке
function createMap($line)
{
  //Находим все возможные слова и записываем их в массив
  $findWords = [];
  $currentWord = "";
  for ($i = 0; $i < count($line); $i++) {
    $currentWord = "";
    $flag = false;
    while ($line[$i] !== "." && $i < count($line)) {
      $currentWord = $currentWord . $line[$i];
      if (checkLetters($line[$i]) === false) {
        $flag = true;
      }
      if ($i + 1 < count($line)) {
        if ($line[$i + 1] === "." && ($flag === true) && (strlen($currentWord) > 1)) {
          array_push($findWords, $currentWord);
        }
      } else {
        if (($flag === true) && (strlen($currentWord) > 1)) {
          array_push($findWords, $currentWord);
        }
      }
      $i++;
    }
  }

  $horizontalMap = [];
  //Формируем карту соответствий; 
  //Номер символа в строке => Слово; Номер строки возьмем снаружи;
  //Возвращаем карту;
  foreach ($findWords as $p => $word) {
    $horizontalMap[$word] =  strpos(implode("", $line), $word); //поменял тут местами с № => $word; на $word => №
  }

  return $horizontalMap;
}

//Проверяет слова найденные по маске
//Возвращает массив возможных слов;
function checkMap($map, $claim)
{
  $answersArr = [];
  foreach ($map as $word => $pos) {
    $word = preg_split("//u", $word, -1, PREG_SPLIT_NO_EMPTY);
    $mask = getMask($word);
    $name = count($word) . $mask . ".json";
    $words = checkCache($name, $mask, "https://poncy.ru/crossword/crossword-solve.json?mask=", 800000);
    $encryptedWord = createEncrtyptedWord($word, $claim);
    $result = findMatch($words, $claim, $encryptedWord);
    $answersArr = addVariant($result, $answersArr);
  }
  echoResult($answersArr, $encryptedWord);
  return $answersArr;
}

//Записать найденное неразгаданное слово в строку
//Возвращает результирующий массив
function writeWord($resultArr, $answersArr, $row = 0, $map, $columnMax = 0)
{
  foreach ($map as $word => $pos) {
    if ($pos - 1 >= 0) {
      $column = $pos - 1;
    } else {
      $column = $pos;
    }
  }

  if (count($answersArr) === 1) {
    $result = preg_split("//u", $answersArr[0], -1, PREG_SPLIT_NO_EMPTY);
    $resultCount = 0;
    $i = $row;
    for ($j = $column; $j < $columnMax; $j++) {
      $resultArr[$i][$j] = $result[$resultCount];
      $resultCount++;
    }
  }
  return $resultArr;
}


//Собирает Набор букв для Результирующего слова
//Возвращает массив из 3 элементов;
function getFinalClaim($resultArr, $claim = [])
{
  $converter = [
    "1" => $claim[0],
    "2" => $claim[1],
    "3" => $claim[2],
    "4" => $claim[3],
    "5" => $claim[4],
    "6" => $claim[5]
  ];

  $finalWordpart1 = [
    $resultArr[0][4], $resultArr[3][3], $resultArr[4][7]
  ];

  $finalWordpart2 = [
    $resultArr[2][3], $resultArr[3][9], $resultArr[4][9]

  ];

  $finalWordpart3 = [
    $resultArr[1][6], $resultArr[2][1], $resultArr[3][0]
  ];

  $finalClaim = [strtr(implode("", $finalWordpart1), $converter), strtr(implode("", $finalWordpart2), $converter), strtr(implode("", $finalWordpart3), $converter)];
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
  ["3", "6", "5", "5", "5", "1", "6", "5", "6", "3"],
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
$horizontalResultArr = solveCrossword($resultArr, $encryptedWords, $claim, $i, $url);

//Слова по вертикали
echo ("<br>\n");
echo ("Слова по вертикали:" . "<br>\n");
$verticalArr = createVerticalArr($horizontalResultArr, $encryptedWords);
$verticalResultArr = solveCrossword($verticalArr[0], $verticalArr[1], $claim, $i, $url);

//Слияние вертикального и горизонтального массивов
$resultArr = mergeVerticalHorizontal($verticalResultArr, $horizontalResultArr);

//Отмечаю черные поля;
$resultArr = fillBlackFields($resultArr);

//Создаем актуальные массивы
$horizontalArr = $resultArr;
$verticalArr = createVerticalArr($resultArr, $encryptedWords);
$verticalArr = $verticalArr[0];

//Заполняем пробелы
$horizontalArr = fillGaps($horizontalArr, $claim);
$verticalArr = fillGaps($verticalArr, $claim);

//Делаем слияние
$resultArr = mergeVerticalHorizontal($verticalArr, $horizontalArr);

//Выводим результат
echo "<br>\n";
echo "В итоге получилось: ";
echo "<br>\n";
viewHorizontalResult($resultArr);
viewVerticalResult($resultArr);


//Результирующее слово
//Входные данные для поиска
$finalClaim = getFinalClaim($resultArr, $claim);
$finalEncryptedWord = ["1", "1", "1", "2", "2", "2", "3", "3", "3"];

//Поиск и вывод на экран
echo ("<br>\n");
echo ("Финальное слово: " . "<br>\n");
getFinalWord($finalClaim, $finalEncryptedWord);
