<?php

namespace Wertzui123\SignIt;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

	public function onEnable() : void{
		$this->getLogger()->info("The SignIt plugin has been aktivatet! \nCommands:\n/signit and /sign\nThanks for using my plugin. Have Fun!");
	    $this->saveResource("config.yml");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch ($command->getName()){
     case "signit":
	 $settings = new Config($this->getDataFolder() . "config.yml", Config::YAML);
$runingame = $settings->get("run_in_game");
$usage = $settings->get("usage");
$signsucces = $settings->get("sign_succes");
$missingpermission = $settings->get("missing_permission");
$signer = $sender->getName();
                        $now = new \DateTime("now");
		if(!$sender instanceof Player) {
			$sender->sendMessage($runingame);
			return true;
		}
		if($sender->hasPermission("sign.wg.command")) {
		$item = $sender->getInventory()->getItemInHand();
				if(empty($args[0])){
					$sender->sendMessage($usage);
					return true;
				} else {
		if($item === null) {
			$sender->sendMessage($pleaseholdanitem);
		} else {
$item->setLore(["§r§b" . "§d(" . $now->format("d.m.Y H:i") . ")§b " . $signer . ": \n§r§f" . $args[0]]);
		$sender->getInventory()->setItemInHand($item);
$text = str_replace("{signedas}", $args[0], $signsucces); 
     $sender->sendMessage($text);
      }
      }
      } else{
			$sender->sendMessage($missingpermission);
     }
     return true;
		}
  }
	public function onDisable() : void{
		$this->getLogger()->info("The SignIt plugin has been deaktivatet! Have a nice day!");
	}
}
//This Plugin was written by Wertzui123 and you're not allowed to modify or rewrite it!
//To adjust it, just use the config.yml in the plugin_data/SignIt folder.
//© 2019 Wertzui123
