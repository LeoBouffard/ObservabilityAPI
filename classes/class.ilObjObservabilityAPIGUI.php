<?php

/**
 * This class handles the creation of a H5P repository object.
 * For this plugin, this class also serves as the entry-point
 * of all 'repository' controllers.
 *
 * When implementing a new controller which should be reachable by any
 * other repository controller, this class must be extended with an
 * according @ilCtrl_Calls statement. If the controller should also be
 * reachable from the administration, do the same in
 *
 * NOTE that commands routing via this class MUST NEVER be
 * named 'save', otherwise the parent class will invoke its
 * own save method since executeCommand is not overwritten.
 *
 * @ilCtrl_isCalledBy ilObjObservabilityAPIGUI: ilAdministrationGUI
 * @ilCtrl_isCalledBy ilObjObservabilityAPIGUI: ilRepositoryGUI
 *
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjObservabilityAPIGUI: ilObjectCopyGUI
 *
 * @author            Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection      AutoloadingIssuesInspection
 */
class ilObjObservabilityAPIGUI extends ilObjectPluginGUI
{
    public function __construct($a_data = null, int $a_id = 0, bool $a_call_by_reference = true)
    {
        parent::__construct($a_data, $a_id, $a_call_by_reference);
    }

    function getType(): string {
        return "xobs";
    }

    public function getStandardCmd(): string {
        echo "getStandardCmd() is deprecated, use getStartCmd() instead.";
        return "pageOne";
    }

    public function getAfterCreationCmd(): string {
        return "pageOne";
    }

    public function performCommand($cmd): void {
        switch ($cmd) {
            case "pageOne":
            case "pageTwo":
                $this->$cmd();
                break;
        }
    }
    
    public function executeCommand(): void
    {
        global $DIC;
        
        $next_class = $DIC->ctrl()->getNextClass($this);
        $cmd = $DIC->ctrl()->getCmd("view");
        
        switch ($next_class) {
            default:
                if (in_array($cmd, ["view", "edit", "update", "refresh"])) {
                    $this->performCommand($cmd);
                }
                break;
        }
    }
    
    public function view(): void
    {
        global $DIC;
        
        $this->setTabs();
        $this->tabs_gui->activateTab("view");
        
        $tpl = new ilTemplate("tpl.content.html", true, true, $this->plugin->getDirectory());
        
        // Récupération des données API
        $data1 = $this->object->fetchApiData1();
        $data2 = $this->object->fetchApiData2();
        
        // Configuration des variables de template
        $tpl->setVariable("TXT_API_DATA_SECTION_1", $this->plugin->txt("observability_metrics"));
        $tpl->setVariable("TXT_API_DATA_SECTION_2", $this->plugin->txt("system_status"));
        $tpl->setVariable("TXT_NO_DATA_AVAILABLE", $this->plugin->txt("no_data_available"));
        
        $has_data = false;
        
        // Affichage des données API 1
        if ($data1) {
            $tpl->setCurrentBlock("api_data_1");
            $tpl->setVariable("API_DATA_1", $this->formatObservabilityData($data1, "metrics"));
            $tpl->parseCurrentBlock();
            $has_data = true;
        }
        
        // Affichage des données API 2
        if ($data2) {
            $tpl->setCurrentBlock("api_data_2");
            $tpl->setVariable("API_DATA_2", $this->formatObservabilityData($data2, "status"));
            $tpl->parseCurrentBlock();
            $has_data = true;
        }
        
        // Si aucune donnée
        if (!$has_data) {
            $tpl->setCurrentBlock("no_data");
            $tpl->parseCurrentBlock();
        }
        
        // Bouton de rafraîchissement
        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
            $toolbar = $DIC->toolbar();
            $toolbar->addComponent(
                $DIC->ui()->factory()->button()->standard(
                    $this->plugin->txt("refresh_data"),
                    $DIC->ctrl()->getLinkTarget($this, "refresh")
                )
            );
        }
        
        $DIC->ui()->mainTemplate()->setContent($tpl->get());
    }
    
    protected function refresh(): void
    {
        global $DIC;
        
        $this->object->refreshCache();
        $DIC->ctrl()->redirect($this, "view");
    }
    
    public function edit(): void
    {
        $this->setTabs();
        $this->tabs_gui->activateTab("settings");
        
        $form = $this->initEditForm();
        global $DIC;
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function initEditForm(): ilPropertyFormGUI
    {
        global $DIC;
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->setTitle($this->plugin->txt("edit_settings"));
        
        // URL API 1 - Métriques
        $url1 = new ilTextInputGUI($this->plugin->txt("metrics_api_url"), "api_url_1");
        $url1->setValue($this->object->getApiUrl1());
        $url1->setRequired(true);
        $url1->setInfo($this->plugin->txt("metrics_api_url_info"));
        $form->addItem($url1);
        
        // URL API 2 - Status
        $url2 = new ilTextInputGUI($this->plugin->txt("status_api_url"), "api_url_2");
        $url2->setValue($this->object->getApiUrl2());
        $url2->setInfo($this->plugin->txt("status_api_url_info"));
        $form->addItem($url2);
        
        $form->addCommandButton("update", $DIC->language()->txt("save"));
        
        return $form;
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
            
            $DIC->ctrl()->redirect($this, "view");
        } else {
            $form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($form->getHTML());
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
            // Format générique JSON
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
    
    protected function setTabs(): void
    {
        global $DIC;
        
        $DIC->tabs()->addTab("view", $DIC->language()->txt("view"), 
            $DIC->ctrl()->getLinkTarget($this, "view"));
            
        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
            $DIC->tabs()->addTab("settings", $DIC->language()->txt("settings"), 
                $DIC->ctrl()->getLinkTarget($this, "edit"));
        }
    }
}
