<?php

require_once('./Services/Object/classes/class.ilObjectPluginListGUI.php');

/**
 * List GUI class for ObservabilityAPI repository objects
 */
class ilObjObservabilityAPIListGUI extends ilObjectPluginListGUI
{
    /**
     * Retourne le nom de la classe GUI principale
     */
    public function getGuiClass(): string
    {
        return ilObjObservabilityAPIGUI::class;
    }

    /**
     * Affiche des propriétés dans la liste, comme "offline"
     */
    public function getProperties(): array
    {
        if (ilObjObservabilityAPIAccess::_isOffline($this->obj_id)) {
            return [
                [
                    "property" => $this->plugin->txt("status"),
                    "value"    => $this->plugin->txt("offline"),
                    "alert"    => true,
                ],
            ];
        }

        return [];
    }

    /**
     * Commandes disponibles dans le menu contextuel
     */
    public function initCommands(): array
    {
        $this->commands_enabled = true;
        $this->copy_enabled = true;
        $this->description_enabled = true;
        $this->notice_properties_enabled = true;
        $this->properties_enabled = true;

        // Toutes les autres options sont désactivées
        $this->comments_enabled = false;
        $this->comments_settings_enabled = false;
        $this->expand_enabled = false;
        $this->info_screen_enabled = false;
        $this->notes_enabled = false;
        $this->preconditions_enabled = false;
        $this->rating_enabled = false;
        $this->rating_categories_enabled = false;
        $this->repository_transfer_enabled = false;
        $this->search_fragment_enabled = false;
        $this->static_link_enabled = false;
        $this->tags_enabled = false;
        $this->timings_enabled = false;

        return [
            [
                "cmd"        => ilObjObservabilityAPIGUI::getStartCmd(),
                "permission" => "read",
                "default"    => true,
            ]
        ];
    }

    /**
     * Génère les liens pour les commandes du menu contextuel
     */
    public function getCommandLink($cmd): string
    {
        if ($cmd === ilObjObservabilityAPIGUI::getStartCmd()) {
            return $this->ctrl->getLinkTargetByClass(
                [ilObjPluginDispatchGUI::class, ilObjObservabilityAPIGUI::class],
                $cmd
            );
        }

        return parent::getCommandLink($cmd);
    }

    /**
     * Initialise le type d’objet (doit correspondre au plugin ID)
     */
    public function initType(): void
    {
        $this->setType(ilObservabilityAPIPlugin::PLUGIN_ID);
    }
}
