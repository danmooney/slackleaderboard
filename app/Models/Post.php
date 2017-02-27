<?php

namespace App\Models;
use App;

class Post extends ModelAbstract
{
    const DEMO_BASENAMES_ARR = [
        'cool_file.png',
        'pasted_image_at_2016_11_09_11_55_pm.png',
        'funny_cat.gif',
        'recursion.csv',
        'casual_photoshop.jpg'
    ];

    const DEMO_ALLOWABLE_CHANNEL_NAMES = [
        'general',
        'random',
        'lookwhatidid',
        'creativecaffeine',
        'developers',
        'no-context',
        'bizdev'
    ];

    public function getUrlAttribute($value)
    {
        static $demo_users;

        $url = $value;

        if (!App::getDemoMode()) {
            return $url;
        }

        $demo_slack_permalink_host = sprintf('%s.slack.com', Team::DEMO_TEAM_DOMAIN_FACADE);

        $url_with_host_replaced = preg_replace('#https://(.+)\.slack\.com#', "https://$demo_slack_permalink_host", $url);
        $url = $url_with_host_replaced;

        // determine if file upload or message
        preg_match('#/(archives|files)/([^/]+)/#', $url, $matches);

        if (count($matches)) {
            list($post_type, $subject) = array_slice($matches, 1);

            switch ($post_type) {
                case 'files': // subject is a channel name
                    $user_handle = $subject;

                    // get demo users to figure out how to replace the URL sensibly
                    if (!isset($demo_users)) {
                        $demo_users = User::where('team_id', '=', Team::DEMO_TEAM_ID)->get([
                            'user_id',
                            'name',
                            'handle',
                            'slack_user_id',
                        ]);
                    }

                    $demo_user_with_matching_handle = $demo_users->filter(function ($demo_user) use ($user_handle) {
                        return $demo_user->getOriginal('handle') === $user_handle;
                    })->first();

                    // remove numeral hyphenated suffix from handle
                    $demo_user_url_handle = preg_replace('#\-\d+$#', '', $demo_user_with_matching_handle->handle);

                    $url = preg_replace('#\.slack\.com/files/([^/]+)/#', ".slack.com/files/$demo_user_url_handle/", $url);

                    // replace file basename (in case puts sensitive info in filename)
                    $random_demo_basename = static::DEMO_BASENAMES_ARR[
                        mt_rand(0, count(static::DEMO_BASENAMES_ARR) - 1)
                    ];

                    $url = str_replace(basename($url), $random_demo_basename, $url);

                    break;
                case 'archives': // subject is a user handle
                default:
                    $channel_name = $subject;

                    if (!in_array($channel_name, static::DEMO_ALLOWABLE_CHANNEL_NAMES)) {
                        $channel_name_replacement = static::DEMO_ALLOWABLE_CHANNEL_NAMES[
                            crc32($channel_name) % count(static::DEMO_ALLOWABLE_CHANNEL_NAMES)
                        ];
                        $url = preg_replace('#\.slack\.com/archives/([^/]+)/#', ".slack.com/archives/$channel_name_replacement/", $url);
                    }

                    break;
            }
        }

        return $url;
    }
}