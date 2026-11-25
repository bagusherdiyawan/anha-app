<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PageController extends Controller
{
    /**
     * Tampilkan landing page utama.
     */
    public function landing()
    {
        $contact = Cache::remember('landing_contact_info', 600, function () {
            try {
                $res = Http::acceptJson()
                    ->timeout(5)
                    ->get(rtrim(config('anha.api_base_url'), '/').'/contact-info');

                return $res->successful() ? (array) data_get($res->json(), 'data', []) : null;
            } catch (\Throwable $e) {
                return null;
            }
        }) ?? [];

        if ($contact === []) {
            Cache::forget('landing_contact_info');
        }

        $wa = preg_replace('/[^0-9]/', '', (string) ($contact['whatsapp_number'] ?? ''));
        $waUrl = $wa !== ''
            ? 'https://wa.me/'.$wa.'?text='.rawurlencode((string) ($contact['whatsapp_template'] ?? ''))
            : '#';

        return view('landing', [
            'contact' => $contact,
            'waUrl' => $waUrl,
            'playStoreUrl' => config('anha.play_store_url'),
        ]);
    }

    /**
     * Tampilkan halaman privacy policy.
     */
    public function privacyPolicy()
    {
        return view('privacy');
    }
}
