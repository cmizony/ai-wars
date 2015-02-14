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
