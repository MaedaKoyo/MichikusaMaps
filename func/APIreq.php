<?php
const APIkey = 'xxx';

/*GoogleMapAPIから観光地のデータを配列で返す。
引数はそれぞれ（緯度,経度,半径）*/
function GAPI_spotSearch($lat,$lon,$rad){
    $req = file_get_contents('https://maps.googleapis.com/maps/api/place/textsearch/json?key='.APIkey.'&query=tourist%20attraction&location='.$lat.','.$lon.'&radius='.$rad);
    if($req == false){
        return false;
    }else{
        return json_decode($req,true);
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

?>
