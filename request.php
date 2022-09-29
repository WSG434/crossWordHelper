<?php
include_once 'simple_html_dom.php';


//Функция получения данных;
function getData($url = "", $mask = "", $count = 0, $arr_words = [], $i = 0)
{
  //Отправили запрос, получили ответ
  $response = file_get_contents($url);
  $myJson = json_decode($response, true);

  $context = stream_context_create(
    array(
      "http" => array(
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
      )
    )
  );

  // echo file_get_contents("https://www.google.com/", false, $context);



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


// $context = stream_context_create(
//   array(
//     "http" => array(
//       "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
//     )
//   )
// );
// echo file_get_contents("https://www.google.com/", false, $context);


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
    if ($result !== "") {
      // echo ("Шифр: " . implode("", $encryptedWord) . "; ");
      // echo ("Результирующий массив: " . $result . "; ");
      // echo ("Текущий массив: " . $c . " Количество элементов: " . count($currentLetterWords) . "<br>\n");
      array_push($resultWords, $result);
    }
  }
  return implode(", ", $resultWords);
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
function echoResult($result, $encryptedWord)
{
  if ($result !== "") {
    echo ("Результат: " . $result . " Шифр: " . implode("", $encryptedWord) . "<br>\n");
    return true;
  } else {
    echo ("К сожалению, слово "  . " Шифр: " . implode("", $encryptedWord) .  " не найдено;" . "<br>\n");
    return false;
  }
}


//-----------------------------


//Получаю данные в JSON, обрабатываю их и привожу к массиву;
// $myJson = json_decode($data1, true);
// $words1 = $myJson["words"];

// $words1 = json_decode($data1, true);

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


//Массив заданных условием букв;
$claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ", "ЬЯ"];


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
  echo ("Слово №: " . ($e + 1) . " ");
  // echo ("Результирующий массив: " . $result . "; " . "<br>\n");

  echoResult($result, $currentEncryptedWord);
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
  echo ("Слово №: " . ($e + 1) . " ");
  // echo ("Результирующий массив: " . $result . "; " . "<br>\n");
  echoResult($result, $currentEncryptedWord);
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