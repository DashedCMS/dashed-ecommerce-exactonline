<form wire:submit.prevent="submit">
    @if($order->exactonlineOrder)
        @if($order->exactonlineOrder->pushed == 1)
            <span
                class="bg-green-100 text-green-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling naar Exactonline gepushed
                                </span>
        @elseif($order->exactonlineOrder->pushed == 2)
            <span
                class="bg-red-100 text-red-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling niet naar Exactonline gepushed
                                </span>
            <button type="submit"
                    class="inline-flex items-center justify-center font-medium tracking-tight rounded-lg focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 px-4 text-white shadow focus:ring-white w-full mt-2">
                Opnieuw naar Exactonline pushen
            </button>
        @else
            <span
                class="bg-yellow-100 text-yellow-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling wordt naar Exactonline gepushed
                                </span>
        @endif
    @else
        <span
            class="bg-yellow-100 text-yellow-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling niet gekoppeld aan Exactonline
                                </span>
        <button type="addToExact"
                class="inline-flex items-center justify-center font-medium tracking-tight rounded-lg focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 px-4 text-white shadow focus:ring-white w-full mt-2">
            Bestelling aan Exactonline koppelen
        </button>
    @endif
</form>
