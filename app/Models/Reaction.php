<?php

namespace App\Models;

class Reaction extends ModelAbstract
{
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