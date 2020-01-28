<?php

namespace ElementalMinecraftGaming\StableMythicWarriors;

use pocketmine\scheduler\Task;
use ElementalMinecraftGaming\StableMythicWarriors\Main;

class XpInterval extends Task {
    
    public $plugin;
	
	public function __construct(Main $pg) {
		$this->plugin = $pg;
	}

    public function onRun(int $currentTick){
        $this->plugin->maddLevel(1);
    }
}
