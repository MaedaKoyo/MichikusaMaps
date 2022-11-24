<?php
    /*
    ログインしてるかチェックする関数
    引数なし
    返り値：ログインしていればtrue, していなければfalse
    */
    function is_login(){
        if(isset($_SESSION['user_id'])){
            return true;
        }
        return false;
    }


    /*
    ログインをする関数
    第二引数：メールアドレス
    第三引数：パスワード（平文）
    メールアドレス、パスワードが一致すれば sessionにuser_idを保持させてログイン状態にする
    ログインできなければエラーメッセージを返す
    */
    function login($link, $user_mail, $password){
        $query = "SELECT id, user_mail_address, user_password FROM user WHERE user_mail_address='".$user_mail."'";
        $result = mysqli_query($link, $query);
        if($result === false){
            return $alert = '一致するメールアドレスがありません。';
        }
        $result = mysqli_fetch_assoc($result);
        if(password_verify($password, $result['user_password'])){
            $_SESSION['user_id'] = $result['id'];
            return true;
        }
        return $alert = 'パスワードが一致しません。';
    }