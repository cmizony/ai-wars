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
	public function testDo_cast()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->debuff = array();
		$player->cooldowns = array();

		$effect = new AI_wars\AI_Effect($this->getInvalidSpell());
		$effect->p_source = $player;
		$effect->p_target = $player;

		$this->setExpectedException('Exception');
		$effect->do_cast();
	}

	public function testCastEffects ()
	{

		$list_spells = array(
			AI_wars\AI_Spell::LASER_SHOT => "offensive",
			AI_wars\AI_Spell::REPARING => "defensive",
			AI_wars\AI_Spell::ELECTROMAGNETIC_SHOCK => "neutral",
		);

		foreach ($list_spells as $spell_id => $spell_type)
		{
			$player_source = $this->getMockBuilder('AI_player')->getMock();
			$player_source->debuff = array();
			$player_source->cooldowns = array();

			$player_target = $this->getMockBuilder('AI_player')->getMock();
			$player_target->life =100;
			$player_target->debuff = array();
			$player_target->cooldowns = array();
			$player_target->cast_bar = array(
				$this->getMockBuilder('AI_Effect')->getMock()
			);

			$spell = $this->getMockBuilder('AI_spell')->getMock();
			$spell->id = $spell_id;
			$spell->type = $spell_type;
			$spell->duration = 3;
			$spell->power = 10;
			$spell->cooldown = 3;

			$effect = new AI_wars\AI_Effect($spell);
			$effect->p_source = $player_source;
			$effect->p_target = $player_target;

			$effect->do_cast();

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


			}
		}
	}

	public function testDo_buff()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->debuff = array();
		$player->cooldowns = array();

		$effect = new AI_wars\AI_Effect($this->getInvalidSpell());
		$effect->p_source = $player;
		$effect->p_target = $player;

		$this->setExpectedException('Exception');
		$effect->do_buff();
	}

	public function testDo_debuff()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->debuff = array();
		$player->cooldowns = array();

		$effect = new AI_wars\AI_Effect($this->getInvalidSpell());
		$effect->p_source = $player;
		$effect->p_target = $player;

		$this->setExpectedException('Exception');
		$effect->do_debuff();
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
