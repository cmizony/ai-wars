<?php 

require_once 'php_simulator/application/AI_player.php';

class AI_playerTest extends PHPUnit_Framework_TestCase
{
	public function testConstruct ()
	{
		$player = new AI_player ();

		$this->assertEmpty($player->id);
		$this->assertEmpty($player->team);
		$this->assertEmpty($player->cast_bar);
		$this->assertEmpty($player->buffs);
		$this->assertEmpty($player->debuffs);
		$this->assertEmpty($player->cooldowns);

		$this->assertEquals(PLAYER_MAX_LIFE,$player->life);
		$this->assertEquals(PLAYER_MAX_ENERGY,$player->energy);
	}

	public function testTo_json ()
	{
		$mock_json = array("duration" => 5, "power" => 10);
		$mock_effects_json = array($mock_json,$mock_json);

		$effect = $this->getMockBuilder('AI_effect')
			->setMethods(array('to_json'))
			->getMock();

		$effect->expects($this->any())
			->method('to_json')
			->will($this->returnValue(json_encode($mock_json)));

		$player = new AI_player ();
		$player->id = 1;
		$player->team = 2;
		$player->energy = 100;
		$player->life = 50;
		$player->cast_bar = array($effect,$effect);
		$player->buffs = array($effect,$effect);
		$player->debuffs = array($effect,$effect);
		$player->cooldowns = array($effect,$effect);

		$json_result = json_decode($player->to_json(),TRUE);

		$this->assertTrue((boolean) $json_result);
		$this->assertEquals(1,$json_result['id']);
		$this->assertEquals(2,$json_result['team']);
		$this->assertEquals(100,$json_result['energy']);
		$this->assertEquals(50,$json_result['life']);

		$this->assertEquals($mock_effects_json,$json_result['cast_bar']);
		$this->assertEquals($mock_effects_json,$json_result['buffs']);
		$this->assertEquals($mock_effects_json,$json_result['debuffs']);
		$this->assertEquals($mock_effects_json,$json_result['cooldowns']);
	}
}
