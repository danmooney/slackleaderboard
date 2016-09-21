<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'SlackController@fetchData');
Route::get('/{team_domain}', 'TeamController@showLeaderboardAction');
Route::get('/{team_domain}/u/{user_handle}', 'UserController@showLeaderboardAction');
Route::get('/{team_domain}/r/{reaction_alias}', 'ReactionController@showLeaderboardAction');