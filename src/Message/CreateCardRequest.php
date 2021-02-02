<?php

namespace Omnipay\Paystation\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Paystation Create Card (token) Request
 *
 * Documentation:
 * @link https://docs.paystation.co.nz/#token-save
 */
class CreateCardRequest extends AbstractRequest
{

    protected $endpoint = 'https://www.paystation.co.nz/direct/paystation.dll';

    public function getPaystationId()
    {
        return $this->getParameter('paystationId');
    }

    public function setPaystationId($value)
    {
        return $this->setParameter('paystationId', $value);
    }

    public function getGatewayId()
    {
        return $this->getParameter('gatewayId');
    }

    public function setGatewayId($value)
    {
        return $this->setParameter('gatewayId', $value);
    }

    public function getMerchantSession()
    {
        return $this->getParameter('merchantSession');
    }

    public function setMerchantSession($value)
    {
        return $this->setParameter('merchantSession', $value);
    }

    public function getHmacKey()
    {
        return $this->getParameter('hmacKey');
    }

    public function setHmacKey($value)
    {
        return $this->setParameter('hmacKey', $value);
    }

    protected function getBaseData()
    {
        $data = array();
        $data['paystation'] = '_empty';
        $data['pstn_nr'] = 't';
        $data['pstn_pi'] = $this->getPaystationId();
        $data['pstn_gi'] = $this->getGatewayId();
        $merchantSession = $this->getMerchantSession();
        if (!$merchantSession) {
            $merchantSession = uniqid();
        }
        $data['pstn_ms'] = $merchantSession;
        $data['pstn_fp'] = 't';
        $data['pstn_fs'] = 't';

        return $data;
    }

    public function getData()
    {
        $this->validate('paystationId', 'gatewayId');
        //required
        $data = $this->getBaseData();
        $data['pstn_tm'] = $this->getTestMode() ? 'T' : null;
        $data['pstn_mr'] = $this->getTransactionId();
        if ($this->getHmacKey() && $this->getReturnUrl()) {
            $data['pstn_du'] = urlencode($this->getReturnUrl());
        }

        return $data;
    }

    public function sendData($data)
    {
        $postdata = http_build_query($data);
        $httpResponse = $this->httpClient->request('POST', $this->getEndPoint($postdata), ['Content-Type' => 'application/x-www-form-urlencoded'], $postdata);

        return $this->response = new CreateCardResponse($this, $httpResponse->getBody());
    }


    /**
     * Get the endpoint for this request.
     * Will include hmac data in GET query, if necessary.
     * @return string endpoint url
     */
    protected function getEndPoint($postdata)
    {
        $url = $this->endpoint;
        if ($this->getHmacKey()) {
            $qd = array();
            $timestamp = time();
            $qd['pstn_HMACTimestamp'] = $timestamp;
            $qd['pstn_HMAC'] = $this->getHmac($timestamp, $postdata);
            $url .= '?' . http_build_query($qd);
        }

        return $url;
    }

    /**
     * Generate the hmac hash to be passed in endpoint url
     *
     * Code modified from
     * @link http://www.paystation.co.nz/cms_show_download.php?id=69
     * @return string hmac
     */
    protected function getHmac($timestamp, $postdata)
    {
        $authenticationKey = $this->getHmacKey();
        $hmacWebserviceName = 'paystation'; //webservice identification.
        $hmacBody = pack('a*', $timestamp) . pack('a*', $hmacWebserviceName) . pack('a*', $postdata);
        $hmacHash = hash_hmac('sha512', $hmacBody, $authenticationKey);

        return $hmacHash;
    }
}
