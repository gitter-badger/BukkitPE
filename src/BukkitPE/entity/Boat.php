<?php
namespace BukkitPE\entity;

use BukkitPE\Player;
use BukkitPE\event\entity\EntityDamageEvent;
use BukkitPE\network\protocol\EntityEventPacket;
use BukkitPE\item\Item as ItemItem;

class Boat extends Vehicle{
	const NETWORK_ID = 90;

	public function spawnTo(Player $player){
		$pk = $this->addEntityDataPacket($player);
		$pk->type = self::NETWORK_ID;
		
		$player->dataPacket($pk);
		parent::spawnTo($player);
	}

	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);

		if(!$source->isCancelled()){
			$pk = new EntityEventPacket();
			$pk->eid = $this->id;
			$pk->event = EntityEventPacket::HURT_ANIMATION;
			foreach($this->getLevel()->getPlayers() as $player){
				$player->dataPacket($pk);
			}
		}
	}
	
	public function onUpdate($currentTick){
		if($this->isAlive()){
			$this->timings->startTiming();
			$hasUpdate = false;
			
			if($this->isInsideOfWater()){
				$hasUpdate = true;
				$this->move(0,0.1,0);
				$this->updateMovement();
			}
			
			$this->timings->stopTiming();

			return $hasUpdate;
		}
	}
	
	public function kill(){
		parent::kill();

		foreach($this->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
	}

	public function getDrops(){
		return [
			ItemItem::get(ItemItem::BOAT, 0, 1)
		];
	}

	public function getSaveId(){
		$class = new \ReflectionClass(static::class);
		return $class->getShortName();
	}
}
