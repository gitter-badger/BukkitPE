<?php
namespace BukkitPE\block;

use BukkitPE\event\block\BlockSpreadEvent;
use BukkitPE\item\Item;
use BukkitPE\item\Tool;
use BukkitPE\level\Level;
use BukkitPE\math\Vector3;
use BukkitPE\Server;

class Mycelium extends Solid{

	protected $id = self::MYCELIUM;

	public function __construct(){

	}

	public function getName(){
		return "Mycelium";
	}

	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	public function getHardness(){
		return 0.6;
	}

	public function getDrops(Item $item){
		return [
			[Item::DIRT, 0, 1],
		];
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_RANDOM){
			//TODO: light levels
			$x = mt_rand($this->x - 1, $this->x + 1);
			$y = mt_rand($this->y - 2, $this->y + 2);
			$z = mt_rand($this->z - 1, $this->z + 1);
			$block = $this->getLevel()->getBlock(new Vector3($x, $y, $z));
			if($block->getId() === Block::DIRT){
				if($block->getSide(1) instanceof Transparent){
					Server::getInstance()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, new Mycelium()));
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($block, $ev->getNewState());
					}
				}
			}
		}
	}
}
