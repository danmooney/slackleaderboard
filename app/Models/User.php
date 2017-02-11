<?php

namespace App\Models;
use App\Collections\User as Collection;

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
        } else {
            $avatar = preg_replace('#(192|original)\.(png|jpe?g)$#', "$size.$2", $this->avatar);
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

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}