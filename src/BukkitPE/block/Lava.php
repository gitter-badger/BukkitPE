<?php
namespace BukkitPE\block;

use BukkitPE\entity\Effect;
use BukkitPE\entity\Entity;
use BukkitPE\event\entity\EntityCombustByBlockEvent;
use BukkitPE\event\entity\EntityDamageByBlockEvent;
use BukkitPE\event\entity\EntityDamageEvent;
use BukkitPE\item\Item;
use BukkitPE\Player;
use BukkitPE\Server;

class Lava extends Liquid{

	protected $id = self::LAVA;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getLightLevel(){
		return 15;
	}

	public function getName(){
		return "Lava";
	}

	public function onEntityCollide(Entity $entity){
		$entity->fallDistance *= 0.5;
		if(!$entity->hasEffect(Effect::FIRE_RESISTANCE)){
			$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
			$entity->attack($ev->getFinalDamage(), $ev);
		}

		$ev = new EntityCombustByBlockEvent($this, $entity, 15);
		Server::getInstance()->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}

		$entity->resetFallDistance();
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$ret = $this->getLevel()->setBlock($this, $this, true, false);
		$this->getLevel()->scheduleUpdate($this, $this->tickRate());

		return $ret;
	}

}
