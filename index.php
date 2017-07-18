<!--
Author: Damon Godwin
Date: 4/16/2016
-->
<?php
    session_start();
    $_SESSION["listFirst"] = false;
    if(isset($_SESSION["duplicate"])) {
        echo "Question already exists";
        unset($_SESSION["duplicate"]);
        unset($_SESSION["inserted"]);
    }
    elseif(isset($_SESSION["inserted"])) {
        $_SESSION["listFirst"] = true;
        echo "Question inserted succesfully";
        unset($_SESSION["inserted"]);
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
            $problem = $page = $probNum = $book = "";
            $probErr = $pgErr = $pNumErr = "";
            $duplicate = false;
            $hasErrors = false;
            $inserted = false;
            if($_SERVER["REQUEST_METHOD"] === "POST") {
                if (empty($_POST["problem"]) || $_POST["problem"] == "Enter a problem") {
                    $probErr = "* A problem is required";
                    $hasErrors = true;
                }
                elseif($_POST["book"] == "-- Select a book --") {
                    $link = mysqli_connect("localhost","root", "", "mathdb");
                    $problem = addslashes($_POST["problem"]);
                    $insertQuery = "INSERT INTO questions (content, create_time)"
                                . "VALUES ('$problem', CURRENT_TIMESTAMP())";
                    mysqli_query($link, $insertQuery);
                    $_SESSION["inserted"] = true;
                    mysqli_close($link);
                    header("Location: {$_SERVER['HTTP_REFERER']}");
                }
                else {
                    if((empty($_POST["page"]) || $_POST["page"] == "page") && (empty($_POST["probNum"]) || $_POST["probNum"] == "prob-number")) {
                        $pgErr = "* A page number is required";
                        $pNumErr = "* A problem number is required";
                        $hasErrors = true;
                    }
                    elseif(empty($_POST["page"]) || $_POST["page"] == "page") {
                        $pgErr = "* A page number is required";
                        $hasErrors = true;
                    }
                    elseif(empty($_POST["probNum"]) || $_POST["probNum"] == "prob-number") {
                        $pNumErr = "* A problem number is required";
                        $hasErrors = true;
                    }
                    else {
                        $link = mysqli_connect("localhost", "root", "", "mathdb");
                        
                        $problem = addslashes($_POST["problem"]);
                        $book = addslashes($_POST["book"]);
                        $page = addslashes($_POST["page"]);
                        $probNum = addslashes($_POST["probNum"]);
                        
                        $sql = "SELECT bid FROM book WHERE book_title = '$book'";
                        $result = mysqli_query($link, $sql);
                        $row = mysqli_fetch_assoc($result);
                        $book_id = $row['bid'];
                        
                        $dup = "SELECT content FROM questions WHERE content = '$problem' AND page_num = '$page' AND question_num = '$probNum'";
                        $isDup = mysqli_query($link, $dup);
                        $num_rows = mysqli_num_rows($isDup);
                        
                        if($num_rows) {
                            $_SESSION["duplicate"] = true;
                            mysqli_close($link);
                            header("Location: {$_SERVER['HTTP_REFERER']}");
                        }
                        else {
                            $insertQuery = "INSERT INTO questions (book_id, content, create_time, page_num, question_num)"
                                    . "VALUES ('$book_id', '$problem', CURRENT_TIMESTAMP(), '$page', '$probNum')";
                            mysqli_query($link, $insertQuery);
                            $_SESSION["inserted"] = true;
                            mysqli_close($link);
                            header("Location: {$_SERVER['HTTP_REFERER']}");
                        }
                    }
                }                
            }
        ?>
        <?php
        if($_SERVER["REQUEST_METHOD"] === "GET" || $hasErrors) {
        ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="mainForm">
                <p>
                    <p><span class="error"><?php echo $probErr;?></span></p>
                    <p><span class="error"><?php echo $pgErr;?></span></p>
                    <p><span class="error"><?php echo $pNumErr;?></span></p>
                    <p><textarea class="textBox probBox borders" name="problem" form="mainForm">Enter a problem</textarea></p>
                    <?php
                        echo '<table class="tbl">';
                        echo '<tr class="tbl">';
                        echo '<td class="tbl borders">Question</td>';
                        echo '<td class="tbl borders">Page Number</td>'; 
                        echo '<td class="tbl borders">Question Number</td>';
                        echo '</tr>';
                        
                        $linkdb = mysqli_connect("localhost","root", "");
                        
                        $query = "CREATE DATABASE IF NOT EXISTS mathdb";
                        mysqli_query($linkdb, $query);
                        mysqli_close($linkdb);
                        
                        $link = mysqli_connect("localhost", "root", "", "mathdb");
                        
                        $tblquery = "CREATE TABLE IF NOT EXISTS book ("
                                . "bid INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                                . "book_title VARCHAR(150) NOT NULL,"
                                . "subtitle VARCHAR(200),"
                                . "authors VARCHAR(150) NOT NULL,"
                                . "isbn VARCHAR(150),"
                                . "edition_number INT(3) UNSIGNED DEFAULT '1',"
                                . "year_number INT(4) UNSIGNED DEFAULT '0',"
                                . "publisher VARCHAR(60),"
                                . "number_of_chapters INT(2) UNSIGNED DEFAULT '0',"
                                . "problem_total INT(5) NOT NULL DEFAULT '0',"
                                . "prob_ori_num INT(4) NOT NULL DEFAULT '0',"
                                . "knowledge_total INT(4) NOT NULL DEFAULT '0',"
                                . "book_type_id INT(1) UNSIGNED DEFAULT '1',"
                                . "old_book_id INT(10) NOT NULL DEFAULT '0',"
                                . "member_id INT(10) UNSIGNED DEFAULT '0',"
                                . "create_time DATETIME NOT NULL,"
                                . "del INT(1) UNSIGNED DEFAULT '0')";
                        
                        mysqli_query($link, $tblquery);
                        
                        $tblquery = "CREATE TABLE IF NOT EXISTS questions ("
                                . "ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                                . "content VARCHAR(255) NOT NULL,"
                                . "book_id INT(10) UNSIGNED,"
                                . "page_num INT(4) UNSIGNED,"
                                . "chapter_num INT(2) UNSIGNED,"
                                . "section_num INT(3) UNSIGNED,"
                                . "question_num INT(3) UNSIGNED,"
                                . "create_time DATETIME)";
                        
                        mysqli_query($link, $tblquery);
                        
                        if($_SESSION["listFirst"] == true) {
                            $query = "SELECT * FROM questions WHERE create_time NOT IN (SELECT MAX(create_time) FROM questions)";
                            $mostRecent = "SELECT * FROM questions WHERE create_time IN (SELECT MAX(create_time) FROM questions)";
                            $firstRes = mysqli_query($link, $mostRecent);
                            $row1 = mysqli_fetch_assoc($firstRes);
                            echo '<tr class="tbl">';
                            echo '<td class="tbl borders">' . $row1['content'] . '</td>';
                            echo '<td class="tbl borders">' . $row1['page_num'] . '</td>'; 
                            echo '<td class="tbl borders">' . $row1['question_num'] . '</td>';
                            echo '</tr>';
                        }
                        else {
                            $query = "SELECT * FROM questions";
                        }
                        
                        $res = mysqli_query($link, $query);
                        while($rows = mysqli_fetch_assoc($res)) {
                            echo '<tr class="tbl">';
                            echo '<td class="tbl borders">' . $rows['content'] . '</td>';
                            echo '<td class="tbl borders">' . $rows['page_num'] . '</td>'; 
                            echo '<td class="tbl borders">' . $rows['question_num'] . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    ?>
                <p>
                    <select class="borders" name="book">
                        <?php
                            echo '<option name="book0">-- Select a book --</option>' . "\n";
                            $sql = "SELECT book_title FROM book";
                            $result = mysqli_query($link, $sql);
                            $i = 1;
                            while($row = mysqli_fetch_assoc($result)) {
                            echo '<option name="book' . $i . '"' . '>' . $row["book_title"] . '</option>' . "\n";
                                $i = $i + 1;
                            }
                        ?>
                    </select>
                </p>
                <p>
                    <input class="textBox borders" type="text" value="page" name="page">
                    <input class="textBox borders" type="text" value="prob-number" name="probNum">
                </p>
                <input class="buttons borders" type="submit" value="Enter" name="submit">
                <input class="buttons borders" type="button" value="Book-on" onclick="window.location.href='bookPage.php'"/>
            </form>
            
        <?php
        }
        ?>
    </body>
</html>
