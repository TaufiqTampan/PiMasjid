<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AgendaController extends Controller
{
    public function index()
    {
        $agendas = Agenda::latest('date')
            ->when(request('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Agendas/Index', [
            'agendas' => $agendas,
            'filters' => request()->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'location' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(6);

        Agenda::create($validated);

        return redirect()->route('agendas.index')
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function update(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'location' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($agenda->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(6);
        }

        $agenda->update($validated);

        return redirect()->back()
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Agenda $agenda)
    {
        $agenda->delete();

        return redirect()->back()
            ->with('success', 'Agenda berhasil dihapus.');
    }
}
