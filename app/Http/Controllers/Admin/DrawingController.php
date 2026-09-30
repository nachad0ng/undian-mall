<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Prize;
use App\Models\RaffleTicket;
use App\Models\Winner;
use App\Services\ManualDrawingService;
use App\Services\PointDrawingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrawingController extends Controller
{
    public function __construct(
        private PointDrawingService $drawingService,
        private ManualDrawingService $manualDrawingService
    ) {}

    public function draw(Prize $prize): JsonResponse
    {
        try {
            $drawing = $this->drawingService->draw($prize, auth()->user());

            return response()->json([
                'success' => true,
                'message' => "Pengundian hadiah {$prize->name} berhasil.",
                'drawing' => $drawing,
                'winners' => $drawing->winners->map(fn ($winner) => [
                    'id' => $winner->id,
                    'customer_id' => $winner->customer_id,
                    'customer_name' => $winner->customer->name,
                    'won_at' => $winner->won_at->toDateTimeString(),
                ]),
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function preview(Prize $prize): JsonResponse
    {
        return response()->json($this->drawingService->preview($prize));
    }

    public function manualPreview(Prize $prize): JsonResponse
    {
        return response()->json($this->manualDrawingService->preview($prize));
    }

    public function manualDraw(Request $request, Prize $prize): JsonResponse
    {
        $validated = $request->validate([
            'winning_number' => ['required', 'string', 'max:20'],
        ]);

        try {
            $drawing = $this->manualDrawingService->draw($prize, $validated['winning_number'], $request->user());
            $winner = $drawing->winners->first();

            return response()->json([
                'success' => true,
                'message' => "Nomor {$winner->winning_number} milik {$winner->customer->name} menang {$prize->name}.",
                'drawing' => $drawing,
                'winner' => [
                    'id' => $winner->id,
                    'customer_id' => $winner->customer_id,
                    'customer_name' => $winner->customer->name,
                    'ticket_number' => $winner->winning_number,
                    'won_at' => $winner->won_at->toDateTimeString(),
                ],
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Cari pemilik nomor undian (untuk verifikasi MC sebelum input bola).
     */
    public function lookupTicket(Prize $prize, string $ticketNumber): JsonResponse
    {
        $ticket = RaffleTicket::query()
            ->where('prize_id', $prize->id)
            ->where('ticket_number', trim($ticketNumber))
            ->with('customer')
            ->first();

        if (! $ticket) {
            return response()->json(['success' => false, 'message' => 'Nomor tidak terdaftar.'], 404);
        }

        $alreadyWon = Winner::query()
            ->where('prize_id', $prize->id)
            ->where('raffle_ticket_id', $ticket->id)
            ->exists();

        return response()->json([
            'success' => true,
            'ticket_number' => $ticket->ticket_number,
            'customer_name' => $ticket->customer->name,
            'already_won' => $alreadyWon,
        ]);
    }

    public function publishWinner(Request $request, Winner $winner): JsonResponse
    {
        $winner->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'winner.published',
            'auditable_type' => Winner::class,
            'auditable_id' => $winner->id,
            'metadata' => ['published' => true],
        ]);

        return response()->json(['success' => true, 'message' => 'Pemenang berhasil dipublikasikan.']);
    }

    public function unpublishWinner(Request $request, Winner $winner): JsonResponse
    {
        $winner->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'winner.unpublished',
            'auditable_type' => Winner::class,
            'auditable_id' => $winner->id,
            'metadata' => ['published' => false],
        ]);

        return response()->json(['success' => true, 'message' => 'Publikasi pemenang dibatalkan.']);
    }
}
