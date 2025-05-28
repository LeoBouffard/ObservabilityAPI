<?php
require_once("./Services/Object/classes/class.ilObject2.php");

class ilObjObservability extends ilObjectPlugin {

    protected function initType() {
        $this->setType("xobs");
    }

    public function doCreate() {
        // Nothing specific to persist
    }

    public function doRead() {}

    public function doUpdate() {}

    public function doDelete() {
        return true;
    }

    public function doCloneObject($new_obj) {}
}
