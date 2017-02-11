<?php

namespace App\Models;

class ReactionFetchLog extends ModelAbstract
{
    protected $primaryKey = 'team_id';
    protected $guarded = [];

    public $incrementing = false;

	const STALL_CONSIDERATION_THRESHOLD_SECONDS = 60 * 60 * 24; // 24 hours
	const RECENT_THRESHOLD_SECONDS = 60 * 10; // 10 minutes ago

	public function hasMostLikelyBeenStalled()
	{
        return strtotime($this->updated_at) + self::STALL_CONSIDERATION_THRESHOLD_SECONDS < time();
	}

	public function lastFetchHasBeenMadeTooRecently()
    {
        return !$this->in_process && strtotime($this->updated_at) + self::RECENT_THRESHOLD_SECONDS > time();
    }
}