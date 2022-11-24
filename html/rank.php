<?php
//db関数読み込み(昔のでーたかも)
require_once '../func/db.php';
//db接続
$link = connect_db();

$sql = "SELECT * FROM ranking ORDER BY count desc LIMIT 5";
$res = mysqli_query($link,$sql);
$list = mysqli_fetch_array($res);
$rank_list = [];
$cnt = 1;
while($list){
	$list = $list + array('rank'=>$cnt."位: ");
	array_push($rank_list, $list);
	$list = mysqli_fetch_array($res);
	$cnt++;
}
mysqli_close($link);
?>
<!doctype html>
<html lang="ja">
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="../css/text.css">
<script src="../js/jquery-3.5.1.min.js"></script>
<script src="../js/jquery.validate.min.js"></script>
</head>
<body>
	<header>
		<h1 class="headline">
		  <a href="./top.php"><img src="../img/logo.jpg"></a>
		</h1>
		<ul class="nav-list">
			<li class="nav-list-item">
			<a href="./rank.php">ランキング</a>
			</li>
			<li class="nav-list-item"><a href="./favorite.php">お気に入り一覧</a></li>
		</ul>
	  </header>
	<hr>
	<h1 class="text-center">👑寄り道ランキング👑</h1>
	<div id="table" class="container">
	 <table class="table table1 text-center">
        <tr><td>ランキング</td><td>アクセス数</td></tr>
    	<?php foreach($rank_list as $row):?>
		<tr><td class="text-center col-6"><a href="./top.php?name=<?php echo $row['store_name'];?>&place_id=<?php echo $row['place_id'];?>"><?php echo $row['rank'].$row['store_name'] ;?></td><td class="text-center col-6"><?php echo $row['count'];?>回</td></tr>
		<?php endforeach;?>
     </table>
	</div>
</body>
</html>
