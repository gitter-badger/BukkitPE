<?php

/*
 * THIS IS COPIED FROM THE PLUGIN FlowerPot MADE BY @beito123!!
 * https://github.com/beito123/BukkitPE-MP-Plugins/blob/master/test%2FFlowerPot%2Fsrc%2Fbeito%2FFlowerPot%2Fomake%2FSkull.php
 * 
 */

namespace BukkitPE\tile;

use BukkitPE\level\format\FullChunk;
use BukkitPE\nbt\tag\Compound;
use BukkitPE\nbt\tag\Int;
use BukkitPE\nbt\tag\String;

class Skull extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->SkullType)){
			$nbt->SkullType = new String("SkullType", 0);
		}

		parent::__construct($chunk, $nbt);
	}

	public function saveNBT(){
		parent::saveNBT();
		unset($this->namedtag->Creator);
	}
	
	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", Tile::SKULL),
			$this->namedtag->SkullType,
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			$this->namedtag->Rot
		]);
	}
	
	public function getSkullType(){
		return $this->namedtag["SkullType"];
	}
}
