<!--
Author: Damon Godwin
Date: 4/16/2016
-->
<?php
    session_start();
    if(isset($_SESSION["dupl"])) {
        echo "Book already exists";
        unset($_SESSION["dupl"]);
        unset($_SESSION["insrtd"]);
    }
    elseif(isset($_SESSION["insrtd"])) {
        echo "Book inserted succesfully";
        unset($_SESSION["insrtd"]);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php
            $bt = $st = $au = $is = $pub = $ed = $yr = $ch = "";
            $btErr = $auErr = $isErr = $pubErr = $edErr = $yrErr = $chErr = "";
            $dupl = false;
            $hasErrs = false;
            $insrtd = false;
            if($_SERVER["REQUEST_METHOD"] === "POST") {
                if (empty($_POST["book_title"])) {
                    $btErr = "* A book title is required";
                    $hasErrs = true;
                }
                if(empty($_POST["authors"])) {
                    $auErr = "* An author is required";
                    $hasErrs = true;
                }
                if(empty($_POST["isbn"])) {
                    $isErr = "* An ISBN required";
                    $hasErrs = true;
                }
                if(empty($_POST["publisher"])) {
                    $pubErr = "* A publisher is required";
                    $hasErrs = true;
                }
                if(empty($_POST["edition"])) {
                    $edErr = "* An edition is required";
                    $hasErrs = true;
                }
                if(empty($_POST["year"])) {
                    $yrErr = "* A year is required";
                    $hasErrs = true;
                }
                if(empty($_POST["chapters"])) {
                    $chErr = "* A chapter is required";
                    $hasErrs = true;
                }
                if(!$hasErrs) {
                    $link = mysqli_connect("localhost","root", "", "mathdb");

                    $bt = addslashes($_POST["book_title"]);
                    $st = addslashes($_POST["subtitle"]);
                    $au = addslashes($_POST["authors"]);
                    $is = addslashes($_POST["isbn"]);
                    $pub = addslashes($_POST["publisher"]);
                    $ed = addslashes($_POST["edition"]);
                    $yr = addslashes($_POST["year"]);
                    $ch = addslashes($_POST["chapters"]);

                    $dup = "SELECT isbn FROM book WHERE isbn = '$is'";
                    $isDup = mysqli_query($link, $dup);
                    $num_rows = mysqli_num_rows($isDup);

                    if($num_rows) {
                        $_SESSION["dupl"] = true;
                        mysqli_close($link);
                        header("Location: {$_SERVER['HTTP_REFERER']}");
                    }
                    else {
                        $insertQuery = "INSERT INTO book (book_title, subtitle, authors, isbn, edition_number, year_number, publisher, number_of_chapters, create_time)"
                                . "VALUES ('$bt', '$st', '$au', '$is', '$ed', '$yr', '$pub', '$ch', CURRENT_TIMESTAMP())";
                        mysqli_query($link, $insertQuery);
                        $_SESSION["insrtd"] = true;
                        mysqli_close($link);
                        header("Location: {$_SERVER['HTTP_REFERER']}");
                    }
                }
            }
        ?>
        
        <?php
        if($_SERVER["REQUEST_METHOD"] === "GET" || $hasErrs) {
        ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="bookForm">
                <table>
                    <tr>
                        <td>Book Title:</td>
                        <td><input class="borders" type="text" name="book_title"><span class="error"><?php echo $btErr;?></span></td>
                    </tr>
                    <tr>
                        <td>Subtitle:</td>
                        <td><input class="borders" type="text" name="subtitle"><span class="error"></span></td>
                    </tr>
                    <tr>
                        <td>Authors:</td>
                        <td><input class="borders" type="text" name="authors"><span class="error"><?php echo $auErr;?></span></td>
                    </tr>
                    <tr>
                        <td>ISBN:</td>
                        <td><input class="borders" type="text" name="isbn"><span class="error"><?php echo $isErr;?></span></td>
                    </tr>
                    <tr>
                        <td>Publisher:</td>
                        <td><input class="borders" type="text" name="publisher"><span class="error"><?php echo $pubErr;?></span></td>
                    </tr>
                    <tr>
                        <td>Edition:</td>
                        <td><input class="borders" type="text" name="edition"><span class="error"><?php echo $edErr;?></span></td>
                        <td>Year:</td>
                        <td><input class="borders" type="text" name="year"><span class="error"><?php echo $yrErr;?></span></td>
                        <td>Chapters:</td>
                        <td><input class="borders" type="text" name="chapters"><span class="error"><?php echo $chErr;?></span></td>
                    </tr>
                </table>
                <p>
                    <input class="buttons borders" type="submit" value="Enter" name="submit">
                    <input class="buttons borders" type="button" value="Book-Off" onclick="window.location.href='index.php'"/>
                </p>
            </form>
            
        <?php
        }
        ?>
    </body>
</html>
