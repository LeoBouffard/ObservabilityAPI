<?php
// require_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

class ilObjObservabilityListGUI extends ilObjectPluginListGUI {

    function initType(): void {
        $this->setType("xobs");
    }

    function getGuiClass(): string {
        return "ilObjObservabilityGUI";
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
