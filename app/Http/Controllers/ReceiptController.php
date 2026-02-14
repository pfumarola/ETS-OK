<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * Elenco ricevute e download PDF.
 */
class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria,contabile');
    }

    public function index(Request $request)
    {
        $query = Receipt::with('member');

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('issued_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('issued_at', '<=', $request->to);
        }

        $receipts = $query->orderByDesc('issued_at')->paginate(20)->withQueryString();
        return Inertia::render('Receipts/Index', [
            'receipts' => $receipts,
            'filters' => $request->only('member_id', 'from', 'to'),
        ]);
    }

    public function show(Receipt $receipt)
    {
        $receipt->load('member');
        return Inertia::render('Receipts/Show', ['receipt' => $receipt]);
    }

    /**
     * Download del file PDF della ricevuta.
     */
    public function download(Receipt $receipt)
    {
        if (! $receipt->file_path || ! Storage::disk('local')->exists($receipt->file_path)) {
            abort(404, 'File non trovato.');
        }
        $safeNumber = str_replace(['/', '\\'], '-', $receipt->number);
        $filename = 'ricevuta-' . $safeNumber . '.pdf';
        return Storage::disk('local')->download($receipt->file_path, $filename, ['Content-Type' => 'application/pdf']);
    }
}
