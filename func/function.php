<?php
    /*
    入力された値が一致するかチェックする関数(データ型もチェックする)
    一致すればtrue, 一致しなければfalseを一致する

    入力されたパスワードと確認用のパスワードの一致を確かめるのに使ってます。
    */
    function is_matched($value, $checkd_value){
        if($value === $checkd_value){
            return true;
        }
        return false;
    }

    /*
    ~~画像のアップロードの関数~~
    保存先は
    RETAS_fs\data\imgです。
    第一引数に$_FILES['hoge']
    第二引数に保存するときのファイル名(拡張子なし)
    */
    function upload_img($upload_file, $name){
        $path_info = pathinfo($upload_file['name']);
        $file_name = './data/img/'.$name.'.'.$path_info['extension'];
    
        if(move_uploaded_file($upload_file['tmp_name'], $file_name)){
            return $file_name;
        }
        return false;        
    }

    /*
    ヘッダーに表示するアイコン画像とニックネーム名を取得する
    第二引数にsessionで保持してるユーザーidを入れる。
    返り値は連想配列でsqlで取得したデータ
    */
    function user_info($link, $id){
        return mysqli_fetch_assoc(mysqli_query($link, 'SELECT user_nickname, user_image FROM user WHERE id='.$id));   
    }
    
      //日付の形式を変換
    function date_ja_format($date){
        return date('Y年m月d日', strtotime($date));
    }
      
      //日付を数値のみに変換
    function date_to_num($date){
        if($date == ''){
            return $date;
        }
        return date('Ymd', strtotime($date));
    }

      //引数に値が入ってなかったらNULLを入れる
    function is_value($value){
        if($value == ''){
            return 'NULL';
        }
        return $value;
    }

    /*送られてきた文字列をスペースごとに分割しては配列で返す。
    $keywords :string
    返り値 :array
    ※全角スペースはすべて半角に置き換えてます。
    */
    function keywords_array($keywords){
        $keywords = str_replace('　', ' ', $keywords);
        return explode(' ', $keywords);
    }
    

