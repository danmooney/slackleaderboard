<?php
use App\Models\User;

/**
 * @var $current_user App\Models\User
 * @var $team App\Models\Team
 */
$current_user = User::getFromSession();

// if user isn't logged in, use demo user for header
$user_for_display_in_header = $current_user->isLoggedIn() ? $current_user : User::getDemoUser()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-KPCSVF3');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta http-equiv="Content-Language" content="en-US">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0">
    <meta name="google" content="notranslate">
    <meta name="description" content="An emoji reaction leaderboard for teams using Slack.  Find out who's using the most reactions and who's reacting to whom the most!">
    <meta name="author" content="Dan Mooney">

    <meta itemprop="name" content="Slack Leaderboard">
    <meta itemprop="description" content="An emoji reaction leaderboard for teams using Slack.  Find out who's using the most reactions and who's reacting to whom the most!">
    <meta itemprop="image" content="/img/logo.png">
    
    <meta property="og:url" content="https://www.slackleaderboard.com">
    <meta property="og:title" content="Slack Leaderboard">
    <meta property="og:description" content="An emoji reaction leaderboard for teams using Slack.  Find out who's using the most reactions and who's reacting to whom the most!">
    <meta property="og:image" content="/img/logo.png">

    <?php /*
    <link rel="canonical" href="xxx" >

 */ ?>
    <meta name="twitter:card" content="summary">
    <meta name="twitter:creator" content="@hiremephotoshop">
    <meta name="twitter:site" content="@reactionleaders">
    <meta name="twitter:title" content="Slack Leaderboard">
    <meta name="twitter:description" content="An emoji reaction leaderboard for teams using Slack.  Find out who's using the most reactions and who's reacting to whom the most!">
    <meta name="twitter:image" content="/img/logo.png">

    <?php /*
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type='image/png' sizes="192x192"  href="/android-icon-192x192.png">
    <link rel="icon" type='image/png' sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type='image/png' sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type='image/png' sizes="16x16" href="/favicon-16x16.png">
*/ ?>
    <meta name="msapplication-TileColor" content="#FFF">
    <meta name="msapplication-TileImage" content="/img/logo.png">
    <meta name="theme-color" content="#FFF">

    <title><?= View::yieldContent('title', 'Slack Leaderboard') ?></title>

    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96.png">
    <link rel="icon" type="image/png" sizes="64x64" href="/favicon-64.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-32.png">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <?= View::yieldContent('style') ?>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KPCSVF3"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
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
                <ul class="list">
                    <li>
                        <a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user_for_display_in_header->handle]) ?>" class="user-container">
                            <img class="user-avatar" width="32" src="<?= $user_for_display_in_header->avatar ?>" />
                            <span class="user-name"><?= htmlspecialchars($user_for_display_in_header->name_binary) ?></span>
                        </a>
                        <ul class="sublist">
                            <li class="arrow-box"></li>
                            <li>
                                <a href="<?= action('TeamController@showLeaderboardAction', [$team->domain]) ?>">
                                    <img class="user-avatar" width="32" src="<?= $team->icon ?>" />
                                    Logged into <?= htmlspecialchars($team->name) ?>
                                </a>
                            </li>
                            <li>
                                <?php
                                    if ($current_user->isLoggedIn()): ?>
                                        <form method="POST" action="<?= action('UserController@logoutAction') ?>">
                                            <button class="button-logout" type="submit">Logout</button>
                                        </form>
                                <?php
                                    else: ?>
                                        <?= view('_partials.button_sign_in_with_slack', $__data) ?>
                                <?php
                                    endif ?>
                            </li>
                        </ul>
                    </li>
                </ul>
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
        <a data-size="large" class="twitter-share-button" href="https://twitter.com/intent/tweet?text=<?= urlencode('See who\'s reacting with the most emojis on your #slack team! #slackleaderboard') ?>">
            Tweet
        </a>
        <a target="_blank" href="https://twitter.com/reactionleaders">
            @reactionleaders
        </a>
        <a href="mailto:&#115;&#108;&#097;&#099;&#107;&#108;&#101;&#097;&#100;&#101;&#114;&#098;&#111;&#097;&#114;&#100;&#064;&#103;&#109;&#097;&#105;&#108;&#046;&#099;&#111;&#109;">
            Contact
        </a>
    </div>
</footer>
<script>window.SL_OPTIONS = <?= json_encode(config('app.options')) ?>;</script>
<script src="/js/main.js"></script>
</body>
</html>
