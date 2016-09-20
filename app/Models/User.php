<?php

namespace App\Models;

class User extends ModelAbstract
{
	const AVATAR_SIZES = [24, 32, 48, 72, 192, 512, 1024, 'original'];
	const DEFAULT_AVATAR_SIZE = 32;

//    protected $guarded = [];

	public function isEligibleToBeOnLeaderBoard()
	{
		return (
			!$this->slack_deleted &&
			!$this->slack_restricted &&
			!$this->slack_ultra_restricted &&
			!$this->slack_bot &&
			$this->name !== 'slackbot'
		);
	}

	public function getAvatar($size = self::DEFAULT_AVATAR_SIZE)
	{
		$is_gravatar = stripos(parse_url($this->avatar, PHP_URL_HOST), 'gravatar') !== false;

		if ($is_gravatar) {
			$avatar  = preg_replace('#\?s=\d+#', "?s=$size", $this->avatar);
		} else {
			$avatar = preg_replace('#(192|original)\.(png|jpe?g)$#', "$size.$2", $this->avatar);
		}


		return $avatar;
	}
}