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
        return response()->json(Car::latest()->paginate(10));
    }
}