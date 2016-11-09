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
    <a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>"><img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
        <span class="user-name">
            <?= htmlspecialchars($user->name_binary) ?>
        </span>
    </a>
    <?= $user->isSameAs($current_user) ? '(That\'s You!)' : '' ?>
</h3>

<br>
<br>
<hr>
<h3><strong>Top Reaction Givers<?php /* to this User's Posts */ ?></strong></h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th># Reactions Given</th>
            <th>% of this Giver's Total Reactions Given<?php /*Reaction Giver's Total Reactions */ ?></th>
            <th class="nosort">Top Reactions Given<?php /* to this User */ ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($reactions_to_this_users_posts_grouped_by_user as $reaction_user):
            $user = $users->find($reaction_user->user_id);
            $users_by_user_id[$reaction_user->user_id] = $user;
            if (!$user->isEligibleToBeOnLeaderBoard()) continue;
            $total_reaction_count_title = $user->total_reactions_given_count ? sprintf('%s%% of all user\'s reactions given', round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reactions_given_count) * 100, 2)) : '';
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
                    <strong><?= htmlspecialchars($reaction_user->total_reactions_to_this_users_posts) ?></strong>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= $user->total_reactions_given_count ? round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reactions_given_count) * 100, 2) . '%' : '' ?>
                </td>
                <td class="table-cell-reaction-list">
                    <div>
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
                            $anchor_title = sprintf('%s &#013;%s%% of all user\'s reactions given', htmlspecialchars($reaction->getMainAlias()->alias), round(($total_count / $user->total_reactions_given_count) * 100, 2));
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
<h3><strong>Top Mutual Post Reaction Givers</strong></h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th># Mutual Reactions Given<?php /*Total Mutual Reaction Count*/ ?></th>
            <th>% of this Giver's Total Reactions Given</th>
            <th class="nosort">Top Mutual Reactions Given (to the same posts)</th>
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
            $total_reaction_count_title = $user->total_reactions_given_count ? sprintf('%s%% of all user\'s reactions given', round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reactions_given_count) * 100, 2)) : '';
            arsort($data['reactions'], SORT_NUMERIC);
            ?>
            <tr>
                <td class="table-cell-user">
                    <a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <strong><?= htmlspecialchars($data['total']) ?></strong>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= $user->total_reactions_given_count ? round(($data['total'] / $user->total_reactions_given_count) * 100, 2) . '%' : '' ?>
                </td>
                <td class="table-cell-reaction-list">
                    <div>
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

<?php /*
<h3><strong>Top Followers to This User's First Reactions</strong></h3>
 */
