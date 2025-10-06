<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/payment-methods",
     *     tags={"Payment Methods"},
     *     summary="Get list of payment methods",
     *     @OA\Response(response=200, description="List of payment methods")
     *  )
     */
    public function index()
    {
        return response()->json(PaymentMethod::paginate(10));
    }

    /**
     *  @OA\Get(
     *     path="/payment-methods/{id}",
     *     tags={"Payment Methods"},
     *     summary="Get payment method detail",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Payment method detail"),
     *     @OA\Response(response=404, description="Payment method not found")
     *  )
     */
    public function show($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        return response()->json($paymentMethod);
    }

    /**
     *  @OA\Post(
     *     path="/payment-methods",
     *     tags={"Payment Methods"},
     *     summary="Create new payment method",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"type"},
     *                  @OA\Property(property="type", type="string", enum={"bank_transfer", "qris"}, example="bank_transfer"),
     *                  @OA\Property(property="account_number", type="string", example="1234567890"),
     *                  @OA\Property(property="account_name", type="string", example="John Doe"),
     *                  @OA\Property(property="bank_name", type="string", example="BCA"),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Upload payment image (png, jpg, webp)"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Payment method created successfully"),
     *     @OA\Response(response=404, description="Payment method not found")
     *  )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'           => 'required|in:bank_transfer,qris',
            'account_number' => 'nullable|string|max:255',
            'account_name'   => 'nullable|string|max:255',
            'bank_name'      => 'nullable|string|max:255',
            'image'          => 'nullable|image|mimes:png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $data['image'] = $image->hashName();
            $image->storeAs('payment_methods', $data['image'], 'public');
        }

        $payment = PaymentMethod::create($data);

        return response()->json([
            'message' => 'Payment method created successfully',
            'data'    => $payment
        ], 201);
    }

    /**
     *  @OA\Post(
     *     path="/payment-methods/{id}",
     *     tags={"Payment Methods"},
     *     summary="Update payment method (use _method=PUT)",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"_method", "type"},
     *                  @OA\Property(property="_method", type="string", example="PUT"),
     *                  @OA\Property(property="type", type="string", enum={"bank_transfer", "qris"}, example="bank_transfer"),
     *                  @OA\Property(property="account_number", type="string", example="1234567890"),
     *                  @OA\Property(property="account_name", type="string", example="John Doe"),
     *                  @OA\Property(property="bank_name", type="string", example="BCA"),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Upload payment image (png, jpg, webp)"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Payment method updated successfully"),
     *     @OA\Response(response=404, description="Payment method not found")
     *  )
     * */
    public function update(Request $request, $id)
    {
        $payment_method = PaymentMethod::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type'           => 'required|in:bank_transfer,qris',
            'account_number' => 'nullable|string|max:255',
            'account_name'   => 'nullable|string|max:255',
            'bank_name'      => 'nullable|string|max:255',
            'image'          => 'nullable|image|mimes:png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // 🔹 Hapus file lama jika ada
        if ($payment_method->image) {
            $oldImage = basename(parse_url($payment_method->image, PHP_URL_PATH));

            if (Storage::disk('public')->exists('payment_methods/' . $oldImage)) {
                Storage::disk('public')->delete('payment_methods/' . $oldImage);
            }
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $validated['image'] = $file->hashName();
            $file->storeAs('payment_methods', $validated['image'], 'public');
        }

        $payment = PaymentMethod::findOrFail($id);
        $payment->update($data);

        return response()->json([
            'message' => 'Payment method updated successfully',
            'data'    => $payment
        ], 200);
    }

    /**
     *  @OA\Delete(
     *     path="/payment-methods/{id}",
     *     tags={"Payment Methods"},
     *     summary="Delete payment method",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Payment method deleted successfully"),
     *     @OA\Response(response=404, description="Payment method not found")
     *  )
     */
    public function destroy($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();
        return response()->json(['message' => 'Payment method deleted successfully']);
    }
}
