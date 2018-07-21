<?php

namespace Core;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\Entity;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\utils\Textformat as Color;

class Main extends PluginBase implements Listener {

    public $prefix = "§7[§9DushyMC.de§r§7]";
    public $hideall = [];

    public function onEnable () {
		
		$prefix = new Config($this->getDataFolder() . "prefix.yml", Config::YAML);
            if(empty($prefix->get("Prefix"))) {
                $prefix->set("Prefix", "§7[§9DushyMC.de§r§7]");
			}
			$prefix->save();

        $this->saveResource("config.yml");
        @mkdir($this->getDataFolder());
        $this->prefix = $prefix->get("Prefix");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info("§4--------------------------------");
        $this->getServer()->getLogger()->info("§7[§9DushyMC.de§r§7] §awurde Aktiviert");
        $this->getServer()->getLogger()->info("§5§lPlugin by Phantom");
        $this->getServer()->getLogger()->info("§4--------------------------------");
		
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            if(empty($config->get("JoinBroadcast"))) {
                $config->set("JoinBroadcast1", "§7=======================");
                $config->set("LEER", "");
                $config->set("JoinBroadcast2", " §8»§6Willkommen auf DushyMC.de");
                $config->set("JoinBroadcast3", " §8»§fWebseite§7 §9DushyMC.de");
                $config->set("JoinBroadcast4", " §8»§fDiscord§7 §9https://discord.gg/zMyF9FV");
                $config->set("LEER2", "");
                $config->set("JoinBroadcast5", "§7=======================");
                $config->set("BlockBreakMessage", " §cDu darfst hier nicht abbauen!");
                $config->set("Hub/Lobby", " §c Willkommen in der Lobby");
                $config->set("JoinTitle", " §7[§a»§7] §aWillkommen");
                $config->set("Prefix", "§7[§9DushyMC.de§r§7]");
				$config->set("Chat", " §7Du musst den Rang §6Premium§7 besitzen um schreiben zu können!");
        }
        $config->save();

        $info = new Config($this->getDataFolder() . "info.yml", Config::YAML);
        if(empty($info->get("infoline1"))){
            $info->set("infoline1", "§7===§7[§f§lDushyMC.de§r§7]===");
            $info->set("infoline2", "§7» §9Bei Weiteren Fragen melde dich im Discord!");
            $info->set("infoline3", "§7» §9https://discord.gg/zMyF9FV");
            $info->set("infoline4", "§7» §9Unsere Webseite findest du unter DushyMC.de!");
            $info->set("infoline5", "§7=================");
            $info->set("Popup", "» §6Vielen Spaß");
        }
        $info->save();

        $LobbyTitle = new Config($this->getDataFolder() . "Title.yml", Config::YAML);
        if(empty($LobbyTitle->get("LobbySendigBackTitle"))){
            $LobbyTitle->set("LobbySendigBackTitle", "§7» §6Lobby");
        }
        $LobbyTitle->save();


    }
    public function onJoin(PlayerJoinEvent $ev) {
		
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $player = $ev->getPlayer();
        $name = $player->getName();
        $player->getInventory()->clearAll();
        $ev->setJoinMessage("§7[§9+§7] ". Color::DARK_GRAY . $name);
        $player->setFood(20);
        $player->setHealth(20);
        $player->setGamemode(0);
        $player->getlevel()->addSound(new AnvilUseSound($player));
        $player->addTitle("§7[§a»§7] §aWllkommen", "");
        $player->sendPopup("§7× §6Willkommen " . Color::WHITE . $player->getDisplayName() . Color::DARK_GRAY . "");
        $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        $player->sendMessage($config->get("JoinBroadcast1"));
        $player->sendMessage($config->get("LEER"));
        $player->sendMessage($config->get("JoinBroadcast2"));
        $player->sendMessage($config->get("JoinBroadcast3"));
        $player->sendMessage($config->get("JoinBroadcast4"));
        $player->sendMessage($config->get("LEER2"));
        $player->sendMessage($config->get("JoinBroadcast5"));

        $player->getInventory()->setSize(9);
        $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
        $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
        $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
        if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
        }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
        }
        $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

    }

    public function onBreak(BlockBreakEvent $ev) {
		
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $player = $ev->getPlayer();
        $ev->setCancelled(true);
        $player->sendMessage($this->prefix . $config->get("BlockBreakMessage"));

    }

    public function onQuit(PlayerQuitEvent $ev) {

        $player = $ev->getPlayer();
        $name = $player->getName();

        $ev->setQuitMessage("");
        $player->sendPopup("§7[§c-§7] ". Color::DARK_GRAY . $name);
    }

    public function onPlace(BlockPlaceEvent $ev) {

        $player = $ev->getPlayer();
        $ev->setCancelled(true);

    }

    public function Hunger(PlayerExhaustEvent $ev) {

        $ev->setCancelled(true);

    }

    public function ItemMove(PlayerDropItemEvent $ev){

        $ev->setCancelled(true);
    }

    public function onConsume(PlayerItemConsumeEvent $ev){

        $ev->setCancelled(true);
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {

        switch($cmd->getName()){

            case "hub";

                $LobbyTitle = new Config($this->getDataFolder() . "Title.yml", Config::YAML);
				$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                $sender->sendMessage($this->prefix . $config->get("Hub/Lobby"));
                $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                $sender->addTitle($LobbyTitle->get("LobbySendigBackTitle"));

            case "lobby";

                $LobbyTitle = new Config($this->getDataFolder() . "Title.yml", Config::YAML);
				$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                $sender->sendMessage($this->prefix . $config->get("Hub/Lobby"));
                $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                $sender->addTitle($LobbyTitle->get("LobbySendigBackTitle"));
                return true;
                
                case "fly";
                
                $sender->setAllowFlight(true);
                $sender->sendMessage($this->prefix . Color::WHITE . " §7Du hast den §fFlugModus§r §7aktiviert.");
                $sender->sendPopup("§fFlugModus§7: §aAktiviert");
                }else{
                $sender->setAllowFlight(false);
                $sender->setFood(20);
                $sender->setHealth(20);
                $sender->sendMessage($this->prefix . Color::WHITE . " §7Du hast den §fFlugModus§r §7Deaktiviert.");
                $sender->sendPopup("§fFlugModus§7: §cDeaktiviert");
            }
        }
    }

    public function onDamage(EntityDamageEvent $ev){

        if($ev->getCause() === EntityDamageEvent::CAUSE_FALL){
            $ev->setCancelled(true);
        }

    }
	
	public function onChat(PlayerChatEvent $ev){
        
        $p = $ev->getPlayer();
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        		
	    if($p->hasPermission("lobby.chat")){
		$ev->setCancelled(false);    
	    }else{
	    	$p->sendMessage($this->prefix . $config->get("Chat"));
	    	$ev->setCancelled(true);
	    }
		
	}

    public function onInteract(PlayerInteractEvent $ev){

        $player = $ev->getPlayer();
        $item = $ev->getItem();
        $info = new Config($this->getDataFolder() . "info.yml", Config::YAML);
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        if($item->getCustomName() == "§aInfos"){
            $player->sendMessage($info->get("infoline1"));
            $player->sendMessage($info->get("infoline2"));
            $player->sendMessage($info->get("infoline3"));
            $player->sendMessage($info->get("infoline4"));
            $player->sendMessage($info->get("infoline5"));
            $player->sendPopup($info->get("Popup"));

        }elseif($item->getCustomName() == "§eTeleporter"){

            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(1, Item::get(2)->setCustomName("§eSkyWars"));
            $player->getInventory()->setItem(2, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(3, Item::get(138)->setCustomName("§bCityBuild"));
            $player->getInventory()->setItem(4, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(5, Item::get(399)->setCustomName("§5Lobby"));
            $player->getInventory()->setItem(6, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§4Soon..."));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));

        }elseif($item->getCustomName() == "§aCosmetics"){

            $player->getlevel()->addSound(new AnvilUseSound($player));
            $player->getInventory()->clearAll();
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§2Boots"));
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName("§7-"));
            }else {
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName("§7-"));
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte §7[§6Premium§7]"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§2Boots §7[§6Premium§7]"));
            }

        }elseif($item->getCustomName() == "§6Effekte"){

            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(264)->setCustomName("§aJumpboost"));
            $player->getInventory()->setItem(1, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(2, Item::get(388)->setCustomName("§3Speedboost"));
            $player->getInventory()->setItem(3, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(4, Item::get(265)->setCustomName("§fUnsichtbar"));
			$player->getInventory()->setItem(6, Item::get(32)->setCustomName("§causschalten"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));

        }elseif($item->getCustomName() == "§eSkyWars"){

            $player->sendMessage("");
            $player->sendMessage($this-> prefix . Color::RED . " §7Du wurdest zu §eSkyWars §7teleportiert");
            $player->teleport(new Vector3(212, 71, 138));
            $player->getlevel()->addSound(new EndermanTeleportSound($player));
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§bCityBuild"){

            $player->sendMessage("");
            $player->sendMessage($this-> prefix . Color::RED . " §7Du wurdest zu §bCityBuild §7teleportiert");
            $player->teleport(new Vector3(212, 71, 138));
            $player->getlevel()->addSound(new EndermanTeleportSound($player));
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§cZurück"){

            $player->getInventory()->clearAll();
			$player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§aJumpboost") {

            $player->removeAllEffects();
            $eff = new EffectInstance(Effect::getEffect(Effect::JUMP) , 500 * 20 , 1 , false);
            $player->addEffect($eff); 
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §aJumpBoost§r §7ausgewählt");
            $player->sendPopup("§8§l»§r §aJumpBoost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§3Speedboost") {

            $player->removeAllEffects();
            $eff = new EffectInstance(Effect::getEffect(Effect::SPEED) , 500 * 20 , 1 , false);
            $player->addEffect($eff);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §3SpeedBoost§r §7ausgewählt");
            $player->sendPopup("§8§l»§r §3Speedboost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§fUnsichtbar"){

            $player->removeAllEffects();
            $eff = new EffectInstance(Effect::getEffect(Effect::INVISIBILITY) , 500 * 20 , 1 , false);
            $player->addEffect($eff);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §fUnsichtbar§r §7ausgewählt");
            $player->sendPopup("§8§l»§r §fUnsichtbar§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("e4Teleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§fFly"){


            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(351, 10)->setCustomName("§aAktivieren"));
            $player->getInventory()->setItem(8, Item::get(351, 8)->setCustomName("§4Deaktivieren"));
            $player->getInventory()->setItem(4, Item::get(351, 1)->setCustomName("§cZurück"));

        }elseif($item->getCustomName() == "§aAktivieren"){

            $player->setAllowFlight(true);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den §fFlugModus§r §7aktiviert.");
            $player->sendPopup("§fFlugModus§7: §aAktiviert");

        }elseif($item->getCustomName() == "§4Deaktivieren"){

            $player->setAllowFlight(false);
            $player->setHealth(20);
            $player->setFood(20);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den §fFlugModus§r §7deaktiviert.");
            $player->sendPopup("§fFlugModus§7: §4Deaktiviert");

        }elseif($item->getCustomName() == "§eSpieler verstecken"){

            $player->getInventory()->setItem(1, Item::get(280)->setCustomName("§eSpieler verstecken"));
            $this->hideall[] = $player;
            $player->sendMessage ($this->prefix . " §7Die Spieler sind jetzt §cUnsichtbar");

        }elseif($item->getCustomName() == "§eSpieler verstecken"){

            unset($this->hideall[array_search($player, $this->hideall)]);
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $player->showPlayer($p);
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));
            $player->sendMessage ($this->prefix . " §7Die Spieler sind jetzt §aSichtbar");

        }elseif($item->getCustomName() == "§2Boots") {
			
            $player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(309)->setCustomName("§7EisenSchuhe"));
			$player->getInventory()->setItem(6, Item::get(32)->setCustomName("§4ausschalten"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));

        }elseif($item->getCustomName() == "§fFly §7[§6Premium§7]"){

            $player->sendMessage($this->prefix . " §7Dieses Funktion duerfen nur §6Premium§7 Spieler verwenden");

        }elseif($item->getCustomName() == "§5Lobby"){

            $player->sendMessage($this->prefix . $config->get("Hub/Lobby"));
            $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
            $player->addTitle("§7» §6Lobby", "");
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));

        }elseif($item->getCustomName() == "§6Effekte §7[§6Premium§7]"){

            $player->sendMessage($this->prefix . " §7Dieses Funktion duerfen nur §6Premium§7 Spieler verwenden");

        }elseif($item->getCustomName() == "§2Boots §7[§6Premium§7]"){

            $player->sendMessage($this->prefix . " §7Dieses Funktion duerfen nur §6Premium§7 Spieler verwenden");
			
        }elseif($item->getCustomName() == "§7EisenSchuhe"){
			
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setBoots(Item::get(Item::IRON_BOOTS));
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§aInfos"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§eTeleporter"));
            $player->getInventory()->setItem(8, Item::get(130)->setCustomName("§aCosmetics"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§fFly"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§eSpieler verstecken"));
			$player->sendMessage($this->prefix . " §7Du hast die §7EisenSchuhe§r §7angezogen");
			
		}elseif($item->getCustomName() == "§causschalten"){
			
			$player->removeAllEffects();
			$player->sendMessage($this->prefix . " §7Du hast alle Effekte §cDeaktiviert§r");
			
		}elseif($item->getCustomName() == "§4ausschalten"){
			
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->sendMessage($this->prefix . " §7Du hast alle Boots §cDeaktiviert§r");
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(309)->setCustomName("§7EisenSchuhe"));
			$player->getInventory()->setItem(6, Item::get(32)->setCustomName("§4ausschalten"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));
			
		}elseif($item->getCustomName() == "§cZurück"){
			
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§2Boots"));
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName(""));
            }else {
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§cZurück"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName(""));
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte §7[§6Premium§7]"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§2Boots §7[§6Premium§7]"));
            }
			
		}

    }

}