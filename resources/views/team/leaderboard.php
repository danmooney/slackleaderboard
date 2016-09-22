<?php
/**
 * @var $users App\Collections\User
 * @var $user App\Models\User
 * @var $team App\Models\Team
 * @var $emojis App\Collections\Reaction
 * @var $reaction App\Models\Reaction
 */
use App\Models\User;
$emojis_by_reaction_id = $emojis->generateFlatArrayByKey();
$total_reaction_count_among_all_users = $users->getTotalReactionCountAmongAllUsers();
?>
<br>
<h3>Top Reactors All-Time</h3>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Reaction Count</th>
			<th>Top Reactions</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($users as $user):
			if (!$user->isEligibleToBeOnLeaderBoard() /*|| !$user->total_reaction_count*/) continue;
			$total_reaction_count_title = sprintf('%s%% of all team\'s reactions', round(($user->total_reaction_count / $total_reaction_count_among_all_users) * 100, 2));
			?>
			<tr>
				<td>
					<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
						<img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
					</a>
				</td>
				<td align="right" title="<?= $total_reaction_count_title ?>">
					<strong><?= htmlspecialchars($user->total_reaction_count) ?></strong>
				</td>
				<td>
					<?php
						$emojis_output_for_this_user_count = 0;

						foreach ((array) $user->total_reactions_by_reaction_id as $reaction_id => $total_count):
							if ($emojis_output_for_this_user_count === 10) {
								break;
							}

							$reaction = $emojis_by_reaction_id[$reaction_id];

							if (!$reaction) {
								continue;
							}

							$emojis_output_for_this_user_count += 1;
							$anchor_title = sprintf('%s &#013;%s%% of all user\'s reactions', htmlspecialchars($reaction->getMainAlias()->alias), round(($total_count / $user->total_reaction_count) * 100, 2));
						?>
							<a class="reaction-anchor" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
								<span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
								<span class="reaction-count"><?= htmlspecialchars($total_count) ?></span>
							</a>
					<?php
						endforeach ?>
				</td>
			</tr>
	<?php
		endforeach ?>
	</tbody>
</table>
