<?php

class ilObservabilityAPIRouterGUI
{
    public function executeCommand()
    {
        $cmd = $_GET['cmd'] ?? 'showHealthStatus';
        $gui = new ilObservabilityAPIGUI();

        switch ($cmd) {
            case 'showHealthStatus':
                $gui->showHealthStatus();
                break;
            case 'showInfoStatus':
                $gui->showInfoStatus();
                break;
            default:
                $gui->showHealthStatus();
        }
    }
}
