<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatusEnum::class)],
        ];
    }

    /**
     * Reject transitions that are not part of the order lifecycle graph. The
     * legal transitions are defined once on {@see OrderStatusEnum}.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('status')) {
                return;
            }

            /** @var Order $order */
            $order = $this->route('order');
            $target = OrderStatusEnum::from((string) $this->input('status'));

            if ($order->status !== $target && ! $order->status->canTransitionTo($target)) {
                $validator->errors()->add('status', __('You cannot move an order from :from to :to.', [
                    'from' => $order->status->label(),
                    'to' => $target->label(),
                ]));
            }
        });
    }
}

