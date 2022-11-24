<?php

//db関数読み込み
require_once '../func/db.php';
//db接続
$link = connect_db();

//ngワード削除
if(isset($_POST['ngword'])){
  $sql = "DELETE FROM ngword WHERE id = ".$_POST["ngword"];
  mysqli_query($link, $sql);
  header('Location:./management.php');
}

//ngワード登録
if(isset($_POST['insertngword']) && $_POST['insertngword'] != ''){
  $sql = "INSERT INTO ngword (word) VALUES('".$_POST['insertngword']."')";
  mysqli_query($link, $sql);
  header('Location:./management.php');
}

//ランキング取得
// $rank = select($link, 'ranking',null,null);

//ngワード取得
$list = select($link, 'ngword', null, null);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="../css/management.css">
<title>管理者画面</title>
</head>
<body>
  <header class="nav">
    <h1>管理者画面</h1>
  </header>
<div class="d-flex flex-row bd-highlight mb-3 text-center">
  <div class="p-2 bd-highlight">
    <div class="row">
      <table class="table table1">
        <tr><th>👑総合ランキング👑</th></tr>
        <tr><td>ランキング1位</td></tr>
        <tr><td>ランキング2位</td></tr>
        <tr><td>ランキング3位</td></tr>
        <tr><td>ランキング4位</td></tr>
        <tr><td>ランキング5位</td></tr>
      </table>
    </div>
  </div>
  <div class="p-2 bd-highlight right-table">
    <div class="row table2">
      <div class="form">
        <form action="#" method="post">
          <input type="text" name="insertngword"><br>
          <button type="submit">登録</button>
      </div>

        <table class="table">
          <?php foreach($list as $row): ?>
          <tr><td><?php echo $row["word"];?></td><td><button type="submit" name="ngword" value="<?php echo $row["id"];?>">削除</button></td></tr>
          <?php endforeach; ?>
        </table>
      </form>
    </div>
  </div>
</div>

</body>
</html>
