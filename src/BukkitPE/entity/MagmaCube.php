<?php
namespace BukkitPE\entity;

use BukkitPE\item\Item as ItemItem;
use BukkitPE\Player;

class MagmaCube extends Living{
	const NETWORK_ID = 42;

	public $width = 2;
	public $length = 2;
	public $height = 2;
	
	protected $exp_min = 1;
	protected $exp_max = 1; //TODO: Size

	public function initEntity(){
		//$this->setMaxHealth(10); //TODO Size
		parent::initEntity();
	}

	public function getName(){
		return "Magma Cube";
	}

	 public function spawnTo(Player $player){
		$pk = $this->addEntityDataPacket($player);
		$pk->type = MagmaCube::NETWORK_ID;

		$player->dataPacket($pk);
		parent::spawnTo($player);
	}

	public function getDrops(){
		return [
			ItemItem::get(ItemItem::MAGMA_CREAM, 0, mt_rand(0, 2))
		];
	}
}
