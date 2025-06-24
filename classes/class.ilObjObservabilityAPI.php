<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ilObjObservabilityAPI extends ilObjectPlugin
{
    private string $api_url_1 = 'http://127.0.0.1:8000/health';
    private string $api_url_2 = 'http://127.0.0.1:8000/info';
    private ?array $cached_data_1 = null;
    private ?array $cached_data_2 = null;
    
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
            "INSERT INTO rep_robj_xobs_data (obj_id, api_url_1, api_url_2) VALUES (" .
            $DIC->database()->quote($this->getId(), 'integer') . "," .
            $DIC->database()->quote($this->getApiUrl1(), 'text') . "," .
            $DIC->database()->quote($this->getApiUrl2(), 'text') . ")"
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
            $this->api_url_1 = $rec['api_url_1'] ?? '';
            $this->api_url_2 = $rec['api_url_2'] ?? '';
        }
    }
    
    protected function doUpdate(): void
    {
        global $DIC;
        
        $DIC->database()->manipulate(
            "UPDATE rep_robj_xobs_data SET " .
            "api_url_1 = " . $DIC->database()->quote($this->api_url_1, 'text') . "," .
            "api_url_2 = " . $DIC->database()->quote($this->api_url_2, 'text') . " " .
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
    public function setApiUrl1(string $url): void
    {
        $this->api_url_1 = $url;
    }
    
    public function getApiUrl1(): string
    {
        return $this->api_url_1;
    }
    
    public function setApiUrl2(string $url): void
    {
        $this->api_url_2 = $url;
    }
    
    public function getApiUrl2(): string
    {
        return $this->api_url_2;
    }
    
    // Méthodes pour récupérer les données API
    public function fetchApiData1(): ?array
    {
        if ($this->cached_data_1 === null && !empty($this->api_url_1)) {
            $this->cached_data_1 = $this->fetchJsonData($this->api_url_1);
        }
        return $this->cached_data_1;
    }
    
    public function fetchApiData2(): ?array
    {
        if ($this->cached_data_2 === null && !empty($this->api_url_2)) {
            $this->cached_data_2 = $this->fetchJsonData($this->api_url_2);
        }
        return $this->cached_data_2;
    }
    
    private function fetchJsonData(string $url): ?array
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'ILIAS ObservabilityAPI Plugin',
                    'method' => 'GET',
                    'header' => [
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ]
                ]
            ]);
            
            $json = file_get_contents($url, false, $context);
            if ($json === false) {
                return null;
            }
            
            $data = json_decode($json, true);
            return $data ?: null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Méthode pour rafraîchir le cache
    public function refreshCache(): void
    {
        $this->cached_data_1 = null;
        $this->cached_data_2 = null;
    }
}
