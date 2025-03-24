<?php

namespace App\Http\Controllers\Services\Payout;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionController;
use App\Models\Payout;
use App\Models\Transaction;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function eko(Request $request)
    {
        Log::info(['callback-eko' => $request->all()]);
        $response = DB::transaction(function () use ($request) {
            $transaction = Transaction::where('reference_id', $request->client_ref_id)->firstOrFail();
            $lock = $this->lockRecords($transaction->user_id);

            if (!$lock->get()) {
                throw new HttpResponseException(response()->json(['data' => ['message' => "Failed to acquire lock"]], 423));
            }

            if (in_array($request['tx_status'], [1, 4])) {
                if ($transaction->status == 'failed') {
                    return response("Success", 200);
                }
                TransactionController::reverseTransaction($transaction->reference_id);
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'failed',
                    'utr' => $request['bank_ref_num']
                ]);
            } elseif ($request['tx_status'] == 0) {
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'success',
                    'utr' => $request['bank_ref_num']
                ]);
            }


            $lock->release();
            return response("Success", 200);
        }, 2);

        return $response;
    }

    public function safexpay(Request $request)
    {
        Log::info(['callback-sxpay' => $request->all()]);
        $request->validate([
            'payload' => ['required']
        ]);

        $response = DB::transaction(function () use ($request) {
            $controller = new SafexpayController;
            $decryption = $controller->decrypt($request->payload, config('services.safexpay.merchant_key'), config('services.safexpay.iv'));

            $transaction = Transaction::where('reference_id', $decryption->transactionDetails->orderRefNo)->firstOrFail();
            $lock = $this->lockRecords($transaction->user_id);

            if (!$lock->get()) {
                throw new HttpResponseException(response()->json(['data' => ['message' => "Failed to acquire lock"]], 423));
            }

            if (in_array(strtolower($decryption->transactionDetails->txnStatus), ["failed", "reversed"])) {
                if ($transaction->status == 'failed' || $transaction->status == 'reversed') {
                    return response("Success", 200);
                }
                TransactionController::reverseTransaction($transaction->reference_id);
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'failed',
                    'utr' => $decryption->transactionDetails->bankRefNo ?? null
                ]);
            } elseif (strtolower($decryption->transactionDetails->txnStatus) == "success") {
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'success',
                    'utr' => $decryption->transactionDetails->bankRefNo ?? null
                ]);
            }


            $lock->release();
            return response("Success", 200);
        }, 2);

        return $response;
    }

    public function groscope(Request $request)
    {
        Log::info(['callback-gro' => $request->all()]);

        $response = DB::transaction(function () use ($request) {

            $transaction = Transaction::where('reference_id', $request['data']['order_id'])->firstOrFail();
            $lock = $this->lockRecords($transaction->user_id);

            if (!$lock->get()) {
                throw new HttpResponseException(response()->json(['data' => ['message' => "Failed to acquire lock"]], 423));
            }

            if (in_array(strtolower($request['data']['status']), ["failed", "reversed"])) {
                if ($transaction->status == 'failed' || $transaction->status == 'reversed') {
                    return response("Success", 200);
                }
                TransactionController::reverseTransaction($transaction->reference_id);
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'failed',
                    'utr' => $request['data']['utr_number'] ?? null
                ]);
            } elseif (strtolower($request['data']['status']) == "success") {
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'success',
                    'utr' => $request['data']['utr_number'] ?? null
                ]);
            }

            $lock->release();
            return response("Success", 200);
        }, 2);

        return $response;
    }

    public function payscope(Request $request)
    {
        Log::info(['callback-payscope' => $request->all()]);

        $data = DB::transaction(function () use ($request) {
            $transaction = Transaction::where('reference_id', $request['data']['clientRefId'])->firstOrFail();
            $lock = $this->lockRecords($transaction->user_id);

            if (!$lock->get()) {
                throw new HttpResponseException(response()->json(['data' => ['message' => "Failed to acquire lock"]], 423));
            }

            if ($request['event'] == 'payout.transfer.failed') {
                if ($transaction->status == 'failed' || $transaction->status == 'reversed') {
                    return response("Success", 200);
                }
                TransactionController::reverseTransaction($transaction->reference_id);
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'failed',
                    'utr' => $request['data']['utr'] ?? null
                ]);
            } elseif ($request['event'] == 'payout.transfer.success') {
                Payout::where('reference_id', $transaction->reference_id)->update([
                    'status' => 'success',
                    'utr' => $request['data']['utr'] ?? null
                ]);
            }

            $lock->release();
            return response("Success", 200);
        }, 2);

        return $data;
    }

    public function cashfree(Request $request)
    {
        Log::info(['callback-cf' => $request->all()]);

        if ($request->has('type') && in_array(strtolower($request->type), ['transfer_rejected', 'transfer_reversed', 'transfer_failed', 'transfer_acknowledged', 'transfer_success', 'transfer_approved'])) {
            $response = DB::transaction(function () use ($request) {
                $transaction = Transaction::where('reference_id', $request['data']['transfer_id'])->firstOrFail();
                $lock = $this->lockRecords($transaction->user_id);

                if (!$lock->get()) {
                    throw new HttpResponseException(response()->json(['data' => ['message' => "Failed to acquire lock"]], 423));
                }

                if (in_array(strtolower($request['type']), ['transfer_rejected', 'transfer_reversed', 'transfer_failed'])) {
                    if ($transaction->status == 'failed' || $transaction->status == 'success') {
                        return response("Success", 200);
                    }
                    TransactionController::reverseTransaction($transaction->reference_id);
                    Payout::where('reference_id', $transaction->reference_id)->update([
                        'status' => 'failed',
                        'utr' => $request['data']['transfer_utr'] ?? null
                    ]);
                } elseif (in_array(strtolower($request['type']), ['transfer_acknowledged', 'transfer_success', 'transfer_approved'])) {
                    Payout::where('reference_id', $transaction->reference_id)->update([
                        'status' => 'success',
                        'utr' => $request['data']['transfer_utr'] ?? null
                    ]);
                }


                $lock->release();
                return response("Success", 200);
            }, 2);
            return $response;
        } else {
            return response("Success", 200);
        }
    }
}
