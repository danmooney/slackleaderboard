<?php

namespace App\Collections;

class User extends Collection
{
	public function getTotalReactionCountAmongAllUsers()
	{
		$total_reaction_count_among_all_users = 0;

		foreach ($this->items as $item) {
			$total_reaction_count_among_all_users += intval($item->total_reaction_count);
		}

		return $total_reaction_count_among_all_users;
	}
}