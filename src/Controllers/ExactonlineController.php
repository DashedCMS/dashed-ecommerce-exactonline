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

        // Verifieer de OAuth-state tegen de sessie (CSRF-bescherming).
        $expectedState = session()->pull('exactonline_oauth_state');
        if (! $expectedState || ! hash_equals($expectedState, (string) $request->state)) {
            return redirect(ExactonlineSettingsPage::getUrl())->with('error', 'Ongeldige OAuth state.');
        }

        $code = $request->code;
        if ($code) {
            Exactonline::saveAuthentication($code, $site['id']);
        }

        return redirect(ExactonlineSettingsPage::getUrl());
    }
}
