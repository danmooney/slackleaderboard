<?php

namespace App\Models;

class ReactionFetchLog extends ModelAbstract
{
    protected $primaryKey = 'team_id';
    protected $guarded = [];

    public $incrementing = false;

	const STALL_CONSIDERATION_THRESHOLD_SECONDS = 60 * 60 * 24;

	public function hasMostLikelyBeenStalled()
	{
        return strtotime($this->updated_at) + self::STALL_CONSIDERATION_THRESHOLD_SECONDS < time();
	}
}