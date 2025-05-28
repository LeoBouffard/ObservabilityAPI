<?php
// require_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

class ilObservabilityAPIPlugin extends ilRepositoryObjectPlugin {
    function getPluginName(): string {
        return "ObservabilityAPI";
    }

    protected function uninstallCustom(): void {
        // Optional cleanup
    }
}
