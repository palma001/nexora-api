<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Http\Requests\Api\StorePaymentMethodRequest;
use App\Http\Requests\Api\UpdatePaymentMethodRequest;
use App\Http\Resources\Api\PaymentMethodResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentMethodController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', PaymentMethod::class);
        return PaymentMethodResource::collection(PaymentMethod::orderBy('name')->get());
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $this->authorize('create', PaymentMethod::class);
        $paymentMethod = PaymentMethod::create($request->validated());
        return new PaymentMethodResource($paymentMethod);
    }

    public function show(PaymentMethod $paymentMethod)
    {
        $this->authorize('view', $paymentMethod);
        return new PaymentMethodResource($paymentMethod);
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);
        $paymentMethod->update($request->validated());
        return new PaymentMethodResource($paymentMethod);
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('delete', $paymentMethod);
        $paymentMethod->delete();
        return response()->json(null, 204);
    }
}
