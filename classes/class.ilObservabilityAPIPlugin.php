<?php

include_once("./Services/Component/classes/class.ilPlugin.php");

class ilObservabilityAPIPlugin extends ilPlugin
{
    private static $instance = null;

    public static function getInstance(): ilObservabilityAPIPlugin
    {
        global $DIC;

        if (null === self::$instance) {
            return self::$instance = ilPluginAdmin::getPluginInfo(
                "Services",
                "UIComponent",
                "uihk",
                "ObservabilityAPI"
            );
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
        global $ilMainMenu;

        if (!isset($ilMainMenu)) {
            throw new Exception("Impossible d'accéder au menu principal.");
        }

        $ilMainMenu->addItem(
            "Observabilité - Health",
            "observability_health",
            "./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showHealthStatus"
        );
        $ilMainMenu->addItem(
            "Observabilité - Info",
            "observability_info",
            "./ilias.php?baseClass=ilObservabilityAPIGUI&cmd=showInfoStatus"
        );
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
