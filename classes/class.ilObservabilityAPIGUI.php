<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ObservabilityAPI/classes/class.ilObservabilityAPIHandler.php");
include_once("./Services/Component/classes/class.ilPluginAdmin.php");

class ilObservabilityAPIGUI
{
    protected $tpl;
    protected $handler;

    public function __construct()
    {
        global $tpl;
        $this->tpl = $tpl;

        // Vérifier si le plugin est bien récupéré
        $plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", "ObservabilityAPI");
        if (!$plugin) {
            throw new Exception("Impossible de charger le plugin ObservabilityAPI.");
        }

        $this->handler = new ilObservabilityAPIHandler($plugin);
    }

    public function showHealthStatus()
    {
        try {
            $data = $this->handler->fetchHealthStatus();
            $content = "<h2>Statut du système</h2>";

            if (isset($data['error'])) {
                $content .= "<p style='color:red;'>{$data['error']}</p>";
            } else {
                $content .= "<p><strong>Statut :</strong> {$data['data']['statut']}</p>";
                foreach ($data['data']['services'] as $service) {
                    $content .= "<p><strong>{$service['nom']}</strong>: {$service['statut']} ({$service['tempsReponse']}ms)</p>";
                }
            }

            $this->tpl->setContent($content);
        } catch (Exception $e) {
            $this->tpl->setContent("<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>");
        }
    }

    public function showInfoStatus()
    {
        try {
            $data = $this->handler->fetchInfoStatus();
            $content = "<h2>Informations sur le SI</h2>";

            if (isset($data['error'])) {
                $content .= "<p style='color:red;'>{$data['error']}</p>";
            } else {
                $info = $data['data']['infoSi'];
                $content .= "<p><strong>Nom :</strong> {$info['nom']}</p>";
                $content .= "<p><strong>Trigramme :</strong> {$info['trigramme']}</p>";
                $content .= "<p><strong>Version :</strong> {$info['version']}</p>";
                $content .= "<p><strong>Environnement :</strong> {$data['data']['environnement']}</p>";
                $content .= "<p><strong>Classification :</strong> {$data['data']['classificationMaxDonnees']}</p>";
                $content .= "<p><strong>Direction :</strong> {$data['data']['directionApplication']}</p>";
                $content .= "<p><strong>Type d'Homologation :</strong> {$data['data']['typeHomologation']}</p>";
                $content .= "<p><strong>Fin d'Homologation :</strong> {$data['data']['dateFinHomologation']}</p>";
            }

            $this->tpl->setContent($content);
        } catch (Exception $e) {
            $this->tpl->setContent("<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>");
        }
    }
}
?>
