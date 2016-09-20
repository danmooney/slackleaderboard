<?php

namespace App\Models;

class ReactionAlias extends ModelAbstract
{
	use Traits\HasCompositePrimaryKey;

	protected $primaryKey = ['reaction_id', 'alias'];
}