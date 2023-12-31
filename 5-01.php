<?php
//DB接続用関数
function db_connect(){
    $mysqli = new mysqli('localhost', 'DBUser', 'DBPass', 'DBName');
    if ($mysqli->connect_error) {
        echo $mysqli->connect_error;
        exit();
    }
    return $mysqli; }
//新規書き込み用関数
function Write_New_File($content,$password,$mysqli){
        #プリペアードステートメント（SQLインジェクション対策）
        var_dump($mysqli);
        $stmt = $mysqli->prepare("INSERT INTO board (comment,name,date) VALUES (?, ?, ?);");
        $stmt->bind_param("sss", $content[0],$content[1],$content[2]);
        $stmt->execute();
        $stmt = $mysqli->prepare("INSERT INTO pw (password) VALUES (?);");
        $stmt->bind_param("s", $password);
        $stmt->execute();
        unset($_POST["submit_choice"]); }
//投稿削除用関数
function Delete_value($number,$mysqli,$password,$count){
        $SQL="SELECT password FROM pw WHERE id =".$number.";";
        $query=mysqli_query($mysqli, $SQL); 
        if($query){
            foreach($query as $query_){
                $collect_password=implode("",$query_);
        }}
        if($collect_password==$password){
            $stmt = $mysqli->prepare("DELETE t1, t2 FROM board AS t1 INNER JOIN pw AS t2 ON t1.id = t2.id WHERE t1.id = ?;");
            $stmt->bind_param("i", $number);
            $stmt->execute();
            var_dump($stmt);
            for($i = 1 ; $i<=$count ; $i=$i+1){
                $stmt = $mysqli->prepare("UPDATE board SET id=id-1 WHERE id > ?;");
                $stmt->bind_param("i", $number);
                $stmt->execute();
                $stmt = $mysqli->prepare("UPDATE pw SET id=id-1 WHERE id > ?;");
                $stmt->bind_param("i", $number);
                $stmt->execute();
                //AUTO_INCREMENTの初期化
                $altSQL1 = "ALTER TABLE board auto_increment = 1;";
                mysqli_query($mysqli, $altSQL1);
                $altSQL2 = "ALTER TABLE pw auto_increment = 1;";
                mysqli_query($mysqli, $altSQL2);}
        }else{
            echo "<h2>パスワードが違いますよ！</h2>";
        }}
//編集用関数（編集フォーム②のあと）        
function rewrite_file($number,$comment,$name,$mysqli){
        $stmt = $mysqli->prepare("UPDATE board SET comment=? , name=?  WHERE id = ?;");
        $stmt->bind_param("ssi", $comment,$name,$number);
        $stmt->execute();
}

?>
<html>
<header>
<link rel="stylesheet" href="5-01.css">
<script type="text/javascript">
<!--
/*
function openwin() {
    window.open("./5-01sub.php", "", "width=400,height=400,toolbar=no,location=yes,status=yes,menubar=no,scrollbars=yes,resizable=yes");
}
*/
// -->
</script>
</header>
<body>
<h3>課題5-1</h3>
<!-- <form method="GET" action="./5-01sub.php"> -->
<form method="POST" action="">

<h3>おかえりなさい！書込にする？削除にする？編集にする？それとも・・・？</h3>
<font size=4 color=#55FF55>
<input type="radio" name="choice" value="write">新規書込<br>
<input type="radio" name="choice" value="delete">投稿削除<br>
<input type="radio" name="choice" value="rewrite">投稿編集<br>
</font>
<input type="submit" name="submit_choice" value="決定" onclick="openwin()">
</form>

<?php 
$mysqli = db_connect();
#var_dump($mysqli);

if(isset($_POST["submit_choice"])){ 
    switch($_POST["choice"]){
        case("write"):?>
            <form method="POST" action="">
            <!--Valueじゃなくてplaceholderを使えばいちいち値を消す必要がなくなる-->
            名前<br>
            <input type="text" name="name" placeholder="名前を入れてね" value="test"><br>
            パスワードを設定してください<br>
            <input type="password" name="password" placeholder="パスワードを入れてね"><br>
            コメント<br>
            <textarea name="comment" placeholder="何か書いてね">test</textarea><br>
            <input type="submit" name="submit_write" value="送信">
            </form><?php
            if (!empty ($_POST["comment"]) && !empty ($_POST["name"]) && !empty ($_POST["password"]) && isset($_POST["submit_write"])){
                $content = array(htmlspecialchars($_POST["comment"]),htmlspecialchars($_POST["name"]),"投稿日時：".date('Y-m-d H:i:s'));
                $password = htmlspecialchars($_POST["password"]);
                Write_New_File($content,$password,$mysqli);
            }else{
                if(isset($_POST['submit_write'])){
                    if(empty($_POST["comment"])){ echo "<h2>ERROR! コメントを入力してくださいまし！</h2>"; }
                    if(empty($_POST["name"])){ echo "<h2>ERROR! お名前を入力してくださいまし！</h2>"; }
                    if(empty($_POST["password"])){ echo "<h2>ERROR! パスワードを設定してくださいまし！</h2>"; }
                    echo "<input value='前のページに戻る' onclick='history.back();' type='button'>";
                    #全てを終わらせる。
                    exit;
            }}
            break;
        case("delete"): ?>
            削除フォーム<br>
            <form method="POST",target="">
            <?php
                $SQL5="SELECT count(*) FROM board;";
                $query5=mysqli_query($mysqli, $SQL5); 
                if($query5){
                    foreach($query5 as $query_5){
                       $count=implode("",$query_5); 
                }}
                echo '<select name="number">';
                for($i = 1 ; $i <= (int)$count ; $i = $i + 1){
                    echo '<option value='.$i.'>'.$i.'</option><br>';
                }
                echo '</select>'; ?><br>
                パスワード:<input type="password" name="password" placeholder="パスワードを入れてね"><br>
                <input type="submit" name="submit_delete" value~"削除するます！">
                </form>
                <input value='前のページに戻る' onclick='history.back();' type='button'>
                <?php
                if (!empty  ($_POST["password"]) && isset($_POST["submit_delete"])){
                $number = $_POST["number"];
                $password = htmlspecialchars($_POST["password"]);
                Delete_value($number,$mysqli,$password,$count);
                $_POST["submit_choice"]=array();
            }else{
                if(isset($_POST['submit_delete'])){
                    if(empty($_POST["comment"])){ echo "<h2>ERROR! コメントを入力してくださいまし！</h2>"; }
                    if(empty($_POST["name"])){ echo "<h2>ERROR! お名前を入力してくださいまし！</h2>"; }
                    if(empty($_POST["password"])){ echo "<h2>ERROR! パスワードを設定してくださいまし！</h2>"; }
                    echo "<input value='前のページに戻る' onclick='history.back();' type='button'>";
                    #全てを終わらせる。
                    exit;
            }}
            break;
        case("rewrite"):
            if(!isset($_POST["submit_rewrite"])){ ?>
            編集フォーム<br>
            <form method='POST' action=''>
            <?php
                $SQL5="SELECT count(*) FROM board;";
                $query5=mysqli_query($mysqli, $SQL5); 
                if($query5){
                    foreach($query5 as $query_5){
                       $count=implode("",$query_5); 
                }}
                echo '<select name="number">';
                for($i = 1 ; $i <= (int)$count ; $i = $i + 1){
                    echo '<option value='.$i.'>'.$i.'</option><br>';
                }
                echo '</select>'; ?>
                パスワード:<input type="password" name="password" placeholder="パスワードを入れてね"><br>
                <input type="submit" name="submit_rewrite" value="編集するます！">
                </form>
            <?php }else{
            // 編集フォーム 
            $number=$_POST['number'];
            $password=$_POST['password'];
            //パスワード一致処理
            $SQL="SELECT password FROM pw WHERE id =".$number.";";
            $query=mysqli_query($mysqli, $SQL); 
            if($query){
                foreach($query as $query_){
                    $collect_password=implode("",$query_);
            }}
            if($password==$collect_password){
            //プリペアードステートメントで動かしたいけど上手いこと動いてくれない。
            #$stmt = $mysqli->prepare("SELECT * FROM board where id = ?;");
            #$stmt->bind_param("i", $number);
            #$stmt->execute();
            $SQL="SELECT * FROM board where id=".$number.";";
            $query=mysqli_query($mysqli, $SQL);
            if($query){
            foreach($query as $query_2){
                $name=$query_2["name"];
                //$date=$query_2["date"]."<br>";
                $Show_com=$query_2["comment"];
            }}
            ?>
            <form method="POST" action="">
            <!--Valueじゃなくてplaceholderを使えばいちいち値を消す必要がなくなる-->
            <input type="hidden" name="number" value=<?php echo $number; ?>>
            名前<br>
            <input type="text" name="name" value=<?php echo $name; ?>><br>
            コメント<br>
            <textarea name="comment" placeholder="何か書いてね"><?php echo $Show_com; ?></textarea><br> <!-- textareaはvalue引数を持たない -->
            <input type="submit" name="submit_rewrite2" value="送信">
            </form>
            <?php }else{ //パスワード不一致時の処理
                echo "<h2>パスワードが違います！</h2>";
                echo "<input value='前のページに戻る' onclick='history.back();' type='button'>";
                exit;
            }}
            if(isset($_POST["submit_rewrite2"])){
                $number=$_POST["number"];
                $comment=$_POST["comment"];
                $name=$_POST["name"];
                rewrite_file($number,$comment,$name,$mysqli);
            }
            }} //submit_choice判定用とSwitch文の閉じ ?>
<!--データベースを表示させます。-->
<h3>Welcome to Buliding Board</h3>
<h4>デバッグしてください！！！むちゃくちゃにしてください！！！もう無茶苦茶だよ！！！</h4>
<h4>テーマ：自由に遊んでください！</h4>
<?php
$SQL5="SELECT * FROM board;";
$query5=mysqli_query($mysqli, $SQL5); 
if($query5){
    foreach($query5 as $query_5){
        echo $query_5["id"].$query_5["name"].$query_5["date"]."<br>";
        $Show_Com=str_replace("\n","<br>",$query_5["comment"]);
        echo $Show_Com;
        echo "<br>";
    }
} ?>
<?php $mysqli->close(); ?>
</body>
</html>
<?php  ?>