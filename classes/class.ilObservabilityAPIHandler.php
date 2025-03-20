<?php

class ilObservabilityAPIHandler
{
    private $plugin;

    public function __construct(ilObservabilityAPIPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function fetchHealthStatus(): array
    {
        $api_url = $this->plugin->getApiUrl() . "/health";
        
        $response = file_get_contents($api_url);
        if ($response === FALSE) {
            return ["error" => "Impossible de récupérer les données de l'API"];
        }

        return json_decode($response, true);
    }

    public function fetchInfoStatus(): array
    {
        $api_url = $this->plugin->getApiUrl() . "/info";
        
        $response = file_get_contents($api_url);
        if ($response === FALSE) {
            return ["error" => "Impossible de récupérer les données de l'API"];
        }

        return json_decode($response, true);
    }
}
