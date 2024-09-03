<?php

namespace Dashed\DashedEcommerceExactonline\Filament\Pages\Settings;

use Closure;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Dashed\DashedCore\Classes\Sites;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedEcommerceExactonline\Classes\Exactonline;

class ExactonlineSettingsPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Exactonline';

    protected static string $view = 'dashed-core::settings.pages.default-settings';
    public array $data = [];

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

            //            dd(count(Exactonline::getGLAccounts($site['id'])),Exactonline::getItems($site['id']),Exactonline::getCustomers($site['id']));
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $GLAccounts = Exactonline::getGLAccounts($site['id']);
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
                    ->maxLength(255),
                TextInput::make("exactonline_client_secret_{$site['id']}")
                    ->label('Exactonline client secret')
                    ->maxLength(255),
                TextInput::make("exactonline_division_{$site['id']}")
                    ->label('Exactonline division')
                    ->maxLength(255),
                Select::make("exactonline_vat_codes_gl_to_pay_{$site['id']}")
                    ->label('Exactonline VAT rate GL rekening ID (to pay)')
                    ->required()
                    ->options(collect($GLAccounts)->pluck('Description', 'ID'))
                    ->visible(Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false),
                Select::make("exactonline_vat_codes_gl_to_claim_{$site['id']}")
                    ->label('Exactonline VAT rate GL rekening ID (to claim)')
                    ->required()
                    ->options(collect($GLAccounts)->pluck('Description', 'ID'))
                    ->visible(Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false),
                TextInput::make("exactonline_payment_costs_search_product_id_{$site['id']}")
                    ->label('Zoek product voor betalingskosten')
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) use ($site) {
                        $response = Exactonline::getItems($site['id'], $state);
                        if ($response[0] ?? false) {
                            $set('exactonline_payment_costs_product_id_' . $site['id'], $response[0]['ID']);
                        }
                    })
                    ->helperText('Indien er een product gevonden is wordt het volgende veld automatisch ingevuld')
                    ->visible(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                TextInput::make("exactonline_payment_costs_product_id_{$site['id']}")
                    ->label('Exactonline product om betalingskosten op te boeken')
                    ->required()
                    ->reactive()
                    ->visible(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                TextInput::make("exactonline_shipping_costs_search_product_id_{$site['id']}")
                    ->label('Zoek product voor verzendkosten')
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) use ($site) {
                        $response = Exactonline::getItems($site['id'], $state);
                        if ($response[0] ?? false) {
                            $set('exactonline_shipping_costs_product_id_' . $site['id'], $response[0]['ID']);
                        }
                    })
                    ->helperText('Indien er een product gevonden is wordt het volgende veld automatisch ingevuld')
                    ->visible(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                TextInput::make("exactonline_shipping_costs_product_id_{$site['id']}")
                    ->label('Exactonline product om verzendkosten op te boeken')
                    ->required()
                    ->reactive()
                    ->visible(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                TextInput::make("exactonline_search_customer_id_{$site['id']}")
                    ->label('Zoek klant voor alle bestellingen')
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) use ($site) {
                        $response = Exactonline::getCustomers($site['id'], $state);
                        if ($response[0] ?? false) {
                            $set('exactonline_customer_id_' . $site['id'], $response[0]['ID']);
                        }
                    })
                    ->helperText('Indien er een klant gevonden is wordt het volgende veld automatisch ingevuld')
                    ->visible(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                TextInput::make("exactonline_customer_id_{$site['id']}")
                    ->label('Exactonline customer ID (alle bestellingen worden op deze klant geboekt)')
                    ->required()
                    ->reactive()
                    ->visible(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
                Placeholder::make("")
                    ->label('Maak de connectie af, bezoek: ' . route('dashed.exactonline.authenticate', [$site['id']]))
                    ->hidden(fn () => (Customsetting::get('exactonline_connected', $site['id'], 0) ? true : false)),
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

    public function getFormStatePath(): ?string
    {
        return 'data';
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

        Notification::make()
            ->title('De Exactonline instellingen zijn opgeslagen')
            ->success()
            ->send();

        return redirect(ExactonlineSettingsPage::getUrl());
    }
}
