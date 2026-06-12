<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_code' => $this->ticket_code,
            'report_id' => $this->report_id,
            'asset_id' => $this->asset_id,
            'user_id' => $this->user_id,
            'seksi_id' => $this->seksi_id,
            'category' => $this->category,
            'kepemilikan' => $this->kepemilikan,
            'subject' => $this->subject,
            'description' => $this->description,
            'location_address' => $this->location_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'priority' => $this->priority,
            'technician_name' => $this->technician_name,
            'started_at' => $this->started_at ? $this->started_at->toIso8601String() : null,
            'finished_at' => $this->finished_at ? $this->finished_at->toIso8601String() : null,
            'deadline' => $this->deadline ? $this->deadline->format('Y-m-d') : null,
            'jenis_aset' => $this->jenis_aset,
            'foto_perbaikan' => $this->foto_perbaikan,
            'foto_perbaikan_url' => $this->foto_perbaikan ? asset('storage/' . $this->foto_perbaikan) : null,
            'completion_notes' => $this->completion_notes,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'report' => new ReportResource($this->whenLoaded('report')),
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'user' => $this->relationLoaded('user') && $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'nip' => $this->user->nip,
                'email' => $this->user->email,
                'role' => $this->user->role,
            ] : null,
        ];
    }
}
