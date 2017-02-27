<?php

namespace App\Models;
use App\Collections\User as Collection;
use App;

class User extends ModelAbstract
{
    const SESSION_KEY = 'user';
    const COLUMN_SESSION_SAVE_TIMESTAMP_KEY = 'session_save_timestamp';
    const REFRESH_SESSION_THRESHOLD_SECONDS = 60 * 10; // 10 minutes
    const AVATAR_SIZES = [24, 32, 48, 72, 192, 512, 1024, 'original'];
    const DEFAULT_AVATAR_SIZE = 32;

    protected static $unguarded = true;

    public static function getFromSession($return_empty_user_object_if_not_exists = true)
    {
        $user = session()->get(static::SESSION_KEY);

        if (!$user && $return_empty_user_object_if_not_exists) {
            $user = new User();
        }

        return $user;
    }

    public static function getDemoUser()
    {
        static $demo_user;

        if (!$demo_user) {
            $demo_user = User::find(config('app.demo.demo_user_id'));
        }

        return $demo_user;
    }

    public function isEligibleToBeOnLeaderBoard()
    {
        return (
            trim($this->name) &&
            !$this->slack_deleted &&
            !$this->slack_restricted &&
            !$this->slack_ultra_restricted &&
            !$this->slack_bot &&
            $this->name !== 'slackbot'
        );
    }

    public function getAvatar($size = self::DEFAULT_AVATAR_SIZE)
    {
        $is_gravatar = stripos(parse_url($this->avatar, PHP_URL_HOST), 'gravatar') !== false;

        if ($is_gravatar) {
            $avatar = preg_replace('#\?s=\d+#', "?s=$size", $this->avatar);
        } elseif (!App::getDemoMode()) {
            $avatar = preg_replace('#(192|original)\.(png|jpe?g)$#', "$size.$2", $this->avatar);
        } else {
            $avatar = $this->avatar;
        }

        return $avatar;
    }

    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    public function needsToRefreshSession()
    {
        return (
            $this->exists &&
            $this->{static::COLUMN_SESSION_SAVE_TIMESTAMP_KEY} + static::REFRESH_SESSION_THRESHOLD_SECONDS < time()
        );
    }

    public function refreshSession()
    {
        $refreshed_user = $this->fresh();

        if (!$refreshed_user) {
            // TODO - exception
            // flush the session.. maybe the team/user got removed from the DB; TLDR something weird and unexpected happened
            session()->flush();
            return false;
        }

        $this->fill($refreshed_user->getAttributes());
        $this->setNewSessionSaveTimestamp();

        $this->saveIntoSession();

        return true;
    }

    public function setNewSessionSaveTimestamp()
    {
        $this->{static::COLUMN_SESSION_SAVE_TIMESTAMP_KEY} = time();
    }

    public function saveIntoSession($set_session_save_timestamp = true)
    {
        if ($set_session_save_timestamp) {
            $this->setNewSessionSaveTimestamp();
        }

        session()->put(static::SESSION_KEY, $this);
    }

    public function isLoggedIn()
    {
        $user = static::getFromSession(false);
        return $user && $this->isSameAs($user);
    }

    public function isOnTeam(Team $team)
    {
        return $this->team_id === $team->getKey();
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getAvatarAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        $slack_default_avatar_urls = config('app.demo.slack_default_avatar_urls');
        return $slack_default_avatar_urls[$this->getKey() % count($slack_default_avatar_urls)];
    }

    public function getNameAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        if ($this->getKey() === config('app.demo.demo_user_id')) {
            return "Leaderboard Lover";
        }

        $first_names = config('app.demo.first_names');
        $last_names  = config('app.demo.first_names');

        $reassigned_first_name = $first_names[crc32($this->getKey()) % count($first_names)];
        $reassigned_last_name  = $last_names[crc32($this->slack_user_id) % count($last_names)];

        return "$reassigned_first_name $reassigned_last_name";

    }

    public function getNameBinaryAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        return $this->getNameAttribute($value);
    }

    public function getHandleAttribute($value)
    {
        if (!App::getDemoMode()) {
            return $value;
        }

        return strtolower(str_replace(' ', '.', $this->name)). '-' . $this->getKey();
    }
}