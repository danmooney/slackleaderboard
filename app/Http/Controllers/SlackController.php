<?php

namespace App\Http\Controllers;

use App\Models\Team;
use DB;
use App;

class SlackController extends Controller
{
	public function guestHomepageAction()
	{
		$current_user = session()->get('user');

		if ($current_user) {
			$team = Team::find($current_user->team_id);
			if ($team) {
				return redirect()->action(
					'TeamController@showLeaderboardAction', ['domain' => $team->domain]
				);
			}
		}

		$sign_in_url = config('app.slack_oauth_url');

		$this->_layout->content = view('guest.homepage', [
			'sign_in_url' => $sign_in_url
		]);

		return $this->_layout;
	}
}