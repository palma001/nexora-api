<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PaymentMethod;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('payment_methods.view');
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('payment_methods.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('payment_methods.create');
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('payment_methods.update');
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('payment_methods.delete');
    }
}
