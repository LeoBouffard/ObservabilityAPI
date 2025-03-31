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
        global $ilCtrl;

        $ilMainMenu->addItem(
            "Observabilité - Health",
            "observability_health",
            $ilCtrl->getLinkTargetByClass(ilObservabilityAPIGUI::class, "showHealthStatus")
        );

        $ilMainMenu->addItem(
            "Observabilité - Info",
            "observability_info",
            $ilCtrl->getLinkTargetByClass(ilObservabilityAPIGUI::class, "showInfoStatus")        );
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
