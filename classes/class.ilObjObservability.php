<?php
require_once("./Services/Object/classes/class.ilObject2.php");

class ilObjObservability extends ilObjectPlugin {

    protected function initType(): void {
        $this->setType("xobs");
    }

    public function doCreate(bool $clone_mode = false): void {
        // Nothing specific to persist
    }

    public function doRead(): void {}

    public function doUpdate(): void {}

    public function doDelete(): void {}

    public function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void{}
}
