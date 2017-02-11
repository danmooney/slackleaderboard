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
use Illuminate\Support\Facades\Log;

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

        if (!$response['ok']) {
            Log::warning('oauth.access call failed', $response);
            return redirect()->action('SlackController@guestHomepageAction');
        }

        $their_slack_access_token = isset($response['access_token']) ? $response['access_token'] : '';
        $commander                = new Commander($their_slack_access_token, $interactor);

        DB::beginTransaction();

        // add team if it doesn't already exist
        $response = $commander->execute('team.info');
        $team     = Team::importFromSlackResponseBody($response->getBody());

        // get current user's identity and store in session
        $response              = $commander->execute('auth.test');
        $current_user_slack_id = $response->getBody()['user_id'];

        $user 	       		   = User::where('slack_user_id', $current_user_slack_id)->first();

        if (!$user) {
            // just get you first! too slow otherwise
            $response = $commander->execute('users.info', [
                'user' => $current_user_slack_id
            ]);

            $response = $response->getBody();

            if (!$response['ok']) {
                Log::warning('users.info call failed', $response);
                return redirect()->action('SlackController@guestHomepageAction');
            }

            $response['members'] = [$response['user']];

            $users = UserCollection::importFromSlackResponseBody($response, [$current_user_slack_id]);
            $user  = $users->where('slack_user_id', $current_user_slack_id)->first();
        }

        $token = Token::find($user->getKey()) ?: new Token();
        $token->user_id = $user->getKey();
        $token->token   = $their_slack_access_token;
        $token->save();

        User::saveIntoSession($user);

        DB::commit();

        // if posts from beginning of time haven't been fetched yet for the team, then run the SlackDataFetch command with team id as an argument
		if (!$team->posts_from_beginning_of_time_fetched) {
		    $slack_data_fetch_artisan_command = sprintf(
		        '%s/php %s/artisan %s %s > /dev/null 2>/dev/null &',
                PHP_BINDIR,
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