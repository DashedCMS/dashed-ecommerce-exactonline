<?php

namespace Dashed\DashedEcommerceExactonline;

use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Illuminate\Console\Scheduling\Schedule;
use Dashed\DashedEcommerceCore\Models\Order;
use Dashed\DashedEcommerceCore\Models\Product;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineOrder;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineProduct;
use Dashed\DashedEcommerceExactonline\Livewire\Orders\ShowExactonlineOrder;
use Dashed\DashedEcommerceExactonline\Commands\PushOrdersToExactonlineCommand;
use Dashed\DashedEcommerceExactonline\Commands\RefreshExactonlineTokenCommand;
use Dashed\DashedEcommerceExactonline\Commands\PushProductsToExactonlineCommand;
use Dashed\DashedEcommerceExactonline\Commands\SyncProductsWithExactonlineCommand;
use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;

class DashedEcommerceExactonlineServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-ecommerce-exactonline';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command(RefreshExactonlineTokenCommand::class)
                ->everyMinute()
                ->withoutOverlapping();
            $schedule->command(PushProductsToExactonlineCommand::class)
                ->everyFifteenMinutes()
                ->withoutOverlapping();
            //Only for vat rate atm, but not used
            //                    $schedule->command(SyncProductsWithExactonlineCommand::class)->everyFifteenMinutes();
            $schedule->command(PushOrdersToExactonlineCommand::class)
                ->everyMinute()
                ->withoutOverlapping();
        });

        Livewire::component('show-exactonline-order', ShowExactonlineOrder::class);

        Order::addDynamicRelation('exactonlineOrder', function (Order $model) {
            return $model->hasOne(ExactonlineOrder::class);
        });

        Product::addDynamicRelation('exactonlineProduct', function (Product $model) {
            return $model->hasOne(ExactonlineProduct::class);
        });
        Gate::policy(\Dashed\DashedEcommerceExactonline\Models\ExactonlineProduct::class, \Dashed\DashedEcommerceExactonline\Policies\ExactonlineProductPolicy::class);

        cms()->registerRolePermissions('Integraties', [
            'view_exactonline_product' => 'Exactonline producten bekijken',
            'edit_exactonline_product' => 'Exactonline producten bewerken',
            'delete_exactonline_product' => 'Exactonline producten verwijderen',
        ]);

        cms()->registerResourceDocs(
            resource: \Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource::class,
            title: 'Exact Online producten',
            intro: 'Een overzicht van de producten die met Exact Online gesynchroniseerd worden. Per product zie je de sync status en eventuele foutmeldingen, zodat je meteen weet of de koppeling goed loopt.',
            sections: [
                [
                    'heading' => 'Wat kun je hier doen?',
                    'body' => <<<MARKDOWN
- De sync status per product bekijken.
- Zien welke producten succesvol zijn doorgezet naar Exact Online.
- Foutmeldingen inzien bij producten die vastlopen.
- Snel filteren op producten die nog aandacht nodig hebben.
- Periodiek controleren of alles netjes in sync is.
MARKDOWN,
                ],
                [
                    'heading' => 'Wat is er bijzonder?',
                    'body' => 'Dit overzicht is bewust alleen om te lezen. Je kunt producten hier niet aanpassen of verwijderen, zodat je de sync status altijd betrouwbaar kunt aflezen. Pas producten zelf aan in het reguliere productenscherm.',
                ],
            ],
            tips: [
                'Controleer dit overzicht regelmatig zodat fouten niet opstapelen.',
                'Pak foutmeldingen snel op, vaak gaat het om een kleine oorzaak.',
            ],
        );

        cms()->registerSettingsDocs(
            page: \Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage::class,
            title: 'Exact Online instellingen',
            intro: 'Koppel de webshop met Exact Online voor het automatisch doorboeken van facturen, betalingen en kosten. Per site stel je hier de OAuth gegevens, de juiste administratie en de standaard grootboekrekeningen en producten in. Let op: een aantal velden verschijnt pas nadat de OAuth koppeling is gelegd.',
            sections: [
                [
                    'heading' => 'Wat kun je hier instellen?',
                    'body' => <<<MARKDOWN
Op deze pagina regel je in grote lijnen vier dingen:

1. De OAuth koppeling met Exact Online: client ID, client secret en de division (de administratie).
2. De grootboekrekeningen voor BTW: een voor BTW die jij moet betalen en een voor BTW die jij terugkrijgt.
3. De producten in Exact Online die gebruikt worden voor verzendkosten en betalingskosten.
4. De standaard klant in Exact Online waarop alle webshop bestellingen geboekt worden, meestal een verzamelklant zoals "Webshop klanten".
MARKDOWN,
                ],
                [
                    'heading' => 'Hoe zet je dit op?',
                    'body' => <<<MARKDOWN
De koppeling werkt in twee stappen. Eerst leg je de basis, daarna verschijnen de overige velden.

**Stap 1: OAuth koppeling leggen**

1. Ga naar start.exactonline.nl, log in en open Mijn apps in het developer portaal.
2. Maak een nieuwe app registratie aan voor de webshop.
3. Kopieer de client ID en de client secret.
4. Zoek in Exact Online de division code op (dit is het nummer van de administratie waar je naartoe wilt boeken).
5. Vul client ID, client secret en de division in op deze pagina en sla op.
6. Klik daarna op de knop om de OAuth koppeling te leggen. Je wordt doorgestuurd naar Exact Online om de koppeling goed te keuren.

**Stap 2: rest invullen**

Na een geslaagde OAuth koppeling worden de extra velden zichtbaar. Loop ze een voor een langs:

1. Kies de grootboekrekening voor BTW die betaald moet worden.
2. Kies de grootboekrekening voor BTW die teruggevorderd wordt.
3. Zoek het product op dat je wilt koppelen aan betalingskosten en kies het juiste resultaat. Het Exact product ID wordt automatisch ingevuld.
4. Doe hetzelfde voor het verzendkosten product.
5. Zoek de standaard klant op (vaak een verzamelklant zoals "Webshop klanten") en kies de juiste. Ook hier wordt het customer ID automatisch ingevuld.
6. Sla alles op.
MARKDOWN,
                ],
                [
                    'heading' => 'Wat doet de standaard klant?',
                    'body' => 'Om te voorkomen dat er voor elke webshop bestelling een aparte klant in Exact Online wordt aangemaakt, worden alle bestellingen geboekt op een verzamelklant. Maak daarvoor een klant aan in Exact Online (bijvoorbeeld "Webshop klanten") en koppel die hier. De individuele klantgegevens blijven natuurlijk gewoon zichtbaar in de webshop zelf.',
                ],
            ],
            fields: [
                'Client ID' => 'De client ID uit je app registratie in het Exact Online developer portaal. Dit is een soort gebruikersnaam waarmee de webshop zich aan Exact Online voorstelt.',
                'Client secret' => 'De geheime sleutel die hoort bij de client ID. Deel deze waarde nooit en bewaar hem veilig in een wachtwoordkluis.',
                'Division' => 'Het nummer van de administratie binnen Exact Online waar de webshop naartoe moet boeken. Heb je meerdere administraties? Kies dan het nummer van de juiste, want na koppelen kun je dit niet zomaar aanpassen zonder opnieuw te koppelen.',
                'Grootboekrekening BTW betalen' => 'De grootboekrekening waarop de af te dragen BTW (BTW die jij moet betalen aan de Belastingdienst) wordt geboekt. Dit veld is pas zichtbaar nadat de OAuth koppeling is gelegd.',
                'Grootboekrekening BTW terugvorderen' => 'De grootboekrekening voor BTW die jij terugkrijgt van de Belastingdienst. Ook dit veld verschijnt pas na de OAuth koppeling.',
                'Zoek product betalingskosten' => 'Gebruik dit zoekveld om het product op te zoeken in Exact Online dat hoort bij betalingskosten. Selecteer het juiste resultaat, dan wordt het bijbehorende product ID automatisch ingevuld.',
                'Product ID betalingskosten' => 'Het Exact Online product ID voor betalingskosten. Wordt automatisch gevuld op basis van het zoekveld hierboven, je hoeft hier zelf niets in te typen.',
                'Zoek product verzendkosten' => 'Zoek hier het product op dat in Exact Online hoort bij verzendkosten en kies het juiste resultaat.',
                'Product ID verzendkosten' => 'Het Exact Online product ID voor verzendkosten. Wordt automatisch gevuld op basis van het zoekveld hierboven.',
                'Zoek standaard klant' => 'Zoek hier de klant op waar alle webshop bestellingen op geboekt moeten worden. Meestal is dit een verzamelklant met een naam als "Webshop klanten".',
                'Standaard klant ID' => 'Het Exact Online customer ID van de standaard klant. Wordt automatisch gevuld op basis van het zoekveld hierboven.',
            ],
            tips: [
                'Vul eerst alleen client ID, client secret en division in en sla op. Klik daarna op de OAuth knop. Pas na een geslaagde koppeling verschijnen de velden voor BTW, kostenproducten en standaard klant.',
                'Maak in Exact Online een verzamelklant aan voor de webshop (bijvoorbeeld "Webshop klanten") en koppel die als standaard klant. Zo blijft je klantenkaart in Exact Online overzichtelijk.',
                'Vraag je boekhouder welke grootboekrekeningen je voor BTW moet kiezen en welke producten je voor verzend- en betalingskosten gebruikt. Een verkeerde keuze hier zorgt later voor veel correctieboekingen.',
                'Bewaar je client secret in een wachtwoordkluis. Bij verlies moet je een nieuwe app registratie aanmaken en de OAuth koppeling opnieuw leggen.',
            ],
        );
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        cms()->registerSettingsPage(ExactonlineSettingsPage::class, 'Exactonline', 'archive-box', 'Koppel Exactonline');

        ecommerce()->widgets(
            'orders',
            array_merge(ecommerce()->widgets('orders'), [
                'show-exactonline-order' => [
                    'name' => 'show-exactonline-order',
                    'width' => 'sidebar',
                ],
            ])
        );

        $package
            ->name('dashed-ecommerce-exactonline')
            ->hasViews()
            ->hasRoutes([
                'exactonlineRoutes',
            ])
            ->hasCommands([
                RefreshExactonlineTokenCommand::class,
                PushProductsToExactonlineCommand::class,
                SyncProductsWithExactonlineCommand::class,
                PushOrdersToExactonlineCommand::class,
            ]);

        cms()->builder('plugins', [
            new DashedEcommerceExactonlinePlugin(),
        ]);
    }
}
