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
$claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ", "ЬЯ"];



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
      if (strpos($claim[$encryptedWord[(int)$letter] - 1], $value) !== false) {
      } else {
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




//Ищет слово по маске в заданном наборе букв; Передаем массив из 3 подмассивов на каждую букву;
function checkClaimWords($currentClaimWords, $encryptedWord, $claim = [])
{
  $resultWords = [];
  foreach ($currentClaimWords as $c => $currentLetterWords) {
    var_dump("Результирующий массив: " . implode("", findMatch($currentLetterWords, $claim, $encryptedWord)));
    var_dump("Текущий массив: " . $c . " Количество элементов: " . count($currentLetterWords) . "<br>\n");
  }
  return;
}

// var_dump("Результирующий массив: " . implode("", findMatch($words4[2], $claim, $encryptedWords[4])));

// var_dump("Результирующий массив: " . implode("", findMatch(Конкретный_Набор_БУКВ[Конректная_буква], Все_наборы_БУКВ, Массив_ШИФРОВ[Конкретный_ШИФР])));

// function checkClaimWords(Конкретный_набор_букв, Конкретный_ШИФР, Все_наборы_БУКВ)

// Нужно найти совпадение Первой_Буквы_ШИФРА и Конкретного_Набора_Букв

$myMap = [
  $claim[0] => $words1,
  $claim[1] => $words2,
  $claim[2] => $words3,
  $claim[3] => $words4,
  $claim[4] => $words5
];

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
  }
}

// var_dump(findCurrentClaim($claim, $encryptedWords[0]));
// var_dump($myMap[findCurrentClaim($claim, $encryptedWords[0])]);
foreach ($encryptedWords as $e => $currentEncryptedWord) {
  checkClaimWords($myMap[findCurrentClaim($claim, $currentEncryptedWord)], $currentEncryptedWord, $claim);
}

// Вызов функции
// checkClaimWords($myMap[findCurrentClaim($claim, $encryptedWords[0])], $encryptedWord[0], $claim);


//encryptedWords = масстив; зашифрованные слова
//encryptedWords[0] = первое код зашифрованного слова; 1153241526
//encryptedWords[0][0] = первый символ кода зашифрованного слова; 1
//words1 - массив данных под первый тип букв; АВГ
//words1[0] - массив слов начинающихся на букву А
//words2 - массив данных под второй тип букв; ЕИК
//words2[0] - массив слов начинающихся на букву Е












//Результирующее слово
$encryptedWordResult = preg_split("//u", "111222333", -1, PREG_SPLIT_NO_EMPTY);


$wordsR[0] = json_decode(file_get_contents("./data/dataResultC.json"), true)["words"];
$wordsR[1] = json_decode(file_get_contents("./data/dataResultK.json"), true)["words"];
$wordsR[2] = json_decode(file_get_contents("./data/dataResultU.json"), true)["words"];

$claimNew = [$claim[1] . $claim[3] . $claim[4],  $claim[3] . $claim[2] . $claim[5], $claim[2] . $claim[3] . $claim[4]];

// var_dump("<br>\n");
// var_dump("ВОоооооооооооот здесь:" . $claimNew[0] . " " . $claimNew[1] . " " . $claimNew[2] . "<br>\n");


checkClaimWords($wordsR, $encryptedWordResult, $claimNew);
