
const board = document.querySelector("#board");
const crossword = document.querySelector("#crossword");
const column = 5;
const row = 10;
const colors = [
  '#e74c3c',
  '#8e44ad',
  '#3498db',
  '#e67e22',
  '#2ecc71'];


//Наполним форму шифрами
const inputArr = [
  ["1", "1", "5", "3", "2", "4", "1", "5", "2", "6"],
  ["1", "6", "5", "6", "3", "3", "5", "3", "6", "1"],
  ["5", "4", "2", "4", "2", "5", "1", "3", "2", "2"],
  ["3", "6", "5", "5", "5", "1", "6", "5", "6", "3"],
  ["4", "2", "1", "3", "6", "3", "3", "4", "5", "6"],
];

//Наборы букв
const claim = ["АВГ", "ЕИК", "ЛНО", "ПРС", "ТУЩ", "ЬЯ"];

//Заголовок
const crossword_header = document.createElement('div');
crossword_header.classList.add("crossword__header");
crossword_header.innerText = "crossWordHelper"
crossword.append(crossword_header);

//Доска для букв
for (let i = 0; i < column; i++) {
  const crossword_row = document.createElement('div');
  crossword_row.classList.add("crossword__row");
  crossword_row.id = `line_${i}`;
  for (let j = 0; j < row; j++) {
    const square = document.createElement('div');
    square.classList.add("square");
    square.id = `element_${i}${j}`;
    square.innerText = inputArr[i][j];
    crossword_row.append(square);
  }
  crossword.append(crossword_row);
}


//Строка для финального слова
const crossword_final = document.createElement('div');
const finalCol = 9;
crossword_final.classList.add("crossword__final", "crossword__row");
for (let i = 0; i < finalCol; i++) {
  const square = document.createElement('div');
  square.classList.add("square");
  square.id = `final_${i}`;
  crossword_final.append(square);
}
crossword.append(crossword_final);


//Кнопка для разгадывания
const crossword_button = document.createElement('a');
crossword_button.classList.add("crossword__button");
// crossword_button.href = "./request.php";
crossword_button.innerText = "Разгадать";
crossword.append(crossword_button);



//Добавляем цвета для финальног ослова
let element;
let color;
let finalWordElement;
const finalWordPos = [
  [[0, 4], [3, 3], [4, 7]],
  [[2, 3], [3, 9], [4, 9]],
  [[1, 6], [2, 1], [3, 0]]
]

for (let i = 0; i < 3; i++) {
  color = colors[i];
  for (let j = 0; j < 3; j++) {
    finalWordElement = document.querySelector(`#final_${3 * i + j}`);
    finalWordElement.style.backgroundColor = `${color}`;
    finalWordElement.style.boxShadow = `0 0 3px ${color}, 0 0 15px ${color}`;

    element = document.querySelector(`#element_${finalWordPos[i][j][0]}${finalWordPos[i][j][1]}`);
    element.style.backgroundColor = `${color}`;
    element.style.boxShadow = `0 0 3px ${color}, 0 0 15px ${color}`;
  }
}


//Отправляю запрос на сервер

let obj = {
  "inputArr": inputArr,
  "claim": claim
}

let myJson = JSON.stringify(obj);

let jsonGET;
let resultArr;
let finalWord;
const spinner = document.querySelector(".spinner");

crossword_button.addEventListener("click", e => {
  spinner.classList.remove("hidden");
  $.ajax({
    url: "ajaxJSON.php", //Страница к которой обращаемся
    type: "POST", //Тип запроса
    dataType: "text",
    data: { //Параметры запроса
      myData: myJson
    },
    success: function (res) {
      // В случае удачного обращения мы получаем данные
      // от test.php в переменную res
      //Выполняем какие то действия..
      // $("#preloader").hide(); //Скрыли гифку, с id preloader
      spinner.classList.add("hidden");
      // $("#test").html(res); //Добавили код, который получили со страницы test.php
      // $("#my_div").html("Parsing is DONE; CROSSWORD SOLVED"); // STOP SPINNER.
      jsonGET = JSON.parse(res);
      resultArr = jsonGET["resultArr"];
      finalWord = jsonGET["finalWord"];
      console.log(resultArr);
      console.log(finalWord);
      //Заполняю доску ответами из полученного массива
      for (let i = 0; i < column; i++) {
        for (let j = 0; j < row; j++) {
          element = document.querySelector(`#element_${i}${j}`);
          element.innerHTML = resultArr[i][j];
        }
      }
      //Заполняю результирующеее слово
      for (let i = 0; i < finalCol; i++) {
        element = document.querySelector(`#final_${i}`);
        element.innerHTML = finalWord[i];
      }
    }
  });
})


