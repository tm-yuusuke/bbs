<?php
	session_start();
	require('../dbconnect.php');

	if (!isset($_SESSION['join'])){//正規の手続きでcheck.phpが呼び出されない時強制的にindexへ飛ばす
		header('Location: index_join.php');
		exit();
	}
//↓のifポストの値はこのファイルの登録（サブミット）がされた時
	if (!empty($_POST)){
	$statement = $db->prepare('INSERT INTO members SET name=?, email=?, password=?, picture=?, created=NOW()');
	$statement->execute(array(
		$_SESSION['join']['name'],//_SESSION['join']の中は$_POST。
		$_SESSION['join']['email'],
		password_hash($_SESSION['join']['password'],PASSWORD_DEFAULT),
		$_SESSION['join']['image']
	));//パスワードをハッシュ化してDBに保存する。
	unset($_SESSION['join']);

	header('Location: thanks.php');//セッションの内容を消去する。重複等を防ぐ
	exit();
	}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>
<body>
<div id="wrap">
<div id="head">
<h1>会員登録</h1>
</div>

<div id="content">
<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
<form action="" method="post">
	<input type="hidden" name="action" value="submit" />
	<dl>
		<dt>ニックネーム</dt>
		<dd>
		<?php print(htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES)); ?>
        </dd>
		<dt>メールアドレス</dt>
		<dd>
		<?php print(htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES)); ?>
        </dd>
		<dt>パスワード</dt>
		<dd>
		【表示されません】
		</dd>
		<dt>写真など</dt>
		<dd>
		<?php if ($_SESSION['join']['image'] !== ''): ?>
		<image src="../member_picture/<?php print(htmlspecialchars($_SESSION['join']['image'], ENT_QUOTES)); ?>">
		<?php endif; ?>
		</dd>
	</dl>
	<div><a href="index_join.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
</form>
</div>

</div>
</body>
</html>
