<?php

namespace elemental\MythicWarriors;

use pocketmine\scheduler\Task;
use elemental\MythicWarriors\Main;

class XpInterval extends Task {
    
    public $plugin;
	
	public function __construct(Main $pg) {
		$this->plugin = $pg;
	}

    public function onRun(int $currentTick){
        $this->plugin->maddLevel(1);
    }
}
