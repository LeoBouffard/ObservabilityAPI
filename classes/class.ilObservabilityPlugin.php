<?php
require_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

class ilObservabilityPlugin extends ilRepositoryObjectPlugin {
    function getPluginName() {
        return "Observability";
    }

    protected function uninstallCustom() {
        // Optional cleanup
    }
}
