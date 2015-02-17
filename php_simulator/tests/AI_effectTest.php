<?php 

class AI_effectTest extends PHPUnit_Framework_TestCase
{
	public function testContruct()
	{
		$spell = $this->getMockBuilder('AI_spell')->getMock();
		$spell->duration = 5;
		$spell->power = 10;

		$effect = new AI_wars\AI_Effect ($spell);

		// Objects must be NULL
		$this->assertNull($effect->p_target);
		$this->assertNull($effect->p_source);

		// Date should be in local time
		$this->assertEquals(date("H:i:s"),date("H:i:s",$effect->date));

		$this->assertEquals($spell,$effect->spell);

		$this->assertEquals(5,$effect->spell->duration);
		$this->assertEquals(10,$effect->spell->power);
	}

	/*
	 * @depends testCastEffects
	 *
	 */
	public function testDoCast()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->debuff = array();
		$player->cooldowns = array();

		$effect = new AI_wars\AI_Effect($this->getInvalidSpell());
		$effect->p_source = $player;
		$effect->p_target = $player;

		$this->setExpectedException('Exception');
		$effect->doCast();
	}

	public function testCastEffects ()
	{

		$list_spells = array(
			AI_wars\AI_Spell::LASER_SHOT => "offensive",
			AI_wars\AI_Spell::OFFENSIVE_ION_SHOCK => "offensive",
			AI_wars\AI_Spell::EXPLOSIVE_DEVICE => "offensive",
			AI_wars\AI_Spell::OFFENSIVE_BOTS => "offensive",
			AI_wars\AI_Spell::ENERGY_OVERLOAD => "offensive",
			AI_wars\AI_Spell::OFFENSIVE_SYSTEM_VIRUS => "offensive",

			AI_wars\AI_Spell::REPARING => "defensive",
			AI_wars\AI_Spell::DEFENSIVE_ION_SHOCK => "defensive",
			AI_wars\AI_Spell::MOBILE_REPAIR_ROBOT => "defensive",
			AI_wars\AI_Spell::DEFENSIVE_SYSTEM_VIRUS => "defensive",

			AI_wars\AI_Spell::ELECTROMAGNETIC_SHOCK => "neutral",
			AI_wars\AI_Spell::ELECTROMAGNETIC_BLAST => "neutral",
		);

		foreach ($list_spells as $spell_id => $spell_type)
		{
			// For the tests players bars start with 2 effects 
			$effects = array (
				$this->getMockBuilder('AI_Effect')->getMock(),
				$this->getMockBuilder('AI_Effect')->getMock()
			);

			$player_source = $this->getMockBuilder('AI_player')->getMock();
			$player_source->life = 100;
			$player_source->energy = 100;
			$player_source->debuffs = $effects;
			$player_source->buffs = $effects;
			$player_source->cooldowns = $effects;
			$player_source->cast_bar = $effects;
			
			$player_source->cooldowns = $effects;

			$player_target = $this->getMockBuilder('AI_player')->getMock();
			$player_target->life = 100;
			$player_target->energy = 100;
			$player_target->debuffs = $effects;
			$player_target->buffs = $effects;
			$player_target->cooldowns = $effects;
			$player_target->cast_bar = $effects;

			$spell = $this->getMockBuilder('AI_spell')->getMock();
			$spell->id = $spell_id;
			$spell->type = $spell_type;
			$spell->duration = 3;
			$spell->power = 10;
			$spell->cooldown = 3;

			$effect = new AI_wars\AI_Effect($spell);
			$effect->p_source = $player_source;
			$effect->p_target = $player_target;

			$effect->doCast();

			switch ($spell_id)
			{
			case AI_wars\AI_Spell::LASER_SHOT:
				$this->assertEquals(90,$player_target->life);
				break;
			case AI_wars\AI_Spell::REPARING:
				$this->assertEquals(110,$player_target->life);
				break;
			case AI_wars\AI_Spell::ELECTROMAGNETIC_SHOCK:
				$this->assertEmpty($player_target->cast_bar);
				break;
			case AI_wars\AI_Spell::ELECTROMAGNETIC_BLAST:
				$this->assertEmpty($player_target->cast_bar);
				$this->assertArrayHasKey(AI_wars\AI_Spell::ELECTROMAGNETIC_BLAST,$player_target->debuffs);
				$this->assertEquals(3,$player_target->debuffs[AI_wars\AI_Spell::ELECTROMAGNETIC_BLAST]->duration);
				break;
			case AI_wars\AI_Spell::OFFENSIVE_ION_SHOCK:
				$this->assertEquals(1,count($player_target->buffs));
				break;
			case AI_wars\AI_Spell::DEFENSIVE_ION_SHOCK:
				$this->assertEquals(1,count($player_target->debuffs));
				break;
			case AI_wars\AI_Spell::EXPLOSIVE_DEVICE:
				// Turn 1
				$this->assertEquals(90,$player_target->life);
				$this->assertArrayHasKey(AI_wars\AI_Spell::EXPLOSIVE_DEVICE,$player_source->buffs);
				$this->assertEquals($effect->p_target,$player_source); // Become buff to source
				// Turn 2
				$effect->doCast();
				$this->assertEquals(110,$player_source->energy);
				break;
			case AI_wars\AI_Spell::MOBILE_REPAIR_ROBOT:
				// Turn 1
				$this->assertEquals(110,$player_target->life);
				$this->assertArrayHasKey(AI_wars\AI_Spell::MOBILE_REPAIR_ROBOT,$player_source->buffs);
				$this->assertEquals($effect->p_target,$player_source); // Become buff to source
				// Turn 2
				$effect->doCast();
				$this->assertEquals(110,$player_source->energy);
				break;
			case AI_wars\AI_Spell::OFFENSIVE_BOTS:
				$this->assertArrayHasKey(AI_wars\AI_Spell::OFFENSIVE_BOTS,$player_target->debuffs);
				break;
			case AI_wars\AI_Spell::ENERGY_OVERLOAD:
				$this->assertArrayHasKey(AI_wars\AI_Spell::ENERGY_OVERLOAD,$player_target->debuffs);
				break;
			case AI_wars\AI_Spell::OFFENSIVE_SYSTEM_VIRUS:
				$this->assertArrayHasKey(AI_wars\AI_Spell::OFFENSIVE_SYSTEM_VIRUS,$player_target->debuffs);
				break;
			case AI_wars\AI_Spell::DEFENSIVE_SYSTEM_VIRUS:
				$this->assertArrayHasKey(AI_wars\AI_Spell::DEFENSIVE_SYSTEM_VIRUS,$player_target->debuffs);
				break;


			}
		}
	}

	public function testDoBuff()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->debuff = array();
		$player->cooldowns = array();

		$effect = new AI_wars\AI_Effect($this->getInvalidSpell());
		$effect->p_source = $player;
		$effect->p_target = $player;

		$this->setExpectedException('Exception');
		$effect->doBuff();
	}

	public function testDoDebuff()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->debuff = array();
		$player->cooldowns = array();

		$effect = new AI_wars\AI_Effect($this->getInvalidSpell());
		$effect->p_source = $player;
		$effect->p_target = $player;

		$this->setExpectedException('Exception');
		$effect->doDebuff();
	}

	private function getInvalidSpell ()
	{
		$spell = $this->getMockBuilder('AI_spell')->getMock();
		$spell->id = -1;
		$spell->duration = 5;
		$spell->power = 5;
		$spell->cooldown = 5;

		return $spell;
	}

}
