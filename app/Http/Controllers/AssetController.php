<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_operations');

        $assets = Asset::latest()->get()->map(function ($asset) {
            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'condition' => $asset->condition,
                'quantity' => $asset->quantity,
                'purchase_date' => $asset->purchase_date ? $asset->purchase_date->format('d M Y') : null,
                'purchase_price' => $asset->purchase_price,
                'notes' => $asset->notes,
                'created_at' => $asset->created_at,
                'updated_at' => $asset->updated_at,
            ];
        });

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'condition' => 'required|in:good,damaged,lost',
            'quantity' => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Asset::create($validated);

        return redirect()->back()->with('success', 'Aset ditambahkan!');
    }

    public function update(Request $request, Asset $asset)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'condition' => 'required|in:good,damaged,lost',
            'quantity' => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->back()->with('success', 'Aset diupdate!');
    }

    public function destroy(Asset $asset)
    {
        Gate::authorize('manage_operations');

        $asset->delete();

        return redirect()->back()->with('success', 'Aset dihapus!');
    }

    public function export(Request $request)
    {
        Gate::authorize('manage_operations');

        $type = $request->get('type', 'excel'); // excel or pdf

        $assets = Asset::latest()->get();
        $settings = \App\Models\Setting::pluck('value', 'key');

        if ($type === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AssetExport,
                'Laporan_Aset_'.date('Y-m-d').'.xlsx'
            );
        }

        // PDF
        $totalQuantity = $assets->sum('quantity');
        $totalValue = $assets->sum('purchase_price');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.assets_pdf', [
            'assets' => $assets,
            'settings' => $settings,
            'totalQuantity' => $totalQuantity,
            'totalValue' => $totalValue,
        ]);

        return $pdf->download('Laporan_Aset_'.date('Y-m-d').'.pdf');
    }
}
