<?php

class AI_Game_content
{
	static $sort_key;	// String
	static $find_key;	// String

	public function __construct()
	{
	}

	public static function sorter($a,$b)
	{
		$return=strcasecmp($a->{self::$sort_key},$b->{self::$sort_key});
		return ($return==0?TRUE:$return); //equiprobability for random set
	}

	public static function sort_by_property(&$array,$property)
	{
		if(empty($array))
			return;
		self::$sort_key=$property;
		usort($array,array( __CLASS__,'sorter'));
	}

	// TODO Binary search algorithm
	public static function search_by_property(&$array,$property,$value)
	{
		self::$find_key=$property;
		foreach($array as $player)
			if(strcasecmp($player->{self::$find_key},$value)==0)
				return $player;
		return false;
	}

}

?>
