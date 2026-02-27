<?php

namespace App\Http\Controllers;

use App\Http\Requests\Form\StoreAnalysisRequest;
use App\Models\AnalysisRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FormController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Form/StartGrowth', [
            'locale' => app()->getLocale(),
        ]);
    }

    public function store(StoreAnalysisRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        AnalysisRequest::create([
            ...$validatedData,
            'tiktok_username' => $this->normalizeNotInformed($validatedData['tiktok_username'] ?? null),
            'bio' => $this->normalizeNotInformed($validatedData['bio'] ?? null),
            'video_url_1' => $this->normalizeNotInformed($validatedData['video_url_1'] ?? null),
            'video_url_2' => $this->normalizeNotInformed($validatedData['video_url_2'] ?? null),
            'video_url_3' => $this->normalizeNotInformed($validatedData['video_url_3'] ?? null),
            'locale' => app()->getLocale(),
            'payment_status' => 'pending',
        ]);

        return redirect('/thank-you');
    }

    private function normalizeNotInformed(?string $value): string
    {
        return blank($value) ? '<Not Informed>' : $value;
    }
}
