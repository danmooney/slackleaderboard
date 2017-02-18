<?php
/**
 * @var $users App\Collections\User
 * @var $user App\Models\User
 * @var $team App\Models\Team
 * @var $emojis App\Collections\Reaction
 * @var $reaction App\Models\Reaction
 * @var $reaction_given_counts_by_users App\Collections\PostUserReaction
 * @var $reaction_received_counts_by_users App\Collections\PostUserReaction
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
    <span>:<?= htmlspecialchars($reaction->getMainAlias()->alias) ?>:
</div>
<br>
<br>
<hr>
<h3><strong>Top Reaction Givers</strong></h3>
<table>
    <thead>
        <tr>
            <th data-sortInitialOrder="asc">Name</th>
            <th># Reactions Given</th>
            <th>
                % of this Giver's Total Reactions Given
                <i class="fa fa-fw fa-sort-desc"></i>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($reaction_given_counts_by_users as $reaction_user):
            $user = $users->find($reaction_user->user_id);
            $users_by_user_id[$reaction_user->user_id] = $user;
            $reaction_user->total_reactions_given_percentage = $user->total_reactions_given_count ? round(($reaction_user->total_count_using_this_reaction / $user->total_reactions_given_count) * 100, 2) : '';
        endforeach;

        $reaction_given_counts_by_users = $reaction_given_counts_by_users->sort(function ($a, $b) {
            return $b->total_reactions_given_percentage * 100 - $a->total_reactions_given_percentage * 100;
        });

        $total_eligible_reaction_counts_given_by_users = 0;

        foreach ($reaction_given_counts_by_users as $reaction_user):
            $user = $users_by_user_id[$reaction_user->user_id];
            if ($user->isEligibleToBeOnLeaderBoard()) {
                $total_eligible_reaction_counts_given_by_users += 1;
            }
        endforeach;

        foreach ($reaction_given_counts_by_users as $reaction_user):
            $user = $users_by_user_id[$reaction_user->user_id];
            if (!$user->isEligibleToBeOnLeaderBoard()) continue;
            $total_reaction_count_title = $user->total_reactions_given_count ? sprintf('%s%% of all user\'s reactions given', round(($reaction_user->total_count_using_this_reaction / $user->total_reactions_given_count) * 100, 2)) : '';
            ?>
            <tr <?= $app->tableRow->shouldBeInvisible($total_eligible_reaction_counts_given_by_users) ? 'style="display:none;"' : '' ?>>
                <td>
                    <a class="user-avatar-name-anchor" href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
                        <span class="user-name"><?= htmlspecialchars($user->name_binary) ?></span>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <?= htmlspecialchars($reaction_user->total_count_using_this_reaction) ?>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= ltrim($reaction_user->total_reactions_given_percentage . '%', '%') ?>
                </td>
            </tr>
    <?php
        endforeach ?>
    </tbody>
</table>
<?php
    if ($app->tableRow->hasInvisibleRows()):
        echo view('_partials/button_show_more');
    endif
?>
<br>
<br>
<hr>
<h3><strong>Top Reaction Receivers</strong></h3>
<table>
    <thead>
    <tr>
        <th data-sortInitialOrder="asc">Name</th>
        <th>Total Reactions Received</th>
        <th>
            % of this Receiver's Total Reactions Received
            <i class="fa fa-fw fa-sort-desc"></i>
        </th>
    </tr>
    </thead>
    <tbody>
        <?php
        foreach ($reaction_received_counts_by_users as $reaction_user):
            $user = $users->find($reaction_user->user_id);
            $users_by_user_id[$reaction_user->user_id] = $user;

            $reaction_user->total_reactions_received_percentage = $user->total_reactions_received_count ? round(($reaction_user->total_count_using_this_reaction / $user->total_reactions_received_count) * 100, 2) : '';
        endforeach;

        $reaction_received_counts_by_users = $reaction_received_counts_by_users->sort(function ($a, $b) {
            return $b->total_reactions_received_percentage * 100 - $a->total_reactions_received_percentage * 100;
        });

        $total_eligible_reaction_received_counts = 0;

        foreach ($reaction_received_counts_by_users as $reaction_user):
            $user = $users_by_user_id[$reaction_user->user_id];
            if ($user->isEligibleToBeOnLeaderBoard()) {
                $total_eligible_reaction_received_counts += 1;
            }
        endforeach;

        foreach ($reaction_received_counts_by_users as $reaction_user):
            $user = $users_by_user_id[$reaction_user->user_id];
            if (!$user->isEligibleToBeOnLeaderBoard()) continue;
            $total_reaction_count_title = $user->total_reactions_given_count ? sprintf('%s%% of all user\'s reactions given', round(($reaction_user->total_count_using_this_reaction / $user->total_reactions_given_count) * 100, 2)) : '';
            ?>
            <tr <?= $app->tableRow->shouldBeInvisible($total_eligible_reaction_received_counts) ? 'style="display:none;"' : '' ?>>
                <td>
                    <a class="user-avatar-name-anchor" href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
                        <span class="user-name"><?= htmlspecialchars($user->name_binary) ?></span>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <?= htmlspecialchars($reaction_user->total_count_using_this_reaction) ?>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= ltrim($reaction_user->total_reactions_received_percentage . '%', '%') ?>
                </td>
            </tr>
    <?php
        endforeach ?>
    </tbody>
</table>
<?php
    if ($app->tableRow->hasInvisibleRows()):
        echo view('_partials/button_show_more');
    endif;