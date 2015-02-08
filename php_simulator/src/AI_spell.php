<?php

require_once('AI_game_content.php');

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

	public function valid_version()
	{
		switch ($this->id)
		{
		case LASER_SHOT:
			if($this->effect != '%power damage' OR
				$this->codename != 'LASER_SHOT')
				return FALSE;
			break;
		case OFFENSIVE_BOTS:
			if($this->effect != '%power damage each turn for %duration turns' OR
				$this->codename != 'OFFENSIVE_BOTS')
				return FALSE;
			break;
		case ENERGY_OVERLOAD;
			if($this->effect != '%power damage in %cast turns' OR
				$this->codename != 'ENERGY_OVERLOAD')
				return FALSE;
			break;
		case EXPLOSIVE_DEVICE;
			if($this->effect != '%power damage, and for %duration turns provide %power extra energy when you use this spell again' OR
				$this->codename != 'EXPLOSIVE_DEVICE')
				return FALSE;
			break;
		case OFFENSIVE_SYSTEM_UPGRADE;
			if($this->effect != 'increase damage delivered by %power percent for %duration turns' OR
				$this->codename != 'OFFENSIVE_SYSTEM_UPGRADE')
				return FALSE;
			break;
		case OFFENSIVE_SYSTEM_VIRUS;
			if($this->effect != 'increase damage received by %power percent for %duration turns' OR
				$this->codename != 'OFFENSIVE_SYSTEM_VIRUS')
				return FALSE;
			break;
		case REPARING;
			if($this->effect != '%power health' OR
				$this->codename != 'REPARING')
				return FALSE;
			break;
		case REPARING_BOTS;
			if($this->effect != '%power health each turn for %duration turns' OR
				$this->codename != 'REPARING_BOTS')
				return FALSE;
			break;
		case SPARE_PARTS;
			if($this->effect != '%power health in %cast turns' OR
				$this->codename != 'SPARE_PARTS')
				return FALSE;
			break;
		case MOBILE_REPAIR_ROBOT;
			if($this->effect != '%power health and for %duration turns provide %power extra energy when you use this spell again' OR
				$this->codename != 'MOBILE_REPAIR_ROBOT')
				return FALSE;
			break;
		case DEFENSIVE_SYSTEM_VIRUS;
			if($this->effect != 'reduce damage delivered by %power % for %duration turns' OR
				$this->codename != 'DEFENSIVE_SYSTEM_VIRUS')
				return FALSE;
			break;
		case DEFENSIVE_SYSTEM_UPGRADE;
			if($this->effect != 'reduce damage received by %power % for %duration turns' OR
				$this->codename != 'DEFENSIVE_SYSTEM_UPGRADE')
				return FALSE;
			break;
		case ELECTROMAGNETIC_SHOCK;
			if($this->effect != 'interrupt all casts from the target' OR
				$this->codename != 'ELECTROMAGNETIC_SHOCK')
				return FALSE;
			break;
		case ELECTROMAGNETIC_BLAST;
			if($this->effect != 'stun the target for %duration turns' OR
				$this->codename != 'ELECTROMAGNETIC_BLAST')
				return FALSE;
			break;
		case OFFENSIVE_ION_SHOCK;
			if($this->effect != 'remove one buff from the target' OR
				$this->codename != 'OFFENSIVE_ION_SHOCK')
				return FALSE;
			break;
		case BATTERY_RECHARGE;
			if($this->effect != 'regain %power energy per turn for %duration turns' OR
				$this->codename != 'BATTERY_RECHARGE')
				return FALSE;
			break;
		case ENERGETIC_SHIELD;
			if($this->effect != 'block all damage for %duration turns' OR
				$this->codename != 'ENERGETIC_SHIELD')
				return FALSE;
			break;
		case DEFENSIVE_ION_SHOCK;
			if($this->effect != 'remove one debuff from the target' OR
				$this->codename != 'DEFENSIVE_ION_SHOCK')
				return FALSE;
			break;
		default:
			return FALSE;
		}
		return TRUE;
	}

	public function is_valid($player)
	{
		// Test version
		if(!$this->valid_version())
		{
			_print("Error, version spell effect \"$this->codename\" (id $this->id) is not yet implemented well in the simulator\n");
			return FALSE;
		}

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
