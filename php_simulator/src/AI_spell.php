<?php
namespace AI_wars;

define('LASER_SHOT'					,100);
define('OFFENSIVE_BOTS'				,101);
define('ENERGY_OVERLOAD'			,102);
define('EXPLOSIVE_DEVICE'			,103);
define('OFFENSIVE_SYSTEM_UPGRADE'	,104);
define('OFFENSIVE_SYSTEM_VIRUS'		,105);

define('REPARING'					,200);
define('REPARING_BOTS'				,201);
define('SPARE_PARTS'				,202);
define('MOBILE_REPAIR_ROBOT'		,203);
define('DEFENSIVE_SYSTEM_UPGRADE'	,204);
define('DEFENSIVE_SYSTEM_VIRUS'		,205);

define('ELECTROMAGNETIC_SHOCK'		,300);
define('ELECTROMAGNETIC_BLAST'		,301);
define('OFFENSIVE_ION_SHOCK'		,302);
define('BATTERY_RECHARGE'			,303);
define('ENERGETIC_SHIELD'			,304);
define('DEFENSIVE_ION_SHOCK'		,305);

class AI_Spell extends AI_Game_content
{
	public $id;				// int
	public $codename;		// String	
	public $energy;			// int 
	public $cooldown;		// int
	public $cast;			// int
	public $power;			// int
	public $duration;		// int
	private $type;			// String
	private $effect;		// String


	public function __construct()
	{
		parent::__construct();
		$this->id			=0;
		$this->name			='';
		$this->energy		=0;
		$this->cooldown		=0;
		$this->cast			=0;
		$this->power		=0;
		$this->duration		=0;
		$this->type			='';
		$this->effect		='';
	}

	public function __set($codename,$value)
	{
		$this->$codename=$value;
	}

	private function _replace_gamecode($string)
	{
		$patterns=array(
			'/\%power/',
			'/\%duration/',
			'/\%energy/',
			'/\%cast/',
			'/\%cooldown/');

		$replacements=array(
			$this->power,
			$this->duration,
			$this->energy,
			$this->cast,
			$this->cooldown);

		return preg_replace($patterns,$replacements,$string);
	}

	public function __toString()
	{
		return "($this->id;$this->duration)";
	}

	public function get_property($property)
	{
		switch ($property)
		{
		case 'effect':
			return $this->_replace_gamecode($this->$property);
		default:
			return $this->$property;
		}
	}

	public function __get($property) 
	{
		return $this->get_property($property);
	}

	public function to_json()
	{
		$json="
			{
			\"id\"	:			$this->id,
			\"codename\":		\"$this->codename\",
			\"energy\":			$this->energy,
			\"cooldown\":		$this->cooldown,
			\"cast\":			$this->cast,
			\"power\":			$this->power,
			\"duration\":		$this->duration,
			\"type\":			\"$this->type\",
			\"effect\":			\"".$this->get_property('effect')."\"
			}";

		return $json;
	}

	public function is_valid($player)
	{
		// Test energy
		if ($player->energy < $this->energy)
		{
			_print("Player \"$player\" as not enough energy to cast spell $this->id");
			return FALSE;
		}

		// Test cooldown
		if (isset($player->cooldowns[$this->id]) AND
			$player->cooldowns[$this->id]->duration > 0)
		{
			_print("Player \"$player\" cooldown spell $this->id not ready");
			return FALSE;
		}

		return $this;
	}

}
?>
