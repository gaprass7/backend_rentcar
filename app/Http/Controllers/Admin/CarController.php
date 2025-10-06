<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * @OA\Get(
     *     path="/cars",
     *     tags={"Cars"},
     *     summary="Get list of cars",
     *     @OA\Response(response=200, description="List of cars")
     * )
     */
    public function index()
    {
        // Ambil data mobil beserta relasi category-nya
        $cars = Car::with('category')->paginate(10);

        return response()->json($cars);
    }

    /**
     * @OA\Get(
     *     path="/cars/{id}",
     *     tags={"Cars"},
     *     summary="Get car detail",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Car detail"),
     *     @OA\Response(response=404, description="Car not found")
     * )
     */
    public function show($id)
    {
        $car = Car::with('category')->findOrFail($id);
        return response()->json($car);
    }

    /**
     * @OA\Post(
     *     path="/cars",
     *     tags={"Cars"},
     *     summary="Create new car",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"category_id", "brand","model","license_plate","transmission","year","daily_rate","passenger_capacity","fuel_type"},
     *                  @OA\Property(property="category_id", type="integer", example=1, description="ID kategori mobil"),
     *                  @OA\Property(property="brand", type="string", example="Toyota"),
     *                  @OA\Property(property="model", type="string", example="Avanza"),
     *                  @OA\Property(property="license_plate", type="string", example="B1234XYZ"),
     *                  @OA\Property(property="transmission", type="string", enum={"automatic","manual"}),
     *                  @OA\Property(property="year", type="integer", example=2022),
     *                  @OA\Property(property="daily_rate", type="number", format="float", example=500000),
     *                  @OA\Property(property="passenger_capacity", type="integer", example=7),
     *                  @OA\Property(property="fuel_type", type="string", enum={"gasoline","diesel","electric"}),
     *                  @OA\Property(property="status", type="string", enum={"available","rented","maintenance"}, example="available"),
     *                  @OA\Property(property="description", type="string", example="Mobil nyaman untuk keluarga"),
     *                  @OA\Property(property="penalty_rate_per_day", type="number", format="float", example=100000),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Upload image file (jpg, jpeg, png, webp)"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(response=201, description="Car created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'brand'              => 'required|string|max:255',
            'model'              => 'required|string|max:255',
            'license_plate'      => 'required|string|max:255|unique:cars',
            'transmission'       => 'required|in:automatic,manual',
            'year'               => 'required|integer|min:1900|max:' . date('Y'),
            'daily_rate'         => 'required|numeric|min:0',
            'passenger_capacity' => 'required|integer|min:1',
            'fuel_type'          => 'required|in:gasoline,diesel,electric',
            'status'             => 'nullable|in:available,rented,maintenance',
            'description'        => 'nullable|string',
            'penalty_rate_per_day' => 'nullable|numeric|min:0',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            $file->storeAs('cars', $validated['image'], 'public');
        }

        $validated['slug'] = Str::slug($validated['brand'] . '-' . $validated['model'] . '-' . uniqid());
        $car = Car::create($validated);

        return response()->json([
            'message' => 'Car created successfully',
            'data' => $car,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/cars/{id}",
     *     tags={"Cars"},
     *     summary="Update existing car (use _method=PUT)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method"},
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                  @OA\Property(property="category_id", type="integer", example=1, description="ID kategori mobil"),
     *                 @OA\Property(property="brand", type="string", example="Honda"),
     *                 @OA\Property(property="model", type="string", example="Civic"),
     *                 @OA\Property(property="license_plate", type="string", example="B5678ABC"),
     *                 @OA\Property(property="transmission", type="string", enum={"automatic","manual"}),
     *                 @OA\Property(property="year", type="integer", example=2020),
     *                 @OA\Property(property="daily_rate", type="number", format="float", example=400000),
     *                 @OA\Property(property="passenger_capacity", type="integer", example=5),
     *                 @OA\Property(property="fuel_type", type="string", enum={"gasoline","diesel","electric"}),
     *                 @OA\Property(property="status", type="string", enum={"available","rented","maintenance"}),
     *                 @OA\Property(property="description", type="string", example="Sedan nyaman"),
     *                 @OA\Property(property="penalty_rate_per_day", type="number", format="float", example=80000),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Car updated successfully"),
     *     @OA\Response(response=404, description="Car not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */


    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'brand'              => 'required|string|max:255',
            'model'              => 'required|string|max:255',
            'license_plate' => 'required|string|max:255|unique:cars,license_plate,' . $id,
            'transmission'       => 'required|in:automatic,manual',
            'year'               => 'required|integer|min:1900|max:' . date('Y'),
            'daily_rate'         => 'required|numeric|min:0',
            'passenger_capacity' => 'required|integer|min:1',
            'fuel_type'          => 'required|in:gasoline,diesel,electric',
            'status'             => 'nullable|in:available,rented,maintenance',
            'description'        => 'nullable|string',
            'penalty_rate_per_day' => 'nullable|numeric|min:0',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            if ($car->image) {
                Storage::disk('public')->delete('cars/' . $car->image);
            }
            $file = $request->file('image');
            $validated['image'] = $file->hashName();
            $file->storeAs('cars', $validated['image'], 'public');
        }

        if (isset($validated['brand']) || isset($validated['model'])) {
            $validated['slug'] = Str::slug(($validated['brand'] ?? $car->brand) . '-' . ($validated['model'] ?? $car->model) . '-' . uniqid());
        }
        $car->update($validated);

        return response()->json([
            'message' => 'Car updated successfully',
            'data' => $car,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/cars/{id}",
     *     tags={"Cars"},
     *     summary="Delete car",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Car deleted successfully"),
     *     @OA\Response(response=404, description="Car not found")
     * )
     */
    public function destroy($id)
    {
        $car = Car::findOrFail($id);

        if ($car->image) {
            Storage::disk('public')->delete('cars/' . $car->image);
        }

        $car->delete();

        return response()->json(['message' => 'Car deleted successfully']);
    }
}
