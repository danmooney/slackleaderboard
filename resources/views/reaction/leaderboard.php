<?php
/**
 * @var $users App\Collections\User
 * @var $user App\Models\User
 * @var $team App\Models\Team
 * @var $emojis App\Collections\Reaction
 * @var $reaction App\Models\Reaction
 * @var $reaction_counts_by_users App\Collections\PostUserReaction
 */
use App\Models\User;

//$total_reaction_count_among_all_users = $users->getTotalReactionCountAmongAllUsers();
$users_by_user_id = [];
?>
<br>
<div>
	<span style="font-size: 24px;">Reaction:</span>

	<a style="vertical-align: top;" class="reaction-anchor" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
		<span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
		<span class="reaction-count"><?= htmlspecialchars($total_count) ?></span>
	</a>
	<span>:<?= $reaction->getMainAlias()->alias ?>:
</div>
<br>
<h3><strong>Top React Givers</strong></h3>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Reaction Count</th>
			<th>Percentage of This User's Total Reactions</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($reaction_counts_by_users as $reaction_user):
			$user = $users->find($reaction_user->user_id);
			$users_by_user_id[$reaction_user->user_id] = $user;
			if (!$user->isEligibleToBeOnLeaderBoard()) continue;
			$total_reaction_count_title = sprintf('%s%% of all user\'s reactions', round(($reaction_user->total_count_using_this_reaction / $user->total_reaction_count) * 100, 2));
			?>
			<tr>
				<td>
					<a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
						<img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
						<span class="user-name">
							<?= htmlspecialchars($user->name_binary) ?>
						</span>
					</a>
				</td>
				<td class="table-cell-total-reaction-count" align="right">
					<?= htmlspecialchars($reaction_user->total_count_using_this_reaction) ?>
				</td>
				<td class="table-cell-percentage-reaction-count" align="right">
					<?= round(($reaction_user->total_count_using_this_reaction / $user->total_reaction_count) * 100, 2) ?>%
				</td>
			</tr>
	<?php
		endforeach ?>
	</tbody>
</table>

