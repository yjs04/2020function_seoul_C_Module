<?php

namespace Controller;

use App\DB;

class ActionController{
    function worksAddProcess(){
        checkEmpty();
        extract($_POST);

        $work_img = base64_upload($image);
        $sql = "INSERT INTO works(`creater_id`,`work_name`,`work_img`,`creater_type`,`create_date`,`work_tags`,`work_content`,`creater_name`) VALUES(?,?,?,?,?,?,?,?)";
        DB::query($sql,[user()->id,$work_name,$work_img,user()->type,date('Y-m-d'),$work_tags,$work_content,user()->user_name]);

        go("/entry","작품이 등록되었습니다.");
    }

    function inventoryAddProcess(){
        checkEmpty();
        extract($_POST);

        $sql = "SELECT * FROM inventory WHERE `user_id` = ? ";
        $result = DB::fetchAll($sql,[user()->id]);

        $list = json_decode($sell_list);

        $sell_basket = json_decode($sell_basket);
        foreach($sell_basket as $basket){
            $company = DB::fetch("SELECT * FROM users WHERE id = ?",[$basket->company_id]);
            $company_point = $company->point + $basket->sum;
            $sql = "UPDATE users SET `point` = ? WHERE id = ?";
            DB::query($sql,[$company_point,$basket->company_id]);
        }

        $point = user()->point - $sell_point;

        $sql = "UPDATE users SET `point` = ? WHERE id = ?";
        DB::query($sql,[$point,user()->id]);

        if($result == []){
            $sql = "INSERT INTO inventory(`user_id`,`sell_list`) VALUES(?,?)";
            DB::query($sql,[user()->id,$sell_list]);
        }else{
            $sql = "UPDATE inventory SET sell_list = ? WHERE `user_id` = ?";
            DB::query($sql,[$sell_list,user()->id]);
        }
        
        echo json_encode(true);
    }

    function joinProcess(){
        checkEmpty();
        extract($_POST);

        $sql = "INSERT INTO users(`user_email`,`user_name`,`password`,`image`,`type`) VALUES(?,?,?,?,?)";

        $file = $_FILES['image'];
        $filename = time().extname($file['name']);
        move_uploaded_file($file['tmp_name'],UPLOAD."/$filename");

        DB::query($sql,[$user_email,$user_name,$password,$filename,$type]);
        go("/login","회원 가입되었습니다.");
    }

    function loginProcess(){
        checkEmpty();
        extract($_POST);
        $user = DB::who($user_email);
        if(!$user) back("아이디와 일치하는 회원이 존재하지 않습니다.");
        if($user->password !== $password)back("비밀번호가 일치하지 않습니다.");

        $_SESSION['user'] = $user;
        go("/","로그인에 성공했습니다.");
    }

    function logoutProcess(){
        if(user()){
            session_destroy();
            go("/","로그아웃에 성공했습니다.");
        }
    }

    function paperAddProcess(){
        checkEmpty();
        extract($_POST);

        $sql = "INSERT INTO papers(`image`,`paper_name`,`company_name`,`company_id`,`width_size`,`height_size`,`point`,`hash_tags`) VALUES(?,?,?,?,?,?,?,?)";

        $file = $_FILES['image'];
        $filename = time().extname($file['name']);
        move_uploaded_file($file['tmp_name'],UPLOAD."/$filename");

        DB::query($sql,[$filename,$paper_name,user()->user_name,user()->id,$width_size."px",$height_size."px",$point."p",$hash_tags]);
        go("/store","한지가 추가되었습니다.");
    }

}