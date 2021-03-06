<?php
namespace BukkitPE\inventory;

use BukkitPE\item\Item;
use BukkitPE\Server;
use BukkitPE\utils\UUID;

class BrewingRecipe implements Recipe{

	private $id = null;

	/** @var Item */
	private $output;

	/** @var Item */
	private $ingredient;

	/** @var Item  */
	private $bottle;

	/**
	 * @param Item $result
	 * @param Item $ingredient
	 */
	public function __construct(Item $result, Item $ingredient, Item $bottle){
		$this->output = clone $result;
		$this->ingredient = clone $ingredient;
		$this->bottle = clone $bottle;
	}

	public function getId(){
		return $this->id;
	}

	public function setId(UUID $id){
		if($this->id !== null){
			throw new \InvalidStateException("Id is already set");
		}

		$this->id = $id;
	}

	/**
	 * @param Item $item
	 */
	public function setInput(Item $item){
		$this->ingredient = clone $item;
	}

	/**
	 * @return Item
	 */
	public function getInput(){
		return clone $this->ingredient;
	}

	/**
	 * @return Item
	 */
	public function getResult(){
		return clone $this->output;
	}

	public function registerToCraftingManager(){
		Server::getInstance()->getCraftingManager()->registerBrewingRecipe($this);
	}
}