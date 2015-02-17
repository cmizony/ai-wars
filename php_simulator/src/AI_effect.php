<?php namespace AI_wars;

use Exception;

// Effect is like an event
// TODO split into multiple class
class AI_Effect extends AI_Game_content 
{
	public $spell;		// AI_Spell
	public $date;		// float
	public $duration;	// int
	public $power;		// int
	public $p_target;	// AI_player
	public $p_source;	// AI_player

	public function __construct($spell)
	{
		parent::__construct();
		$this->p_target=NULL;
		$this->p_source=NULL;
		$this->date=time();
		$this->spell=$spell;
		$this->duration=$spell->duration;
		$this->power=$spell->power;
	}

	public function doCast()
	{
		if (is_null($this->p_target))
		{
			_print("Spell cast from player \"".$this->p_source->id."\" failed, target dead");
			return;
		}

		if (isset($this->debuffs[AI_Spell::ELECTROMAGNETIC_BLAST]))
		{
			_print("Unable to cast spells when stun");
			return;
		}

		// Apply cooldown to player source
		if (!isset($this->p_source->cooldowns[$this->spell->id]))
			$this->p_source->cooldowns[$this->spell->id]=new AI_Effect($this->spell);
		$this->p_source->cooldowns[$this->spell->id]->duration+=$this->spell->cooldown;

		switch ($this->spell->id)
		{
		case AI_Spell::LASER_SHOT:
			$this->p_target->life-=$this->evalPower();
			break;

		case AI_Spell::REPARING:
			$this->p_target->life+=$this->evalPower();
			break;

		case AI_Spell::ELECTROMAGNETIC_SHOCK:
			$this->p_target->cast_bar=array();
			break;

		case AI_Spell::ELECTROMAGNETIC_BLAST:
			$this->p_target->cast_bar=array();
			$this->createDebuff();
			break;

		case AI_Spell::OFFENSIVE_ION_SHOCK:
			array_pop($this->p_target->buffs);
			break;

		case AI_Spell::DEFENSIVE_ION_SHOCK:
			array_pop($this->p_target->debuffs);
			break;

		case AI_Spell::EXPLOSIVE_DEVICE:
			if (isset($this->p_source->buffs[AI_Spell::EXPLOSIVE_DEVICE]))
				$this->p_source->energy+=$this->spell->power;

			$this->p_target->life-=$this->evalPower();
			$this->p_target=$this->p_source;
			$this->createBuff();
			break;

		case AI_Spell::MOBILE_REPAIR_ROBOT:
			if (isset($this->p_source->buffs[AI_Spell::MOBILE_REPAIR_ROBOT]))
				$this->p_source->energy+=$this->spell->power;

			$this->p_target->life+=$this->evalTargetPower();
			$this->p_target=$this->p_source;
			$this->createBuff();
			break;

		case AI_Spell::OFFENSIVE_BOTS:
		case AI_Spell::ENERGY_OVERLOAD:
		case AI_Spell::OFFENSIVE_SYSTEM_VIRUS:
		case AI_Spell::DEFENSIVE_SYSTEM_VIRUS:
			$this->createDebuff();
			break;

		case AI_Spell::OFFENSIVE_SYSTEM_UPGRADE:
		case AI_Spell::REPARING_BOTS:
		case AI_Spell::SPARE_PARTS:
		case AI_Spell::BATTERY_RECHARGE:
		case AI_Spell::DEFENSIVE_SYSTEM_UPGRADE:
		case AI_Spell::ENERGETIC_SHIELD:
			$this->p_target=$this->p_source;
			$this->createBuff();
			break;
		default:
			throw new Exception("Cast effect error, Spell ".$this->spell->id." is not available in the current version");
		}
	}

	private function createDebuff()
	{
		if (!isset($this->p_target->debuffs[$this->spell->id]))
		{
			$effect=new AI_Effect($this->spell);
			$effect->p_target=$this->p_target;
			$this->p_target->debuffs[$this->spell->id]=$effect;
		}
		$this->p_target->debuffs[$this->spell->id]->duration=$this->evalDuration();
	}

	private function createBuff()
	{
		if (!isset($this->p_target->buffs[$this->spell->id]))
		{
			$effect=new AI_Effect($this->spell);
			$effect->p_target=$this->p_target;
			$this->p_target->buffs[$this->spell->id]=$effect;
		}
		$this->p_target->buffs[$this->spell->id]->duration=$this->evalDuration();
		$this->power=$this->evalTargetPower();
	}

	public function doDebuff()
	{
		switch($this->spell->id)
		{
		case AI_Spell::OFFENSIVE_BOTS:
			$this->p_target->life-=$this->evalTargetPower();
			break;
		case AI_Spell::ENERGY_OVERLOAD:
			if ($this->duration === 1)
				$this->p_target->life-=$this->evalTargetPower();
			break;
		case AI_Spell::OFFENSIVE_SYSTEM_VIRUS:
		case AI_Spell::DEFENSIVE_SYSTEM_VIRUS:
			break;
		default:
			throw new Exception("Debuff effect error, Spell ".$this->spell->id." is implement in the current version");
		}
	}

	public function doBuff()
	{
		switch($this->spell->id)
		{
		case AI_Spell::REPARING_BOTS:
			$this->p_target->life+=$this->evalTargetPower()/(float)$this->spell->duration;
			break;
		case AI_Spell::BATTERY_RECHARGE:
			$this->p_target->energy+=$this->power;
			break;

		case AI_Spell::SPARE_PARTS:
			if ($this->duration === 1)
				$this->p_target->life-=$this->evalTargetPower();
			break;
		case AI_Spell::OFFENSIVE_SYSTEM_UPGRADE:
		case AI_Spell::DEFENSIVE_SYSTEM_UPGRADE:
		case AI_Spell::ENERGETIC_SHIELD:
			break;
		default:
			throw new Exception("Buff effect error, Spell ".$this->spell->id." is implement in the current version");
		}
	}

	public function toJson()
	{
		return "{
			\"spell_id\":".$this->spell->id.",
			\"duration\":$this->duration}";
	}

	public function __toString()
	{
		return "C ".$this->p_source->id.' '. $this->spell->id.' '.$this->p_target->id;
	}

	private function evalPower ()
	{
		return $this->evalTargetPower($this->evalSourcePower());
	}

	private function evalSourcePower()
	{
		switch ($this->spell->type)
		{
		case 'offensive':
			if (isset($this->p_source->buffs[AI_Spell::ENERGETIC_SHIELD]))
				return 0;

			if (isset($this->p_source->buffs[AI_Spell::OFFENSIVE_SYSTEM_UPGRADE]))
			{
				$spell = $this->p_source->buffs[AI_Spell::OFFENSIVE_SYSTEM_UPGRADE];
				return $this->power*($spell->power/(float)100); 
			}
			break;
		case 'defensive':
			if (isset($this->p_source->buffs[AI_Spell::DEFENSIVE_SYSTEM_VIRUS]))
			{
				$spell = $this->p_source->buffs[AI_Spell::DEFENSIVE_SYSTEM_VIRUS];
				return $this->power-$this->power*($spell->power/(float)100); 
			}
			break;
		}
		return $this->power;
	}

	private function evalTargetPower()
	{
		switch ($this->spell->type)
		{
		case 'offensive':
			if (isset($this->p_source->buffs[AI_Spell::OFFENSIVE_SYSTEM_VIRUS]))
			{
				$spell = $this->p_source->buffs[AI_Spell::OFFENSIVE_SYSTEM_VIRUS];
				return $this->power*($spell->power/(float)100); 
			}
			break;
		case 'defensive':
			if (isset($this->p_source->buffs[AI_Spell::DEFENSIVE_SYSTEM_UPGRADE]))
			{
				$spell = $this->p_source->buffs[AI_Spell::DEFENSIVE_SYSTEM_UPGRADE];
				return $this->power-$this->power*($spell->power/(float)100); 
			}
			break;
		}

		return $this->power;
	}

	private function evalDuration()
	{
		return $this->spell->duration;
	}
}

?>
