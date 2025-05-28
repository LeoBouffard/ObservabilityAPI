<?php
// require_once("./Services/Object/classes/class.ilObjectPluginGUI.php");

class ilObjObservabilityAPIGUI extends ilObjectPluginGUI {

    function getType(): string {
        return "xobs";
    }

    protected function afterSave(ilObject $new_object): void {
        global $ilCtrl;
        $ilCtrl->setParameter($this, "ref_id", $this->ref_id);
        $ilCtrl->redirect($this, "pageOne");
    }

    public function getStandardCmd(): string {
        return "pageOne";
    }

    public function getAfterCreationCmd(): string {
        return "pageOne";
    }

    protected function setTabs(): void {
        global $ilTabs;
        $this->addInfoTab();
        $this->addPermissionTab();

        $ilTabs->addTab("pageOne", "Page One", $this->ctrl->getLinkTarget($this, "pageOne"));
        $ilTabs->addTab("pageTwo", "Page Two", $this->ctrl->getLinkTarget($this, "pageTwo"));
    }

    public function performCommand($cmd): void {
        switch ($cmd) {
            case "pageOne":
            case "pageTwo":
                $this->$cmd();
                break;
        }
    }

    protected function pageOne() {
        global $tpl;

        $json = @file_get_contents("http://127.0.0.1:8000/info");
        $data = json_decode($json, true);
        $html = "<h2>Données Page One</h2><pre>" . print_r($data, true) . "</pre>";

        $tpl->setContent($html);
    }

    protected function pageTwo() {
        global $tpl;

        $json = @file_get_contents("http://127.0.0.1:8000/health");
        $data = json_decode($json, true);
        $html = "<h2>Données Page Two</h2><pre>" . print_r($data, true) . "</pre>";

        $tpl->setContent($html);
    }
}
