<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

/**
 * @OA\Tag(
 *     name="Addons",
 *     description="API untuk manajemen data Addon (fitur tambahan dalam penyewaan mobil)"
 * )
 */
class ReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/reviews",
     *     tags={"Reviews"},
     *     summary="Get list of reviews",
     *     @OA\Response(response=200, description="List of reviews")
     * )
    */
    
    public function index()
    {
        $reviews = Review::with(['rental.car', 'user'])->paginate(10);
        return response()->json($reviews, 200);
    }
}
