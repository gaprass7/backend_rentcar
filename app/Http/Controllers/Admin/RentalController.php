<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Rentals",
 *     description="API untuk manajemen data penyewaan mobil"
 * )
 */
class RentalController extends Controller
{
    /**
     * @OA\Get(
     *     path="/rentals",
     *     tags={"Rentals"},
     *     summary="Menampilkan daftar penyewaan",
     *     description="Mengambil daftar semua data rental dengan relasi mobil, metode pembayaran, dan pembayaran.",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data rental",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="car", type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Toyota Avanza")
     *                     ),
     *                     @OA\Property(property="paymentMethod", type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="type", type="string", example="bank_transfer")
     *                     ),
     *                     @OA\Property(property="payments", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=12),
     *                             @OA\Property(property="amount", type="number", format="float", example=250000.00)
     *                         )
     *                     ),
     *                     @OA\Property(property="total_fee", type="number", format="float", example=500000.00)
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $rentals = Rental::with(['car', 'paymentMethod', 'payments'])
            ->latest()
            ->paginate(10);

        return response()->json($rentals, 200);
    }

    /**
     * @OA\Get(
     *     path="/rentals/{id}",
     *     tags={"Rentals"},
     *     summary="Menampilkan detail penyewaan berdasarkan ID",
     *     description="Menampilkan detail satu penyewaan lengkap dengan perhitungan biaya.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Rental",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil detail rental",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="rental", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="car", type="object",
     *                     @OA\Property(property="name", type="string", example="Avanza 2023")
     *                 ),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="name", type="string", example="John Doe")
     *                 ),
     *                 @OA\Property(property="addons", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string", example="Asuransi tambahan"),
     *                         @OA\Property(property="pivot", type="object",
     *                             @OA\Property(property="total_price", type="number", example=50000)
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="rentalFeeDetails", type="object",
     *                 @OA\Property(property="base_rental_fee", type="number", example=300000),
     *                 @OA\Property(property="additional_fee", type="number", example=20000),
     *                 @OA\Property(property="addon_total_fee", type="number", example=50000),
     *                 @OA\Property(property="late_fee", type="number", example=10000),
     *                 @OA\Property(property="total_fee", type="number", example=380000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data rental tidak ditemukan"
     *     )
     * )
     */
    public function show($id)
    {
        $rental = Rental::with(['car', 'payments', 'addons', 'user'])->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Data rental tidak ditemukan'], 404);
        }

        $addons = $rental->addons;
        $addonTotalFee = $addons->sum('pivot.total_price');

        $rentalFeeDetails = [
            'base_rental_fee' => $rental->base_rental_fee,
            'additional_fee' => $rental->additional_fee,
            'addon_total_fee' => $addonTotalFee,
            'late_fee' => $rental->late_fee,
            'total_fee' => $rental->total_fee,
        ];

        return response()->json([
            'rental' => $rental,
            'rentalFeeDetails' => $rentalFeeDetails,
        ], 200);
    }
}
