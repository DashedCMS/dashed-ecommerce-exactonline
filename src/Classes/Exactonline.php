<?php

namespace Dashed\DashedEcommerceExactonline\Classes;

use Exception;
use Dashed\DashedCore\Classes\Mails;
use Dashed\DashedCore\Classes\Sites;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedEcommerceCore\Models\Product;

class Exactonline
{
    public static function isConnected($siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        return Customsetting::get('exactonline_connected', $siteId, false);
    }

    public static function authenticate($siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        return redirect('https://start.exactonline.nl/api/oauth2/auth?client_id=' . Customsetting::get('exactonline_client_id', $siteId) . '&redirect_uri=' . route('dashed.exactonline.save-authentication', $siteId) . '&response_type=code&force_login=0');
    }

    public static function saveAuthentication($code, $siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        try {
            $ch = curl_init();

            if ($ch === false) {
                throw new Exception('failed to initialize');
            }

            curl_setopt_array($ch, [
                CURLOPT_URL => "https://start.exactonline.nl/api/oauth2/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "code=" . $code . "&redirect_uri=" . route('dashed.exactonline.save-authentication', $siteId) . "&grant_type=authorization_code&client_id=" . Customsetting::get('exactonline_client_id', $siteId) . "&client_secret=" . Customsetting::get('exactonline_client_secret', $siteId),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/x-www-form-urlencoded",
                    "Accept: application/json",
                ],
            ]);

            $content = curl_exec($ch);

            if ($content === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            $response = json_decode($content, true);

            Customsetting::set('exactonline_access_token', $response['access_token'], $siteId);
            Customsetting::set('exactonline_refresh_token', $response['refresh_token'], $siteId);
            Customsetting::set('exactonline_connected', true, $siteId);
            Customsetting::set('exactonline_notified_of_logout', false, $siteId);
            Mails::sendNotificationToAdmins('Exact is succesvol gekoppeld aan het CMS');

            Notification::make()
                ->title('Exact is succesvol gekoppeld aan het CMS')
                ->success()
                ->send();
        } catch (Exception $e) {
            Mails::sendNotificationToAdmins('Exact kon niet gekoppeld worden aan het CMS');
            Customsetting::set('exactonline_connected', false, $siteId);
            Customsetting::set('exactonline_access_token', null, $siteId);
            Customsetting::set('exactonline_refresh_token', null, $siteId);

            Notification::make()
                ->title('Exact kon niet gekoppeld worden aan het CMS')
                ->danger()
                ->send();

            //            trigger_error(
            //                sprintf(
            //                    'Curl failed with error #%d: %s',
            //                    $e->getCode(),
            //                    $e->getMessage()
            //                ),
            //                E_USER_ERROR
            //            );
        }

        return redirect()->route('filament.dashed.pages.dashboard');
    }

    public static function refreshToken($siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        //        if (Customsetting::get('exactonline_connected', $siteId)) {
        //            try {
        $ch = curl_init();

        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://start.exactonline.nl/api/oauth2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "refresh_token=" . Customsetting::get('exactonline_refresh_token', $siteId) . "&grant_type=refresh_token&client_id=" . Customsetting::get('exactonline_client_id', $siteId) . "&client_secret=" . Customsetting::get('exactonline_client_secret', $siteId),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
                "Accept: application/json",
            ],
        ]);

        $content = curl_exec($ch);

        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $response = json_decode($content, true);

        if (! isset($response['access_token'])) {
            if (isset($response['error_description']) && $response['error_description'] == 'Rate limit exceeded: access_token not expired') {
                dump('rate limit, do nothing');

                return;
            }
            Customsetting::set('exactonline_connected', false, $siteId);
            Customsetting::set('exactonline_access_token', null, $siteId);
            Customsetting::set('exactonline_refresh_token', null, $siteId);
            if (! Customsetting::get('exactonline_notified_of_logout', $siteId, false)) {
                Mails::sendNotificationToAdmins('Exact is uitgelogd en moet opnieuw worden gekoppeld in Dashed');
                Customsetting::set('exactonline_notified_of_logout', true, $siteId);
            }
        } else {
            Customsetting::set('exactonline_access_token', $response['access_token'], $siteId);
            Customsetting::set('exactonline_refresh_token', $response['refresh_token'], $siteId);
            Customsetting::set('exactonline_connected', true, $siteId);
            Customsetting::set('exactonline_notified_of_logout', false, $siteId);
        }
        //            } catch (Exception $e) {
        //                Customsetting::set('exactonline_connected', false, $siteId);
        //                Customsetting::set('exactonline_access_token', null, $siteId);
        //                Customsetting::set('exactonline_refresh_token', null, $siteId);
        //                Mails::sendNotificationToAdmins('Exact is uitgelogd en moet opnieuw worden gekoppeld in Dashed');
        //                trigger_error(
        //                    sprintf(
        //                        'Curl failed with error #%d: %s',
        //                        $e->getCode(),
        //                        $e->getMessage()
        //                    ),
        //                    E_USER_ERROR
        //                );
        //            }
        //        }
    }

    public static function getDivision($siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        try {
            $ch = curl_init();

            if ($ch === false) {
                throw new Exception('failed to initialize');
            }

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://start.exactonline.nl/api/v1/current/Me?$select=CurrentDivision',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/x-www-form-urlencoded",
                    "Accept: application/json",
                    "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
                ],
            ]);

            $content = curl_exec($ch);

            if ($content === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            $content = json_decode($content, true);
            $division = $content['d']['results'][0]['CurrentDivision'];
            Customsetting::set('exactonline_division', $division, $siteId);

            return $division;
        } catch (Exception $e) {
            trigger_error(
                sprintf(
                    'Curl failed with error #%d: %s',
                    $e->getCode(),
                    $e->getMessage()
                ),
                E_USER_ERROR
            );
        }
    }

    public static function pushProduct(Product $product, $siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        if (! self::isConnected($siteId) || ($product->exactonlineProduct && $product->exactonlineProduct->exactonline_id)) {
            return;
        }

        //        try {

        $data = [
            'Code' => $product->sku,
            'Description' => $product->name,
            'Barcode' => $product->ean,
        ];

        $content = Http::withHeaders([
            'Content-type' => "application/json",
            'Accept' => "application/json",
            'Authorization' => 'Bearer ' . Customsetting::get('exactonline_access_token', $siteId) . 's',
        ])
            ->post('https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/logistics/Items', $data)
            ->json();

        $exactonlineProduct = $product->exactonlineProduct ?: $product->exactonlineProduct()->create();

        if (! isset($content['d'])) {
            if (($content['error']['message']['value'] ?? '') == 'Gegeven bestaat reeds.') {
                $content = Http::withHeaders([
                    'Content-type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . Customsetting::get('exactonline_access_token', $siteId) . 's',
                ])
                    ->get('https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/logistics/Items?$filter=Barcode eq \'' . $product->ean . '\'')
                    ->json();

                $exactonlineProduct->error = 'Er is iets fout gegaan';
                $exactonlineProduct->save();

                return;
            } else {
                $exactonlineProduct->error = $content['error']['message']['value'] ?? 'Er is iets fout gegaan';
                $exactonlineProduct->save();

                return;
            }
        } else {
            $id = $content['d']['ID'];
            $exactonlineProduct->exactonline_id = $id;
            $exactonlineProduct->error = '';
            $exactonlineProduct->save();

            return $content['d'];
        }
        //        } catch (Exception $e) {
        //            trigger_error(
        //                sprintf(
        //                    'Curl failed with error #%d: %s',
        //                    $e->getCode(),
        //                    $e->getMessage()
        //                ),
        //                E_USER_ERROR
        //            );
        //        }
    }

    public static function syncProduct(Product $product, $siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        if (! $product->exactonlineProduct || ! $product->exactonlineProduct->exactonline_id) {
            return;
        }

        //        try {
        $ch = curl_init();

        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        $data = [
            'Code' => $product->sku,
            'Description' => $product->name,
            'Barcode' => $product->ean,
        ];

        $data = json_encode($data);

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/logistics/Items(guid\'' . $product->exactonlineProduct->exactonline_id . '\')?$select=SalesVatCode',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "Accept: application/json",
                "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
            ],
        ]);

        $content = curl_exec($ch);

        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $content = json_decode($content, true);

        $product->exactonlineProduct->vat_code_id = $content['d']['SalesVatCode'];
        $product->exactonlineProduct->save();

        //        } catch (Exception $e) {
        //            trigger_error(
        //                sprintf(
        //                    'Curl failed with error #%d: %s',
        //                    $e->getCode(),
        //                    $e->getMessage()
        //                ),
        //                E_USER_ERROR
        //            );
        //        }
    }

    public static function getCustomers($siteId = null, ?string $search)
    {
        if (! $search) {
            return;
        }

        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        //        try {
        //        $ch = curl_init();
        //
        //        if ($ch === false) {
        //            throw new Exception('failed to initialize');
        //        }

        return Http::withHeaders([
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ])
            ->withToken(Customsetting::get('exactonline_access_token', $siteId))
            ->get('https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/crm/Accounts?$select=ID,Accountant,AccountManager,AccountManagerFullName,CreatorFullName,Email,Name&$filter=Name eq \'' . $search . '\'')
            ->json()['d']['results'] ?? [];
        //        dump($response->body(), $response->status());

        //        curl_setopt_array($ch, [
        //            CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/crm/Accounts?$select=ID,Accountant,AccountManager,AccountManagerFullName,CreatorFullName,Email,Name&$filter=Name eq \'Scooperz\'',
        //            CURLOPT_RETURNTRANSFER => true,
        //            CURLOPT_ENCODING => "",
        //            CURLOPT_MAXREDIRS => 10,
        //            CURLOPT_TIMEOUT => 0,
        //            CURLOPT_FOLLOWLOCATION => true,
        //            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //            CURLOPT_CUSTOMREQUEST => "GET",
        //            CURLOPT_HTTPHEADER => [
        //                "Content-Type: application/json",
        //                "Accept: application/json",
        //                "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
        //            ],
        //        ]);
        //
        //        $content = curl_exec($ch);
        //
        //        if ($content === false) {
        //            throw new Exception(curl_error($ch), curl_errno($ch));
        //        }
        //        curl_close($ch);
        //
        //        curl_getinfo($ch);
        //        curl_errno($ch);
        //        $content = curl_exec($ch);
        //        $content = json_decode($content, true);
        //        dd($content);

        //        dump($response);
        return $response['d']['results'] ?? [];

        return $content['d']['results'] ?? [];
        //        } catch (Exception $e) {
        //            trigger_error(
        //                sprintf(
        //                    'Curl failed with error #%d: %s',
        //                    $e->getCode(),
        //                    $e->getMessage()
        //                ),
        //                E_USER_ERROR
        //            );
        //        }
    }

    public static function getGLAccounts($siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        try {
            $content = Http::withHeaders([
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                "Authorization" => "Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
            ])
                ->get('https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/financial/GLAccounts?$select=ID,Code,Description')
                ->json();

            $results = $content['d']['results'] ?? [];
            $nextUrl = $content['d']['__next'] ?? '';
            while ($nextUrl) {
                $content = Http::withHeaders([
                    "Content-Type" => "application/json",
                    "Accept" => "application/json",
                    "Authorization" => "Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
                ])
                    ->get($nextUrl)
                    ->json();

                $results = array_merge($results, $content['d']['results']);
                $nextUrl = $content['d']['__next'] ?? '';
            }

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function getItems($siteId = null, ?string $search): array
    {
        if (! $search) {
            return [];
        }

        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        return Http::withHeaders([
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ])
            ->withToken(Customsetting::get('exactonline_access_token', $siteId))
            ->get('https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/logistics/Items?$select=ID,Code,Description&$filter=Code eq \'' . $search . '\'')
            ->json()['d']['results'] ?? [];
        //        dd($response->json(), $response->status());

        //        try {
        //        $ch = curl_init();
        //
        //        if ($ch === false) {
        //            throw new Exception('failed to initialize');
        //        }
        //
        //        curl_setopt_array($ch, [
        ////            CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/financial/GLAccounts?$select=ID,Code,Description',
        //            CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/logistics/Items?$select=ID,Code,Description',
        //            CURLOPT_RETURNTRANSFER => true,
        //            CURLOPT_ENCODING => "",
        //            CURLOPT_MAXREDIRS => 10,
        //            CURLOPT_TIMEOUT => 0,
        //            CURLOPT_FOLLOWLOCATION => true,
        //            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //            CURLOPT_CUSTOMREQUEST => "GET",
        //            CURLOPT_HTTPHEADER => [
        //                "Content-Type: application/json",
        //                "Accept: application/json",
        //                "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
        //            ],
        //        ]);
        //
        //        $content = curl_exec($ch);
        //
        //        if ($content === false) {
        //            throw new Exception(curl_error($ch), curl_errno($ch));
        //        }
        //        curl_close($ch);
        //        $content = json_decode($content, true);
        //
        //        $results = $content['d']['results'] ?? [];
        //        $nextUrl = $content['d']['__next'] ?? '';
        //        while ($nextUrl) {
        //            $ch = curl_init();
        //
        //            if ($ch === false) {
        //                throw new Exception('failed to initialize');
        //            }
        //
        //            curl_setopt_array($ch, [
        //                CURLOPT_URL => $nextUrl,
        //                CURLOPT_RETURNTRANSFER => true,
        //                CURLOPT_ENCODING => "",
        //                CURLOPT_MAXREDIRS => 10,
        //                CURLOPT_TIMEOUT => 0,
        //                CURLOPT_FOLLOWLOCATION => true,
        //                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //                CURLOPT_CUSTOMREQUEST => "GET",
        //                CURLOPT_HTTPHEADER => [
        //                    "Content-Type: application/json",
        //                    "Accept: application/json",
        //                    "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
        //                ],
        //            ]);
        //
        //            $content = curl_exec($ch);
        //
        //            if ($content === false) {
        //                throw new Exception(curl_error($ch), curl_errno($ch));
        //            }
        //            curl_close($ch);
        //            $content = json_decode($content, true);
        //            $results = array_merge($results, $content['d']['results']);
        //            $nextUrl = $content['d']['__next'] ?? '';
        //        }
        //
        //        return $results;
        //        } catch (Exception $e) {
        //            trigger_error(
        //                sprintf(
        //                    'Curl failed with error #%d: %s',
        //                    $e->getCode(),
        //                    $e->getMessage()
        //                ),
        //                E_USER_ERROR
        //            );
        //        }
    }

    public static function pushOrder($order): array
    {
        if (! self::isConnected($order->site_short) || ! $order->exactonlineOrder || $order->exactonlineOrder->pushed == 1) {
            return [
                'success' => false,
                'error' => 'Exactonline is not connected or order is already pushed',
            ];
        }

        foreach ($order->orderProducts as $orderProduct) {
            if ($orderProduct->product && ! $orderProduct->product->exactonlineProduct || ! $orderProduct->product->exactonlineProduct->exactonline_id) {
                $order->exactonlineOrder->pushed = 2;
                $order->exactonlineOrder->error = 'Product ' . $orderProduct->product->name . ' is not pushed to Exactonline yet';
                $order->exactonlineOrder->save();

                throw new Exception('Product ' . $orderProduct->product->name . ' is not pushed to Exactonline yet');
            }
        }

        $exactCustomerId = Customsetting::get('exactonline_customer_id', $order->site_short);
        if ($exactCustomerId && ($order->isPaidFor() || $order->isReturnStatus())) {
            //            try {

            $discount = $order->discount;

            $salesOrderLines = [];
            foreach ($order->orderProducts as $orderProduct) {
                if ($orderProduct->product && $orderProduct->product->is_bundle) {
                    $productsPrice = 0;
                    foreach ($orderProduct->product->bundleProducts as $bundleProduct) {
                        $productsPrice += $bundleProduct->currentPrice * $orderProduct->quantity;
                    }
                    $discount += $productsPrice - $orderProduct->product->currentPrice;

                    continue;
                }

                $vatRate = $orderProduct->vat_rate;
                $vatCodeId = null;
                $exactonlineProductId = null;

                if ($orderProduct->product) {
                    //                    $vatCodeId = $orderProduct->product ? $orderProduct->product->exactonlineProduct->vat_code_id : null;
                    $exactonlineProductId = $orderProduct->product->exactonlineProduct->exactonline_id;
                }

                if ($orderProduct->sku == 'payment_costs') {
                    $exactonlineProductId = Customsetting::get('exactonline_payment_costs_product_id', $order->site_short);
                    if (! $exactonlineProductId) {
                        $order->exactonlineOrder->pushed = 2;
                        $order->exactonlineOrder->error = 'Betaalmethode kosten zit nog niet aan een product in Exactonline gekoppeld';
                        $order->exactonlineOrder->save();
                        Mails::sendNotificationToAdmins('Order #' . $order->id . ' failed to push to Exactonline');

                        return [
                            'success' => false,
                            'error' => 'Payment costs not linked to product in Exactonline',
                        ];
                    }
                } elseif ($orderProduct->sku == 'shipping_costs') {
                    $exactonlineProductId = Customsetting::get('exactonline_shipping_costs_product_id', $order->site_short);
                    if (! $exactonlineProductId) {
                        $order->exactonlineOrder->pushed = 2;
                        $order->exactonlineOrder->error = 'Verzend kosten zit nog niet aan een product in Exactonline gekoppeld';
                        $order->exactonlineOrder->save();
                        Mails::sendNotificationToAdmins('Order #' . $order->id . ' failed to push to Exactonline');

                        return [
                            'success' => false,
                            'error' => 'Shipping costs not linked to product in Exactonline',
                        ];
                    }
                }

                if (! $vatCodeId) {
                    $vatCodeId = self::getVatCodeIdForVateRate($vatRate, $order->site_short);
                }

                $data = [
                    'NetPrice' => $orderProduct->priceWithoutDiscount / $orderProduct->quantity,
                    'Item' => $exactonlineProductId,
                    'Description' => $orderProduct->name,
                    'VATAmount' => (float)number_format($orderProduct->vatWithoutDiscount / $orderProduct->quantity, 2),
//                    'VATPercentage' => $orderProduct->vat_rate,
                    'VATCode' => $vatCodeId,
                    'Quantity' => $orderProduct->quantity,
                ];
                $salesOrderLines[] = $data;
            }

            //            if ($order->payment_costs > 0.00) {
            //                $taxTotal = ($order->payment_costs / 121 * 21);
            //
            //                //Receive vat codes from exact
            //                //Check if correct vat code is present
            //                //If not, create it, and link it
            //                $data = [
            //                    'NetPrice' => $order->payment_costs,
            //                    'Item' => null,
            //                    'Description' => 'Betaalmethode kosten',
            //                    'VATAmount' => $taxTotal,
            //                    'VATCode' => 4,
            //                    'Quantity' => $orderProduct->quantity
            //                ];
            //                $salesOrderLines[] = $data;
            //            }

            $ch = curl_init();

            if ($ch === false) {
                throw new Exception('failed to initialize');
            }

            $data = [
                'OrderedBy' => $exactCustomerId,
                'OrderDate' => $order->created_at->format('Y-m-d'),
                'YourRef' => 'Order #' . $order->invoice_id,
                'Description' => 'Order #' . $order->invoice_id,
                'OrderNumber' => $order->id,
                'SalesOrderLines' => $salesOrderLines,
                'AmountDiscount' => $discount,
            ];
            $data = json_encode($data);

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $order->site_short) . '/salesorder/SalesOrders',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => [
                    "Content-type: application/json",
                    "Accept: application/json",
                    "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $order->site_short) . 's',
                ],
            ]);

            $content = curl_exec($ch);

            if ($content === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);
            $content = json_decode($content, true);
            //            dd($content);
            if (isset($content['d'])) {
                $id = $content['d']['OrderID'];

                $order->exactonlineOrder->pushed = 1;
                $order->exactonlineOrder->error = null;
                $order->exactonlineOrder->exactonline_id = $id;
                $order->exactonlineOrder->save();

                return [
                    'success' => true,
                    'order' => $content['d'],
                    'orderId' => $id,
                ];
            } else {
                dd($content, $salesOrderLines);
                if (($content['error']['message']['value'] ?? '') == 'Verkoopordernummer: Bestaat reeds') {
                    $order->exactonlineOrder->pushed = 1;
                    $order->exactonlineOrder->error = $content ? $content['error']['message']['value'] : 'Geen error teruggegeven';
                    $order->exactonlineOrder->save();

                    return [
                        'success' => true,
                        'error' => $content ? $content['error']['message']['value'] : 'Geen error teruggegeven',
                    ];
                } else {
                    if ($order->exactonlineOrder->pushed != 2) {
                        Mails::sendNotificationToAdmins('Order #' . $order->id . ' failed to push to Exactonline');
                    }

                    $order->exactonlineOrder->pushed = 2;
                    $order->exactonlineOrder->error = $content ? $content['error']['message']['value'] : 'Geen error teruggegeven';
                    $order->exactonlineOrder->save();

                    return [
                        'success' => false,
                        'error' => $content ? $content['error']['message']['value'] : 'Geen error teruggegeven',
                    ];
                }
            }
            //            } catch (Exception $e) {
            ////                if ($order->exactonlineOrder->pushed != 2) {
            //                $order->exactonlineOrder->pushed = 2;
            //                $order->exactonlineOrder->error = 'Er ging iets mis met pushen naar Exactonline: ' . $e->getMessage();
            //                $order->exactonlineOrder->save();
            //                try {
            //                    $notificationInvoiceEmails = Customsetting::get('notification_invoice_emails', Sites::getActive(), '[]');
            //                    if ($notificationInvoiceEmails) {
            //                        foreach (json_decode($notificationInvoiceEmails) as $notificationInvoiceEmail) {
            //                            Mail::to($notificationInvoiceEmail)->send(new NotificationMail('Order #' . $order->id . ' failed to push to Exactonline', 'Order #' . $order->id . ' failed to push to Exactonline'));
            //                        }
            //                    }
            //                } catch (\Exception $e) {
            //                }
            ////                }
            //
            //                trigger_error(
            //                    sprintf(
            //                        'Curl failed with error #%d: %s',
            //                        $e->getCode(),
            //                        $e->getMessage()
            //                    ),
            //                    E_USER_ERROR
            //                );
            //            }
        } else {
            return [
                'success' => false,
                'error' => 'No customer id found or order not paid',
            ];
        }
    }

    public static function getVatCodes($siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        //        try {
        $ch = curl_init();

        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/vat/VATCodes?$select=Account,Code,Percentage,Type',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
            ],
        ]);

        $content = curl_exec($ch);

        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $content = json_decode($content, true);

        return $content['d']['results'] ?? [];
        //        } catch (Exception $e) {
        //            trigger_error(
        //                sprintf(
        //                    'Curl failed with error #%d: %s',
        //                    $e->getCode(),
        //                    $e->getMessage()
        //                ),
        //                E_USER_ERROR
        //            );
        //        }
    }

    public static function getVatCodeIdForVateRate($vatRate, $siteId = null)
    {
        if (! $siteId) {
            $siteId = Sites::getActive();
        }

        $vatCodes = self::getVatCodes($siteId);
        //        dd($vatCodes);
        foreach ($vatCodes as $vatCode) {
            if ($vatCode['Percentage'] == $vatRate / 100 && $vatCode['Type'] == 'I') {
                return $vatCode['Code'];
            }
        }

        //        try {
        $ch = curl_init();

        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        $code = rand(500, 999);

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://start.exactonline.nl/api/v1/' . Customsetting::get('exactonline_division', $siteId) . '/vat/VATCodes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
//                'Percentage' => $vatRate,
                'Code' => $code,
                'Description' => 'Dashed',
                'Type' => 'I',
                'GLToClaim' => Customsetting::get('exactonline_vat_codes_gl_to_pay', $siteId),
                'GLToPay' => Customsetting::get('exactonline_vat_codes_gl_to_claim', $siteId),
                'VATPercentages' => [
                    [
                        'Percentage' => $vatRate / 100,
                        'Type' => 0,
                    ],
                ],
            ]),
//            CURLOPT_POSTFIELDS => "code=" . rand(500, 999) . "&Description=Dashed&Type=I&VatPercentage=" . $vatRate,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer " . Customsetting::get('exactonline_access_token', $siteId) . 's',
            ],
        ]);

        $content = curl_exec($ch);

        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $content = json_decode($content, true);

        return $code;
        //        } catch (Exception $e) {
        //            trigger_error(
        //                sprintf(
        //                    'Curl failed with error #%d: %s',
        //                    $e->getCode(),
        //                    $e->getMessage()
        //                ),
        //                E_USER_ERROR
        //            );
        //        }
    }
}
