<?php

include_once("./Services/Component/classes/class.ilPlugin.php");

class ilObservabilityAPIPlugin extends ilPlugin
{
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
        $ilMainMenu->addItem(
            "Observabilité - Health",
            "observability_health",
            "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ObservabilityAPI/classes/class.ilObservabilityAPIGUI.php?cmd=showHealthStatus"
        );
        $ilMainMenu->addItem(
            "Observabilité - Info",
            "observability_info",
            "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ObservabilityAPI/classes/class.ilObservabilityAPIGUI.php?cmd=showInfoStatus"
        );
    }

    /**
     * Méthode appelée après l'activation du plugin
     */
    protected function afterActivation(): void
    {
        $this->addToMenu();
    }
}
?>
