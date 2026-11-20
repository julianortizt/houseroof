<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items;

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        return [
            'id' => $this->id,
            'reference' => 'COT-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'service_type' => $this->service_type,
            'service_type_label' => $this->service_type_label,
            'area_m2' => $this->area_m2,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'address' => $this->address,
            'message' => $this->message,
            'items' => $items,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
