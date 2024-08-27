<?php

namespace Dashed\DashedEcommerceExactonline\Controllers;

use Illuminate\Http\Request;
use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedEcommerceExactonline\Classes\Exactonline;
use Dashed\DashedCore\Controllers\Frontend\FrontendController;
use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;

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
