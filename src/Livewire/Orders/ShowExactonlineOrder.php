<?php

namespace Dashed\DashedEcommerceExactonline\Livewire\Orders;

use Dashed\DashedCore\Models\Customsetting;
use Livewire\Component;

class ShowExactonlineOrder extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function render()
    {
        return view('dashed-ecommerce-exactonline::orders.components.show-exactonline-order');
    }

    public function submit()
    {
        if (! $this->order->exactonlineOrder) {
            $this->dispatch('notify', [
                'status' => 'error',
                'message' => 'De bestelling mag niet naar Exactonline gepushed worden.',
            ]);
        } elseif ($this->order->exactonlineOrder->pushed == 1) {
            $this->dispatch('notify', [
                'status' => 'error',
                'message' => 'De bestelling is al naar Exactonline gepushed.',
            ]);
        } elseif ($this->order->exactonlineOrder->pushed == 0) {
            $this->dispatch('notify', [
                'status' => 'error',
                'message' => 'De bestelling wordt al naar Exactonline gepushed.',
            ]);
        }

        $this->order->exactonlineOrder->pushed = 0;
        $this->order->exactonlineOrder->save();

        $this->dispatch('refreshPage');
        $this->dispatch('notify', [
            'status' => 'success',
            'message' => 'De bestelling wordt binnen enkele minuten opnieuw naar Exactonline gepushed.',
        ]);
    }

    public function addToExact()
    {
        if (Customsetting::get('exactonline_client_id', $this->order->site_id) && ! $this->order->exactonlineOrder) {
            $this->order->exactonlineOrder()->create([]);
        }

        $this->dispatch('refreshPage');
        $this->dispatch('notify', [
            'status' => 'success',
            'message' => 'De bestelling wordt binnen enkele minuten naar Exactonline gepushed.',
        ]);
    }
}
