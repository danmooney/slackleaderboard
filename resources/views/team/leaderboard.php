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
$total_reaction_count_by_reaction_id_among_all_users = $users->getTotalReactionCountByReactionIdAmongAllUsers();
$users_by_id = [];
?>
<h3>
    Team:
    <a href="<?= action('TeamController@showLeaderboardAction', [$team->domain]) ?>">
        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($team->icon) ?>" />
        <span class="user-name">
            <?= htmlspecialchars($team->name) ?>
        </span>
    </a>
</h3>
<h3>
    <strong><?= $total_reaction_count_among_all_users ?> total <?= str_plural('reaction', $total_reaction_count_among_all_users) ?></strong>
</h3>
<br>
<hr>
<h3><strong>Top Reaction Givers All-Time</strong></h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Total Reactions Given</th>
            <th>
                % of Team's Total Reactions
                <i class="fa fa-fw fa-sort-desc"></i>
            </th>
            <th class="nosort">Top Reactions Given</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($users as $user):
            $users_by_id[$user->getKey()] = $user;
            if (!$user->isEligibleToBeOnLeaderBoard() /*|| !$user->total_reactions_given_count*/) continue;
            ?>
            <tr>
                <td class="table-cell-user">
                    <a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <?= htmlspecialchars($user->total_reactions_given_count) ?>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right"><?= $total_reaction_count_among_all_users ? round(($user->total_reactions_given_count / $total_reaction_count_among_all_users) * 100, 2) . '%' : '' ?></td>
                <td class="table-cell-reaction-list">
                    <div>
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
                            $anchor_title = $user->total_reactions_given_count ? sprintf('%s &#013;%s%% of all user\'s reactions given', htmlspecialchars($reaction->getMainAlias()->alias), round(($total_count / $user->total_reactions_given_count) * 100, 2)) : '';
                        ?>
                            <a class="reaction-anchor" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
                                <span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
                                <span class="reaction-count"><?= htmlspecialchars($total_count) ?></span>
                            </a>
                    <?php
                        endforeach ?>
                    </div>
                </td>
            </tr>
    <?php
        endforeach ?>
    </tbody>
</table>

<br>
<br>
<hr>
<h3><strong>Top Reaction Receivers All-Time</strong></h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Total Reactions Received</th>
            <th>
                % of Team's Total Reactions
                <i class="fa fa-fw fa-sort-desc"></i>
            </th>
            <th class="nosort">Top Reactions Received</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($single_user_reaction_received_counts as $data):
            if (!isset($users_by_id[$data->posting_user_id])) {
                $users_by_id[$data->posting_user_id] = $users->find($data->posting_user_id);
            }

            $user = $users_by_id[$data->posting_user_id];

            if (!$user->isEligibleToBeOnLeaderBoard() /*|| !$user->total_reactions_given_count*/) continue;
            $total_reaction_count_title = $total_reaction_count_among_all_users ? sprintf('%s%% of all team\'s reactions received', round(($user->total_reactions_received_count / $total_reaction_count_among_all_users) * 100, 2)) : '';
            ?>
            <tr>
                <td class="table-cell-user">
                    <a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
                        <span class="user-name">
                            <?= htmlspecialchars($user->name_binary) ?>
                        </span>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <?= htmlspecialchars($data->total_reactions_received_count) ?>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right"><?= $total_reaction_count_among_all_users ? round(($user->total_reactions_received_count / $total_reaction_count_among_all_users) * 100, 2) . '%' : '' ?></td>
                <td class="table-cell-reaction-list">
                    <div>
                        <?php
                        $emojis_output_for_this_user_count = 0;

                        foreach ((array) $data->total_reactions_by_reaction_id as $reaction_id => $total_count):
                            if ($emojis_output_for_this_user_count === 10) {
                                break;
                            }

                            $reaction = $emojis_by_reaction_id[$reaction_id];

                            if (!$reaction) {
                                continue;
                            }

                            $emojis_output_for_this_user_count += 1;
                            $anchor_title = $user->total_reactions_received_count ? sprintf('%s &#013;%s%% of all user\'s reactions received', htmlspecialchars($reaction->getMainAlias()->alias), round(($total_count / $user->total_reactions_received_count) * 100, 2)) : '';
                            ?>
                            <a class="reaction-anchor" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
                                <span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
                                <span class="reaction-count"><?= htmlspecialchars($total_count) ?></span>
                            </a>
                            <?php
                        endforeach ?>
                    </div>
                </td>
            </tr>
    <?php
        endforeach ?>
    </tbody>
</table>

<br>
<br>
<hr>
<h3><strong>Top Emojis Used All-Time</strong></h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Total Times Used</th>
            <th>
                % of Team's Total Reactions
                <i class="fa fa-fw fa-sort-desc"></i>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($total_reaction_count_by_reaction_id_among_all_users as $reaction_id => $total_count):
            $reaction = $emojis_by_reaction_id[$reaction_id];
            if (!$reaction) {
                continue;
            }

            ?>
            <tr>
                <td class="table-cell-user">
                    <a class="reaction-anchor" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
                        <span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
                        <span class="reaction-count">:<?= htmlspecialchars($reaction->getMainAlias()->alias) ?>:</span>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right"><?= $total_count ?></td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= $total_reaction_count_among_all_users ? round(($total_count / $total_reaction_count_among_all_users) * 100, 2) . '%' : '' ?>
                </td>
            </tr>
    <?php
        endforeach ?>
    </tbody>
</table>