<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\IndonesiaRegionService;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        return view('storefront.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request, IndonesiaRegionService $regions): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province_id' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        $provinceId = $validated['province_id'] ?? null;
        $cityName = $validated['city'] ?? null;

        if ($provinceId || $cityName) {
            if (!$provinceId || !$cityName) {
                throw ValidationException::withMessages([
                    'city' => 'Pilih provinsi dan kota/kabupaten yang sesuai.',
                ]);
            }

            try {
                $province = $regions->provinces()->first(
                    fn (array $item) => $item['id'] === $provinceId
                );
                $city = $province ? $regions->findCity($provinceId, $cityName) : null;
            } catch (\Throwable $exception) {
                throw ValidationException::withMessages([
                    'city' => 'Data wilayah belum dapat diverifikasi. Coba lagi beberapa saat.',
                ]);
            }

            if (!$province || !$city) {
                throw ValidationException::withMessages([
                    'city' => 'Kota/kabupaten tidak sesuai dengan provinsi yang dipilih.',
                ]);
            }

            $validated['province'] = $province['name'];
            $validated['city'] = $city['name'];
        } else {
            $validated['province'] = null;
            $validated['city'] = null;
        }

        unset($validated['province_id']);

        unset($validated['profile_photo'], $validated['remove_profile_photo']);

        $oldPhoto = $user->profile_photo_path;
        $newPhoto = null;

        if ($request->boolean('remove_profile_photo')) {
            $validated['profile_photo_path'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            $newPhoto = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            $validated['profile_photo_path'] = $newPhoto;
        }

        $user->update($validated);

        if (($newPhoto || $request->boolean('remove_profile_photo')) && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('customer.profile.edit')
            ->with('status', 'Profil berhasil diperbarui.');
    }
}
