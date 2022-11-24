<?php

function zoom($rad){
  if($rad > 300000){
    return 4;
  }else if(200000 < $rad && $rad <= 300000){
    return 5;
  }else if(100000 < $rad && $rad <= 200000){
    return 6;
  }else if(50000 < $rad && $rad <= 100000){
    return 7;
  }else if(20000 < $rad && $rad <= 50000){
    return 8;
  }else if(10000 < $rad && $rad <= 20000){
    return 9;
  }else if(5000 < $rad && $rad <= 10000){
    return 10;
  }else{
    return 11;
  }
}


/**
 * ２地点間の距離(m)を求める
 * ヒュベニの公式から求めるバージョン
 *
 * @param float $lat1 緯度１
 * @param float $lon1 経度１
 * @param float $lat2 緯度２
 * @param float $lon2 経度２
 * @param boolean $mode 測地系 true:世界(default) false:日本
 * @return float 距離(m)
 */
function distance($lat1, $lon1, $lat2, $lon2, $mode=true)
{
    // 緯度経度をラジアンに変換
    $radLat1 = deg2rad($lat1); // 緯度１
    $radLon1 = deg2rad($lon1); // 経度１
    $radLat2 = deg2rad($lat2); // 緯度２
    $radLon2 = deg2rad($lon2); // 経度２

    // 緯度差
    $radLatDiff = $radLat1 - $radLat2;

    // 経度差算
    $radLonDiff = $radLon1 - $radLon2;

    // 平均緯度
    $radLatAve = ($radLat1 + $radLat2) / 2.0;

    // 測地系による値の違い
    $a = $mode ? 6378137.0 : 6377397.155; // 赤道半径
    $b = $mode ? 6356752.314140356 : 6356078.963; // 極半径
    //$e2 = ($a*$a - $b*$b) / ($a*$a);
    $e2 = $mode ? 0.00669438002301188 : 0.00667436061028297; // 第一離心率^2
    //$a1e2 = $a * (1 - $e2);
    $a1e2 = $mode ? 6335439.32708317 : 6334832.10663254; // 赤道上の子午線曲率半径

    $sinLat = sin($radLatAve);
    $W2 = 1.0 - $e2 * ($sinLat*$sinLat);
    $M = $a1e2 / (sqrt($W2)*$W2); // 子午線曲率半径M
    $N = $a / sqrt($W2); // 卯酉線曲率半径

    $t1 = $M * $radLatDiff;
    $t2 = $N * cos($radLatAve) * $radLonDiff;
    $dist = sqrt(($t1*$t1) + ($t2*$t2));

    return $dist;
}

/*GoogleMapAPIから観光地のデータを配列で返す。
引数はそれぞれ（緯度,経度,半径）*/
const APIkey = 'xxx';
function GAPI_spotSearch($lat,$lon,$rad){
    $req = file_get_contents('https://maps.googleapis.com/maps/api/place/textsearch/json?key='.APIkey.'&query=tourist%20attraction&location='.$lat.','.$lon.'&radius='.$rad);
    if($req == false){
        return false;
    }else{
        return json_decode($req,true);
    }
}


//施設名から詳細取得
function GAPI_placeSearch($keyword){
	$res = file_get_contents('https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input='.$keyword.'&inputtype=textquery&fields=formatted_address,name,opening_hours,geometry&key='.APIkey);
	if($res == false){
			return false;
	}else{
			return json_decode($res,true);
	}
}

//place_idからスポットの詳細を検索
function GAPI_spotDetail($place_id){
  $res = file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$place_id.'&key='.APIkey);
	if($res == false){
			return false;
	}else{
			return json_decode($res,true);
	}
}




//検索受け取り
if(!empty($_POST['dep'])&&!empty($_POST['des'])){
  $post=1;
  $searchDep = GAPI_placeSearch($_POST['dep']);
  $searchDes = GAPI_placeSearch($_POST['des']);
  $dep = array($searchDep['candidates'][0]['geometry']['location']['lat'],$searchDep['candidates'][0]['geometry']['location']['lng']);
  $des = array($searchDes['candidates'][0]['geometry']['location']['lat'],$searchDes['candidates'][0]['geometry']['location']['lng']);
  $lat = ($dep[0]+$des[0])/2;
  $lon = ($dep[1]+$des[1])/2;
  $rad = distance($dep[0],$dep[1],$des[0],$des[1])/2.5;
}else{
  $post = 0;
  //初期状態はHAL大阪を中心としたMAPを表示
  $rad = 3000;
  $lat = 34.699875;
  $lon = 135.493032;
}



//place_id取得
$result = GAPI_spotSearch($lat,$lon,$rad);

$place_id_arr = array();
for ($i=0; $i < 20; $i++) {
  $place_arr[$i] = $result['results'][$i];
}
$data_json = json_encode($place_arr,JSON_PRETTY_PRINT);

//DB処理
if(isset($_GET['name'])){
  $name = $_GET['name'];
  $id = $_GET['place_id'];

  //db関数読み込み(昔のでーたかも)
  require_once '../func/db.php';
  //db接続
  $link = connect_db();
  //place_idでランキングに登録されているか確認
  $sql = "SELECT count(*) FROM ranking WHERE place_id = '".$id."'";
  $res = mysqli_query($link, $sql);
  $res = mysqli_fetch_array($res);

  //あればcount+1する
  if($res["count(*)"] == 1){
    $sql = "SELECT * FROM ranking WHERE place_id = '".$id."'";
    $res = mysqli_query($link,$sql);
    $list = mysqli_fetch_array($res);
    $count = $list['count']+1;
    $sql = "UPDATE ranking  SET count = ".$count." WHERE place_id = '".$id."'";
    mysqli_query($link, $sql);
  }else{
    $insert_list = array("store_name"=>$name,"place_id"=>$id,"count"=>"1");
    insert($link,$insert_list,'ranking');
  }

  header('location:detail.php?place_id='.$id);
}

?>
<!doctype html>
<html lang="ja">
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="../css/text.css">
<script src="../js/jquery-3.5.1.min.js"></script>
<script type="text/javascript"></script>
<style type="text/css">
</style>
<script>
//place_idをphpから取得
  var id_array = JSON.parse(JSON.stringify(<?php echo $data_json; ?>));
</script>
</head>

<body>
	<header>
		<h1 class="headline">
		  <a><img src="../img/logo.jpg"></a>
		</h1>
		<ul class="nav-list">
		  <li class="nav-list-item"><a href="./rank.php">ランキング</a></li>
		</ul>
	  </header>

    <form action="#" method="post">
	<p>出発地</p>
	<input type="text" name="dep" maxlength="30">
	<p>目的地</p>
	<input type="text" name="des" maxlength="30">
	<input class="btn btn-primary" type="submit" value="検索">
	</form>
  <div id="detail">
	<div id="map-canvas" style="width:100%; height:400px" class="map"></div>
  <div id="text" class="spot_text"></div>
  </div>
	<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=xxx"></script>
	<script>

  // var placeid_json = [{
  //     "placeid": 'ChIJbbXqmojmAGAReS1R3KehMAI',
  //     "content": "   1   "
  // }, {
  //     "placeid": 'ChIJDw5JRWDnAGARwnkfZmEyN10',
  //     "content": "   2   "
  // }, {
  //     "placeid": 'ChIJoa_W2ZLmAGARsZxXPEBDLAU',
  //     "content": "   3   "
  // }];
  //var placeid_json = [{"placeid": 'ChIJbbXqmojmAGAReS1R3KehMAI'}, {"placeid": 'ChIJDw5JRWDnAGARwnkfZmEyN10'}, {"placeid": 'ChIJoa_W2ZLmAGARsZxXPEBDLAU'}];




var openedInfoWindow = null;
var cnt = 0;

function initialize() {
    var latitude = <?php echo $lat; ?>,
        longitude = <?php echo $lon; ?>,
        radius = 100,
        center = new google.maps.LatLng(latitude, longitude),
        mapOptions = {
            center: center,
            zoom: <?php echo zoom($rad); ?>,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false
        };

    var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

    setMarkers(center, radius, map);
}

function setMarkers(center, radius, map) {

    var json = id_array;
    for (var i = 0, length = json.length; i < length; i++) {
        var data = json[i];
        createMarker(data, map, center);
    }
    <?php if($post == 1): ?>
            const dep = { lat: <?php echo $dep[0]; ?>, lng: <?php echo $dep[1]; ?> };
            const des = { lat: <?php echo $des[0]; ?>, lng: <?php echo $des[1]; ?> };
            var DepMarker = new google.maps.Marker({
                map:map,
                position:dep,
                icon: {
                  fillColor: "#FF7700",                //塗り潰し色
                  fillOpacity: 0.8,                    //塗り潰し透過率
                  path: google.maps.SymbolPath.CIRCLE, //円を指定
                  scale: 8,                           //円のサイズ
                  strokeColor: "#FF7700",              //枠の色
                  strokeWeight: 1.0                    //枠の透過率
                },
            });
            var DesMarker = new google.maps.Marker({
                map:map,
                position:des,
                icon: {
                  fillColor: "#00FF00",                //塗り潰し色
                  fillOpacity: 0.8,                    //塗り潰し透過率
                  path: google.maps.SymbolPath.CIRCLE, //円を指定
                  scale: 8,                           //円のサイズ
                  strokeColor: "#00FF00",              //枠の色
                  strokeWeight: 1.0                    //枠の透過率
                },
            });
            var centerMarker = new google.maps.Marker({
                map:map,
                position:center,
                icon: {
                  fillColor: "#000000",                //塗り潰し色
                  fillOpacity: 0.8,                    //塗り潰し透過率
                  path: google.maps.SymbolPath.CIRCLE, //円を指定
                  scale: 4,                           //円のサイズ
                  strokeColor: "#000000",              //枠の色
                  strokeWeight: 1.0                    //枠の透過率
                },
            });
            <?php endif; ?>
}

function createMarker(data, map, center) {
            //候補欄生成
            var text = document.getElementById('text_'+cnt);
            var add_code ='<p>'+data.name+'</p><p>'+data.formatted_address+'</p>';
            text.innerHTML = add_code;
            cnt = cnt + 1;
            //marker作成
            var marker = new google.maps.Marker({
                map: map,
                place: {
                    placeId: data.place_id,
                    location: data.geometry.location
                }
            });

            google.maps.event.addListener(marker, 'click', function() {
            //ピンの上に情報が表示される
            //infowindow.setContent(place.name);
            //infowindow.open(map, this);

            //マップサイズ変更
            document.getElementById("map-canvas").style.width = "50%";
            //html生成
            var text = document.getElementById('text');
            var add_code ='<ul><li>'+data.name+'</li><li>'+data.formatted_address+'</li></ul>';
            text.innerHTML = add_code;
            });
}




google.maps.event.addDomListener(window, 'load', initialize);

	</script>

	<div id="shopshosai">
	</div>

	<div id="near">
		<div class="d-grid gap-3 text-center">
			<div class="p-2 bg-light border" id="text_0"></div>
      <div class="p-2 bg-light border" id="text_1"></div>
      <div class="p-2 bg-light border" id="text_2"></div>
      <div class="p-2 bg-light border" id="text_3"></div>
      <div class="p-2 bg-light border" id="text_4"></div>
      <div class="p-2 bg-light border" id="text_5"></div>
      <div class="p-2 bg-light border" id="text_6"></div>
      <div class="p-2 bg-light border" id="text_7"></div>
      <div class="p-2 bg-light border" id="text_8"></div>
      <div class="p-2 bg-light border" id="text_9"></div>
      <div class="p-2 bg-light border" id="text_10"></div>
      <div class="p-2 bg-light border" id="text_11"></div>
      <div class="p-2 bg-light border" id="text_12"></div>
      <div class="p-2 bg-light border" id="text_13"></div>
      <div class="p-2 bg-light border" id="text_14"></div>
      <div class="p-2 bg-light border" id="text_15"></div>
      <div class="p-2 bg-light border" id="text_16"></div>
      <div class="p-2 bg-light border" id="text_17"></div>
      <div class="p-2 bg-light border" id="text_18"></div>
      <div class="p-2 bg-light border" id="text_19"></div>


		  </div>
	</div>

</body>
</html>
