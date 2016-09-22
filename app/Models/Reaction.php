<?php

namespace App\Models;
use DB;

class Reaction extends ModelAbstract
{
	protected $guarded = [];

	public static function getReactionByAlias($reaction_alias)
	{
		$row = DB::table('reaction AS r')
			->join('reaction_alias AS ra', 'r.reaction_id', '=', 'ra.reaction_id')
			->where('ra.alias', '=', $reaction_alias)
			->select('r.*')
			->first()
		;

		return $row ? new static((array) $row) : $row;
	}

	public function aliases()
	{
		return $this->hasMany(ReactionAlias::class);
	}

	/**
	 * @return ReactionAlias
	 */
	public function getMainAlias()
	{
		if (!isset($this->main_alias)) {
			foreach ($this->aliases as $alias) {
				if ($alias->is_main_alias) {
					$this->main_alias = $alias;
					break;
				}
			}
		}

		return $this->main_alias;
	}
}