<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App;
use DB;
use Input;
use App\Models\User;
use App\Models\Team;
use App\Collections\User as UserCollection;
use App\Console\Commands\SlackDataFetch;

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

		if (!$team->posts_from_beginning_of_time_fetched) {
		    $slack_data_fetch_artisan_command = sprintf(
		        '%s %s %s > /dev/null 2>/dev/null &',
                base_path(),
                (new SlackDataFetch())->getSignature(),
                $team->getKey()
            );

			shell_exec($slack_data_fetch_artisan_command);
		}

        return redirect()->action(
            'TeamController@showLeaderboardAction', ['domain' => $team->domain]
        );
	}
}