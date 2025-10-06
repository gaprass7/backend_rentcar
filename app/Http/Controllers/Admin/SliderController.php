<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;



class SliderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/sliders",
     *     tags={"Sliders"},
     *     summary="Get list of sliders",
     *     @OA\Response(response=200, description="List of sliders")
     * )
     */
    public function index()
    {
        return response()->json(Slider::paginate(5));
    }

    /**
     * @OA\Get(
     *     path="/sliders/{id}",
     *     tags={"Sliders"},
     *     summary="Get Sliders detail",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Sliders detail"),
     *     @OA\Response(response=404, description="Sliders not found")
     * )
     */
    public function show($id)
    {
        $slider = Slider::findOrFail($id);
        return response()->json($slider);
    }

    /**
     * @OA\Post(
     *     path="/sliders",
     *     tags={"Sliders"},
     *     summary="Create new slider",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"image"},
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Upload image file (jpg, jpeg, png, webp)"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(response=201, description="Sliders created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'              => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $validated['image'] = $file->hashName();
            $file->storeAs('sliders', $validated['image'], 'public');
        }

        $slider = Slider::create($validated);

        return response()->json([
            'message' => 'Slider created successfully',
            'data' => $slider,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/sliders/{id}",
     *     tags={"Sliders"},
     *     summary="Update slider existing slider (use _method=PUT)",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"image", "_method"},
     *                  @OA\Property(property="_method", type="string", example="PUT"),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Upload image file (jpg, jpeg, png, webp)"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Slider updated successfully")
     * )
     */
    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'image'              => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // 🔹 Hapus file lama jika ada
        if ($slider->image) {
            // Ambil hanya nama file dari accessor URL
            $oldImage = basename(parse_url($slider->image, PHP_URL_PATH));

            if (Storage::disk('public')->exists('sliders/' . $oldImage)) {
                Storage::disk('public')->delete('sliders/' . $oldImage);
            }
        }

        // 🔹 Upload gambar baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $validated['image'] = $file->hashName();
            $file->storeAs('sliders', $validated['image'], 'public');
        }

        // 🔹 Update data
        $slider->update($validated);

        return response()->json([
            'message' => 'Slider updated successfully',
            'data' => $slider,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/sliders/{id}",
     *     tags={"Sliders"},
     *     summary="Delete slider",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Slider ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Slider deleted successfully"),
     *     @OA\Response(response=404, description="Slider not found")
     * )
     */
    public function destroy($id)
    {
        $slider = Slider::find($id);

        if (!$slider) {
            return response()->json([
                'message' => 'Slider not found',
            ], 404);
        }

        // 🔹 Hapus file gambar lama jika ada
        if ($slider->image && Storage::disk('public')->exists('sliders/' . basename($slider->image))) {
            Storage::disk('public')->delete('sliders/' . basename($slider->image));
        }

        // 🔹 Hapus data di database
        $slider->delete();

        return response()->json([
            'message' => 'Slider deleted successfully',
        ], 200);
    }
}
