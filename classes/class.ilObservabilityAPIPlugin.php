<?php
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Provider\Provider;
use Exception;


include_once("./Services/Component/classes/class.ilPlugin.php");

class ilObservabilityAPIPlugin extends ilPlugin implements Provider
{
    private static $instance = null;

    public static function getInstance(): ilObservabilityAPIPlugin
    {
        global $DIC;

        if (self::$instance === null) {
            self::$instance = $DIC['component.factory']->getPlugin("ObservabilityAPI"); //modifier hier pas encore testé (mais normalement ça marche pas)
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
        return "http://127.0.0.1:8000"; // Modifie si l'API est sur une autre IP
    }


public function addToMenu(): void
{
    global $DIC;

    $factory = new MainMenuItemFactory();


    $identification_factory = $DIC->globalScreen()->identification();

    if (!$DIC->globalScreen()->collector()->mainmenu()) {
        throw new Exception("Impossible d'accéder au menu principal.");
    }

    $plugin_health_id = $identification_factory->plugin(
        "uihk",
        $this,
    )->identifier("plugin_health");
    
    $plugin_info_id = $identification_factory->plugin(
        "uihk",
        $this,
    )->identifier("plugin_info");
    

    // Création de l'item "Observabilité - Health"
    $health_item = $factory->topLinkItem($plugin_health_id)
        ->withTitle("Observabilité - Health")
        ->withAction("./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showHealthStatus");

    // Création de l'item "Observabilité - Info"
    $info_item = $factory->topLinkItem($plugin_info_id)
        ->withTitle("Observabilité - Info")
        ->withAction("./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showInfoStatus");

    // Ajout des éléments au menu
    $DIC->globalScreen()->collector()->mainmenu()->map->add($health_item);
    $DIC->globalScreen()->collector()->mainmenu()->map->add($info_item);
    $DIC->globalScreen()->collector()->mainmenu()->getItemsForUIRepresentation();
}



    /**
     * Méthode appelée après l'activation du plugin
     */
    public function afterActivation(): void
    {
        $this->addToMenu();
    }

    /**
     * Cette méthode doit retourner l'instance de la classe UI pour le plugin.
     * Ajoutez cette méthode dans votre plugin.
     */
    public function getUIClassInstance(): ilObservabilityAPIGUI
    {
        return new ilObservabilityAPIGUI();
    }

}
?>
