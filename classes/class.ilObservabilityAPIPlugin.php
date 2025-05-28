<?php
// require_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

class ilObservabilityAPIPlugin extends ilRepositoryObjectPlugin {
    function getPluginName(): string {
        return "Observability";
    }

    protected function uninstallCustom(): void {
        // Optional cleanup
    }
}
