<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_id' => $this->report_id,
            'aksi' => $this->aksi,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'user' => $this->whenLoaded('user'),
        ];
    }
}
