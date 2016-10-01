<?php
/**
 * @var $users App\Collections\User
 * @var $user App\Models\User
 * @var $team App\Models\Team
 * @var $emojis App\Collections\Reaction
 * @var $reaction App\Models\Reaction
 * @var $current_user App\Models\User
 */
use App\Models\User;

$emojis_by_reaction_id = $emojis->generateFlatArrayByKey();
//$total_reaction_count_among_all_users = $users->getTotalReactionCountAmongAllUsers();
$users_by_user_id = [];

$current_user = session()->get('user') ?: new User();
?>
<h3>
	User:
	<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
		<img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
		<?= htmlspecialchars($user->name_binary) ?>
		<?= $user->isSameAs($current_user) ? '(That\'s You!)' : '' ?>
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
			$users_by_user_id[$reaction_user->user_id] = $user;
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

<h3><strong>Top Mutual Post Reactor Reactions</strong></h3>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Mutual Reaction Count</th>
			<th>Percentage of This User's Total Reactions</th>
			<th>Top Mutual Reactions (to the same posts)</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id as $user_id => $data):
			if (!isset($users_by_user_id[$user_id])) {
				$users_by_user_id[$user_id] = $users->find($user_id);
			}
			$user = $users_by_user_id[$user_id];
			if (!$user->isEligibleToBeOnLeaderBoard()) continue;
			$total_reaction_count_title = sprintf('%s%% of all user\'s reactions', round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reaction_count) * 100, 2));
			arsort($data['reactions'], SORT_NUMERIC);
			?>
			<tr>
				<td>
					<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
						<img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
					</a>
				</td>
				<td align="right">
					<strong><?= htmlspecialchars($data['total']) ?></strong>
				</td>
				<td align="right">
					<?= round(($data['total'] / $user->total_reaction_count) * 100, 2) ?>%
				</td>
				<td>
					<?php
						$emojis_output_for_this_user_count = 0;

						foreach ((array) $data['reactions'] as $reaction_id => $total_count):
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

<?php /*
<h3><strong>Top Followers to This User's First Reactions</strong></h3>
 */
