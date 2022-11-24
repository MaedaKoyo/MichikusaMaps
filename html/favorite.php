<?php
	session_start();
	//宣言
	$favorite_places = array();
  $i = 1;
	//SESSIONが存在しているか
	if(!empty($_SESSION['favorite_places'])){
    //$favorite_placesにコピー
    $favorite_places = $_SESSION['favorite_places'];
  }
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>お気に入り</title>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="../css/text.css">
</head>
<body>
<header>
    <h1 class="headline">
      <a href="top.php"><img src="../img/logo.jpg"></a>
    </h1>
		<ul class="nav-list">
		  <li class="nav-list-item">
			<a href="./rank.php">ランキング</a>
		  </li>
		  <li class="nav-list-item"><a href="./favorite.php">お気に入り一覧</a></li>
		</ul>
  </header>
  <form action="" method="get">
	<div id="main">
  	<div id="favorite">
		<?php if(!empty($_SESSION['favorite_places'])){?>
		<table class="table table-striped">
    <tr><td colspan="4">お気に入り一覧</td></td>
		<?php foreach($favorite_places as $key =>$value){ $delete = $key;?>
		<tr>
    <th scope="row"><?php echo $i;?></th>
		<td><?php echo $key;?></td>
		<td><a href="top.php?place_id=<?php echo $value;?>&name=<?php echo $key;?>"><button type="button" class="btn btn-outline-primary">詳細ページ</button></a></td>
		<td><a href="favorite.php?reset=<?php echo $delete;?>"><button type="button" class="btn btn-outline-success">お気に入り削除</button></a></td>
		</tr>
		<?php $i++;} }else{echo '<p class="text-center">お気に入りのスポットなし</p>';}?>
		</table>
		</div>
	</div>
	</form>
<?php
  //削除ボタンが押されたとき(下に置いてね)
  if(isset($_GET['reset']) ) {
    unset($favorite_places[$delete]);
	$_SESSION['favorite_places'] = $favorite_places;
	header('Location: favorite.php');
  }
?>
</body>
</html>
