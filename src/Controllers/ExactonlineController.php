<?php

namespace Dashed\DashedEcommerceExactonline\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedEcommerceExactonline\Classes\Exactonline;

;
use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;

class ExactonlineController extends Controller
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
