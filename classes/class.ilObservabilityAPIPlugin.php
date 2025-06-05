<?php
declare(strict_types=1);

class ilObservabilityAPIPlugin extends ilRepositoryObjectPlugin
{
    const PLUGIN_ID = "xobs";
    const PLUGIN_NAME = "ObservabilityAPI";
    
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }
    
    protected function uninstallCustom(): void
    {
        // Nettoyage lors de la désinstallation
        if ($this->db->tableExists('rep_robj_xobs_data')) {
            $this->db->dropTable('rep_robj_xobs_data');
        }
    }
}
