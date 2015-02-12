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
}
