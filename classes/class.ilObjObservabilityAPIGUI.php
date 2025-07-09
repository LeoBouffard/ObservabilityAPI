<?php

declare(strict_types=1);

use \ILIAS\UI\Component\Input\Container\Form\Standard;
use Symfony\Component\Yaml\Yaml;



/**
 * This class handles the creation of a Observability repository object.
 * It also serves as the entry point of all repository controllers.
 *
 * @ilCtrl_isCalledBy ilObjObservabilityAPIGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI
 */
class ilObjObservabilityAPIGUI extends ilObjectPluginGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    public ilGlobalTemplateInterface $tpl;

    protected function afterConstructor(): void
    {
        global $ilCtrl, $ilTabs, $tpl;
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->tpl = $tpl;
    }

    public final function getType(): string
    {
        return ilObservabilityAPIPlugin::PLUGIN_ID;
    }


    public function performCommand(string $cmd): void
    {
        $this->setTitleAndDescription();
        switch ($cmd) {
            case "editProperties":
            case "updateProperties":
            case "saveProperties":
                $this->checkPermission("write");
                $this->$cmd();
                break;
            case "showContent":
            default:
                $this->checkPermission("read");
                $this->$cmd();
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAfterCreationCmd(): string
    {
        return "editProperties";
    }

    /**
     * @inheritDoc
     */
    public function getStandardCmd(): string
    {
        return "showContent";
    }

    protected function setTabs(): void
    {
        global $ilCtrl, $ilAccess;

        if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "view",
                "view",
                $ilCtrl->getLinkTarget($this, "showContent")
            );
        }

        $this->addInfoTab();

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "settings",
                "settings",
                $ilCtrl->getLinkTarget($this, "editProperties")
            );
        }

        $this->addPermissionTab();
        $this->activateTab();
    }
    /**
     * We need this method if we can't access the tabs otherwise...
     */
    private function activateTab(): void
    {
        $next_class = $this->ctrl->getCmdClass();
    }


    public function editProperties(): void
    {
        $this->tabs_gui->activateTab("settings");

        $form = $this->initPropertiesForm();
        global $DIC;
        $renderer = $DIC->ui()->renderer();
        $this->tpl->setContent($renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    protected function initPropertiesForm(): Standard
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        $url_health = $ui->input()->field()->text($this->plugin->txt("metrics_api_url"))
            ->withRequired(true)
            ->withValue($this->object->getApiUrlHealth())
            ->withByline($this->plugin->txt("metrics_api_url_info"));

        $url_info = $ui->input()->field()->text($this->plugin->txt("status_api_url"))
            ->withRequired(true)
            ->withValue($this->object->getApiUrlInfo())
            ->withByline($this->plugin->txt("status_api_url_info"));

        $form_action = $ctrl->getFormAction($this, "saveProperties");
        $form_fields = [
            "api_url_health" => $url_health,
            "api_url_info" => $url_info
        ];

        return $ui->input()->container()->form()->standard($form_action, $form_fields);
    }

    /**
     * @throws ilCtrlException
     */
    protected function saveProperties(): void
    {
        global $DIC;
        $request = $DIC->http()->request();
        $form = $this->initPropertiesForm();

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $result = $form->getData();
            $this->object->setApiUrlHealth($result["api_url_health"]);
            $this->object->setApiUrlInfo($result["api_url_info"]);
            $this->object->update();
            $this->tpl->setOnScreenMessage("success", $this->plugin->txt("update_successful"), true);
            $this->ctrl->redirect($this, "editProperties");
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
            $yaml = Yaml::dump($data, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
            $html .= "<pre class='yaml-data'>" . htmlspecialchars($yaml) . "</pre>";
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
                $yaml = Yaml::dump($value, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
                $html .= "<pre class='yaml-data'>" . htmlspecialchars($yaml) . "</pre>";
                $html .= "</div>";
            }
        }

        $html .= "</div>";
        return $html;
    }

    private function formatStatusData(array $data): string
    {
        $html = "<div class='status-info'>";
        var_dump($data); // Debugging line to check the structure of $data

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
                $yaml = Yaml::dump($value, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
                $html .= "<pre class='yaml-data'>" . htmlspecialchars($yaml) . "</pre>";
            }

            $html .= "</div>";
        }

        $html .= "</div>";
        return $html;
    }

    public function showContent(): void
    {
        /**
         * @var $DIC \ILIAS\DI\Container
         */
        global $DIC;

        $tpl = $DIC['tpl'];
        $this->tabs_gui->activateTab('view');

        $info = new ilTemplate('tpl.content.html', true, true, $this->plugin->getDirectory());
        
        $data_health = $this->object->fetchApiDataHealth();
        $data_info = $this->object->fetchApiDataInfo();
        
        
        $info->setVariable("TXT_API_DATA_SECTION_HEALTH", $this->plugin->txt("observability_metrics"));
        $info->setVariable("TXT_API_DATA_SECTION_INFO", $this->plugin->txt("system_status"));


        if ($data_health) {
            $info->setCurrentBlock("api_data_health");
            $info->setVariable("API_DATA_HEALTH", $this->formatObservabilityData($data_health, "metrics"));
            $info->parseCurrentBlock();
        }

        if ($data_info) {
            $info->setCurrentBlock("api_data_info");
            $info->setVariable("API_DATA_INFO", $this->formatObservabilityData($data_info, "status"));
            $info->parseCurrentBlock();
        }

        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
            $toolbar = $DIC->toolbar();
            $toolbar->addComponent(
                $DIC->ui()->factory()->button()->standard(
                    $this->plugin->txt("refresh_data"),
                    $this->ctrl->getLinkTarget($this, "showContent")
                )
            );
        }

        $tpl->setContent($info->get());
    }

    
}
