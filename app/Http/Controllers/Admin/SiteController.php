<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/site-settings",
     *     tags={"Site Settings"},
     *     summary="Ambil data pengaturan situs",
     *     description="Mengambil data pengaturan situs seperti nama, logo, deskripsi, dan link media sosial.",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data pengaturan situs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="site_name", type="string", example="RentCar App"),
     *             @OA\Property(property="logo", type="string", example="logos/logo.png"),
     *             @OA\Property(property="facebook_url", type="string", example="https://facebook.com/rentcar"),
     *             @OA\Property(property="twitter_url", type="string", example="https://twitter.com/rentcar"),
     *             @OA\Property(property="instagram_url", type="string", example="https://instagram.com/rentcar"),
     *             @OA\Property(property="whatsapp_url", type="string", example="https://wa.me/628123456789"),
     *             @OA\Property(property="location_embed_links", type="array",
     *                 @OA\Items(type="string", example="https://maps.google.com/embed?pb=...")),
     *             @OA\Property(property="description", type="string", example="Aplikasi penyewaan mobil terbaik di kota Anda.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $siteSetting = SiteSetting::first() ?? new SiteSetting();
        return response()->json($siteSetting, 200);
    }

    /**
     * @OA\Post(
     *     path="/site-settings/update",
     *     tags={"Site Settings"},
     *     summary="Perbarui data pengaturan situs (use _method=PUT)",
     *     description="Memperbarui informasi pengaturan situs termasuk nama, logo, deskripsi, dan tautan media sosial.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"site_name", "_method"},
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="site_name", type="string", example="RentCar App"),
     *                 @OA\Property(property="logo", type="string", format="binary", description="File logo (opsional)"),
     *                 @OA\Property(property="facebook_url", type="string", example="https://facebook.com/rentcar"),
     *                 @OA\Property(property="twitter_url", type="string", example="https://twitter.com/rentcar"),
     *                 @OA\Property(property="instagram_url", type="string", example="https://instagram.com/rentcar"),
     *                 @OA\Property(property="whatsapp_url", type="string", example="https://wa.me/628123456789"),
     *                 @OA\Property(
     *                     property="location_embed_links",
     *                     type="array",
     *                     @OA\Items(type="string", example="https://maps.google.com/embed?pb=...")
     *                 ),
     *                 @OA\Property(property="description", type="string", example="Aplikasi penyewaan mobil terpercaya.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil memperbarui pengaturan situs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Site settings updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="site_name", type="string", example="RentCar App"),
     *                 @OA\Property(property="logo", type="string", example="logos/logo.png"),
     *                 @OA\Property(property="facebook_url", type="string", example="https://facebook.com/rentcar")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal"
     *     )
     * )
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'whatsapp_url' => 'nullable|url|max:255',
            'location_embed_links' => 'nullable',
            'description' => 'nullable|string',
        ]);

        $siteSetting = SiteSetting::first() ?? new SiteSetting();

        $siteSetting->fill($validated);

        // if ($request->has('location_embed_links')) {
        //     $siteSetting->location_embed_links = json_encode($request->location_embed_links);
        // }

        // jika string tunggal, ubah jadi array
        if (is_string($request->location_embed_links)) {
            $siteSetting->location_embed_links = [$request->location_embed_links];
        } else {
            $siteSetting->location_embed_links = $request->location_embed_links;
        }

        // handle logo upload
        if ($request->hasFile('logo')) {
            if ($siteSetting->getRawOriginal('logo') && Storage::disk('public')->exists('logos/' . $siteSetting->getRawOriginal('logo'))) {
                Storage::disk('public')->delete('logos/' . $siteSetting->getRawOriginal('logo'));
            }

            $logo = $request->file('logo');
            $logoName = $logo->hashName();
            $logo->storeAs('logos', $logoName, 'public');
            $siteSetting->logo = $logoName;
        }

        $siteSetting->save();

        return response()->json([
            'message' => 'Site settings updated successfully',
            'data' => $siteSetting
        ], 200);
    }
}
