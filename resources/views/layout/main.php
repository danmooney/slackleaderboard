<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial=scale=1">
    <title><?= View::yieldContent('title', 'Slack Leaderboard') ?></title>
    <link href="/css/app.css" rel="stylesheet">
    <link href="/css/styles.css" rel="stylesheet">
    <?= View::yieldContent('style') ?>
</head>
<body>
<header>
<h1><a href="/">Slack Leaderboard</a></h1>
	<?php
		if (isset($__data['team'])): ?>
			<h2>
				<a href="<?= action('TeamController@showLeaderboardAction', [$__data['team']->domain]) ?>"><img class="user-avatar" width="32" src="<?= $__data['team']->icon ?>" /><?= $__data['team']->name ?></a>
			</h2>
	<?php
		endif ?>
</header>
<main>
	<?= $__data['content'] ?>
	<script src="/js/jquery-3.1.1.js"></script>
</main>
</body>
</html>