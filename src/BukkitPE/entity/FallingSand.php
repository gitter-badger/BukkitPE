<?php
namespace BukkitPE\entity;

use BukkitPE\block\Block;

use BukkitPE\block\Liquid;
use BukkitPE\event\entity\EntityBlockChangeEvent;
use BukkitPE\event\entity\EntityDamageEvent;
use BukkitPE\item\Item as ItemItem;
use BukkitPE\math\Vector3;
use BukkitPE\nbt\tag\Byte;
use BukkitPE\nbt\tag\Int;
use BukkitPE\network\protocol\AddEntityPacket;
use BukkitPE\Player;

class FallingSand extends Entity{
	const NETWORK_ID = 66;

	const DATA_BLOCK_INFO = 20;

	public $width = 0.98;
	public $length = 0.98;
	public $height = 0.98;

	protected $gravity = 0.04;
	protected $drag = 0.02;
	protected $blockId = 0;
	protected $damage;

	public $canCollide = false;

	protected function initEntity(){
		parent::initEntity();
		if(isset($this->namedtag->TileID)){
			$this->blockId = $this->namedtag["TileID"];
		}elseif(isset($this->namedtag->Tile)){
			$this->blockId = $this->namedtag["Tile"];
			$this->namedtag["TileID"] = new Int("TileID", $this->blockId);
		}

		if(isset($this->namedtag->Data)){
			$this->damage = $this->namedtag["Data"];
		}

		if($this->blockId === 0){
			$this->close();
			return;
		}

		$this->setDataProperty(self::DATA_BLOCK_INFO, self::DATA_TYPE_INT, $this->getBlock() | ($this->getDamage() << 8));
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function attack($damage, EntityDamageEvent $source){
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($damage, $source);
		}
	}

	public function onUpdate($currentTick){

		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0 and !$this->justCreated){
			return true;
		}

		$this->lastUpdate = $currentTick;

		$hasUpdate = $this->entityBaseTick($tickDiff);

		if($this->isAlive()){
			$pos = (new Vector3($this->x - 0.5, $this->y, $this->z - 0.5))->round();

			if($this->ticksLived === 1){
				$block = $this->level->getBlock($pos);
				if($block->getId() !== $this->blockId){
					$this->kill();
					return true;
				}
				$this->level->setBlock($pos, Block::get(0), true);
			}

			$this->motionY -= $this->gravity;

			$this->move($this->motionX, $this->motionY, $this->motionZ);

			$friction = 1 - $this->drag;

			$this->motionX *= $friction;
			$this->motionY *= 1 - $this->drag;
			$this->motionZ *= $friction;

			$pos = (new Vector3($this->x - 0.5, $this->y, $this->z - 0.5))->floor();

			if($this->onGround){
				$this->kill();
				$block = $this->level->getBlock($pos);
				if($block->getId() > 0 and !$block->isSolid() and !($block instanceof Liquid)){
					$this->getLevel()->dropItem($this, ItemItem::get($this->getBlock(), $this->getDamage(), 1));
				}else{
					$this->server->getPluginManager()->callEvent($ev = new EntityBlockChangeEvent($this, $block, Block::get($this->getBlock(), $this->getDamage())));
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($pos, $ev->getTo(), true);
					}
				}
				$hasUpdate = true;
			}

			$this->updateMovement();
		}

		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}

	public function getBlock(){
		return $this->blockId;
	}

	public function getDamage(){
		return $this->damage;
	}

	public function saveNBT(){
		$this->namedtag->TileID = new Int("TileID", $this->blockId);
		$this->namedtag->Data = new Byte("Data", $this->damage);
	}

	public function spawnTo(Player $player){
		$pk = $this->addEntityDataPacket($player);
		$pk->type = FallingSand::NETWORK_ID;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
