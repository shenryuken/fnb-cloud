<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Domain-level exception for order creation/validation failures.
 *
 * Carries a user-safe message that can be surfaced directly in the POS UI
 * (via a Livewire notify event) or returned as a 422 response by the API.
 * This lets the order domain logic live in framework-agnostic Actions/Services
 * instead of being coupled to Livewire's dispatch() mechanism.
 */
class OrderException extends RuntimeException
{
    public static function make(string $message): self
    {
        return new self($message);
    }
}
