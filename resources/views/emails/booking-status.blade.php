<x-mail::message>
# Pedido {{ $booking->status === 'aprovado' ? 'Aprovado ✓' : 'Recusado ✗' }}

Olá, **{{ $booking->owner->name }}**!

O seu pedido de **{{ $typeLabel }}** para o **{{ $booking->dog->name }}** foi **{{ $booking->status }}**.

<x-mail::panel>
**Detalhes do pedido:**

@if($booking->type === 'hotel')
- **Tipo:** Estadia
- **Entrada:** {{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') }}
- **Saída:** {{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') }}
@else
- **Tipo:** {{ $typeLabel }}
- **Início:** {{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') }}
- **Frequência:** {{ ucfirst($booking->frequency) }}
@endif

@if($booking->pet_taxi)
- **Pet Taxi:** Incluído
@endif
</x-mail::panel>

@if($booking->staff_notes)
**Nota da nossa equipa:**

{{ $booking->staff_notes }}
@endif

@if($booking->status === 'aprovado')
Entraremos em contacto em breve para confirmar os detalhes.
@else
Se tiver alguma dúvida, não hesite em contactar-nos.
@endif

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
