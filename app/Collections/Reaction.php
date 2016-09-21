<?php

namespace App\Collections;
use App\Models\Team;
use App\Models\Reaction as ReactionModel;

class Reaction extends Collection
{
	private static $_reactions_by_team_id = [];
	private static $_reactions_default_set_by_reaction_id = [];

	public static function getReactionsAndReactionAliasesByTeam(Team $team, $include_default_set = true)
	{
		$need_to_fetch_default_set    = $include_default_set && empty(static::$_reactions_default_set_by_reaction_id);
		$need_to_fetch_team_reactions = !isset(static::$_reactions_by_team_id[$team->getKey()]);

		if (!$need_to_fetch_default_set && !$need_to_fetch_team_reactions) {
			// addition maintains index (in this case, reaction id) association
			return new static (static::$_reactions_by_team_id[$team->getKey()] + static::$_reactions_default_set_by_reaction_id);
		}

		$query = ReactionModel::with('aliases');

		if ($need_to_fetch_team_reactions) {
			static::$_reactions_by_team_id[$team->getKey()] = [];
			$query->where('team_id', $team->getKey());
		}

		if ($need_to_fetch_default_set) {
			$query->orWhere('team_id', null);
		}

		$reactions = $query->get();

		foreach ($reactions as $reaction) {
			if (!$reaction->team_id) {
				if ($need_to_fetch_default_set) {
					static::$_reactions_default_set_by_reaction_id[$reaction->reaction_id] = $reaction;
				}
			} else {
				if ($need_to_fetch_team_reactions) {
					static::$_reactions_by_team_id[$team->getKey()][$reaction->reaction_id] = $reaction;
				}
			}
		}

		if (!$include_default_set) {
			return new static(static::$_reactions_by_team_id[$team->getKey()]);
		} else {
			return new static (static::$_reactions_by_team_id[$team->getKey()] + static::$_reactions_default_set_by_reaction_id);
		}
	}
}