<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Controllers;

use Illuminate\Http\Request;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Controllers\Frontend\FrontendController;
use Qubiqx\QcommerceEcommerceExactonline\Classes\Exactonline;
use Qubiqx\QcommerceEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;

class ExactonlineController extends FrontendController
{
    public function authenticate($siteId = null)
    {
        $site = Sites::get($siteId);

        return Exactonline::authenticate($site['id']);
    }

    public function saveAuthentication(Request $request, $siteId = null)
    {
        $site = Sites::get($siteId);

        $code = $request->code;
        if ($code) {
            Exactonline::saveAuthentication($code, $site['id']);
        }

        return redirect(ExactonlineSettingsPage::getUrl());
    }
}
