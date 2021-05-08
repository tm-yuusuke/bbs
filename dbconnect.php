<?php
try{
    $db = new PDO('mysql:dbname=bbs;host=localhost;port=8889;charset=utf8','root','root');
}catch(PDOException $e){
    echo 'DB接続エラー:'.$e->getMessage();
}
?>

