<?php
namespace BukkitPE\command\defaults;

use BukkitPE\command\Command;
use BukkitPE\command\CommandSender;
use BukkitPE\event\TranslationContainer;
use BukkitPE\level\Level;
use BukkitPE\Player;
use BukkitPE\utils\TextFormat;

class WeatherCommand extends VanillaCommand{

    public function __construct($name){
        parent::__construct(
            $name,
            "%BukkitPE.command.weather.description",
            "%BukkitPE.command.weather.usage"
        );
        $this->setPermission("BukkitPE.command.weather.clear;BukkitPE.command.weather.rain;BukkitPE.command.weather.thunder");
    }

    public function execute(CommandSender $sender, $currentAlias, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) > 2 || count($args) === 0){
            $sender->sendMessage(new TranslationContainer("commands.weather.usage", [$this->usageMessage]));

            return false;
        }

        if(count($args) > 1){
            $seconds = (int) $args[1];
        }else{
            $seconds = 600*20;
        }

        if($sender instanceof Player){
            $level = $sender->getLevel();
        }else{
            $level = $sender->getServer()->getDefaultLevel();
        }

        if($args[0] === "clear"){
            if(!$sender->hasPermission("BukkitPE.command.weather.clear")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
                return true;
            }

            $level->setWeather(Level::WEATHER_CLEARSKY);

            Command::broadcastCommandMessage($sender, new TranslationContainer("commands.weather.clear"));

            return true;

        }elseif($args[0] === "rain"){
            if(!$sender->hasPermission("BukkitPE.command.weather.rain")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
                return true;
            }
			
			if(isset($args[1]) and is_numeric($args[1]) and $args[1] > 0){
				$time = $args[1];
			}else{
				$time = 120;
			}
			
            $level->setWeather(Level::WEATHER_RAIN,$time);

            Command::broadcastCommandMessage($sender, new TranslationContainer("commands.weather.rain"));

            return true;

        }elseif($args[0] === "thunder"){
            if(!$sender->hasPermission("BukkitPE.command.weather.thunder")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
                return true;
            }
//WEATHER TODO : THUNDER
            //$level->setThundering(true);
            //$level->setRainTime($seconds * 20);
            //$level->setThunderTime($seconds * 20);

            Command::broadcastCommandMessage($sender,"WIP"/* new TranslationContainer("commands.weather.thunder")*/);

            return true;

        }else{
            $sender->sendMessage(new TranslationContainer("commands.weather.usage",  [$this->usageMessage]));
            return false;
        }
    }
}
