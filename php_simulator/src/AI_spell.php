<?php namespace AI_wars;

class AI_Spell extends AI_Game_content
{
	const LASER_SHOT =					100;
	const OFFENSIVE_BOTS =				101;
	const ENERGY_OVERLOAD =				102;
	const EXPLOSIVE_DEVICE =			103;
	const OFFENSIVE_SYSTEM_UPGRADE =	104;
	const OFFENSIVE_SYSTEM_VIRUS =		105;

	const REPARING =					200;
	const REPARING_BOTS =				201;
	const SPARE_PARTS =					202;
	const MOBILE_REPAIR_ROBOT =			203;
	const DEFENSIVE_SYSTEM_UPGRADE =	204;
	const DEFENSIVE_SYSTEM_VIRUS =		205;

	const ELECTROMAGNETIC_SHOCK =		300;
	const ELECTROMAGNETIC_BLAST =		301;
	const OFFENSIVE_ION_SHOCK =			302;
	const BATTERY_RECHARGE =			303;
	const ENERGETIC_SHIELD =			304;
	const DEFENSIVE_ION_SHOCK =			305;

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
		$this->codename		='';
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

	private function _replaceGamecode($string)
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

	public function getProperty($property)
	{
		switch ($property)
		{
		case 'effect':
			return $this->_replaceGamecode($this->$property);
		default:
			return $this->$property;
		}
	}

	public function __get($property) 
	{
		return $this->getProperty($property);
	}

	public function toJson()
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
			\"effect\":			\"".$this->getProperty('effect')."\"
			}";

		return $json;
	}

	public function isValid($player)
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
