<html>
<head>

</head>
    <body>



        <h1>BattleShips Game</h1>

        <p><?= $board; ?></p>

        <form action = "" method="post">
            <label for="hit">Enter coordinates (row, col), e.g. A5</label>
            <input type="text" name="hit" autofocus>
            <input type="submit" value="Hit">
        </form>


        <?php if($msg != '') : ?>
            <p><?= $msg ?></p>
        <?php endif; ?>

        <?php if($isGameFinished) : ?>
            <a href="">Play Again</a>
        <?php endif; ?>


    </body>



</html>