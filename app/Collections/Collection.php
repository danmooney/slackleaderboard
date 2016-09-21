<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection as LaravelCollection;

class Collection extends LaravelCollection
{
	/**
	 * @param $relationship_name
	 * @param $column
	 * @param $value
	 * @return static
	 */
	public function whereInRelationship($relationship_name, $column, $value, $store_in_cache = false, $fetch_from_cache_if_available = false)
	{
		static $cache = [];

		$cache_key = $relationship_name . '--' . $column . '--' . $value;

		if ($fetch_from_cache_if_available && isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		$items = $this->filter(function ($item) use ($relationship_name, $column, $value) {
			return $item->$relationship_name->where($column, $value)->count() > 0;
		});

		if ($store_in_cache) {
			$cache[$cache_key] = $items;
		}

		return $items;
	}

	public function generateFlatArrayByKey()
	{
		$flat_items = [];
		foreach ($this->items as $item) {
			$flat_items[$item->getKey()] = $item;
		}

		return $flat_items;
	}
}