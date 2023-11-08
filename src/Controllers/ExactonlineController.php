<?php

namespace Dashed\DashedEcommerceExactonline\Controllers;

use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedCore\Controllers\Frontend\FrontendController;
use Dashed\DashedEcommerceExactonline\Classes\Exactonline;
use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;
use Illuminate\Http\Request;

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
