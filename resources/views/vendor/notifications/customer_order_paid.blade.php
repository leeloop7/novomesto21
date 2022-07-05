@component('mail::message')
# Potrditev prijave

Spoštovani,
potrjujemo, da je bila vaša prijava (**#{{ $order->orderNumber() }}**) zabeležena kot plačana. Pregled naročila je objavljen spodaj, prav tako pa je priložen tudi račun.

@component('mail::table')
| Prijava       | Količina         | Cena |
| :--------- | :------------- | :----- |
@foreach ($order->lineItems() as $lineItem)
@php
$site = \Statamic\Facades\Site::current();
@endphp
| [{{ $lineItem->product()->get('title') }}]({{ optional($lineItem->product()->resource())->absoluteUrl() }}) | {{ $lineItem->quantity() }} | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($lineItem->total(), $site) }} |
@endforeach
@endcomponent

## Tekmovalec oz. nosilec prijave

@if($order->customer())
* **Ime in priimek:** {{ $order->customer()->get("first_name") }} {{ $order->customer()->get("last_name") }}
* **Email:** {{ $order->customer()->email() }}
* **Naslov:** {{ $order->get("address") }}, {{ $order->get("zip") }} {{ $order->get("city") }}, {{ $order->get("country") }}
* **Spol:** {{ __('checkout.' . $order->get('gender')) }}
* **Letnica rojstva:** {{ $order->get("birthyear") }}
* **Tel. številka:** {{ $order->get("phone") }}
* **Klub:** {{ $order->get("club") }}
* **Čas:** {{ $order->get("best_time") }}
* **Vel. majice:** {{ $order->get("shirt_size") }}
@endif

<br>

Če imate kakšno vprašanje o prijavi, prosimo, stopite v kontakt.

Hvala,<br>
{{ config('app.name') }}
@endcomponent
