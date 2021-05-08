<?php
session_start();
require('dbconnect.php');

  //ログインが成功した際セッションidとtimeが付与されるので、セッションidでログイン状態を判別する
  if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
    //アクションを起こした時間を更新して自動ログアウトしないようにする
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members ->execute(array($_SESSION['id']));
    $member  = $members->fetch();
  }else{
    header('Location: login.php');
    exit();
  }
  //ログインしていない状態からアクセスするとログイン画面に強制的に移動させる。
  //投稿するボタンが押されたとき。
  if(!empty($_POST)){
    if($_POST['message'] !==''){
      if($_POST["reply_post_id"] == "")
      $_POST["reply_post_id"] = 0;

      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, created=NOW()');
      $message->execute(array(
      $member['id'],
      $_POST['message'],
      $_POST['reply_post_id']
      ));
      //更新時メッセージの重複登録を防ぐ。
      header('Location: index.php');
      exit();
    }
  }
//ページネーションの処理
$page = $_REQUEST['page'];

  if($page == ''){
    $page = 1;
  }

//max関数。$pageと1を比べ、1の方が大きい時は1を入れる
$page = max($page,1);

$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
//dbからcountした件数を5で割り少数は切り上げて最大ページ数にする。例えば10件なら10÷5=2ページ13件なら13÷5=2.6切り上げて3ページ
$maxPage = ceil($cnt['cnt'] / 5);
//maxpageと入力されたページ数を比較して、入力が大きかったらmaxpageを入れる
$page = min($page, $maxPage);
//１ページ目だと(1-1)×5で0から5件。2ページ目だと(2-1)×5=5で5から10件を取得
$start = ($page - 1) * 5;

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');
//LIMITの?は数字が入る。executeすると文字として入るので、bindParamで数字に縛る
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

  if(isset($_REQUEST['res'])){
    //返信の処理
    $response = $db->prepare('SELECT m.name,m.picture, p.* FROM members m,posts p WHERE m.id=p.member_id AND p.id=?');
    $response ->execute(array($_REQUEST['res']));

    $table = $response->fetch();
    $message = '@' . $table['name'] . ' ' . $table['message'];
  }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
    <div id="head">
      <h1>掲示板</h1>
    </div><!--id="head"の閉じdiv-->
  <div id="content">
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div><!--ログアウトの閉じdiv-->
      <form action="" method="post">
        <dl>
          <dt><?php print(htmlspecialchars($member['name'], ENT_QUOTES));?>さん、メッセージをどうぞ</dt>
          <dd>
           <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
           <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
          </dd>
        </dl>
        <div>
          <p><input type="submit" value="投稿する" /></p>
       </div><!--投稿するの閉じdiv-->
      </form>

      <?php foreach ($posts as $post): ?>
        <div class="msg"><!--ここのdivタグで一覧を表示する。-->
          <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
          <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p><!--$postはdbから取ってきた$postsを代入したもの-->
            <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'])); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
              <?php if ($post['reply_message_id'] > 0): ?>
               <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">返信元のメッセージ</a>
              <?php endif; ?>

             <?php if($_SESSION['id'] == $post['member_id']): ?>
              [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>"style="color: #F33;">削除</a>]
              <?php endif; ?>
            </p><!--p class="day"の閉じp-->
        </div><!--class="msg"の閉じdiv-->
      <?php endforeach; ?>

    <ul class="paging">
      <?php if($page > 1): ?>
        <li><a href="index.php?page=<?php print($page-1);?>">前のページへ</a></li>
       <?php else: ?>
       <li>前のページへ</li>
      <?php endif; ?>

      <?php if($page < $maxPage): ?>
       <li><a href="index.php?page=<?php print($page+1);?>">次のページへ</a></li>
       <?php else: ?>
       <li>次のページへ</li>
      <?php endif; ?>
    </ul>
  </div><!--id="contact"の閉じdiv-->
</div><!--id="wrap"の閉じdiv-->
</body>
</html>
