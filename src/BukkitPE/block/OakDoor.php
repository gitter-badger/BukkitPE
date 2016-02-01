<?php
namespace BukkitPE\block;

use BukkitPE\item\Item;
use BukkitPE\item\Tool;

class OakDoor extends Door{

	protected $id = self::OAK_DOOR_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Oak Door Block";
	}

	public function canBeActivated(){
		return true;
	}

	public function getHardness(){
		return 3;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getDrops(Item $item){
		return [
			[Item::OAK_DOOR, 0, 1],
		];
	}
}