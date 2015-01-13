<?php

require_once('AI_game_content.php');

define('PLAYER_MAX_LIFE',1000);
define('PLAYER_MAX_ENERGY',1000);

class AI_player extends AI_Game_content
{
	public $id;		// int
	public $life;	// int
	public $energy;	// int
	public $team;	// int

	public $cast_bar;	//Array of Effect
	public $buffs;		// Array of Effect
	public $debuffs;	// Array of Effect
	public $cooldowns;	// Array of effect

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

	public function to_json()
	{
		$json="{
		\"id\"		:$this->id,
		\"life\"	:$this->life,
		\"energy\"	:$this->energy,
		\"team\"	:$this->team,
		\"cast_bar\": [";

		// Cast bar
		foreach($this->cast_bar as $cast)
			$json.=$cast->to_json().',';
		$json=rtrim($json,',');

		// Buffs
		$json.="], \"buffs\": [";
		foreach($this->buffs as $buff)
			$json.=$buff->to_json().',';
		$json=rtrim($json,',');

		// Cooldowns
		$json.="], \"cooldowns\": [";
		foreach($this->cooldowns as $cooldown)
			$json.=$cooldown->to_json().',';
		$json=rtrim($json,',');

		// Cooldowns
		$json.="], \"debuffs\": [";
		foreach($this->debuffs as $debuff)
			$json.=$debuff->to_json().',';
		$json=rtrim($json,',');

		$json.=']}';

		return $json;
	}

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
