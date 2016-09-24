<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App;
use DB;
use Input;
use Frlnc\Slack\Http\SlackResponseFactory;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Core\Commander;

class TokenController extends Controller
{
	public function getAndStoreTokenFromOauthFlow()
	{
		$code 		   = isset($_GET['code']) ? $_GET['code'] : '';
		$client_id 	   = config('app.slack_client_id');
		$client_secret = config('app.slack_client_secret');
		$redirect_uri  = "http://{$_SERVER['HTTP_HOST']}/c";

		$slack_token   = config('app.slack_access_token');

		$interactor    = new CurlInteractor;
        $interactor->setResponseFactory(new SlackResponseFactory());
        $commander     = new Commander($slack_token, $interactor);

		$data = [
			'code' 		    => $code,
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'redirect_uri'  => $redirect_uri,
		];

		$response = $commander->execute('oauth.access', $data);

		$response = $response->getBody();

		$token = new Token();
		$token->token = isset($response['access_token']) ? $response['access_token'] : '';
		$token->save();

		setcookie('slack', md5($token->token), strtotime('now + 1 year'));

		// TODO - get users.identity
		$response = $commander->execute('users.identity');


		return redirect()->action(
			'TeamController@showLeaderboardAction', ['domain' => 'digitalsurgeons']
		);
	}
}