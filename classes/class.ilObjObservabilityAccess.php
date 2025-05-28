<?php
class ilObjObservabilityAccess extends ilObjectPluginAccess {

    public static function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool {
        return true;
    }

}
