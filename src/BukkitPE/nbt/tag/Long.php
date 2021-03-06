<?php
namespace BukkitPE\nbt\tag;

use BukkitPE\nbt\NBT;

#include <rules/NBT.h>

class Long extends NamedTag{

	public function getType(){
		return NBT::TAG_Long;
	}

	public function read(NBT $nbt){
		$this->value = $nbt->getLong();
	}

	public function write(NBT $nbt){
		$nbt->putLong($this->value);
	}
}