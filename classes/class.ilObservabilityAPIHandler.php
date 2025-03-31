<?php

class ilObservabilityAPIHandler
{
    private $plugin;

    public function __construct(ilObservabilityAPIPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    private function fetchData(string $endpoint): array
    {
        $api_url = $this->plugin->getApiUrl() . $endpoint;

        $response = @file_get_contents($api_url);
        if ($response === FALSE) {
            return ["error" => "Impossible de récupérer les données de l'API ($api_url)"];
        }

        return json_decode($response, true) ?? ["error" => "Réponse invalide de l'API"];
    }

    public function fetchHealthStatus(): array
    {
        return $this->fetchData("/health");
    }

    public function fetchInfoStatus(): array
    {
        return $this->fetchData("/info");
    }
}
?>
