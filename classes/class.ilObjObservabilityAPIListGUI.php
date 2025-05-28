<?php
// require_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

class ilObjObservabilityAPIListGUI extends ilObjectPluginListGUI {

    function initType(): void {
        $this->setType("xobs");
    }

    function getGuiClass(): string {
        return "ilObjObservabilityAPIGUI";
    }

    public function initCommands(): array {
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
