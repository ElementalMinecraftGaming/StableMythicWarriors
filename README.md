# Stable Mythic Warriors
DISCONTINUED - This has been discontinued to make way for the Jerico Systemizer RP plugin coming at a future date. :)

## General 
Level up in a new D&D style gamemode to get new charecters!

To use Titans please get a mob egg with the name of a race and then run the mythicaltitan command after spawning to transform it.

Please use a mob API if you wish to use mob functions!

[![](https://poggit.pmmp.io/shield.state/StableMythicWarriors)](https://poggit.pmmp.io/p/StableMythicWarriors)

## What
This plugin is a levelling up system that will allow you to gain more privileges when you reach a certain level.

you can also create races and titans that you want.

Titans are mobs that have been edited with the /createtitan command and can be found by walking near a titan with a tag(tries to blend in) and type the /mythictitans command.

These titans must be spawned in to actually work with the /titanegg command.

## Still planned
Custom damage

Abilities

Time of day evolution

Temporary abillities

More commands

## API
userRegister($user)

titanRegistered($name)

raceMade($race)

getCname($user)

getLevel($user)

addLevel($user, $amount)

getPower($user)

getClass($race)

getRace($user)

getTitanRace($entity)

raceMatch($user)

existRaceMatch($user)

getRaceHealth($race)

getRaceHunger($race)

getRaceDamage($race)

getRaceSize($race)

Charecter($user, $name, $race)

Titan($name, $race)

setRace($user, $race)

getRaceLevel($race)

addRace($race, $size, $damage, $health, $hunger, $level, $effect, $ability) - ability not useful yet

maddLevel($amount)

OnKill(PlayerDeathEvent $event)

checkTitans($player)

## Credits
Made by ElementalMinecraftGaming

My original version was as OofDevs.

Github: https://github.com/ElementalMinecraftGaming/StableMythicWarriors
