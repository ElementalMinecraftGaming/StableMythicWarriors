<?php

namespace ElementalMinecraftGaming\StableMythicWarriors;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use ElementalMinecraftGaming\StableMythicWarriors\XpInterval;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\command\Command;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener {

    public $db;
    public $Interval;
    public $plugin;
    public $races;

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->db = new \SQLite3($this->getDataFolder() . "StableMythicWarriors.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Charecter(user TEXT PRIMARY KEY, name TEXT, race TEXT, level INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Titan(name TEXT PRIMARY KEY, race TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Race(race TEXT PRIMARY KEY, size INT,  damage INT, health INT, hunger INT, level INT, effect INT, ability TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS PlayerPower(user TEXT PRIMARY KEY, power INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS RacePower(race TEXT PRIMARY KEY, power INT);");
        $this->Interval = new Config($this->getDataFolder() . "Interval.yml", Config::YAML, array("Interval" => 60));
        $this->getScheduler()->scheduleRepeatingTask(new XpInterval($this), $this->Interval->get("Interval") * 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function addExplosion(Player $player) {
        $lvl = $player->getLevel();
        $z = $player->getZ();
        $x = $player->getX();
        $y = $player->getY();
        $pos = new Position($x, $y, $z, $lvl);
        $explosion = new Explosion($pos, 6, null);
        $explosion->explodeB();
    }

    public function userRegistered($user) {
        $username = \SQLite3::escapeString($user);
        $search = $this->db->prepare("SELECT * FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $username);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function titanRegistered($name) {
        $username = \SQLite3::escapeString($name);
        $search = $this->db->prepare("SELECT * FROM Titan WHERE name = :name;");
        $search->bindValue(":name", $name);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function raceMade($race) {
        $racee = \SQLite3::escapeString($race);
        $search = $this->db->prepare("SELECT * FROM Race WHERE race = :race;");
        $search->bindValue(":race", $racee);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function getCname($user) {
        $search = $this->db->prepare("SELECT name FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $got = $start->fetchArray(SQLITE3_ASSOC);
        return $got["name"];
    }

    public function getLevel($user) {
        $search = $this->db->prepare("SELECT level FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["level"];
    }

    public function addLevel($user, $amount) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Charecter (user, name, race, level) VALUES (:user, :name, :race, :level);");
        $del->bindValue(":user", $user);
        $del->bindValue(":name", $this->getCname($user));
        $del->bindValue(":race", $this->getRace($user));
        $del->bindValue(":level", $this->getlevel($user) + $amount);
        $start = $del->execute();
    }

    public function getPower($user) {
        $search = $this->db->prepare("SELECT power FROM PlayerPower WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["power"];
    }

    public function getClass($race) {
        $search = $this->db->prepare("SELECT ability FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return $da["ability"];
    }

    public function getRace($user) {
        $search = $this->db->prepare("SELECT race FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return $da["race"];
    }
    
    public function getTitanRace($entity) {
        $search = $this->db->prepare("SELECT race FROM Titan WHERE name = :name;");
        $search->bindValue(":name", $entity);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return $da["race"];
    }

    public function raceMatch($user) {
        $level = $this->getLevel($user);
        $search = $this->db->prepare("SELECT race FROM Race WHERE level = :level;");
        $search->bindValue(":level", $level);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return $da["race"];
    }

    public function existRaceMatch($user) {
        $level = $this->getLevel($user);
        $lev = \SQLite3::escapeString($level);
        $search = $this->db->prepare("SELECT race FROM Race WHERE level = :level;");
        $search->bindValue(":level", $lev);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function getRaceHealth($race) {
        $search = $this->db->prepare("SELECT health FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["health"];
    }

    public function getRaceHunger($race) {
        $search = $this->db->prepare("SELECT hunger FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["hunger"];
    }

    public function getRaceDamage($race) {
        $search = $this->db->prepare("SELECT damage FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["damage"];
    }

    public function getRaceSize($race) {
        $search = $this->db->prepare("SELECT size FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["size"];
    }

    public function Charecter($user, $name, $race) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Charecter (user, name, race) VALUES (:user, :name, :race);");
        $del->bindValue(":user", $user);
        $del->bindValue(":name", $name);
        $del->bindValue(":race", $race);
        $start = $del->execute();
        $dell = $this->db->prepare("INSERT OR REPLACE INTO PlayerPower (user) VALUES (:user);");
        $dell->bindValue(":user", $user);
        $start = $dell->execute();
    }

    public function Titan($name, $race) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Titan (name, race) VALUES (:name, :race);");
        $del->bindValue(":name", $name);
        $del->bindValue(":race", $race);
        $start = $del->execute();
    }

    public function setRace($user, $race) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Charecter (user, name, race, level) VALUES (:user, :name, :race, :level);");
        $del->bindValue(":user", $user);
        $name = $this->getCname($user);
        $del->bindValue(":name", $name);
        $del->bindValue(":race", $race);
        $level = $this->getLevel($user);
        $del->bindValue(":level", $level);
        $start = $del->execute();
    }

    public function getRaceLevel($race) {
        $search = $this->db->prepare("SELECT level FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["level"];
    }

    public function addRace($race, $size, $damage, $health, $hunger, $level, $effect, $ability) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO race (race, size, damage, health, hunger, level, effect, ability) VALUES (:race, :size, :damage, :health, :hunger, :level, :effect, :ability);");
        $del->bindValue(":race", $race);
        $del->bindValue(":size", $size);
        $del->bindValue(":damage", $damage);
        $del->bindValue(":health", $health);
        $del->bindValue(":hunger", $hunger);
        $del->bindValue(":level", $level);
        $del->bindValue(":effect", $effect);
        $del->bindValue(":ability", $ability);
        $start = $del->execute();
        $dell = $this->db->prepare("INSERT OR REPLACE INTO RacePower (race) VALUES (:race);");
        $dell->bindValue(":race", $race);
        $start = $dell->execute();
    }

    public function maddLevel($amount) {
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player) {
                $this->addLevel($player, $amount);
        }
    }

    public function OnKill(PlayerDeathEvent $event) {
        $player = $event->getPlayer()->getName();
        if ($player instanceof Player) {
            $play = $player->getLastDamageCause();
            if ($play instanceof EntityDamageByEntityEvent and $play->getDamager() instanceof Player) {
                $killer = $play->getDamager();
                $killer->addLevel(2.0);
            }
        }
    }

    public function checkTitans($player) {
        foreach ($this->getServer()->getPlayer($player)->getLevel()->getEntities() as $titan) {
            $titan->setNameTagVisible(true);
            $titan->setNameTagAlwaysVisible(true);
            $tag = $titan->getNameTag();
            $titanex = $this->titanRegistered($tag);
            if ($titanex == true) {
                $titanRace = $this->getTitanRace($tag);
                $checkrace = $this->raceMade($titanRace);
                if ($checkrace == true) {
                    $maxHealth = $this->getRaceHealth($titanRace);
                    $health = $this->getRaceHealth($titanRace);
                    $size = $this->getRaceSize($titanRace);
                    $titan->setScale($size);
                    $titan->setMaxHealth($maxHealth);
                    $titan->setHealth($health);
                }
            }
        }
    }

    public function join(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $playe = $event->getPlayer()->getName();
        if ($this->userRegistered($playe) == true) {
            $prace = $this->getRace($playe);
            $damage = $this->getRaceDamage($prace);
            $player->setFood($this->getRaceHunger($prace));
            $player->setMaxHealth($this->getRaceHealth($prace));
            $player->setHealth($this->getRaceHealth($prace));
            $player->setScale($this->getRaceSize($prace));
        }
    }
    
    /*public function onDamage(EntityDamageEvent $event) {
        if($event instanceof EntityDamageByEntityEvent){
        $attacked = $event->getEntity();
        $attacker = $event->getDamager();
            if ($attacker instanceof Player) {
                $nameattacker = $attacker->getName();
                $checkrace = $this->userRegistered($nameattacker);
                if ($checkrace == true) {
                    $race = $this->getRace($nameattacker);
                    $checkrace = $this->raceMade($race);
                    if ($checkrace == true) {
                        $damage = $this->getRaceDamage($nameattacker);
                        $attacked->attack(new EntityDamageEvent($attacked, EntityDamageEvent::CAUSE_CUSTOM, $damage));
                    }
                }
            }
        }
    }

    public function OnCrouch(PlayerToggleSneakEvent $event) {
        $player = $event->getPlayer();
        $playe = $player->getName();
        $checkrace = $this->userRegistered($playe);
        if ($checkrace == true) {
            $race = $this->getRace($playe);
            $checkracee = $this->raceMade($race);
            if ($checkracee == true) {
                foreach ($player->getViewers() as $viewer) {
                    if ($player->distance($viewer) <= 4) {
                        $classs = $this->getClass($race);
                        if ($classs == "FlameLord") {
                                $viewer->setOnFire(1);
                        } elseif ($classs == "BOOM") {
                            $this->addExplosion($viewer) *60;
                        }
                    }
                }
            }
        }
    }*/

    public function Msg($string) {
        return TextFormat::RED . "[" . TextFormat::DARK_PURPLE . "MythicWarriors" . TextFormat::RED . "] " . TextFormat::BLUE . "$string";
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (strtolower($command->getName()) == "createcharecter") {
            if ($sender->hasPermission("mythic.create")) {
                if ($sender instanceof Player) {
                    if (isset($args[1])) {
                        $sender->sendMessage($this->Msg("Creating charecter sheet..."));
                        $user = $sender->getName();
                        $name = $args[0];
                        $race = $args[1];
                        $checkname = $this->userRegistered($user);
                        if ($checkname == false) {
                            $checkrace = $this->raceMade($race);
                            if ($checkrace == true) {
                                $RaceLevel = $this->getRaceLevel($race);
                                $level = $this->getLevel($user);
                                if ($level >= $RaceLevel) {
                                    $prace = $race;
                                    $rsize = $this->getRaceSize($prace);
                                    $sender->setFood($this->getRaceHunger($prace));
                                    $sender->setMaxHealth($this->getRaceHealth($prace));
                                    $sender->setHealth($this->getRaceHealth($prace));
                                    $sender->setScale($this->getRaceSize($prace));
                                    $this->Charecter($user, $name, $race);
                                    $sender->sendMessage($this->Msg("Sheet created!"));
                                    return true;
                                } else {
                                    $sender->sendMessage($this->Msg("Requires level: $RaceLevel!"));
                                }
                            } else {
                                $sender->sendMessage($this->Msg("No such race!"));
                            }
                        } else {
                            $sender->sendMessage($this->Msg("Sheet already created!"));
                        }
                    } else {
                        $sender->sendMessage($this->Msg("Please set the charecter name and race!"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "createtitan") {
            if ($sender->hasPermission("mythic.create")) {
                if ($sender instanceof Player) {
                    if (isset($args[1])) {
                        $sender->sendMessage($this->Msg("Creating titan sheet..."));
                        $name = $args[0];
                        $race = $args[1];
                        $checkname = $this->titanRegistered($name);
                        if ($checkname == false) {
                            $checkrace = $this->raceMade($race);
                            if ($checkrace == true) {
                                $this->Titan($name, $race);
                                $sender->sendMessage($this->Msg("Titan Sheet created!"));
                                return true;
                            } else {
                                $sender->sendMessage($this->Msg("No such race!"));
                            }
                        } else {
                            $sender->sendMessage($this->Msg("Sheet already created!"));
                        }
                    } else {
                        $sender->sendMessage($this->Msg("Please set the titan name and race!"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "createrace") {
            if ($sender->hasPermission("mythic.createrace")) {
                if ($sender instanceof Player) {
                    if (isset($args[7])) {
                        $sender->sendMessage($this->Msg("Creating race..."));
                        $user = $sender->getName();
                        $race = $args[0];
                        $size = $args[1];
                        $health = $args[2];
                        $hunger = $args[3];
                        $level = $args[4];
                        $effect = $args[5];
                        $ability = $args[6];
                        $damage = $args[7];
                        $checkrace = $this->raceMade($race);
                        if ($checkrace == false) {
                            $this->addRace($race, $size, $damage, $health, $hunger, $level, $effect, $ability);
                            $sender->sendMessage($this->Msg("Sheet created!"));
                            return true;
                        } else {
                            $sender->sendMessage($this->Msg("Race already created!"));
                        }
                    } else {
                        $sender->sendMessage($this->Msg("Please set the race, size, health, hunger, level, effect, ability and damage!"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "addmlevel") {
            if ($sender->hasPermission("mystic.level")) {
                if ($sender instanceof Player) {
                    $arg = count($args) == 0;
                    if (isset($args[1])) {
                        $user = $args[0];
                        $amount = $args[1];
                        $checkname = $this->userRegistered($user);
                        if ($checkname == true) {
                            $this->addLevel($user, $amount);
                            $sender->sendMessage($this->Msg("Added levels"));
                            return true;
                        } else {
                            $sender->sendMessage($this->Msg("No player sheet"));
                        }
                    } else {
                        $sender->sendMessage($this->Msg("Missing name or level"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("Must be in-game"));
                }
            } else {
                $sender->sendMessage($this->Msg("No perms"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "mythicraces") {
            if ($sender->hasPermission("mythic.races")) {
                if ($sender instanceof Player) {
                    $user = $sender->getName();
                    $plevel = $this->getLevel($user);
                    $sender->sendMessage(TextFormat::BLUE . "==Races==");
                    $load = $this->db->prepare("SELECT race FROM Race ORDER BY level DESC;");
                    $load->bindValue(":level", $plevel);
                    $start = $load->execute();
                    while ($check = $start->fetchArray(SQLITE3_ASSOC)) {
                        $race = $check['race'];
                        $level = $this->getRaceLevel($race);
                        $sender->sendMessage($this->Msg("$race level $level"));
                    }
                    return true;
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "mythictitans") {
            if ($sender->hasPermission("mythic.titans")) {
                if ($sender instanceof Player) {
                    $user = $sender->getName();
                    $this->checkTitans($user);
                    return true;
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "titanegg") {
            if ($sender->hasPermission("mythic.create")) {
                if ($sender instanceof Player) {
                    if (isset($args[2])) {
                        $titan = $args[0];
                        $checktitan = $this->titanRegistered($titan);
                        if ($checktitan == true) {
                            $player = $sender->getName();
                            $egg = $args[1];
                            $amount = $args[2];
                            $sender->getServer()->dispatchCommand(new ConsoleCommandSender(), "give $player 383:$egg $amount {display:{Name:$titan}}");
                            return true;
                        } else {
                            $sender->sendMessage($this->Msg("Titan not registered!"));
                        }
                    } else {
                        $sender->sendMessage($this->Msg("Set the titan name, Set the mob egg id (32 for Zombie, Set the amount!"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }

        if (strtolower($command->getName()) == "csheet") {
            if ($sender->hasPermission("mythic.profile")) {
                if ($sender instanceof Player) {
                    $sender->sendMessage($this->Msg("Getting sheet..."));
                    $user = $sender->getName();
                    $checkrace = $this->userRegistered($user);
                    if ($checkrace == true) {
                        $name = $this->getCname($user);
                        $race = $this->getRace($user);
                        $size = $this->getRaceSize($race);
                        $level = $this->getLevel($user);
                        $class = $this->getClass($race);
                        $sender->sendMessage($this->Msg("\n===Charecter Sheet===\nCharecter: $name\nRace; $race\nSize: $size\nLevel: $level"));
                        return true;
                    } else {
                        $sender->sendMessage($this->Msg("Not charecter sheet found!"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("In-Game only!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }
        return false;
    }

}
