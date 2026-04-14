<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket; // Pastikan Model Ticket sudah ada

class TicketController extends Controller
{
    public function index()
    {
        // Ambil semua tiket beserta data aset terkait (eager loading)
        // supaya tidak error saat panggil $ticket->asset->name
        $tickets = Ticket::with('asset')->latest()->get();

        // Kirim variabel $tickets ke folder admin/pengaduan/index.blade.php
        return view('admin.pengaduan.index', compact('tickets'));
    }
}