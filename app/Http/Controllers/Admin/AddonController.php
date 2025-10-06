<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Addons",
 *     description="API untuk manajemen data Addon (fitur tambahan dalam penyewaan mobil)"
 * )
 */
class AddonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/addons",
     *     tags={"Addons"},
     *     summary="Menampilkan daftar semua addon",
     *     description="Menampilkan daftar semua addon dengan pagination.",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil menampilkan data addon",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Asuransi Tambahan"),
     *                     @OA\Property(property="price", type="number", example=50000),
     *                     @OA\Property(property="type", type="string", example="insurance")
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
        $addons = Addon::latest()->paginate(10);

        return response()->json($addons, 200);
    }

    /**
     * @OA\Post(
     *     path="/addons",
     *     tags={"Addons"},
     *     summary="Menambahkan addon baru",
     *     description="Membuat addon baru dengan nama, harga, dan tipe tertentu.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","type"},
     *             @OA\Property(property="name", type="string", example="Driver Tambahan"),
     *             @OA\Property(property="price", type="number", example=150000),
     *             @OA\Property(property="type", type="string", enum={"driver","fuel","insurance","additional_service"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Addon berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Addon created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="name", type="string", example="Driver Tambahan"),
     *                 @OA\Property(property="price", type="number", example=150000),
     *                 @OA\Property(property="type", type="string", example="driver")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type'  => 'required|in:driver,fuel,insurance,additional_service',
        ]);

        $addon = Addon::create($validated);

        return response()->json([
            'message' => 'Addon created successfully',
            'data' => $addon,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/addons/{id}",
     *     tags={"Addons"},
     *     summary="Menampilkan detail addon berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID dari addon",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil menampilkan data addon",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Asuransi Tambahan"),
     *             @OA\Property(property="price", type="number", example=50000),
     *             @OA\Property(property="type", type="string", example="insurance")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Addon tidak ditemukan")
     * )
     */
    public function show($id)
    {
        $addon = Addon::find($id);

        if (!$addon) {
            return response()->json(['message' => 'Addon not found'], 404);
        }

        return response()->json($addon, 200);
    }

    /**
     * @OA\Put(
     *     path="/addons/{id}",
     *     tags={"Addons"},
     *     summary="Memperbarui data addon",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID addon yang akan diperbarui",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","type"},
     *             @OA\Property(property="name", type="string", example="BBM Tambahan"),
     *             @OA\Property(property="price", type="number", example=75000),
     *             @OA\Property(property="type", type="string", enum={"driver","fuel","insurance","additional_service"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Addon berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Addon updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="BBM Tambahan"),
     *                 @OA\Property(property="price", type="number", example=75000),
     *                 @OA\Property(property="type", type="string", example="fuel")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Addon tidak ditemukan"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function update(Request $request, $id)
    {
        $addon = Addon::find($id);

        if (!$addon) {
            return response()->json(['message' => 'Addon not found'], 404);
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type'  => 'required|in:driver,fuel,insurance,additional_service',
        ]);

        $addon->update($validated);

        return response()->json([
            'message' => 'Addon updated successfully',
            'data' => $addon,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/addons/{id}",
     *     tags={"Addons"},
     *     summary="Menghapus addon berdasarkan ID",
     *     description="Menghapus satu data addon berdasarkan ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID dari addon",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Addon berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Addon deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Addon tidak ditemukan")
     * )
     */
    public function destroy($id)
    {
        $addon = Addon::find($id);

        if (!$addon) {
            return response()->json(['message' => 'Addon not found'], 404);
        }

        $addon->delete();

        return response()->json(['message' => 'Addon deleted successfully'], 200);
    }
}
