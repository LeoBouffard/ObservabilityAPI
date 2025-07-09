<?php

/**
 * Access class for ObservabilityAPI object
 */
class ilObjObservabilityAPIAccess extends ilObjectPluginAccess
{
    /**
     * Vérifie si l'objet est "offline"
     */
    public static function _isOffline(int $obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            'SELECT * FROM rep_robj_xobs_data WHERE obj_id = %s',
            ['integer'],
            [$obj_id]
        );

        $row = $ilDB->fetchAssoc($result);

        // Si aucune donnée n'est trouvée, on considère que l'objet est "offline"
        return $row === false;
    }

    /**
     * Vérifie les droits d'accès sur l'objet
     */
    public function _checkAccess(
        string $cmd,
        string $permission,
        int $ref_id,
        int $obj_id,
        int $user_id = null
    ): bool {
        $user_id = $user_id ?? $this->user->getId();

        // Lire un objet offline est interdit sauf si on a le droit d'écriture
        if (in_array($permission, ['read', 'visible'], true)) {
            if ($this->access->checkAccessOfUser($user_id, "write", "", $ref_id)) {
                return true;
            }

            if (!self::_isOffline($obj_id)) {
                return $this->access->checkAccessOfUser($user_id, $permission, "", $ref_id);
            }

            return false;
        }

        if ($permission === "delete") {
            return $this->access->checkAccessOfUser($user_id, "delete", "", $ref_id)
                || $this->access->checkAccessOfUser($user_id, "write", "", $ref_id);
        }

        // Par défaut, on délègue à ILIAS
        return (bool) $this->access->checkAccessOfUser($user_id, $permission, "", $ref_id);
    }
}
