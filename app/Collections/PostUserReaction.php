<?php

namespace App\Collections;
use App\Collections\User as UserCollection;
use App\Models\Team;
use App\Models\User as UserModel;
use App\Models\Reaction as ReactionModel;
use DB;
use Reaction;

class PostUserReaction extends Collection
{
    public static function getTotalReactionCountsByEachUserOnTeamAndAddToUsers(Team $team, UserCollection $users)
    {
        $rows = DB::table('post_user_reaction AS pur')
            ->join('user AS u', 'pur.user_id', '=', 'u.user_id')
            ->where('u.team_id', '=', $team->getKey())
            ->whereIn('u.user_id', $users->modelKeys())
            ->groupBy('user_id')
            ->select('pur.user_id', DB::raw('COUNT(*) AS total_reaction_count'))
            ->get()
        ;

        foreach ($users as $user) {
            foreach ($rows as $row) {
                if ($user->user_id !== $row->user_id) {
                    continue;
                }

                $user->total_reaction_count = $row->total_reaction_count;
            }

            if (!isset($user->total_reaction_count)) {
                $user->total_reaction_count = 0;
            }
        }
    }

    public static function getCountsOfReactionsToASingleUsersPostsGroupedByUser(Team $team, UserCollection $users)
    {
        //  TODO - to get most reacted posts group by p.post_id instead
        $rows = DB::table('post_user_reaction AS pur')
            ->join('post AS p', 'pur.post_id', '=', 'p.post_id')
            ->join('user AS u', 'p.user_id', '=', 'u.user_id')
            ->where('u.team_id', '=', $team->getKey())
            ->whereIn('u.user_id', $users->modelKeys())
            ->groupBy('p.user_id')
            ->select(
                'p.user_id AS posting_user',
                DB::raw('GROUP_CONCAT(pur.reaction_id) AS reaction_list'),
                DB::raw('COUNT(pur.reaction_id) AS total_reaction_count')
            )
            ->orderBy('total_reaction_count', 'DESC')
            ->get()
        ;

        foreach ($rows as $row) {
            $reaction_list                  = explode(',', $row->reaction_list);
            $total_reactions_by_reaction_id = array_count_values($reaction_list);
            unset($row->reaction_list);

            arsort($total_reactions_by_reaction_id);
            $row->total_reactions_by_reaction_id = $total_reactions_by_reaction_id;
        }

        return $rows;
    }

    public static function getAllPostUserReactionsByEachUserOnTeamAndAddToUsers(Team $team, UserCollection $users)
    {
        $rows = DB::table('post_user_reaction AS pur')
            ->join('user AS u', 'pur.user_id', '=', 'u.user_id')
            ->where('team_id', '=', $team->getKey())
            ->whereIn('u.user_id', $users->modelKeys())
            ->groupBy('user_id', 'reaction_id')
            ->select('u.user_id', 'pur.reaction_id', DB::raw('COUNT(reaction_id) AS total_reactions'))
            ->get()
        ;

        $total_reactions_by_user_id_and_reaction_id = [];

        foreach ($rows as $row) {
            if (!isset($total_reactions_by_user_id_and_reaction_id[$row->user_id])) {
                $total_reactions_by_user_id_and_reaction_id[$row->user_id] = [];
            }

            $total_reactions_by_user_id_and_reaction_id[$row->user_id][$row->reaction_id] = $row->total_reactions;
        }

        foreach ($total_reactions_by_user_id_and_reaction_id as $user_id => $reactions) {
            $user = $users->find($user_id);

            if (!isset($user->total_reactions_by_reaction_id)) {
                $user->total_reactions_by_reaction_id = [];
            }

            arsort($total_reactions_by_user_id_and_reaction_id[$user_id], SORT_NUMERIC);

            $user->total_reactions_by_reaction_id = $total_reactions_by_user_id_and_reaction_id[$user_id];
        }
    }

    public static function getTotalReactionsToASingleUsersPostsGroupedByAllUsers(UserModel $user_of_interest, UserCollection $users)
    {
        $rows = DB::table('post_user_reaction AS pur')
            ->join('post', 'pur.post_id', '=', 'post.post_id')
            ->where('post.user_id', '=', $user_of_interest->getKey())
            ->groupBy('pur.user_id')
            ->select('pur.user_id', DB::raw('COUNT(*) AS total_reactions_to_this_users_posts'), DB::raw('GROUP_CONCAT(pur.reaction_id) AS reaction_id_list'))
            ->get()
        ;


        $rows = $rows->toArray();

        // get all reaction ids for this user and order by count desc
        foreach ($rows as $row) {
            $row->reaction_count_by_reaction_id = array_count_values(explode(',', $row->reaction_id_list));
            unset($row->reaction_id_list);
            arsort($row->reaction_count_by_reaction_id);
        }

        // sort by total_reactions_to_this_users_posts DESC
        usort($rows, function ($a, $b) {
            return $b->total_reactions_to_this_users_posts - $a->total_reactions_to_this_users_posts;
        });

        return new static($rows);
    }

    public static function getMutualReactionsToASingleUsersReactions(UserModel $user_of_interest)
    {
        $rows = DB::select("
                SELECT
                    pur.reaction_id,
                    COUNT(pur.reaction_id) AS total_mutual_reaction_count,
                    pur.user_id
                FROM
                    post_user_reaction pur
                INNER JOIN (
                    SELECT
                        pur2.post_id,
                        pur2.reaction_id
                    FROM
                        post_user_reaction pur2
                    WHERE
                        user_id = {$user_of_interest->getKey()}
                ) pur2 
                ON pur.post_id = pur2.post_id
                AND pur.reaction_id = pur2.reaction_id
                AND pur.user_id != {$user_of_interest->getKey()}
                GROUP BY pur.user_id, pur.reaction_id"
            )
        ;

        $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id = [];

        foreach ($rows as $row) {
            if (!isset($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id[$row->user_id])) {
                $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id[$row->user_id] = [];
            }

            if (!isset($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id[$row->user_id]['total'])) {
                $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id[$row->user_id]['total'] = 0;
            }

            $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id[$row->user_id]['total']           += $row->total_mutual_reaction_count;
            $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id[$row->user_id]['reactions'][$row->reaction_id]  = $row->total_mutual_reaction_count;
        }

        uasort($total_mutual_reactions_for_this_user_by_user_id_and_reaction_id, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id;
    }

    public static function getEmojiReactionGivenCountByReactionGroupedByAllUsers(ReactionModel $reaction, UserCollection $users)
    {
        $rows = DB::table('post_user_reaction AS pur')
            ->where('pur.reaction_id', '=', $reaction->getKey())
            ->whereIn('pur.user_id', $users->modelKeys())
            ->groupBy('pur.user_id')
            ->select('pur.user_id', DB::raw('COUNT(*) AS total_count_using_this_reaction'))
            ->get()
        ;

        return $rows;
    }

    public static function getEmojiReactionReceivedCountByReactionGroupedByAllUsers(ReactionModel $reaction, UserCollection $users)
    {
        // TODO
    }
}