<?php
/**
 * @var $users App\Collections\Collection
 * @var $user App\Models\User
 * @var $team App\Models\Team
 * @var $emojis App\Collections\Reaction
 * @var $reaction App\Models\Reaction
 */
use App\Models\User;
?>

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
			if (!$user->isEligibleToBeOnLeaderBoard() /*|| !$user->total_reaction_count*/) continue
			?>
			<tr>
				<td>
					<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
						<img width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
					</a>
				</td>
				<td><a href="#"><?= htmlspecialchars($user->total_reaction_count) ?></a></td>
				<td>
					<?php
						$emojis_output_for_this_user_count = 0;

						foreach ((array) $user->total_reactions_by_reaction_id as $reaction_id => $total_count):
							if ($emojis_output_for_this_user_count === 10) {
								break;
							}

							$reaction = $emojis->find($reaction_id);

							if (!$reaction) {
								continue;
							}

							$emojis_output_for_this_user_count += 1;
							$anchor_title = sprintf('%s &#013;%s%% of all user\'s reactions', $reaction->getMainAlias()->alias, round(($total_count / $user->total_reaction_count) * 100, 2));
						?>
							<a title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
								<img width="32" src="<?= $reaction->image ?>">
								<?= htmlspecialchars($total_count) ?>
							</a>
					<?php
						endforeach ?>
				</td>
			</tr>
	<?php
		endforeach ?>
	</tbody>
</table>
