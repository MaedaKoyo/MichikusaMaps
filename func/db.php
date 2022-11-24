<?php
    //DB接続関数　戻り値はPH23の授業でやってた$linkと同じもの
    function connect_db(){
        require_once '../config/db_connection.php';
        $link = @mysqli_connect(HOST, USER_ID, PASSWORD, DB_NAME);
        mysqli_set_charset($link, 'utf8');
        if(!$link){
            $err_msg = '予期せぬエラーが発生しました。しばらくたってから再度お試しください。(エラーコード：103)';

            exit;
        }

        return $link;
    }

    /*
    ~~insert文を実行する~~
    第一引数：$link MySQL サーバーへの接続を表すオブジェクト
    第二引数：$insert_list 連想配列　keyが登録したいカラム名　valueが登録する値
    第三引数：$table 文字列　DBのテーブル名

    戻り値：insertが成功したらtrue
    */
    function insert($link, $insert_list, $table){
        $i = 0;
        $insert_query_columns = '';
        $insert_query_values = '';
        foreach($insert_list as $column => $value){
            $i ++;
            if(count($insert_list) === $i){
                $insert_query_columns .= $column;
                $insert_query_values .= "'".$value."'";
            }else{
                $insert_query_columns .= $column.", ";
                $insert_query_values .= "'".$value."', ";
            }
        }

        $insert_query = "INSERT INTO ".$table."(".$insert_query_columns.") VALUES (".$insert_query_values.")";
        
        return mysqli_query($link, $insert_query);
    }

    
    /*
    ~~update文を実行する~~
    基本的に上のinsert関数と同じ
    第四引数：$id ここでは条件指定にidを使ってるのでidを必要とします。
    id以外での条件指定はできないです。
    */
    function update($link, $update_list, $table, $id){
        $i = 0;
        $update_set_list = '';
        foreach($update_list as $column => $value){
            $i ++;
            if(count($update_list) === $i){
                $update_set_list .= $column."='".$value."'";
            }else{
                $update_set_list .= $column."='".$value."', ";
            }
        }
        $update_query = "UPDATE ".$table." SET ".$update_set_list." WHERE id=".$id;
        var_dump($update_query);

        return mysqli_query($link, $update_query);
    }
    
    /*
    ~~select文を実行する~~
    link：connect_db()の戻り値。
    table：(string)参照テーブル
    innerJoin：内部結合（二次元配列[][(string)INNER JOINテーブル名だけ,(string)ON条件だけ]）
    where：(string)条件
    innerJoinとWhereはnull可。
    */
    function select($link, $table, $innerJoin, $where){
        
        $select_query = "SELECT * FROM ".$table." ";
        if($innerJoin){
            foreach($innerJoin as $value){
                $select_query .= "INNER JOIN ".$value[0]." ON ".$value[1]." ";
            }   
        }
        if($where){
            $select_query .= "WHERE ".$where;
        }
        
        $result = mysqli_query($link, $select_query);
        $rows = array();
        while($row = mysqli_fetch_array($result)){
            array_push($rows,$row);
        }
        
        return $rows;
    }


