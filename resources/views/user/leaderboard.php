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
//$total_reaction_count_among_all_users = $users->getTotalReactionCountAmongAllUsers();

?>
<h3>
	User:
	<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
		<img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
	</a>
</h3>
<br>
<h3><strong>Top Reactors to this User's Posts</strong></h3>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Reaction Count</th>
			<th>Percentage of This User's Total Reactions</th>
			<th>Top Reactions Used for this User</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($reactions_to_this_users_posts_grouped_by_user as $reaction_user):
			$user = $users->find($reaction_user->user_id);
			if (!$user->isEligibleToBeOnLeaderBoard()) continue;
			$total_reaction_count_title = sprintf('%s%% of all user\'s reactions', round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reaction_count) * 100, 2));
			?>
			<tr>
				<td>
					<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
						<img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
					</a>
				</td>
				<td align="right">
					<strong><?= htmlspecialchars($reaction_user->total_reactions_to_this_users_posts) ?></strong>
				</td>
				<td align="right">
					<?= round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reaction_count) * 100, 2) ?>%
				</td>
				<td>
					<?php
						$emojis_output_for_this_user_count = 0;

						foreach ((array) $reaction_user->reaction_count_by_reaction_id as $reaction_id => $total_count):
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

<h3><strong>Top Followers to This User's First Reactions</strong></h3>
