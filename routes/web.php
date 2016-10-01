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


//Route::get('/', function () {
//	$current_user = session()->get('user');
//
//	if ($current_user) {
//		$request = Request::create('TeamController@showLeaderboardAction', 'GET', array());
//	} else {
//		$request = Request::create('SlackController@guestHomepageAction', 'GET', array());
//	}
//
//	return Route::dispatch($request)->getContent();
//});

Route::get('/', 'SlackController@guestHomepageAction');
Route::get('/+/fetch', 'SlackController@fetchData');
Route::get('/c', 'TokenController@getAndStoreTokenFromOauthFlow');
Route::get('/messages', 'MessageController@getThem');
Route::get('/+/login', 'UserController@loginAction');
Route::post('/+/logout', 'UserController@logoutAction');
Route::get('/{team_domain}', 'TeamController@showLeaderboardAction');
Route::get('/{team_domain}/u/{user_handle}', 'UserController@showLeaderboardAction');
Route::get('/{team_domain}/r/{reaction_alias}', 'ReactionController@showLeaderboardAction');