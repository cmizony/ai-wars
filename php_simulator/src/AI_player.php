<?php namespace AI_wars;

/**
 * AI_player.php file
 *
 */


/**
 * Maximum life for a player in game
 */
define('PLAYER_MAX_LIFE',1000);

/**
 * Maximum energy for a player in game
 */
define('PLAYER_MAX_ENERGY',1000);

/**
 * Class that represent a player in game
 *
 * @author Camille Mizony
 * @see AI_Effect
 */
class AI_player extends AI_Game_content
{
	
	/** @var int $id Represent player_id in game*/
	public $id;		
	/** @var int $life player life from 0 to PLAYER_MAX_LIFE */
	public $life;
	/** @var int $energy player energy from 0 to PLAYER_MAX_ENERGY */
	public $energy;	
	/** @var int $team Represent player_team in game */
	public $team;

	/** @var object[] $cast_bar An array of AI_Effect */
	public $cast_bar;
	/** @var object[] $buffs An array of AI_Effect */
	public $buffs;	
	/** @var object[] $debuffs An array of AI_Effect */
	public $debuffs;
	/** @var object[] $cooldowns An array of AI_Effect */
	public $cooldowns;

	/**
	 * Constructor, init variables to empty/default values
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->buffs=array();
		$this->debuffs=array();
		$this->cooldowns=array();
		$this->cast_bar=array();
		$this->life=PLAYER_MAX_LIFE;
		$this->energy=PLAYER_MAX_ENERGY;
		$this->id=NULL;
	}

	/**
	 * Convert Player to json format
	 *
	 * @return string json 
	 */
	public function toJson()
	{
		$json="{
		\"id\"		:$this->id,
		\"life\"	:$this->life,
		\"energy\"	:$this->energy,
		\"team\"	:$this->team,
		\"cast_bar\": [";

		// Cast bar
		foreach($this->cast_bar as $cast)
			$json.=$cast->toJson().',';
		$json=rtrim($json,',');

		// Buffs
		$json.="], \"buffs\": [";
		foreach($this->buffs as $buff)
			$json.=$buff->toJson().',';
		$json=rtrim($json,',');

		// Cooldowns
		$json.="], \"cooldowns\": [";
		foreach($this->cooldowns as $cooldown)
			$json.=$cooldown->toJson().',';
		$json=rtrim($json,',');

		// Cooldowns
		$json.="], \"debuffs\": [";
		foreach($this->debuffs as $debuff)
			$json.=$debuff->toJson().',';
		$json=rtrim($json,',');

		$json.=']}';

		return $json;
	}

	/**
	 * Convert Player to string log format
	 *
	 * @return striing Player converted as log format
	 */
	public function __toString()
	{
		$str="P $this->id $this->team $this->life $this->energy ";

		// Buffs
		if (count($this->buffs)>0)
			$str.="B ";
		foreach ($this->buffs as $id=>$effect)
			$str.="($id;$effect->duration) ";

		// Debuffs
		if (count($this->debuffs)>0)
			$str.="D ";
		foreach ($this->debuffs as $id=>$effect)
			$str.="($id;$effect->duration) ";

		// Cast bar
		if (count($this->cast_bar)>0)
			$str.="C ";
		foreach ($this->cast_bar as $id=>$effect)
			$str.="($id;$effect->duration) ";

		// Cooldowns
		if (count($this->cooldowns)>0)
			$str.="CD ";
		foreach ($this->cooldowns as $id=>$effect)
			$str.="($id;$effect->duration) ";

		return rtrim($str);
	}


	/**
	 * Clone Player
	 *
	 * @return void
	 */
	public function __clone()
	{
		$new=array();
		foreach ($this->debuffs as $k => $v)
			$new[$k] = clone $v;
		$this->debuffs=$new;

		$new=array();
		foreach ($this->buffs as $k => $v)
			$new[$k] = clone $v;
		$this->buffs=$new;
		
		$new=array();
		foreach ($this->cooldowns as $k => $v)
			$new[$k] = clone $v;
		$this->cooldowns=$new;
		
		$new=array();
		foreach ($this->cast_bar as $k => $v)
			$new[$k] = clone $v;
		$this->cast_bar=$new;
	}
}

?>
