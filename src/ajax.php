<?php
echo "wait please; OK BRO?";
echo ("<br>\n");
sleep(2);
echo ("Вывожу полученный запрос:");
echo ("<br>\n");

print_r($_POST);
echo "Parsing finished!";

echo ("<br>\n");
echo ("<br>\n");

var_dump($_POST);

$myJson = json_decode($_POST["myData"], true);
$words = $myJson["inputArr"];
$claim = $myJson["claim"];

echo ("<br>\n");
echo ("<br>\n");


// print_r($words);
// foreach
echo (count($words));
echo ("<br>\n");
echo (count($claim));



echo ("<br>\n");

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

viewHorizontalResult($words);
