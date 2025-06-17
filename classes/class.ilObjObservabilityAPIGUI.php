<?php

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * This class handles the creation of a Observability repository object.
 * It also serves as the entry point of all repository controllers.
 *
 * @ilCtrl_isCalledBy ilObjObservabilityAPIGUI: ilObjPluginDispatchGUI
 * @ilCtrl_isCalledBy ilObjObservabilityAPIGUI: ilAdministrationGUI
 * @ilCtrl_isCalledBy ilObjObservabilityAPIGUI: ilRepositoryGUI
 *
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilObjectCopyGUI
 */
class ilObjObservabilityAPIGUI extends ilObjectPluginGUI
{
    public const CMD_VIEW = 'view';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_REFRESH = 'refresh';
    public const CMD_EDIT_PERMISSIONS = 'perm';

    public function __construct(int $a_ref_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
    }

    public static function getStartCmd(): string
    {
        return self::CMD_VIEW;
    }

    public final function getType(): string
    {
        return ilObservabilityAPIPlugin::PLUGIN_ID;
    }

    public function executeCommand(): void
    {
        $next_class = strtolower($this->ctrl->getNextClass());
        $cmd = $this->ctrl->getCmd(self::getStartCmd());

        switch ($next_class) {
            case strtolower(ilInfoScreenGUI::class):
                $this->ctrl->forwardCommand(new ilInfoScreenGUI($this));
                return;

            case strtolower(ilPermissionGUI::class):
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                return;

            case strtolower(ilObjectCopyGUI::class):
                $this->ctrl->forwardCommand(new ilObjectCopyGUI($this));
                return;

            default:
                // Liste blanche des commandes autorisées
                $allowed_cmds = [
                    self::CMD_VIEW,
                    self::CMD_EDIT,
                    self::CMD_UPDATE,
                    self::CMD_REFRESH,
                ];

                if (in_array($cmd, $allowed_cmds, true)) {
                    $this->performCommand($cmd);
                } else {
                    parent::executeCommand();
                }
        }
    }


    public function performCommand(string $cmd): void
    {
        if (method_exists($this, $cmd)) {
            $this->$cmd();
        }
    }

    public function view(): void
    {
        global $DIC;

        $this->setTabs();
        $this->tabs_gui->activateTab('view');

        $tpl = new ilTemplate('tpl.content.html', true, true, $this->plugin->getDirectory());
        var_dump($tpl);
        $data1 = $this->object->fetchApiData1();
        $data2 = $this->object->fetchApiData2();
        var_dump($data1, $data2);

        $tpl->setVariable("TXT_API_DATA_SECTION_1", $this->plugin->txt("observability_metrics"));
        $tpl->setVariable("TXT_API_DATA_SECTION_2", $this->plugin->txt("system_status"));
        $tpl->setVariable("TXT_NO_DATA_AVAILABLE", $this->plugin->txt("no_data_available"));

        $has_data = false;

        if ($data1) {
            $tpl->setCurrentBlock("api_data_1");
            $tpl->setVariable("API_DATA_1", $this->formatObservabilityData($data1, "metrics"));
            $tpl->parseCurrentBlock();
            $has_data = true;
        }

        if ($data2) {
            $tpl->setCurrentBlock("api_data_2");
            $tpl->setVariable("API_DATA_2", $this->formatObservabilityData($data2, "status"));
            $tpl->parseCurrentBlock();
            $has_data = true;
        }

        if (!$has_data) {
            $tpl->setCurrentBlock("no_data");
            $tpl->parseCurrentBlock();
        }

        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
            $toolbar = $DIC->toolbar();
            $toolbar->addComponent(
                $DIC->ui()->factory()->button()->standard(
                    $this->plugin->txt("refresh_data"),
                    $this->ctrl->getLinkTarget($this, self::CMD_REFRESH)
                )
            );
        }

        $DIC->ui()->mainTemplate()->setContent($tpl->get());
    }

    public function edit(): void
    {
        $this->setTabs();
        $this->tabs_gui->activateTab("settings");

        $form = $this->initEditForm();
        global $DIC;
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    public function update(): void
    {
        global $DIC;

        $form = $this->initEditForm();
        if ($form->checkInput()) {
            $this->object->setApiUrl1($form->getInput("api_url_1"));
            $this->object->setApiUrl2($form->getInput("api_url_2"));
            $this->object->update();
            $this->object->refreshCache();

            $DIC->ctrl()->redirect($this, self::CMD_VIEW);
        } else {
            $form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($form->getHTML());
        }
    }

    public function refresh(): void
    {
        $this->object->refreshCache();
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        global $DIC;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->plugin->txt("edit_settings"));

        $url1 = new ilTextInputGUI($this->plugin->txt("metrics_api_url"), "api_url_1");
        $url1->setValue($this->object->getApiUrl1());
        $url1->setRequired(true);
        $url1->setInfo($this->plugin->txt("metrics_api_url_info"));
        $form->addItem($url1);

        $url2 = new ilTextInputGUI($this->plugin->txt("status_api_url"), "api_url_2");
        $url2->setValue($this->object->getApiUrl2());
        $url2->setInfo($this->plugin->txt("status_api_url_info"));
        $form->addItem($url2);

        $form->addCommandButton(self::CMD_UPDATE, $DIC->language()->txt("save"));

        return $form;
    }

    protected function setTabs(): void
    {
        global $DIC;

        $DIC->tabs()->addTab("view", $DIC->language()->txt("view"),
            $this->ctrl->getLinkTarget($this, self::CMD_VIEW));

        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
            $DIC->tabs()->addTab("settings", $DIC->language()->txt("settings"),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT));
        }
    }

    private function formatObservabilityData(array $data, string $type): string
    {
        $html = "<div class='observability-data observability-{$type}'>";

        if ($type === "metrics") {
            $html .= $this->formatMetricsData($data);
        } elseif ($type === "status") {
            $html .= $this->formatStatusData($data);
        } else {
            $html .= "<pre class='json-data'>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }

        $html .= "</div>";
        return $html;
    }

    private function formatMetricsData(array $data): string
    {
        $html = "<div class='metrics-grid'>";

        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $html .= "<div class='metric-card'>";
                $html .= "<div class='metric-label'>" . htmlspecialchars($key) . "</div>";
                $html .= "<div class='metric-value'>" . number_format($value, 2) . "</div>";
                $html .= "</div>";
            } elseif (is_array($value)) {
                $html .= "<div class='metric-section'>";
                $html .= "<h4>" . htmlspecialchars($key) . "</h4>";
                $html .= "<pre>" . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                $html .= "</div>";
            }
        }

        $html .= "</div>";
        return $html;
    }

    private function formatStatusData(array $data): string
    {
        $html = "<div class='status-info'>";

        foreach ($data as $key => $value) {
            $html .= "<div class='status-item'>";
            $html .= "<span class='status-key'>" . htmlspecialchars($key) . ":</span> ";

            if (is_bool($value)) {
                $status_class = $value ? "status-ok" : "status-error";
                $status_text = $value ? "OK" : "ERROR";
                $html .= "<span class='{$status_class}'>{$status_text}</span>";
            } elseif (is_string($value) || is_numeric($value)) {
                $html .= "<span class='status-value'>" . htmlspecialchars($value) . "</span>";
            } else {
                $html .= "<pre>" . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
            }

            $html .= "</div>";
        }

        $html .= "</div>";
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getAfterCreationCmd(): string
    {
        return self::getStartCmd();
    }

    /**
     * @inheritDoc
     */
    public function getStandardCmd(): string
    {
        return self::getStartCmd();
    }
}
