<?php

namespace App\Http\Controllers;

use App\Models\SlaConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SlaConfigController extends Controller
{
    public function index(): Response
    {
        // Ambil semua kategori task yang valid
        $categories = ['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'];

        // Load SLA config yang sudah ada, key by category
        $configs = SlaConfig::all()->keyBy('category');

        // Gabungkan: setiap kategori pasti muncul, meski belum ada config-nya
        $slaList = collect($categories)->map(fn($cat) => [
            'id'           => $configs->get($cat)?->id,
            'category'     => $cat,
            'max_days'     => $configs->get($cat)?->max_days,
            'warning_days' => $configs->get($cat)?->warning_days,
        ]);

        return Inertia::render('Settings/SlaConfig', [
            'slaList' => $slaList,
        ]);
    }

    public function upsert(Request $request)
    {
        $request->validate([
            'configs'               => 'required|array',
            'configs.*.category'    => 'required|string|in:Fitur Berbayar,Regulasi,Saran Fitur,Prioritas',
            'configs.*.max_days'    => 'required|integer|min:1|max:365',
            'configs.*.warning_days'=> 'required|integer|min:1',
        ]);

        foreach ($request->configs as $item) {
            // Validasi warning_days < max_days
            if ($item['warning_days'] >= $item['max_days']) {
                return back()->withErrors([
                    'configs' => "Warning days untuk \"{$item['category']}\" harus lebih kecil dari max days.",
                ]);
            }

            SlaConfig::updateOrCreate(
                ['category'     => $item['category']],
                ['max_days'     => $item['max_days'],
                 'warning_days' => $item['warning_days']]
            );
        }

        return back()->with('success', 'Konfigurasi SLA berhasil disimpan.');
    }
}
