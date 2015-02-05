<?php 

require_once 'php_simulator/application/AI_game_content.php';

class AI_game_contentTest extends PHPUnit_Framework_TestCase
{
	public function testSorter ()
	{
		$game_content = new AI_Game_content();
		AI_Game_content::$sort_key = "value";

		$element_a = (object) array ("value" => 2);
		$element_b = (object) array ("value" => 5);
		$element_c = (object) array ("value" => 0);

		$this->assertEquals(TRUE,$game_content->sorter($element_a,$element_a));
		$this->assertEquals(-3,$game_content->sorter($element_a,$element_b));
		$this->assertEquals(2,$game_content->sorter($element_a,$element_c));
	}

	public function testSort_by_property ()
	{
		$game_content = new AI_Game_content();

		$input = array (
			(object) array ("value" => 5),
			(object) array ("value" => 10),
			(object) array ("value" => 0)
		);

		$output = $game_content->sort_by_property($input,"value");

		$previous_object = $output[0];
		$is_sorted = TRUE;

		for($i = 1 ; $i < count($output) ; $i ++)
		{
			if ($output[$i]->value > $previous_object->value)
				$is_sorted = FALSE;
			$previous_object = $output[$i];
		}

		$this->assertTrue($is_sorted);
	}

	public function testSearch_by_property ()
	{
		$game_content = new AI_Game_content();

		$input = array (
			(object) array ("value" => 5),
			(object) array ("value" => 10),
			(object) array ("value" => 0)
		);

		$this->assertEquals($input[1], $game_content->search_by_property($input,"value",10));
		$this->assertEquals(FALSE, $game_content->search_by_property($input,"value",100));
	}
}
