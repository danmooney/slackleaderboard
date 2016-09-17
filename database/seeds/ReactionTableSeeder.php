<?php

use Illuminate\Database\Seeder;
use App\Models\Reaction;

class ReactionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$all_reactions = Reaction::where(['team_id' => null])->get();
    	$emojis = json_decode(file_get_contents(dirname(__FILE__) . '/emojis.json'));

		$reactions_added = [];

		foreach ($emojis as $alias => $image_url) {
			preg_match('#^:(.+?):#', $alias, $matches);

			$alias_sans_colons = $matches[1];

			if (in_array($alias_sans_colons, $reactions_added)) {
				continue;
			}

			$reaction 			 = $all_reactions->where('alias', $alias_sans_colons)->first() ?: new Reaction();
			$reaction->team_id   = null;
			$reaction->alias   	 = $alias_sans_colons;
			$reaction->image   	 = $image_url;
			$reaction->is_custom = false;

			$reaction->save();

			$reactions_added[] = $alias_sans_colons;
		}
    }
}
