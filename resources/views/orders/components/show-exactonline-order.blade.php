<div class="grid gap-2">
    @if($order->exactonlineOrder)
        @if($order->exactonlineOrder->pushed == 1)
            <span
                class="w-full justify-center bg-green-100 text-green-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling naar Exactonline gepushed
                                </span>
        @elseif($order->exactonlineOrder->pushed == 2)
            <span
                class="w-full justify-center bg-red-100 text-red-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling niet naar Exactonline gepushed
                                </span>
            <form wire:submit.prevent="submit">
                <button type="submit"

                        style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                        class="fi-btn w-full relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                >
                    Opnieuw naar Exactonline pushen
                </button>
            </form>
        @else
            <span
                class="w-full justify-center bg-yellow-100 text-yellow-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling wordt naar Exactonline gepushed
                                </span>
        @endif
        @if(!Customsetting::get('exactonline_connected', $order->site_id, 0) ? true : false)
            <span
                class="w-full justify-center bg-red-100 text-red-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Exactonline koppeling niet actief
                                </span>
        @endif
    @else
        <span
            class="w-full justify-center bg-yellow-100 text-yellow-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling niet gekoppeld aan Exactonline
                                </span>
        <form wire:submit.prevent="addToExact">
            <button type="submit"
                    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    class="fi-btn w-full relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action">
                Bestelling aan Exactonline koppelen
            </button>
        </form>
    @endif

</div>
