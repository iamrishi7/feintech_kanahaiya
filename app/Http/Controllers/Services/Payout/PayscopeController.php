<?php

namespace App\Http\Controllers\Services\Payout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayoutRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PayscopeController extends Controller
{

    public function abortRequest(Response $response, PayoutRequest $request)
    {
        if ($response['code'] != '0x0200') {
            Log::info(['err_req' => $request->validated()]);
            Log::info(['error_payscope' => $response->body()]);
            $this->releaseLock($request->user()->id);
            abort(400, $response['status']);
        }
    }

    public function processResponse(Response $response)
    {
        if ($response['code'] == '0x0200') {
            $data = [
                'status' => 'success',
                'message' => $response['message'],
                'utr' => null,
                'transaction_status' => strtolower($response['data']['status'])
            ];
        } else {
            $data = [
                'status' => 'failed',
                'message' => $response['message']
            ];
        }

        return ['data' => $data, 'response' => $response->body()];
    }

    public function createContact(PayoutRequest $request, $reference_id)
    {
        $data = [
            'firstName' => $request['beneficiary_name'],
            'lastName' => $request['beneficiary_name'],
            'email' => $request->user()->email,
            'mobile' => $request->user()->phone_number,
            'type' => 'customer',
            'accountType' => 'bank_account',
            'bankName' => $request->bank_name2 ?? $request->bank_name ,
            'accountNumber' => $request->account_number,
            'ifsc' => strtoupper($request->ifsc_code),
            'referenceId' => preg_replace("/^PAY-\d+/", 'REF-', $reference_id),
        ];

        $response = Http::withBasicAuth(config('services.payscope.client_id'), config('services.payscope.client_secret'))
            ->post(config('services.payscope.base_url') . '/service/payout/contacts', $data);
        if (is_array($response['message'])) {
            if (array_key_exists('contact_id', $response['message'])) {
                return $response['message']['contact_id'][0];
            }
        }
        Log::info(['req' => $data]);
        $this->abortRequest($response, $request);

        return $response['data']['contactId'];
    }

    public function initiateTransaction(PayoutRequest $request, string $reference_id)
    {
        $data = [
            'amount' => $request->amount,
            'purpose' => 'others',
            'mode' => strtoupper($request->mode),
            'contactId' => $this->createContact($request, $reference_id),
            'clientRefId' => $reference_id,
            'udf1' => '',
            'udf2' => ''
        ];

        $response = Http::withBasicAuth(config('services.payscope.client_id'), config('services.payscope.client_secret'))
            ->post(config('services.payscope.base_url') . '/service/payout/orders', $data);

        $this->abortRequest($response, $request);

        return $this->processResponse($response);
    }
}
