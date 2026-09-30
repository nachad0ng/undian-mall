<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nomor Undian #{{ $redemption->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #000; background: #fff; }
        .struk { width: 80mm; max-width: 100%; margin: 0 auto; padding: 8px 10px 16px; }
        .center { text-align: center; }
        .mall { font-size: 15px; font-weight: bold; }
        .hr { border-top: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .ticket { border: 1.5px solid #000; border-radius: 6px; padding: 6px 4px; margin: 6px 0; text-align: center; }
        .ticket .num { font-size: 28px; font-weight: bold; letter-spacing: 6px; }
        .ticket .prize { font-size: 11px; font-weight: bold; }
        .small { font-size: 10px; }
        .actions { text-align: center; margin-top: 12px; }
        .actions button { font-family: inherit; font-size: 13px; padding: 8px 18px; cursor: pointer; }
        @media print {
            .actions { display: none; }
            .struk { width: 72mm; padding: 0; }
            @page { size: 80mm auto; margin: 4mm; }
        }
    </style>
</head>
<body onload="window.print()">
<div class="struk">
    <div class="center mall">{{ $redemption->prize->rafflePeriod?->name ?? 'MALL LUCKY DRAW' }}</div>
    <div class="center"><strong>KUPON UNDIAN</strong></div>
    <div class="center">{{ $redemption->prize->name }} ({{ $redemption->prize->ticket_digits }} digit)</div>
    <div class="hr"></div>
    <div class="row"><span>Customer</span><span><strong>{{ $redemption->customer->name }}</strong></span></div>
    <div class="row"><span>Struk</span><span>{{ $redemption->purchase?->receipt_number }}</span></div>
    <div class="row"><span>Tgl tukar</span><span>{{ $redemption->redeemed_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>CS</span><span>{{ $redemption->cs?->name ?? '-' }}</span></div>
    <div class="hr"></div>
    @forelse ($redemption->raffleTickets as $ticket)
        <div class="ticket">
            <div class="prize">{{ $redemption->prize->name }}</div>
            <div class="num">{{ $ticket->ticket_number }}</div>
            <div class="small">Simpan kupon ini untuk pengundian bola</div>
        </div>
    @empty
        <div class="center">Tidak ada nomor undian.</div>
    @endforelse
    <div class="hr"></div>
    <div class="center small">Total {{ $redemption->raffleTickets->count() }} nomor • Undian manual dengan bola 0–9</div>
    <div class="center small">* Satu nomor satu kesempatan *</div>
    <div class="actions">
        <button onclick="window.print()">Cetak Lagi</button>
    </div>
</div>
</body>
</html>
