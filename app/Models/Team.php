<?php

namespace App\Models;

class Team extends ModelAbstract
{
    public static function importFromSlackResponseBody(array $response_body)
    {
        $team_response = $response_body['team'];
        $slack_team_id = $team_response['id'];

        $team = static::where('slack_team_id', $slack_team_id)->first() ?: new Team();

        $team->slack_team_id = $slack_team_id;
        $team->name          = $team_response['name'];
        $team->domain        = $team_response['domain'];
        $team->email_domain  = $team_response['email_domain'];
        $team->icon          = $team_response['icon']['image_original'];

        $team->save();

        return $team;
    }
}