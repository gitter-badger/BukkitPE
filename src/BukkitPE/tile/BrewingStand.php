<?php
namespace BukkitPE\tile;

use BukkitPE\event\inventory\BrewingStandBrewEvent;
use BukkitPE\inventory\BrewingInventory;
use BukkitPE\inventory\BrewingRecipe;
use BukkitPE\inventory\InventoryHolder;
use BukkitPE\item\Item;
use BukkitPE\level\format\FullChunk;
use BukkitPE\nbt\NBT;
use BukkitPE\nbt\tag\Compound;
use BukkitPE\nbt\tag\Enum;
use BukkitPE\nbt\tag\Short;
use BukkitPE\nbt\tag\String;
use BukkitPE\network\protocol\ContainerSetDataPacket;

class BrewingStand extends Tile implements InventoryHolder, Container, Nameable{
	/** @var BrewingInventory */
	protected $inventory;

	public function __construct(FullChunk $chunk, Compound $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new BrewingInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof Enum)){
			$this->namedtag->Items = new Enum("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}
	}
	
	public function getServer(){
		return $this->server;
	}

	public function getName(){
		return $this->hasName() ? $this->namedtag->CustomName->getValue() : "Brewing Stand";
	}

	public function hasName(){
		return isset($this->namedtag->CustomName);
	}

	public function setName($str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new String("CustomName", $str);
	}

	public function close(){
		if($this->closed === false){
			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}
			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items = new Enum("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 4;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if($slot["Slot"] === $index){
				return $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return NBT::getItemHelper($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = NBT::putItemHelper($item, $index);

		if($item->getId() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}

		return true;
	}

	/**
	 * @return BrewingInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function onUpdate(){
		if($this->closed === true){
			return false;
		}

		$this->timings->startTiming();

		$ret = false;

		$ingredient = $this->inventory->getIngredient();
		$product = $this->inventory->getResult();
		$brew = $this->server->getCraftingManager()->matchBrewingRecipe($ingredient);
		$canbrew = ($brew instanceof BrewingRecipe and $ingredient->getCount() > 0 and (($brew->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->getId() === Item::AIR));

		$this->namedtag->BrewTime = new Short("BrewTime", $this->namedtag["BrewTime"] - 1);
		$this->namedtag->BrewTicks = new Short("BrewTicks", 0);

		if($brew instanceof BrewingRecipe and $canbrew){
			
			
				$product = Item::get($brew->getResult()->getId(), $brew->getResult()->getDamage(), $product->getCount() + 1);

				$this->server->getPluginManager()->callEvent($ev = new BrewingStandBrewEvent($this, $ingredient, $product));

				if(!$ev->isCancelled()){
					
					$this->inventory->setResult($ev->getResult());
					$ingredient->setCount($ingredient->getCount() - 1);
					if($ingredient->getCount() === 0){
						$ingredient = Item::get(Item::AIR, 0, 0);
					}
					$this->inventory->setbrewing($ingredient);
				}
			
		}
		$ret = true;


		foreach($this->getInventory()->getViewers() as $player){
			$windowId = $player->getWindowId($this->getInventory());
			if($windowId > 0){
				$pk = new ContainerSetDataPacket();
				$pk->windowid = $windowId;
				$pk->property = 0; //Brew
				$player->dataPacket($pk);

				$pk = new ContainerSetDataPacket();
				$pk->windowid = $windowId;
				$pk->property = 1; //Bubble Icon
				$player->dataPacket($pk);
			}

		}

		$this->lastUpdate = microtime(true);

		$this->timings->stopTiming();

		return $ret;
	}
	
	public function getSpawnCompound(){
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::BREWING_STAND),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new ShortTag("CookTime", self::MAX_BREW_TIME),
		]);
		if($this->hasName()){
			$nbt->CustomName = $this->namedtag->CustomName;
		}
		return $nbt;
	}
}
