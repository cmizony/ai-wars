<?php namespace AI_wars;

use Exception;
use DOMDocument;

define ('SPELL_XML_FILE','spell.xml');
define ('SPELL_XSD_FILE','spell.xsd');

class AI_Spells_validator
{
	private $spells;	// Array of Spell

	public function initialize($config)
	{
		$this->spells=array();
		$this->_loadXml($config);
	}

	private function _loadXml($config)
	{
		$resources_folder=$config['resources_folder'];

		if (!file_exists($resources_folder.SPELL_XML_FILE))
			throw new Exception("Error, spells xml file \"$resources_folder".SPELL_XML_FILE."\" doesn't exist");

		$dom=new DOMDocument();
		if (!$dom->load($resources_folder.SPELL_XML_FILE))
			throw new Exception('Error, unable to load xml file : "'.$resources_folder.SPELL_XML_FILE."\"");
		if (!$dom->schemaValidate($resources_folder.SPELL_XSD_FILE))
		{
			var_dump(libxml_get_errors());
			throw new Exception('Error, "'.$resources_folder.SPELL_XML_FILE.'" doesn\'t validate by "'.$resources_folder.SPELL_XSD_FILE."\"");
		}

		$root=$dom->getElementsByTagName('spells');
		$xml_spells =simplexml_import_dom($root->item(0));

		foreach($xml_spells as $xml_spell)
		{
			$spell=new AI_Spell();

			$spell->id=			(int)	$xml_spell->id;
			$spell->codename=	(string)$xml_spell->codename;
			$spell->energy=		(int)	$xml_spell->energy;
			$spell->cooldown=	(int)	$xml_spell->cooldown;
			$spell->type=		(string)$xml_spell->type;
			$spell->effect=		(string)$xml_spell->effect;
			$spell->cast=		(int)	$xml_spell->cast;
			$spell->power=		(int)	$xml_spell->power;
			$spell->duration=	(int)	$xml_spell->duration;

			array_push($this->spells,$spell);
		}
	}


	public function validSpell($player,$spell_id)
	{
		$spell=AI_Spell::searchByProperty($this->spells,'id',$spell_id);
		if (!$spell)	
		{
			_print("Spell error from player $player->name not found (id $spell_id)");
			return FALSE;
		}
		if(!$spell->isValid($player))
		{
			_print("Spell id \"$spell_id\" from player $player->name failed cast");
			return FALSE;
		}
		return $spell;
	}

	public function toJson()
	{
		$json="
		{ \"spells\":
			[";
			
		foreach($this->spells as $spell)
		{
			$json.= $spell->toJson();
			$json.=',';
		}
		$json=rtrim($json,',');

		$json.="
			]
		}";

		return $json;
	}

}

?>
