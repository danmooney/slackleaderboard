<?php
/**
 * @var $users App\Collections\User
 * @var $user App\Models\User
 * @var $team App\Models\Team
 * @var $emojis App\Collections\Reaction
 * @var $reaction App\Models\Reaction
 * @var $current_user App\Models\User
 * @var $reactions_to_this_users_posts_grouped_by_user App\Collections\PostUserReaction
 * @var $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id App\Collections\PostUserReaction
 * @var $single_user_reaction_received_counts App\Collections\PostUserReaction
 */
use App\Models\User;

$emojis_by_reaction_id = $emojis->generateFlatArrayByKey();
//$total_reaction_count_among_all_users = $users->getTotalReactionCountAmongAllUsers();
$users_by_user_id = [];

$user_page_belongs_to = $user;
$current_user = User::getFromSession();

// if user isn't logged in, use demo user for header
$user_for_display_in_header = $current_user->isLoggedIn() ? $current_user : User::getDemoUser();

?>
<h3>
    User:
    <a class="user-avatar-name-anchor"  href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
        <span class="user-name"><?= htmlspecialchars($user->name_binary) ?></span>
    </a>
    <?= $user->isSameAs($user_for_display_in_header) ? '(That\'s You!)' : '' ?>
</h3>
<table>
    <thead>
        <tr>
            <th align="center" class="nosort"><?= $user->total_reactions_given_count ?> Total Reactions Given By <?= htmlspecialchars($user->name_binary) ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="table-cell-reaction-list">
                <div>
                <?php
                    $emojis_output_for_this_user_count = 0;

                    foreach ((array) $user->total_reactions_by_reaction_id as $reaction_id => $total_count):
                        if ($emojis_output_for_this_user_count === 100) {
                            break;
                        }

                        $reaction = $emojis_by_reaction_id[$reaction_id];

                        if (!$reaction) {
                            continue;
                        }

                        $emojis_output_for_this_user_count += 1;
                        $anchor_title = $user->total_reactions_given_count ? sprintf('%s &#013;%s%% of all user\'s reactions given', htmlspecialchars($reaction->getMainAlias()->alias), round(($total_count / $user->total_reactions_given_count) * 100, 2)) : '';
                    ?>
                        <a data-reaction-id="<?= $reaction_id ?>" data-giver-user-id="<?= $user_page_belongs_to->getKey() ?>" class="reaction-anchor tooltip-permalink" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
                            <span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
                            <span class="reaction-count"><?= htmlspecialchars($total_count) ?></span>
                        </a>
                <?php
                    endforeach ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br>
<table>
    <thead>
        <tr>
            <?php $data = $single_user_reaction_received_counts->first()  ?>
            <th align="center" class="nosort"><?= $data ? $data->total_reactions_received_count : 0 ?> Total Reactions Received By <?= htmlspecialchars($user->name_binary) ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="table-cell-reaction-list">
                <div>
                    <?php
                    $emojis_output_for_this_user_count = 0;
                    $total_reactions_by_reaction_id = $data ? (array) $data->total_reactions_by_reaction_id : [];
                    foreach ($total_reactions_by_reaction_id as $reaction_id => $total_count):
                        if ($emojis_output_for_this_user_count === 100) {
                            break;
                        }

                        $reaction = isset($emojis_by_reaction_id[$reaction_id]) ? $emojis_by_reaction_id[$reaction_id] : null;

                        if (!$reaction) {
                            continue;
                        }

                        $emojis_output_for_this_user_count += 1;
                        $anchor_title = $user->total_reactions_received_count ? sprintf('%s &#013;%s%% of all user\'s reactions received', htmlspecialchars($reaction->getMainAlias()->alias), round(($total_count / $user->total_reactions_received_count) * 100, 2)) : '';
                        ?>
                        <a data-reaction-id="<?= $reaction_id ?>" data-receiver-user-id="<?= $user_page_belongs_to->getKey() ?>" class="reaction-anchor tooltip-permalink" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
                            <span class="reaction-img" style="background-image:url('<?= $reaction->image ?>')"></span>
                            <span class="reaction-count"><?= htmlspecialchars($total_count) ?></span>
                        </a>
                        <?php
                    endforeach ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br>
<br>
<hr>
<h3><strong>Top Reaction Givers<?php /* to this User's Posts */ ?></strong></h3>
<table>
    <thead>
        <tr>
            <th data-sortInitialOrder="asc">Name</th>
            <th># Reactions Given</th>
            <th>
                % of this Giver's Total Reactions Given
                <i class="fa fa-fw fa-sort-desc"></i>
            </th>
            <th class="nosort">Top Reactions Given<?php /* to this User */ ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($reactions_to_this_users_posts_grouped_by_user as $reaction_user):
            $user = $users->find($reaction_user->user_id);
            $user->total_reaction_percentage_given_count = $reaction_user->total_reaction_percentage_given_count = $user->total_reactions_given_count ? round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reactions_given_count) * 100, 2) : '';
            $users_by_user_id[$reaction_user->user_id] = $user;
        endforeach;

        $reactions_to_this_users_posts_grouped_by_user = $reactions_to_this_users_posts_grouped_by_user->sort(function ($a, $b) {
            return $b->total_reaction_percentage_given_count * 100 - $a->total_reaction_percentage_given_count * 100;
        });

        $total_eligible_reactions_to_this_users_posts_grouped_by_user = 0;

        foreach ($reactions_to_this_users_posts_grouped_by_user as $reaction_user):
            $user = $users_by_user_id[$reaction_user->user_id];
            if ($user->isEligibleToBeOnLeaderBoard()) {
                $total_eligible_reactions_to_this_users_posts_grouped_by_user += 1;
            }
        endforeach;

        foreach ($reactions_to_this_users_posts_grouped_by_user as $reaction_user):
            $user = $users_by_user_id[$reaction_user->user_id];
            if (!$user->isEligibleToBeOnLeaderBoard()) continue;
            $total_reaction_count_title = $user->total_reactions_given_count ? sprintf('%s%% of all user\'s reactions given', round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reactions_given_count) * 100, 2)) : '';
            ?>
            <tr <?= $app->tableRow->shouldBeInvisible($total_eligible_reactions_to_this_users_posts_grouped_by_user) ? 'style="display:none;"' : '' ?>>
                <td class="table-cell-user">
                    <a class="user-avatar-name-anchor" href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" />
                        <span class="user-name"><?= htmlspecialchars($user->name_binary) ?></span>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <strong><?= htmlspecialchars($reaction_user->total_reactions_to_this_users_posts) ?></strong>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= ltrim($user->total_reaction_percentage_given_count . '%', '%') ?>
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
                            <a data-reaction-id="<?= $reaction_id ?>" data-giver-user-id="<?= $user->getKey() ?>" data-receiver-user-id="<?= $user_page_belongs_to->getKey() ?>" class="reaction-anchor tooltip-permalink" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
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
<?php
    if ($app->tableRow->hasInvisibleRows()):
        echo view('_partials/button_show_more');
    endif
?>
<br>
<br>
<hr>
<h3><strong>Top Mutual Post Reaction Givers</strong></h3>
<table>
    <thead>
        <tr>
            <th data-sortInitialOrder="asc">Name</th>
            <th># Mutual Reactions Given<?php /*Total Mutual Reaction Count*/ ?></th>
            <th>
                % of this Giver's Total Reactions Given
                <i class="fa fa-fw fa-sort-desc"></i>
            </th>
            <th class="nosort">Top Mutual Reactions Given (to the same posts)</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id as $user_id => &$data):
            if (!isset($users_by_user_id[$user_id])) {
                $users_by_user_id[$user_id] = $users->find($user_id);
            }

            $user = $users_by_user_id[$user_id];
            $user->total_mutual_reaction_percentage_given_count = $data['total_mutual_reaction_percentage_given_count'] = $user->total_reactions_given_count ? round(($data['total'] / $user->total_reactions_given_count) * 100, 2) : '';
        endforeach;

        uasort($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id, function ($a, $b) {
            return $b['total_mutual_reaction_percentage_given_count'] * 100 - $a['total_mutual_reaction_percentage_given_count'] * 100;
        });

        $total_eligible_mutual_reactions_for_this_user_by_user_id_and_reaction_id = 0;

        foreach ($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id as $user_id => $data):
            $user = $users_by_user_id[$user_id];
            if ($user->isEligibleToBeOnLeaderBoard()) {
                $total_eligible_mutual_reactions_for_this_user_by_user_id_and_reaction_id += 1;
            }
        endforeach;

        foreach ($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id as $user_id => $data):
            $user = $users_by_user_id[$user_id];
            if (!$user->isEligibleToBeOnLeaderBoard()) continue;
            $total_reaction_count_title = $user->total_reactions_given_count ? sprintf('%s%% of all user\'s reactions given', round(($reaction_user->total_reactions_to_this_users_posts / $user->total_reactions_given_count) * 100, 2)) : '';
            arsort($data['reactions'], SORT_NUMERIC);
            ?>
            <tr <?= $app->tableRow->shouldBeInvisible($total_eligible_mutual_reactions_for_this_user_by_user_id_and_reaction_id) ? 'style="display:none;"' : '' ?>>
                <td class="table-cell-user">
                    <a href="<?= action('UserController@showLeaderboardAction', [$team->domain, $user->handle]) ?>">
                        <img class="user-avatar" width="<?= User::DEFAULT_AVATAR_SIZE ?>" src="<?= htmlspecialchars($user->getAvatar()) ?>" /><?= htmlspecialchars($user->name_binary) ?>
                    </a>
                </td>
                <td class="table-cell-total-reaction-count" align="right">
                    <strong><?= htmlspecialchars($data['total']) ?></strong>
                </td>
                <td class="table-cell-percentage-reaction-count" align="right">
                    <?= ltrim($user->total_mutual_reaction_percentage_given_count . '%', '%') ?>
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
                            <a data-reaction-id="<?= $reaction_id ?>" data-giver-user-id="<?= "{$user->getKey()}|{$user_page_belongs_to->getKey()}" ?>" class="reaction-anchor tooltip-permalink" title="<?= $anchor_title ?>" href="<?= action('ReactionController@showLeaderboardAction', [$team->domain, $reaction->getMainAlias()->alias]) ?>">
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
<?php
    if ($app->tableRow->hasInvisibleRows()):
        echo view('_partials/button_show_more');
    endif;
 /*
<h3><strong>Top Followers to This User's First Reactions</strong></h3>
 */
