<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ilObjObservabilityAPI extends ilObjectPlugin
{
    private string $api_url_health = 'http://127.0.0.1:8000/health.yaml';
    private string $api_url_info = 'http://127.0.0.1:8000/info.yaml';
    private ?array $cached_data_health = null;
    private ?array $cached_data_info = null;
    
    public function __construct(int $a_id = 0)
    {
        $this->type = "xobs";
        parent::__construct($a_id);
    }
    
    public function initType(): void
    {
        $this->type = "xobs";
    }
    
    protected function doCreate(bool $clone_mode = false): void
    {
        global $DIC;
        
        $DIC->database()->manipulate(
            "INSERT INTO rep_robj_xobs_data (obj_id, api_url_health, api_url_info) VALUES (" .
            $DIC->database()->quote($this->getId(), 'integer') . "," .
            $DIC->database()->quote($this->getApiUrlHealth(), 'text') . "," .
            $DIC->database()->quote($this->getApiUrlInfo(), 'text') . ")"
        );
    }
    
    protected function doRead(): void
    {
        global $DIC;
        
        $set = $DIC->database()->query(
            "SELECT * FROM rep_robj_xobs_data WHERE obj_id = " .
            $DIC->database()->quote($this->getId(), 'integer')
        );
        
        if ($rec = $DIC->database()->fetchAssoc($set)) {
            $this->api_url_health = $rec['api_url_health'] ?? '';
            $this->api_url_info = $rec['api_url_info'] ?? '';
        }
    }
    
    protected function doUpdate(): void
    {
        global $DIC;
        
        $DIC->database()->manipulate(
            "UPDATE rep_robj_xobs_data SET " .
            "api_url_health = " . $DIC->database()->quote($this->api_url_health, 'text') . "," .
            "api_url_info = " . $DIC->database()->quote($this->api_url_info, 'text') . " " .
            "WHERE obj_id = " . $DIC->database()->quote($this->getId(), 'integer')
        );
    }
    
    protected function doDelete(): void
    {
        global $DIC;
        
        $DIC->database()->manipulate(
            "DELETE FROM rep_robj_xobs_data WHERE obj_id = " .
            $DIC->database()->quote($this->getId(), 'integer')
        );
    }
    
    // Getters et Setters
    public function setApiUrlHealth(string $url): void
    {
        $this->api_url_health = $url;
    }
    
    public function getApiUrlHealth(): string
    {
        return $this->api_url_health;
    }
    
    public function setApiUrlInfo(string $url): void
    {
        $this->api_url_info = $url;
    }
    
    public function getApiUrlInfo(): string
    {
        return $this->api_url_info;
    }
    
    // Méthodes pour récupérer les données API
    public function fetchApiDataHealth(): ?array
    {
        if ($this->cached_data_health === null && !empty($this->api_url_health)) {
            $this->cached_data_health = $this->fetchYamlData($this->api_url_health);
        }
        return $this->cached_data_health;
    }
    
    public function fetchApiDataInfo(): ?array
    {
        if ($this->cached_data_info === null && !empty($this->api_url_info)) {
            $this->cached_data_info = $this->fetchYamlData($this->api_url_info);
        }
        return $this->cached_data_info;
    }
    
    private function fetchYamlData(string $url): ?array
{
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'ILIAS ObservabilityAPI Plugin',
                'method' => 'GET',
                'header' => [
                    'Accept: application/x-yaml',
                ]
            ]
        ]);

        $yaml = file_get_contents($url, false, $context);
        if ($yaml === false) {
            return null;
        }

        $data = yaml_parse($yaml);
        return is_array($data) ? $data : null;

    } catch (Exception $e) {
        return null;
    }
}

    
    // Méthode pour rafraîchir le cache
    public function refreshCache(): void
    {
        $this->cached_data_health = null;
        $this->cached_data_info = null;
    }
}
