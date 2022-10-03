
const board = document.querySelector("#board");
const crossword = document.querySelector("#crossword");
const column = 5;
const row = 10;

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
crossword_button.href = "./request.php";
crossword_button.innerText = "Разгадать";
crossword.append(crossword_button);

