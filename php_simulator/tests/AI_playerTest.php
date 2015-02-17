<?php 

class AI_playerTest extends PHPUnit_Framework_TestCase
{
	public function testConstruct ()
	{
		$player = new AI_wars\AI_player ();

		$this->assertEmpty($player->id);
		$this->assertEmpty($player->team);
		$this->assertEmpty($player->cast_bar);
		$this->assertEmpty($player->buffs);
		$this->assertEmpty($player->debuffs);
		$this->assertEmpty($player->cooldowns);

		$this->assertEquals(PLAYER_MAX_LIFE,$player->life);
		$this->assertEquals(PLAYER_MAX_ENERGY,$player->energy);
	}

	public function testToJson ()
	{
		$mock_json = array("duration" => 5, "power" => 10);
		$mock_effects_json = array($mock_json,$mock_json);

		$effect = $this->getMockBuilder('AI_effect')
			->setMethods(array('toJson'))
			->getMock();

		$effect->expects($this->any())
			->method('toJson')
			->will($this->returnValue(json_encode($mock_json)));

		$player = new AI_wars\AI_player ();
		$player->id = 1;
		$player->team = 2;
		$player->energy = 100;
		$player->life = 50;
		$player->cast_bar = array($effect,$effect);
		$player->buffs = array($effect,$effect);
		$player->debuffs = array($effect,$effect);
		$player->cooldowns = array($effect,$effect);

		$json_result = json_decode($player->toJson(),TRUE);

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

	public function testToString ()
	{
		$effect = $this->getMockBuilder('AI_effect')->getMock();
		$effect->duration = 5;

		$player = new AI_wars\AI_player ();
		$player->id = 1;
		$player->team = 2;
		$player->energy = 100;
		$player->life = 50;
		$player->cast_bar = array($effect,$effect);
		$player->buffs = array($effect,$effect);
		$player->debuffs = array($effect,$effect);
		$player->cooldowns = array($effect,$effect);

		$string_result = (string)$player;
		$expected_string = "P 1 2 50 100 B (0;5) (1;5) D (0;5) (1;5) C (0;5) (1;5) CD (0;5) (1;5)";
		$this->assertEquals($expected_string,$string_result);
	}

	public function testClone ()
	{
		$effect = $this->getMockBuilder('AI_effect')->getMock();
		$effect->duration = 5;

		$player_model = new AI_wars\AI_player();
		$player_model->id = 1;
		$player_model->team = 2;
		$player_model->energy = 100;
		$player_model->life = 50;
		$player_model->cast_bar = array($effect,$effect);
		$player_model->buffs = array($effect,$effect);
		$player_model->debuffs = array($effect,$effect);
		$player_model->cooldowns = array($effect,$effect);

		$player_cloned = clone $player_model;

		$this->assertEquals(1,$player_cloned->id);
		$this->assertEquals(2,$player_cloned->team);
		$this->assertEquals(100,$player_cloned->energy);
		$this->assertEquals(50,$player_cloned->life);

		$player_model->cast_bar = NULL;
		$player_model->buffs = NULL;
		$player_model->debuffs = NULL;
		$player_model->cooldowns = NULL;

		$this->assertEquals(array($effect,$effect),$player_cloned->debuffs);
		$this->assertEquals(array($effect,$effect),$player_cloned->buffs);
		$this->assertEquals(array($effect,$effect),$player_cloned->debuffs);
		$this->assertEquals(array($effect,$effect),$player_cloned->cooldowns);
	}
}
