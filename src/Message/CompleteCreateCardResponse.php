<?php

namespace Omnipay\Paystation\Message;

/**
 * Paystation Complete Create Card Response
 */
class CompleteCreateCardResponse extends CompletePurchaseResponse
{
    public function isSuccessful()
    {
        return $this->getCode() === "34";
    }
}
