<?php

namespace App\Collections;
use App\Models\User as UserModel;
use App\Models\Team as TeamModel;

class User extends Collection
{
    public function getTotalReactionCountAmongAllUsers()
    {
        $total_reaction_count_among_all_users = 0;

        foreach ($this->items as $item) {
            $total_reaction_count_among_all_users += intval($item->total_reactions_given_count);
        }

        return $total_reaction_count_among_all_users;
    }

    public function getTotalReactionCountByReactionIdAmongAllUsers()
    {
        $total_reaction_count_by_reaction_id_among_all_users = [];

        foreach ($this->items as $item) {
            if (!$item->total_reactions_by_reaction_id) {
                continue;
            }

            foreach ($item->total_reactions_by_reaction_id as $reaction_id => $reaction_count) {
                if (!isset($total_reaction_count_by_reaction_id_among_all_users[$reaction_id])) {
                    $total_reaction_count_by_reaction_id_among_all_users[$reaction_id] = 0;
                }

                $total_reaction_count_by_reaction_id_among_all_users[$reaction_id] += $reaction_count;
            }
        }

        arsort($total_reaction_count_by_reaction_id_among_all_users, SORT_NUMERIC);

        return $total_reaction_count_by_reaction_id_among_all_users;
    }

    public static function importFromSlackResponseBody(array $response_body, array $slack_id_whitelist = null)
    {
        $users_response   = $response_body['members'];

        $users_by_team_id       = [];
        $teams_by_slack_team_id = [];

        $users_saved = [];

        foreach ($users_response as $member) {
            if ($slack_id_whitelist && !in_array($member['id'], $slack_id_whitelist)) {
                continue;
            }

            $slack_team_id = $member['team_id'];

            if (!isset($teams_by_slack_team_id[$slack_team_id])) {
                $teams_by_slack_team_id[$slack_team_id] = TeamModel::where(['slack_team_id' => $slack_team_id])->first();
            }

            $team    = $teams_by_slack_team_id[$slack_team_id];
            $team_id = $team->getKey();

            if (!isset($users_by_team_id[$team_id])) {
                $users_by_team_id[$team_id] = UserModel::where(['team_id' => $team_id])->get();
            }

            $users = $users_by_team_id[$team_id];

            $user                 = $users->where('slack_user_id', $member['id'])->first() ?: new UserModel();
            $user->slack_user_id  = $member['id'];
            $user->team_id        = $team->getKey();
            $user->name_binary    = isset($member['real_name']) ? $member['real_name'] : $member['profile']['real_name'];
            $user->name           = app()->removeEmojis($user->name_binary);
            $user->handle         = $member['name'];
            $user->avatar         = isset($member['profile']['image_original']) ? $member['profile']['image_original'] : $member['profile']['image_192'];
            $user->email          = isset($member['profile']['email']) ? $member['profile']['email'] : null;
            $user->slack_deleted  = $member['deleted'];

            if ($member['deleted']) {
                $user->slack_restricted = $user->slack_ultra_restricted = true;
            } else {
                $user->slack_admin            = $member['is_admin'];
                $user->slack_owner            = $member['is_owner'];
                $user->slack_primary_owner    = $member['is_primary_owner'];
                $user->slack_restricted       = $member['is_restricted'];
                $user->slack_ultra_restricted = $member['is_ultra_restricted'];
                $user->slack_bot              = $member['is_bot'];
            }

            $user->save();

            $users_saved[] = $user;
        }

        return new static($users_saved);
    }
}