<?php
use App\Models\User;

/**
 * @var $current_user App\Models\User
 */
$current_user = session()->get('user') ?: new User();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial=scale=1">
    <title><?= View::yieldContent('title', 'Slack Leaderboard') ?></title>
    <link href="/css/app.css" rel="stylesheet">
    <?= View::yieldContent('style') ?>
</head>
<body>
<header>
	<div class="nav-container u-outerContainer">
		<div class="logo-container">
			<h1>
				<a href="/">
					<span class="icon"></span>
					#slackleaderboard
				</a>
			</h1>
		</div>
		<nav>
			<?php
				if ($current_user->isLoggedIn()): ?>
					<ul class="list">
						<li>
							<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $current_user->handle]) ?>" class="user-container">
								<img class="user-avatar" width="32" src="<?= $current_user->avatar ?>" />
								<span class="user-name"><?= htmlspecialchars($current_user->name_binary) ?></span>
							</a>
							<ul class="sublist">
								<li>
									<img class="user-avatar" width="32" src="<?= $team->icon ?>" />
									Logged into <?= htmlspecialchars($team->name) ?>
								</li>
								<li>
									<form method="POST" action="<?= action('UserController@logoutAction') ?>">
										<button class="button-logout" type="submit">Logout</button>
									</form>
								</li>
							</ul>
						</li>
					</ul>
			<?php
				endif ?>
			<?php
				if (false/*isset($team)*/): ?>
					<h2>
						<a href="<?= action('TeamController@showLeaderboardAction', [$team->domain]) ?>"><?= $team->name ?></a>
					</h2>
			<?php
				endif ?>
		</nav>
	</div>
</header>
<main>
	<div class="u-outerContainer">
		<?= $__data['content'] ?>
		<script src="/js/jquery-3.1.1.js"></script>
	</div>
</main>
<footer>
	&copy; <?= date('Y') ?>
</footer>
</body>
</html>