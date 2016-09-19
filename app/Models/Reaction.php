<?php

namespace App\Models;

class Reaction extends ModelAbstract
{
	public function aliases()
	{
		return $this->hasMany(ReactionAlias::class);
	}
}