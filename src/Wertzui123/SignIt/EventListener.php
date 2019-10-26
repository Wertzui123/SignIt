<?php

namespace Wertzui123\SignIt;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        if(!isset($this->plugin->getPlayersFile()->getAll()[strtolower($player->getName())])){
            $this->plugin->setUntil($player);
        }
    }

}