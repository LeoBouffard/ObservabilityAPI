<?php

include_once("./Customizing/global/plugins/Services/ApiComponent/Observability/ObservabilityAPI/classes/class.ilObservabilityAPIHandler.php");

class ilObservabilityAPIGUI
{
    protected $tpl;
    protected $handler;

    public function __construct()
    {
        global $tpl;
        $this->tpl = $tpl;
        $plugin = new ilObservabilityAPIPlugin();
        $this->handler = new ilObservabilityAPIHandler($plugin);
    }

    public function showHealthStatus()
    {
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
    }

    public function showInfoStatus()
    {
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
    }

}
