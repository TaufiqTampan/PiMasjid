<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'description' => $this->description,
            'date' => $this->date->toDateString(),
            'formatted_date' => $this->date->format('d M Y'),
            'status_label' => $this->status_label,
            'proof_url' => $this->proof_url,
            'has_proof' => ! empty($this->proof_image_path),
            'verified_by_name' => $this->whenLoaded('verifiedBy', fn () => $this->verifiedBy->name),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
