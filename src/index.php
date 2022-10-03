<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>crossWordHelper</title>
  <link rel="stylesheet" href="./main.css">
</head>

<body>
  <div class="preloader">
    <div class="preloader__row">
      <div class="preloader__item"></div>
      <div class="preloader__item"></div>
    </div>
  </div>
  <div class="main">

    Это стартовая страница
    <br>
    Здесь значит будет верстка
    <br>
    <a href="./request.php" class="">Нажать сюда, чтобы перейти к решению</a>
    <br>
    Внимание!
    Поиск может выполняться до 1 минуты;
    Возможно придется немного подождать

  </div>
  <script>
    window.onload = function() {
      document.body.classList.add('loaded_hiding');
      window.setTimeout(function() {
        document.body.classList.add('loaded');
        document.body.classList.remove('loaded_hiding');
      }, 500);
    }
  </script>
</body>

</html>