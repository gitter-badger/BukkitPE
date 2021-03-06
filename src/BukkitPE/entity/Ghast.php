<?php
namespace BukkitPE\entity;

use BukkitPE\item\Item as ItemItem;
use BukkitPE\Player;

class Ghast extends Monster{
	const NETWORK_ID = 41;

	public $width = 4.5;
	public $length = 4.5;
	public $height = 4.5;

 	public static $range = 16;
	public static $speed = 0.25;
	public static $jump = 1.8;
	public static $mindist = 3;
	
	protected $exp_min = 5;
	protected $exp_max = 5;

	public function initEntity(){
		$this->setMaxHealth(10);
		parent::initEntity();
	}

	public function getName(){
		return "Ghast";
	}

	 public function spawnTo(Player $player){
		$pk = $this->addEntityDataPacket($player);
		$pk->type = Ghast::NETWORK_ID;

		$player->dataPacket($pk);
		parent::spawnTo($player);
	}

	public function getDrops(){
		return [
			ItemItem::get(ItemItem::GHAST_TEAR, 0, mt_rand(0, 1)),
			ItemItem::get(ItemItem::GUNPOWDER, 0, mt_rand(0, 2))
		];
	}

}
