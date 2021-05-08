<?php
//動作確認オッケー
session_start();
require('../dbconnect.php');
//入力をチェックしていく。テキストフォームが空じゃない
if (!empty($_POST)){
//名前が空じゃない。空なら$errorにblankを入力。テキストフォームに名前が空ですと表示する
	if ($_POST['name'] === ''){
		$error['name'] = 'blank';
	}
	//メールアドレスが空じゃない。空なら$errorにblankを入力。テキストフォームにメアドが空ですと表示する
	if ($_POST['email'] === ''){
		$error['email']='blank';
	}
	//パスワードが４文字より下じゃない。下なら$errorにlengthを入力。パスを４字以上で設定してと表示する
	if (strlen($_POST['password']) < 4){
		$error['password']='length';
	}
	//パスワードが空じゃない。空なら$errorにblankを入力。テキストフォームにパスワードが空ですと表示する
	if ($_POST['password']===''){
		$error['password']='blank';
	}
	//入力がすべて(名前、メールアドレス、パスワード)入力されたら次に画像をチェックする。filterinputを使うｐｈｐのｐｓｒ規約
	$fileName = $_FILES['image']['name'];
	if(!empty($fileName)){
		$ext = substr($fileName, -3);
		if($ext != 'jpg' && $ext != 'gif' && $ext != 'png'){
			$error['image'] = 'type';
		}
	}

	if (empty($error)) {
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
		//filter_
		$member->execute(array($_POST['email']));
		$record = $member->fetch();
		if($record['cnt'] > 0){
			$error['email'] = 'duplicate';
		}
	}

	if (empty($error)){
		$image = date('YmdHis') . $_FILES['image']['name'];
		move_uploaded_file($_FILES['image']['tmp_name'],'../member_picture/'. $image);
		//postが送信されたら内容をセッションに保存する
		$_SESSION['join'] = $_POST;
		$_SESSION['join']['image'] = $image;
		header('Location: check.php');
		exit();
	}
}

if ($_REQUEST['action'] === 'rewrite' && isset($_SESSION['join'])){
	$_POST = $_SESSION['join'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
    <!--スマホの画面でも見やすく表示するタグ。画面に仮想のキャンバスを設定する-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!--ブラウザのエッジで正しく画面を表示させるタグ-->
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
<!--formが空なら自分自身にジャンプする全てのformにてエラーがない事を確認してcheck.phpへ値を投げるhtml内に記述するとソース表示で見える-->
<p>次のフォームに必要事項をご記入ください。</p>
<!--ファイルをアップロードする時はenctype以降の記述が必要-->
<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>ニックネーム<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="name" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['name'] ,ENT_QUOTES));?>" />
			<?php if ($error['name']==='blank'):?>
			<p class="error"> *ニックネームを入力して下さい</p>
			<?php endif; ?>
			<!--上でif構文で条件判定すると、画面上の左上にエラーのprint構文が表示される。テキストボックス近くに表示する為、上でエラーを変数に入れ、ここでprintする-->
		</dd>
		<dt>メールアドレス<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'] ,ENT_QUOTES));?>" />
			<?php if ($error['email']==='blank'):?>
			<p class="error"> *メールアドレスを入力してください</p>
			<?php endif; ?>
			<?php if ($error['email']==='duplicate'):?>
			<p class="error"> *このメールアドレスは既に使用されています</p>
			<?php endif; ?>
		<dt>パスワード<span class="required">必須</span></dt>
		<dd>
        	<input type="password" name="password" size="10" maxlength="20" value="<?=htmlspecialchars($_POST['password'] ,ENT_QUOTES);?>" />
			<?php if ($error['password']==='length'):?>
			<p class="error"> *パスワードは4文字以上で設定してください</p>
			<?php endif; ?>
			<?php if ($error['password']==='blank'):?>
			<p class="error"> *パスワードを入力してください</p>
			<?php endif; ?>
		</dd>
		<dt>写真など</dt>
		<dd>
        	<input type="file" name="image" size="35" value="test">
			<?php if ($error['image']==='type'):?>
			<p class="error"> *画像は.jpg .png .gifのいずれかを選択ください</p>
			<?php endif; ?>
			<?php if (!empty($error)): ?>
			<p class="error"> *再度画像を指定してください</p>
			<?php endif; ?>
		</dd>
	</dl>
	<div><input type="submit" value="入力内容を確認する"></div>
</form>
</div>
</body>
</html>
