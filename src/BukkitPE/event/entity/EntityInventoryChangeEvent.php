<?php
namespace BukkitPE\event\entity;

use BukkitPE\entity\Entity;
use BukkitPE\Event;
use BukkitPE\event\Cancellable;
use BukkitPE\item\Item;

class EntityInventoryChangeEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	private $oldItem;
	private $newItem;
	private $slot;

	public function __construct(Entity $entity, Item $oldItem, Item $newItem, $slot){
		$this->entity = $entity;
		$this->oldItem = $oldItem;
		$this->newItem = $newItem;
		$this->slot = (int) $slot;
	}

	public function getSlot(){
		return $this->slot;
	}

	public function getNewItem(){
		return $this->newItem;
	}

	public function setNewItem(Item $item){
		$this->newItem = $item;
	}

	public function getOldItem(){
		return $this->oldItem;
	}


}