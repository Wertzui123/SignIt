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
		$this->getLogger()->info("The SignIt plugin has been activated! \nCommands:\n/signit and /sign\nThanks for using my plugin. Have Fun!");
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
$lore = $settings->get("sign_format");
$dateformat = $settings->get("date_format");
if($this->getServer()->getPluginManager()->getPlugin("PurePerms")) {
	$purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
    $group = $purePerms->getUserDataMgr()->getData($sender)['group'];
} else {
    $group = "The plugin isn't installed";
}
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
$lore = str_replace("{signedas}", $args[0], $lore);
$lore = str_replace("{date}", $now->format($dateformat), $lore);
$text = str_replace("{signedas}", $args[0], $signsucces);
$lore = str_replace("{signer}", $sender->getName(), $lore);
$lore = str_replace("{rank}", $group, $lore);
$lore = str_replace("{group}", $group, $lore);
$item->setLore([$lore]);
		$sender->getInventory()->setItemInHand($item);
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
		$this->getLogger()->info("The SignIt plugin has been deactivated! Have a nice day!");
	}
}
//This Plugin was written by Wertzui123 and you're not allowed to modify or rewrite it!
//To adjust it, just use the config.yml in the plugin_data/SignIt folder.
//© 2019 Wertzui123
