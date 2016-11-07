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
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96.png">
    <link rel="icon" type="image/png" sizes="64x64" href="/favicon-64.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-32.png">
    <?= View::yieldContent('style') ?>
</head>
<body>
<div class="header-main-container">
    <header>
        <div class="nav-container u-outerContainer">
            <div class="logo-container">
                <h1>
                    <a href="/">
                        <span class="icon"></span>
                        <span class="title u-hideOnMobile">#slackleaderboard</span>
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
                                        <a href="<?= action('TeamController@showLeaderboardAction', [$team->domain]) ?>">
                                            <img class="user-avatar" width="32" src="<?= $team->icon ?>" />
                                            Logged into <?= htmlspecialchars($team->name) ?>
                                        </a>
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
            <?php
                if (isset($team) && !$team->posts_from_beginning_of_time_fetched): ?>
                    <br>
                    <div class="alert alert-warning">
                        <strong>We are currently fetching reactions for your team for the first time.  Information will change rapidly as reactions get calculated.</strong><br>Refresh the page to see newly updated results.
                    </div>
            <?php
                endif ?>
            <?= $__data['content'] ?>
        </div>
    </main>
</div>
<footer>
    <div class="u-outerContainer">
    </div>
</footer>
<script src="/js/jquery-3.1.1.js"></script>
<script src="/js/app.js"></script>
</body>
</html>
