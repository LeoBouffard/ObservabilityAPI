<?php
require_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

class ilObjObservabilityListGUI extends ilObjectPluginListGUI {

    function initType() {
        $this->setType("xobs");
    }

    function getGuiClass() {
        return "ilObjObservabilityGUI";
    }

    protected function initCommands() {
        return [
            [
                "permission" => "read",
                "cmd" => "pageOne",
                "lang_var" => "show",
                "default" => true
            ]
        ];
    }
}
