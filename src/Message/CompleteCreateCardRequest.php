<?php

namespace Omnipay\Paystation\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Paystation Complete Create Card Request
 *
 *
 * uses quicklookup service, as described here:
 * @link https://docs.paystation.co.nz/#quick-lookup
 */
class CompleteCreateCardRequest extends CreateCardRequest
{
    protected $endpoint = "https://payments.paystation.co.nz/lookup/";

    public function getData()
    {
        $query = $this->httpRequest->query;
        $ti = $query->get('ti'); //transaction reference

        if (!$ti) {
            throw new InvalidRequestException('Transaction reference is missing');
        }
        $data = array();
        $data['pi'] = $this->getPaystationId();
        $data['ti'] = $ti;

        return $data;
    }

    public function sendData($data)
    {
        $postdata = http_build_query($data);
        $httpResponse = $this->httpClient->request('POST', $this->getEndPoint($postdata), ['Content-Type' => 'application/x-www-form-urlencoded'], $postdata);

        return $this->response = new CompleteCreateCardResponse($this, $httpResponse->getBody());
    }
}
