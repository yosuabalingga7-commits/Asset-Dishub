<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_asset' => $this->id_asset,
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'jenis' => $this->jenis,
            'icon_marker' => $this->icon_marker,
            'merk' => $this->merk,
            'status' => $this->status,
            'alamat' => $this->alamat,
            'foto' => $this->foto,
            'foto_url' => $this->foto_url,
            'tgl_pemasangan' => $this->tgl_pemasangan ? $this->tgl_pemasangan->format('Y-m-d') : null,
            'catatan' => $this->catatan,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'jarak' => isset($this->distance) ? (float) $this->distance : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
