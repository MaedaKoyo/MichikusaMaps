<?php
//API関数読み込み
require_once '../func/APIreq.php';

$errmsg = '';

//top
if(isset($_GET['place_id'])){
    $place_id = $_GET['place_id'];
    $json_id = json_encode($place_id);
}

//座標取得
$result = GAPI_spotDetail($place_id);
$coo_x = $result['result']['geometry']['location']['lat'];
$coo_y = $result['result']['geometry']['location']['lng'];
$json_x = json_encode($coo_x);
$json_y = json_encode($coo_y);

//写真のid取得
$photo_arr = array();
$f_flg = 0;
if(isset($result['result']['photos']['2']['photo_reference'])){
  $f_flg = 1;
  for ($i=0; $i < 3; $i++) {
    $photo_arr[$i] = $result['result']['photos'][$i]['photo_reference'];
  }
}
//セッション処理
if (isset($_GET['name'])) {
  //値取得
  $place_id = $_GET['place_id'];
  $name = $_GET['name'];
  //宣言
  $favorite_places = array();
  session_start();
  //$_SESSION['favorite_places']が存在しない時、空の配列を入れる
   if(!isset($_SESSION['favorite_places'])) {
    $_SESSION['favorite_places'] = array();
  }elseif(isset($_SESSION['favorite_places'])){
    //存在していたら$favorite_placesにコピー
    $favorite_places = $_SESSION['favorite_places'];
  }

  //※テスト用リセットボタンが押されたとき
  // if(isset($_GET['reset']) ) {
  //   session_unset();
  // }

  //お気に入りボタンが押されたとき
  if( !empty($_GET['fav_add']) ) {
    //既にお気に入り一覧にあるか検索
    $result = in_array($place_id,(array)$favorite_places);
      if($result){  //あったとき
         $errmsg = "既に登録されています";
      }else{  //なかったとき
        //配列の末尾に$place_idを追加
        $favorite_places = $favorite_places + array($name => $place_id);
        //配列をsessionに格納
        $_SESSION['favorite_places'] = $favorite_places;
        $msg = "登録しました";
      }
    //var_dump($_SESSION['favorite_places']);
  }
  if( !isset($msg)){
    $msg = "お気に入り登録";
  }
}


?>

<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>詳細</title>
<head>
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
  <div id="main">
    <p>観光地詳細</p>
    <div id="map" style="width:100%; height:400px" class="map"></div>
    <script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=xxx"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>

    <script>
        var json_Id = JSON.parse('<?php echo $json_id; ?>');
        var j_x = JSON.parse('<?php echo $json_x; ?>');
        var j_y = JSON.parse('<?php echo $json_y; ?>');

        //place_idで場所指定
  		  var request = {
  		    placeId: json_Id
  		  };

  		  var infowindow = new google.maps.InfoWindow();
  		  var service = new google.maps.places.PlacesService(map);

  		  service.getDetails(request, function(place, status) {
  		  console.log(status);
  		  if (status == google.maps.places.PlacesServiceStatus.OK) {
  		   console.log(place);

         //map初期設定
         var map = new google.maps.Map(document.getElementById('map'), {
           center: new google.maps.LatLng(j_x,j_y),//地図の中心座標
           zoom: 15,//地図の縮尺値
           mapTypeId: 'roadmap'//地図の種類
         });

         //マーカー設置
  		   var marker = new google.maps.Marker({
  		     map: map,
  		     position: place.geometry.location
  		   });

         //html生成(場所の詳細)
         var name = document.getElementById('name');
         var add_code = '<p id="name1">'+place.name+'</p>';
         name.innerHTML = add_code;
        console.log(place.name);

         var adrs = document.getElementById('address');
         var add_code = '<p id="address1">'+place.formatted_address+'</p>';
         adrs.innerHTML = add_code;

         if (place.formatted_phone_number != undefined){
           var p_num = document.getElementById('phone');
           var add_code = '<p id="phone1">'+place.formatted_phone_number+'</p>';
           p_num.innerHTML = add_code;
         }

         if (place.opening_hours != undefined){
           var open_h = document.getElementById('open');
           var days = place.opening_hours.weekday_text;
           var add_code = '<a id="open1">'+days[0]+'</a><br><a id="open2">'+days[1]+'</a><br><a id="open3">'+days[2]+'</a><br><a id="open4">'+days[3]+'</a><br><a id="open5">'+days[4]+'</a><br><a id="open6">'+days[5]+'</a><br><a id="open7">'+days[6]+'</a><br><br>';
           open_h.innerHTML = add_code;
         }

         if (place.website != undefined) {
           var link = document.getElementById('website');
           var add_code = '<p id="website1"><a href="'+place.website+'">'+place.website+'</a></p>';
           link.innerHTML = add_code;
         }

         //html生成(お気に入りボタン)
         var fav = document.getElementById('fav');
         var code = '<input type="button" class="add_favobutton" onclick="location.href=\'detail.php?fav_add=登録&place_id='+json_Id+'&name='+place.name+'\'"value="お気に入り登録">';
         fav.innerHTML = code;


  		    google.maps.event.addListener(marker, 'click', function() {
  			  //クリックでコンソール表示
  			  console.log(place.place_id);
          //html生成
          // var text = document.getElementById('text');
          // var add_code ='<ul><li>'+place.name+'</li><li>'+place.formatted_address+'</li><li><a href="'+place.website+'">'+place.website+'</a></li></ul>';
          // text.innerHTML = add_code;
  		    });
  		   }
  		   });

    </script>

  </div>
  <div class="parent">
    <div class="img">
      <?php if ($f_flg == 1) : ?>
        <img class="image" src="https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=<?php echo $photo_arr['0']; ?>&key=xxx">
        <img class="image" src="https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=<?php echo $photo_arr['1']; ?>&key=xxx">
        <img class="image" src="https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=<?php echo $photo_arr['2']; ?>&key=xxx">
    <?php endif ; ?>
    </div>

  </div>
  <div class="parent">
  <table class="kaisha">
  <tr>
  <th>スポット名</th>
  <td><br><div id="name"></div></td>
  </tr>
  <tr>
  <th>所在地</th>
  <td><br><div id="address"></div></td>
  </tr>
  <tr>
  <th>電話</th>
  <td><br><div id="phone"></div></td>
  </tr>
  <tr>
  <th>営業時間</th>
  <td><br><div id="open"></div></td>
  </tr>
  <tr>
  <th>リンク</th>
  <td><br><div id="website"></div></td>
  </tr>
  <th>お気に入り</th>
  <td><br>
  <div id="fav"></div>
  </td>
  </tr>
  </table>
  <?php if(isset($errmsg)): ?>
    <p style="color: #FF0000;font-size: 20px;"><?php echo $errmsg; ?></p>
  <?php endif; ?>
  <br>
  </div>

</body>
</html>
