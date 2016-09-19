<?php

namespace App\Models;

class PostUserReaction extends ModelAbstract
{
	public function aliases()
	{
		return $this->hasMany(ReactionAlias::class);
	}
}