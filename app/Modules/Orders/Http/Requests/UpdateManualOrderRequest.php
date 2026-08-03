<?php

namespace App\Modules\Orders\Http\Requests;

class UpdateManualOrderRequest extends StoreManualOrderRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user()?->can('update', $order) ?? false;
    }
}
