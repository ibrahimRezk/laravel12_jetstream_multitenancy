<?php 
namespace App\Services;

class PaymentResult
{
    private $successful;
    private $paymentId;
    private $errorMessage;

    public function __construct($successful, $paymentId = null, $errorMessage = null)
    {
        $this->successful = $successful;
        $this->paymentId = $paymentId;
        $this->errorMessage = $errorMessage;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}