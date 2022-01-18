<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Filament\Pages\Settings;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceExactonline\Classes\Exactonline;

class ExactonlineSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Exactonline';

    protected static string $view = 'qcommerce-core::settings.pages.default-settings';

    public function mount(): void
    {
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["exactonline_customer_id_{$site['id']}"] = Customsetting::get('exactonline_customer_id', $site['id']);
            $formData["exactonline_client_id_{$site['id']}"] = Customsetting::get('exactonline_client_id', $site['id']);
            $formData["exactonline_client_secret_{$site['id']}"] = Customsetting::get('exactonline_client_secret', $site['id']);
            $formData["exactonline_division_{$site['id']}"] = Customsetting::get('exactonline_division', $site['id']);
            $formData["exactonline_vat_codes_gl_to_pay_{$site['id']}"] = Customsetting::get('exactonline_vat_codes_gl_to_pay', $site['id']);
            $formData["exactonline_vat_codes_gl_to_claim_{$site['id']}"] = Customsetting::get('exactonline_vat_codes_gl_to_claim', $site['id']);
            $formData["exactonline_payment_costs_product_id_{$site['id']}"] = Customsetting::get('exactonline_payment_costs_product_id', $site['id']);
            $formData["exactonline_shipping_costs_product_id_{$site['id']}"] = Customsetting::get('exactonline_shipping_costs_product_id', $site['id']);
            $formData["exactonline_connected_{$site['id']}"] = Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false;
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $schema = [
                Placeholder::make('label')
                    ->label("Exactonline voor {$site['name']}")
                    ->content('Activeer Exactonline.')
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                Placeholder::make('label')
                    ->label("Exactonline is " . (! Customsetting::get('exactonline_connected', $site['id'], 0) ? 'niet' : '') . ' geconnect')
                    ->content(Customsetting::get('exactonline_connection_error', $site['id'], ''))
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextInput::make("exactonline_client_id_{$site['id']}")
                    ->label('Exactonline client ID')
                    ->rules([
                        'max:255',
                    ]),
                TextInput::make("exactonline_client_secret_{$site['id']}")
                    ->label('Exactonline client secret')
                    ->rules([
                        'max:255',
                    ]),
                TextInput::make("exactonline_division_{$site['id']}")
                    ->label('Exactonline division')
                    ->rules([
                        'max:255',
                    ]),
                Select::make("exactonline_vat_codes_gl_to_pay_{$site['id']}")
                    ->label('Exactonline VAT rate GL rekening ID (to pay)')
                    ->required()
                    ->options(collect(Exactonline::getGLAccounts($site['id']))->pluck('Description', 'ID'))
                    ->visible(Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false),
                Select::make("exactonline_vat_codes_gl_to_claim_{$site['id']}")
                    ->label('Exactonline VAT rate GL rekening ID (to claim)')
                    ->required()
                    ->options(collect(Exactonline::getGLAccounts($site['id']))->pluck('Description', 'ID'))
                    ->visible(Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false),
                Select::make("exactonline_payment_costs_product_id_{$site['id']}")
                    ->label('Exactonline product om betalingskosten op te boeken')
                    ->required()
                    ->options(collect(Exactonline::getItems($site['id']))->pluck('Description', 'ID'))
                    ->visible(fn() => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                Select::make("exactonline_shipping_costs_product_id_{$site['id']}")
                    ->label('Exactonline product om verzendkosten op te boeken')
                    ->required()
                    ->options(collect(Exactonline::getItems($site['id']))->pluck('Description', 'ID'))
                    ->visible(fn() => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                Select::make("exactonline_customer_id_{$site['id']}")
                    ->label('Exactonline customer ID (alle bestellingen worden op deze klant geboekt)')
                    ->required()
                    ->options(collect(Exactonline::getItems($site['id']))->pluck('Description', 'ID'))
                    ->visible(fn() => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
//                Select::make("exactonline_customer_id_{$site['id']}")
//                    ->label('Exactonline customer ID (alle bestellingen worden op deze klant geboekt)')
//                    ->required()
//                    ->options(collect(Exactonline::getCustomers($site['id']))->pluck('Name', 'ID'))
//                    ->visible(fn() => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                Placeholder::make("")
                    ->label('Maak de connectie af, bezoek: ' . route('qcommerce.exactonline.authenticate', [$site['id']]))
                    ->hidden(fn() => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($schema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $tabGroups;
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('exactonline_client_id', $this->form->getState()["exactonline_client_id_{$site['id']}"], $site['id']);
            Customsetting::set('exactonline_client_secret', $this->form->getState()["exactonline_client_secret_{$site['id']}"], $site['id']);
            Customsetting::set('exactonline_division', $this->form->getState()["exactonline_division_{$site['id']}"], $site['id']);
            Customsetting::set('exactonline_connected', Exactonline::isConnected($site['id']), $site['id']);

            if (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false) {
                Customsetting::set('exactonline_customer_id', $this->form->getState()["exactonline_customer_id_{$site['id']}"], $site['id']);
                Customsetting::set('exactonline_vat_codes_gl_to_pay', $this->form->getState()["exactonline_vat_codes_gl_to_pay_{$site['id']}"], $site['id']);
                Customsetting::set('exactonline_vat_codes_gl_to_claim', $this->form->getState()["exactonline_vat_codes_gl_to_claim_{$site['id']}"], $site['id']);
                Customsetting::set('exactonline_payment_costs_product_id', $this->form->getState()["exactonline_payment_costs_product_id_{$site['id']}"], $site['id']);
                Customsetting::set('exactonline_shipping_costs_product_id', $this->form->getState()["exactonline_shipping_costs_product_id_{$site['id']}"], $site['id']);
            }
        }

        $this->notify('success', 'De Exactonline instellingen zijn opgeslagen');

        return redirect(ExactonlineSettingsPage::getUrl());
    }
}
