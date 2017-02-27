<?php

namespace App\Models;
use App;

class Team extends ModelAbstract
{
    const TEAM_DOMAIN_KEY = 'team_domain';
    const DEMO_TEAM_DOMAIN = 'digitalsurgeons';
    const DEMO_TEAM_DOMAIN_FACADE = 'demo-team';
    const DEMO_TEAM_NAME_FACADE = 'Demo Team';
    const DEMO_TEAM_ID = 2;

    public static function importFromSlackResponseBody(array $response_body)
    {
        $team_response = $response_body['team'];
        $slack_team_id = $team_response['id'];

        $team = static::where('slack_team_id', $slack_team_id)->first() ?: new Team();

        $team->slack_team_id = $slack_team_id;
        $team->name          = $team_response['name'];
        $team->domain        = $team_response['domain'];
        $team->email_domain  = $team_response['email_domain'];
        $team->icon          = isset($team_response['icon']['image_original']) ? $team_response['icon']['image_original'] : $team_response['icon']['image_132'];

        $team->save();

        return $team;
    }

    public function getIconAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        return '/img/logo-team-demo.png';
    }

    public function getDomainAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        return Team::DEMO_TEAM_DOMAIN_FACADE;
    }

    public function getNameAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        return static::DEMO_TEAM_NAME_FACADE;
    }
}