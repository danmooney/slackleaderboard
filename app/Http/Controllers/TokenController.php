<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App;
use DB;
use Input;
use App\Models\User;
use App\Models\Team;
use App\Collections\User as UserCollection;

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

        $my_slack_access_token   = config('app.slack_access_token');

		$interactor    = new CurlInteractor;
        $interactor->setResponseFactory(new SlackResponseFactory());
        $commander     = new Commander('', $interactor);

        $data = [
            'code' 		    => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri,
        ];

        $response = $commander->execute('oauth.access', $data);
        $response = $response->getBody();

        $their_slack_access_token = isset($response['access_token']) ? $response['access_token'] : '';
        $commander                = new Commander($their_slack_access_token, $interactor);

        DB::beginTransaction();

        // add team if it doesn't already exist
        $response = $commander->execute('team.info');
        $team     = Team::importFromSlackResponseBody($response->getBody());

        // add users
        $response = $commander->execute('users.list');
        $users    = UserCollection::importFromSlackResponseBody($response->getBody());

        // get current user's identity and store in session
        $response              = $commander->execute('auth.test');
        $current_user_slack_id = $response->getBody()['user_id'];
        $user 	       		   = $users->where('slack_user_id', $current_user_slack_id)->first() ?: new User();

        $token = Token::find($user->getKey()) ?: new Token();
        $token->user_id = $user->getKey();
        $token->token   = $their_slack_access_token;
        $token->save();

        setcookie('slack', md5($token->token), strtotime('now + 1 year'));

        User::saveIntoSession($user);

        DB::commit();

        return redirect()->action(
            'TeamController@showLeaderboardAction', ['domain' => $team->domain]
        );
	}
}