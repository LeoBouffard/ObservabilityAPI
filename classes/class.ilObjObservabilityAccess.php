<?php
class ilObjObservabilityAccess extends ilObjectPluginAccess {

    static function _checkAccess($cmd, $permission, $ref_id, $obj_id, $user_id = null) {
        return true;
    }
}
