<?php

include_once("./Services/Component/classes/class.ilPlugin.php");

class ilObservabilityAPIPlugin extends ilPlugin
{

    public function init()
    {
        $plugin = new ilObservabilityAPIPlugin();
        $plugin->addToMenu();
    }

    function getPluginName(): string
    {
        return "ObservabilityAPI";
    }
}
