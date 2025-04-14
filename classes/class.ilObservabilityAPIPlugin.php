<?php
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use Exception;


include_once("./Services/Component/classes/class.ilPlugin.php");

class ilObservabilityAPIPlugin extends ilPlugin
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

    if (!$DIC->globalScreen()->collector()->mainmenu()) {
        throw new Exception("Impossible d'accéder au menu principal.");
    }

    // Création de l'item "Observabilité - Health"
    $health_item = $factory->topLinkItem(IdentificationInterface::ILIAS("observability_health"))
        ->withTitle("Observabilité - Health")
        ->withActionUrl("./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showHealthStatus");

    // Création de l'item "Observabilité - Info"
    $info_item = $factory->topLinkItem(IdentificationInterface::ILIAS("observability_info"))
        ->withTitle("Observabilité - Info")
        ->withActionUrl("./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showInfoStatus");

    // Ajout des éléments au menu
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
