<?php
namespace BukkitPE\event\inventory;

use BukkitPE\event\Cancellable;
use BukkitPE\event\Event;
use BukkitPE\inventory\TransactionGroup;

/**
 * Called when there is a transaction between two Inventory objects.
 * The source of this can be a Player, entities, mobs, or even hoppers in the future!
 */
class InventoryTransactionEvent extends Event implements Cancellable{
	public static $handlerList = null;

	/** @var TransactionGroup */
	private $ts;

	/**
	 * @param TransactionGroup $ts
	 */
	public function __construct(TransactionGroup $ts){
		$this->ts = $ts;
	}

	/**
	 * @return TransactionGroup
	 */
	public function getTransaction(){
		return $this->ts;
	}

}
