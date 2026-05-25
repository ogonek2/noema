<?php

namespace App\Services;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TtnStatus;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderManagementService
{
    public function transitionStatus(
        Order $order,
        OrderStatus $status,
        ?User $user = null,
        ?string $note = null,
    ): Order {
        return DB::transaction(function () use ($order, $status, $user, $note): Order {
            $from = $order->status;
            $order->status = $status;

            match ($status) {
                OrderStatus::Paid => $order->paid_at ??= now(),
                OrderStatus::Shipped => $order->shipped_at ??= now(),
                OrderStatus::Completed => $order->completed_at ??= now(),
                OrderStatus::Cancelled => $order->cancelled_at ??= now(),
                default => null,
            };

            $order->save();

            $this->logEvent($order, OrderEventType::StatusChange, $user, [
                'from_status' => $from->value,
                'to_status' => $status->value,
                'body' => $note,
            ]);

            return $order->fresh();
        });
    }

    public function markPaymentPaid(Order $order, ?User $user = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $user, $note): Order {
            $order->payment_status = PaymentStatus::Paid;
            $order->paid_at ??= now();

            if ($order->status === OrderStatus::Pending) {
                $order->status = OrderStatus::Paid;
            }

            $order->save();

            $this->logEvent($order, OrderEventType::PaymentChange, $user, [
                'body' => $note ?? 'Оплату підтверджено вручну',
                'to_status' => $order->status->value,
            ]);

            return $order->fresh();
        });
    }

    public function assign(Order $order, ?int $userId, ?User $actor = null): Order
    {
        $order->assigned_to = $userId;
        $order->save();

        $this->logEvent($order, OrderEventType::Assigned, $actor, [
            'body' => $userId ? "Призначено менеджеру #{$userId}" : 'Призначення знято',
            'meta' => ['assigned_to' => $userId],
        ]);

        return $order->fresh();
    }

    public function addNote(Order $order, string $note, ?User $user = null): OrderEvent
    {
        return $this->logEvent($order, OrderEventType::Note, $user, ['body' => $note]);
    }

    public function recordTtnCreated(Order $order, string $ref, string $number, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $ref, $number, $user): Order {
            $order->ttn_ref = $ref;
            $order->ttn_number = $number;
            $order->ttn_status = TtnStatus::Created;
            $order->status = OrderStatus::Processing;
            $order->save();

            $this->logEvent($order, OrderEventType::TtnCreated, $user, [
                'body' => "ТТН {$number} створено",
                'meta' => ['ttn_ref' => $ref, 'ttn_number' => $number],
            ]);

            return $order->fresh();
        });
    }

    public function recordTtnFailed(Order $order, string $message, ?User $user = null): OrderEvent
    {
        return $this->logEvent($order, OrderEventType::TtnFailed, $user, [
            'body' => $message,
        ]);
    }

    /** @param  array<string, mixed>  $payload */
    private function logEvent(Order $order, OrderEventType $type, ?User $user, array $payload = []): OrderEvent
    {
        return OrderEvent::query()->create([
            'order_id' => $order->id,
            'user_id' => $user?->id,
            'type' => $type,
            'from_status' => $payload['from_status'] ?? null,
            'to_status' => $payload['to_status'] ?? null,
            'body' => $payload['body'] ?? null,
            'meta' => $payload['meta'] ?? null,
        ]);
    }
}
