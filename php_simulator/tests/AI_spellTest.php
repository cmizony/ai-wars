<?php 

class AI_spellTest extends PHPUnit_Framework_TestCase
{
	public function testContruct()
	{
		$spell = new AI_wars\AI_Spell ();

		// Strings
		$this->assertEmpty($spell->codename);
		$this->assertEmpty($spell->type);
		$this->assertEmpty($spell->effect);

		// Integers
		$this->assertEquals(0,$spell->id);
		$this->assertEquals(0,$spell->power);
		$this->assertEquals(0,$spell->duration);
		$this->assertEquals(0,$spell->energy);
		$this->assertEquals(0,$spell->cooldown);
		$this->assertEquals(0,$spell->cast);
	}

	public function testGetProperty ()
	{
		$spell = new AI_wars\AI_Spell ();
		$spell->power = 2;
		$spell->effect = '%power p';

		$this->assertEquals (2,$spell->getProperty('power'));
		$this->assertEquals ('2 p',$spell->getProperty('effect'));
	}

	/*
	 * @depends testGetProperty
	 *
	 */
	public function testToJson ()
	{
		$spell = new AI_wars\AI_Spell ();
		$spell->id = 1;
		$spell->power = 2;
		$spell->duration = 3;
		$spell->energy = 4;
		$spell->cooldown = 5;
		$spell->cast = 6;

		$spell->codename = 'codename';
		$spell->type = 'type';
		$spell->effect = '%power p %duration d %energy e %cast c %cooldown c';

		$json_result = json_decode($spell->toJson(),TRUE);

		$this->assertEquals(1,$json_result['id']);
		$this->assertEquals(2,$json_result['power']);
		$this->assertEquals(3,$json_result['duration']);
		$this->assertEquals(4,$json_result['energy']);
		$this->assertEquals(5,$json_result['cooldown']);
		$this->assertEquals('codename',$json_result['codename']);
		$this->assertEquals('type',$json_result['type']);
		$this->assertEquals('2 p 3 d 4 e 6 c 5 c',$json_result['effect']);
	}

	public function testIsValid ()
	{
		$player = $this->getMockBuilder('AI_player')->getMock();
		$player->energy = 5;

		$spell = new AI_wars\AI_Spell ();
		$spell->energy = 5;

		$this->assertEquals ($spell,$spell->isValid($player));
	}
}
