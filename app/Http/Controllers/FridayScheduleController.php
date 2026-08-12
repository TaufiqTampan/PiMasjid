<?php

namespace App\Http\Controllers;

use App\Models\FridaySchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FridayScheduleController extends Controller
{
    public function index()
    {
        $schedules = FridaySchedule::latest('date')->paginate(10);

        return Inertia::render('FridaySchedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:friday_schedules,date',
            'time' => 'required',
            'khatib' => 'required|string|max:255',
            'imam' => 'required|string|max:255',
            'muadzin' => 'required|string|max:255',
            'bilal' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
        ]);

        FridaySchedule::create($validated);

        return redirect()->back()->with('success', 'Jadwal Jumat berhasil ditambahkan.');
    }

    public function update(Request $request, FridaySchedule $fridaySchedule)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:friday_schedules,date,'.$fridaySchedule->id,
            'time' => 'required',
            'khatib' => 'required|string|max:255',
            'imam' => 'required|string|max:255',
            'muadzin' => 'required|string|max:255',
            'bilal' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
        ]);

        $fridaySchedule->update($validated);

        return redirect()->back()->with('success', 'Jadwal Jumat berhasil diperbarui.');
    }

    public function destroy(FridaySchedule $fridaySchedule)
    {
        $fridaySchedule->delete();

        return redirect()->back()->with('success', 'Jadwal Jumat berhasil dihapus.');
    }
}
