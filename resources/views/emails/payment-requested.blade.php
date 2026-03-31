<x-mail::message>
# Pagamento Pendente

Olá, **{{ $booking->owner->name }}**!

Tem um pagamento pendente referente ao serviço do seu cão **{{ $booking->dog->name }}**.

<x-mail::panel>
**Valor a pagar: {{ number_format($booking->payment->amount, 2, ',', '.') }}€**

@if($booking->payment->method === 'mbway')
**Método:** MBWay
Enviámos um pedido de pagamento para o seu telemóvel. Tem **3 minutos** para aceitar.

Se não recebeu a notificação, pode reenviar o pedido na sua área de cliente.
@else
**Método:** Multibanco

- **Entidade:** {{ $booking->payment->mb_entity }}
- **Referência:** {{ $booking->payment->mb_reference }}
- **Valor:** {{ number_format($booking->payment->amount, 2, ',', '.') }}€

Pode pagar em qualquer ATM ou homebanking.
@endif
</x-mail::panel>

@if($booking->payment->method === 'mbway')
<x-mail::button :url="route('owner.payments.index')">
Ver os meus pagamentos
</x-mail::button>
@endif

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
