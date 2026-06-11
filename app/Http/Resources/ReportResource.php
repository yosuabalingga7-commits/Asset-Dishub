<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'source' => $this->source,
            'nama_pelapor' => $this->nama_pelapor,
            'kontak_pelapor' => $this->kontak_pelapor,
            'nama_petugas' => $this->nama_petugas,
            'nip' => $this->nip,
            'no_wa' => $this->no_wa,
            'judul_laporan' => $this->judul_laporan,
            'deskripsi_keluhan' => $this->deskripsi_keluhan,
            'deskripsi' => $this->deskripsi,
            'kondisi_aset' => $this->kondisi_aset,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'alamat' => $this->alamat,
            'lokasi_koordinat' => $this->lokasi_koordinat,
            'foto' => $this->foto,
            'status' => $this->status,
            'is_validated' => (bool) $this->is_validated,
            'kepemilikan' => $this->kepemilikan,
            'catatan_admin' => $this->catatan_admin,
            'id_asset' => $this->id_asset,
            'asset_id' => $this->asset_id,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'logs' => ReportLogResource::collection($this->whenLoaded('logs')),
        ];
    }
}
