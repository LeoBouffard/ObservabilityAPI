<?php
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\UI\Component\MainControls\MainBar\StandardManager;
use Exception;
use ReflectionClass;

/**
 * @param \ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector $main_menu
 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map\Map
 */
function getMapFromMainMenu($main_menu): \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map\Map
{
    $reflection = new ReflectionClass($main_menu);
    $property = $reflection->getProperty('map');
    $property->setAccessible(true);
    return $property->getValue($main_menu);
}

include_once("./Services/Component/classes/class.ilPlugin.php");

class ilObservabilityAPIPlugin extends ilPlugin implements Provider
{
    private static $instance = null;

    public static function getInstance(): ilObservabilityAPIPlugin
    {
        global $DIC;

        if (self::$instance === null) {
            self::$instance = $DIC['component.factory']->getPlugin("ObservabilityAPI");
        }

        return self::$instance;
    }

    public function getProviderNameForPresentation(): string
    {
        return $this->getPluginName();
    }

    public function getFullyQualifiedClassName(): string
    {
        return $this->getPluginName();
    }

    function getPluginName(): string
    {
        return "ObservabilityAPI";
    }

    function getApiUrl(): string
    {
        return "http://127.0.0.1:8000";
    }

    public function addToMenu(): void
    {
        global $DIC;

        $factory = new MainMenuItemFactory();
        $identification_factory = $DIC->globalScreen()->identification();

        if (!$DIC->globalScreen()->collector()->mainmenu()) {
            throw new Exception("Impossible d'accéder au menu principal.");
        }

        $plugin_health_id = $identification_factory->plugin("uihk", $this)->identifier("plugin_health");
        $plugin_info_id   = $identification_factory->plugin("uihk", $this)->identifier("plugin_info");

        $health_item = $factory->topLinkItem($plugin_health_id)
            ->withTitle("Observabilité - Health")
            ->withAction("./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showHealthStatus");

        $info_item = $factory->topLinkItem($plugin_info_id)
            ->withTitle("Observabilité - Info")
            ->withAction("./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showInfoStatus");

        $map = getMapFromMainMenu($DIC->globalScreen()->collector()->mainmenu());

        $map->add($health_item);
        $map->add($info_item);

        // Appel à la main bar
        /** @var StandardManager $main_bar */
        $main_bar = $DIC->maincontrols()->mainbar();
        if ($main_bar) {
            $main_bar->add($health_item);
            $main_bar->add($info_item);
        }

        // Préparation du menu (optionnelle selon version)
        $DIC->globalScreen()->collector()->mainmenu()->collectStructure();
        $DIC->globalScreen()->collector()->mainmenu()->filterItemsByVisibilty();
        $DIC->globalScreen()->collector()->mainmenu()->prepareItemsForUIRepresentation();
        $DIC->globalScreen()->collector()->mainmenu()->cleanupItemsForUIRepresentation();
        $DIC->globalScreen()->collector()->mainmenu()->sortItemsForUIRepresentation();
    }

    /**
     * Ne plus appeler manuellement addToMenu ici
     */
    public function afterActivation(): void
    {
        // Rien ici : on injecte proprement via getGlobalScreenProvider()
    }

    /**
     * Cette méthode doit retourner l'instance de la classe UI pour le plugin.
     */
    public function getUIClassInstance(): ilObservabilityAPIGUI
    {
        return new ilObservabilityAPIGUI();
    }

    /**
     * Fournisseur GlobalScreen propre (remplace afterActivation)
     */
    public function getGlobalScreenProvider(): array
    {
        return [new class($this) implements Provider {
            private ilObservabilityAPIPlugin $plugin;

            public function __construct(ilObservabilityAPIPlugin $plugin)
            {
                $this->plugin = $plugin;
            }

            public function getProviderNameForPresentation(): string
            {
                return $this->plugin->getPluginName();
            }

            public function getFullyQualifiedClassName(): string
            {
                return __CLASS__;
            }

            public function collect(\ILIAS\GlobalScreen\Services $services): void
            {
                $this->plugin->addToMenu();
            }
        }];
    }
}
?>
