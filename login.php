<?php
session_start();
require('dbconnect.php');

  //emailをcookieに入れ、自動ログインできるようにする
  if($_COOKIE['email'] !== ''){
    $email = $_COOKIE['email'];
  }
  //$_POSTの中身はユーザーが入力した<input type>タグのname属性の値
  //echo($_POST['email']);エコーで渡ってきた$_POSTの値を確認
  //echo($_POST['password']);$_POSTのpasswordの値を確認
  if(!empty($_POST)){
       //クッキーを使うので、$emailをPOST[email]で上書きする
      $email = $_POST['email'];
      //idで拾ってくる。$_POST['id']とSQLのWHERE id=?でidが一緒の人の行を引っ張ってきてverify($_POST['pass',db内のパス])
      $hashpass = $db->prepare('SELECT password FROM members WHERE email=?');
      //ログインしようとするユーザーのid(登録時に自動的に割り振られるid)を箱(配列)に入れて$hashpassに入れる。
      $hashpass->execute(array($_POST['email']));
      //$_POST['id']はない。
      //fetchしないと実際のデータは値で変数に入らない。
      $hash = $hashpass->fetch();
      //var_dump($hash);
      if($_POST['email'] !=='' && $_POST['password'] !=='' && password_verify(htmlspecialchars($_POST['password'],ENT_QUOTES),$hash['password'])){
        //echo ($hash['password']);
        $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
        //正しいログインメールアドレスとパスワードが入力されたらデータベースに値を取得しに行く。DBのpassはハッシュ後pass
        $login->execute(array($_POST['email'],$hash['password']));
        //$loginに対するexecuteも、DBのemailとpasswordが入る。つまり、パスワードは$hashのパスワードを入れる必要がある。
        //もしフェッチで情報が１件帰ってきたらログインに成功している。１件も返ってこないならログインに失敗している。
        //fetchにて、DBから全件取得した行を１行づつ$memberに入れる。
        $member = $login->fetch();
          if($member){
            //$_SESSIONの中は$_POST(name,email,password,image)の４つ。ここの$_SESSIONはPOSTの中身ではなく、SESSION_id
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] = time();

              //次回から自動入力のチェックボックス。value=onならcookie内のemailを自動でセット
              if($_POST['save'] === "on"){
                setcookie('email', $_POST['email'], time()+60*60*24*14);
              }

            header('Location: index.php');
            exit();
          }else{
            //fetchにデータが返ってこなかったら
            $error['login'] = 'failed';
          }
      }else{
        //ネームかパスワードが空なら
        $error['login'] = 'blank';
      }
  }
?>

<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>ログインする</title>
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ログインする</h1>
  </div>
  <div id="content">
    <div id="lead">
      <p>メールアドレスとパスワードを記入してログインしてください。</p>
      <p>入会手続きがまだの方はこちらからどうぞ。</p>
      <p>&raquo;<a href="join/index_join.php">入会手続きをする</a></p>
    </div>
    <form action="" method="post">
      <dl>
        <dt>メールアドレス</dt>
        <dd>
          <input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($email, ENT_QUOTES)); ?>" />
          <?php if ($error['login'] === 'blank'): ?>
            <P class="error">*メールアドレスとパスワードを入力して下さい</P>
          <?php endif; ?>
          <?php if ($error['login'] === 'failed'): ?>
            <P class="error">*ログインに失敗しました。正しくご記入下さい</P>
          <?php endif; ?>
        </dd>
        <dt>パスワード</dt>
        <dd>
          <input type="password" name="password" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" /><!--メソッドの引数の設定の仕方に注意。第一引数とENT-->
        </dd>
        <dt>ログイン情報の記録</dt>
        <dd>
          <input id="save" type="checkbox" name="save" value="on">
          <label for="save">次回からは自動的にログインする</label>
        </dd>
      </dl>
      <div>
        <input type="submit" value="ログインする" />
      </div>
    </form>
  </div>
  <div id="foot">
    <p></p>
  </div>
</div>
</body>
</html>
