<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\MembershipPlan;
use App\Models\Notification;
use Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

use Illuminate\Support\Facades\Crypt;


class MembershipPaymentController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage membership payment')) {


            if (\Auth::user()->type == 'member') {
                $user = Auth::user();
                $member = Member::where('user_id', $user->id)->first();
                
                // Get member's own payments
                $membershipPayments = MembershipPayment::where('parent_id', parentId())
                    ->where('member_id', $member->id)
                    ->orderBy('id', 'desc')
                    ->get();
                
                // If parent, also get all children's payments
                if ($member && $member->is_parent == 1) {
                    $childrenIds = Member::where('parent_member_id', $member->id)->pluck('id')->toArray();
                    $childPayments = MembershipPayment::where('parent_id', parentId())
                        ->whereIn('member_id', $childrenIds)
                        ->orderBy('id', 'desc')
                        ->get();
                    $membershipPayments = $membershipPayments->merge($childPayments);
                }
            } else {
                $membershipPayments = MembershipPayment::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            }


            return view('membership_payment.index', compact('membershipPayments'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        if (\Auth::user()->can('show membership payment')) {
            $id = \Illuminate\Support\Facades\Crypt::decrypt($id);
            $payment = MembershipPayment::where('id', $id)->where('parent_id', parentId())->first();
            // dd($payment);
            $membership = Membership::where('member_id', $payment->member_id)->where('plan_id', $payment->plan_id)->first();
            $invoicePaymentSettings = invoicePaymentSettings($payment->parent_id);

            return view('membership_payment.show', compact('payment', 'membership', 'invoicePaymentSettings'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(Request $request, $id)
    {
        $payment_methods = MembershipPayment::$payment_method;
        if (!empty($request->plan)) {
            $membership = MembershipPlan::find($id);
            $amount = $membership->price;
        } else {
            $membership = Membership::find($id);
            $amount = $membership->plans->price;
        }

        $settings = $this->paymentSettings();
        $invoicePaymentSettings = invoicePaymentSettings(parentId());

        return view('membership_payment.payment', compact('membership', 'amount', 'payment_methods', 'settings', 'invoicePaymentSettings'));
    }


    public function update(Request $request, MembershipPayment $membershipPayment)
    {

        // dd($request->all());
        $validetor = \Validator::make(
            $request->all(),
            [
                'payment_date' => 'required',
                'payment_method' => 'required',
            ]
        );

        if ($validetor->fails()) {
            $messages = $validetor->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        // $membershipPayment->payment_date = $request->payment_date;
        // $membershipPayment->payment_method = $request->payment_method;
        // $membershipPayment->notes = !empty($request->notes) ? $request->notes : '';
        // $membershipPayment->status = 'Paid';
        // $membershipPayment->save();

        $transactionID = uniqid('', true);
        $payment['payment_id'] = $membershipPayment->payment_id;
        $payment['member_id'] = $membershipPayment->member_id;
        $payment['plan_id'] = $membershipPayment->plan_id;
        $payment['transaction_id'] = $transactionID;
        $payment['payment_type'] = $request->payment_method;
        $payment['amount'] = $request->amount;
        $payment['notes'] = !empty($request->notes) ? $request->notes : '';

        Membership::addPayment($payment);


        $module = 'payment_create';
        $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
        $setting = settings();
        $errorMessage = '';

        if (!empty($notification)) {
            $notificationResponse = MessageReplace($notification, $membershipPayment->id);

            $data['subject'] = $notificationResponse['subject'];
            $data['message'] = $notificationResponse['message'];
            $data['module'] = $module;
            $data['logo'] = $setting['company_logo'];
            $to = $membershipPayment->members->email;


            if ($notification->enabled_email == 1) {
                $response = commonEmailSend($to, $data);

                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }
            if ($notification->enabled_sms == 1) {
                $twilio_sid = getSettingsValByName('twilio_sid');
                if (!empty($twilio_sid)) {
                    send_twilio_msg($membershipPayment->members->phone, $response['sms_message']);
                }
            }
        }


        return redirect()->back()->with('success', __('Membership Payment successfully.') . '' . $errorMessage);
    }


    public function destroy(MembershipPayment $membershipPayment)
    {
        if (\Auth::user()->can('delete membership payment')) {
            $payment = MembershipPayment::where('id', $membershipPayment->id)->where('parent_id', parentId())->first();
            if ($payment) {

                $payment->delete();
                // $payment->payment_date = null;
                // $payment->payment_method = null;
                // $payment->notes = null;
                // $payment->status = 'Unpaid';
                // $payment->save();

                return redirect()->route('membership-payment.index')->with('success', __('Membership Payment successfully deleted.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function paymentSettings()
    {
        $paymentSetting = invoicePaymentSettings(parentId());
        return $paymentSetting;
    }

    public function stripePayment(Request $request, $ids)
    {
        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->first();
        // dd($request->all());
        $settings = $this->paymentSettings();
        $id = \Illuminate\Support\Facades\Crypt::decrypt($ids);

        if (!empty($request->type) && $request->type == 'member_payment') {
            $MembershipPlan = MembershipPayment::find($id);
            $plan_name = $MembershipPlan->plan_name;
            $amount = $MembershipPlan->amount;
        } else {
            $MembershipPlan = MembershipPlan::find($id);
            $plan_name = $MembershipPlan->plan_name;
            $amount = $MembershipPlan->price;
        }

        if ($MembershipPlan) {
            try {
                $transactionID = uniqid('', true);
                Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                $data = Stripe\Charge::create(
                    [
                        "amount" => 100 * $amount,
                        "currency" => $settings['CURRENCY'],
                        "source" => $request->stripeToken,
                        "description" => "Membership Plan Purchase - " . $plan_name,
                        "metadata" => ["order_id" => $transactionID, 'type' => $request->type],
                        "shipping" => [
                            'name' => $request->name,
                            'address' => [
                                'line1' => $request->state ?? 'NA',
                                'city' => $request->city ?? 'NA',
                                'postal_code' => $request->zipcode ?? '000000',
                                'country' => $request->country ?? 'NA',
                            ]
                        ],
                    ]
                );


                if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {


                    if ($data['status'] == 'succeeded') {
                        $payment['payment_id'] = paymentNumber();
                        $payment['member_id'] = $member->id;

                        if (!empty($request->type) && $request->type == 'member_payment') {
                            $payment['plan_id'] = $MembershipPlan->plan_id;
                        } else {
                            $payment['plan_id'] = $MembershipPlan->id;
                        }
                        $payment['transaction_id'] = $transactionID;
                        $payment['payment_type'] = 'Stripe';

                        if (!empty($request->type) && $request->type == 'member_payment') {
                            $payment['amount'] = $MembershipPlan->amount;
                        } else {
                            $payment['amount'] = $MembershipPlan->price;
                        }

                        $payment['receipt'] =  isset($data['receipt_url']) ? $data['receipt_url'] : '';
                        $payment['notes'] = 'Stripe Payment';


                        $type = $request->type ?? '';

                        Membership::addPayment($payment, $type);

                        if (!empty($request->type) && $request->type == 'member_payment') {
                            return redirect()->back()->with('success', __('Membership payment successfully completed.'));
                        } else {
                            return redirect()->back()->with('success', __('Membership payment successfully completed.'));
                        }
                    } else {
                        return redirect()->back()->with('error', __('Your payment has failed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Transaction has been failed.'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->back()->with('error', __('Membership is deleted.'));
        }
    }


    public function invoicePaypal(Request $request, $id)
    {

        // dd($request->all(),$id);
        $membershipId = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $id = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $paypalSetting = $this->paymentSettings();

        if ($paypalSetting['paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        }

        if (!empty($request->type) && $request->type == 'member_payment') {
            $MembershipPlan = MembershipPayment::find($id);
            $plan_name = $MembershipPlan->plan_name;
            $amount = $MembershipPlan->amount;
        } else {
            $MembershipPlan = MembershipPlan::find($id);
            $plan_name = $MembershipPlan->plan_name;
            $amount = $MembershipPlan->price;
        }


        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('membership.paypal.status', [$membershipId, 'success'], ['amount' =>  $amount]),
                "cancel_url" => route('membership.paypal.status', [$membershipId, 'cancel'], ['amount' =>  $amount]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
                        "value" =>  $amount
                    ]
                ]
            ]
        ]);

        // dd($response);
        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()
                ->back()
                ->with('error', 'Something went wrong.');
        } else {
            return redirect()
                ->back()
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    public function invoicePaypalStatus(Request $request, $membershipId, $status)
    {
        if ($status == 'success') {

            $user = Auth::user();
            $member = Member::where('user_id', $user->id)->first();


            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $transactionID = uniqid('', true);
            $membership = MembershipPlan::find($membershipId);
            $response = $provider->capturePaymentOrder($request['token']);
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $payment['payment_id'] = paymentNumber();
                $payment['member_id'] = $member->id;
                $payment['plan_id'] = $membership->plan_id;
                $payment['transaction_id'] = $transactionID;
                $payment['payment_type'] = 'Paypal';
                $payment['amount'] = $request->amount;
                $payment['receipt'] = '';
                $payment['notes'] = 'Paypal Payment';


                Membership::addPayment($payment);

                if (!empty($request->type) && $request->type == 'member_payment') {
                    return redirect()->back()->with('success', __('Membership payment successfully completed.'));
                } else {
                    return redirect()->back()->with('success', __('Membership payment successfully completed.'));
                }
            } else {
                return redirect()
                    ->back()
                    ->with('error', $response['message'] ?? __('Something went wrong.'));
            }
        } else {
            return redirect()
                ->back()
                ->with('error', __('Transaction has been failed.'));
        }
    }

    public function banktransferPayment(Request $request, $id)
    {
        $membershipId = \Illuminate\Support\Facades\Crypt::decrypt($id);

        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->first();

        $validator = \Validator::make(
            $request->all(),
            [
                'receipt' => 'required',
                'amount' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // $membershipPayment = MembershipPayment::find($membershipId);

        if (!empty($request->type) && $request->type == 'member_payment') {
            $membership = MembershipPayment::find($membershipId);
        } else {
            $membership = MembershipPlan::find($membershipId);
        }

        $transactionID = uniqid('', true);

        $payment['payment_id'] = paymentNumber();
        $payment['member_id'] = $member->id;
        $payment['plan_id'] = $membership->plan_id;
        $payment['transaction_id'] = $transactionID;
        $payment['payment_type'] = 'Bank Transfer';
        $payment['amount'] = $request->amount;
        $payment['receipt'] =  '';
        $payment['notes'] = $request->notes;

          if (!empty($request->receipt)) {
            $recieptFilenameWithExt = $request->file('receipt')->getClientOriginalName();
            $recieptFilename = pathinfo($recieptFilenameWithExt, PATHINFO_FILENAME);
            $recieptExtension = $request->file('receipt')->getClientOriginalExtension();
            $recieptFileName = $recieptFilename . '_' . time() . '.' . $recieptExtension;

            $dir = storage_path('upload/receipt');
            $image_path = $dir . $recieptFilenameWithExt;


            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $request->file('receipt')->storeAs('upload/receipt/', $recieptFileName);
            $payment['receipt'] = $recieptFileName;
        }
 
        Membership::addPayment($payment);

        if (!empty($request->type) && $request->type == 'member_payment') {
            return redirect()->back()->with('success', __('Membership payment successfully completed.'));
        } else {
            return redirect()->back()->with('success', __('Membership payment successfully completed.'));
        }
    }


    public function invoiceFlutterwave(Request $request, $membership_id, $pay_id)
    {
        $membershipID = \Illuminate\Support\Facades\Crypt::decrypt($membership_id);
        $MembershipPlan = MembershipPlan::find($membershipID);
        // dd($request->all(),$membership_id,$pay_id,$membershipID,$MembershipPlan);

        if (!empty($request->type) && $request->type == 'member_payment') {
            $MembershipPlan = MembershipPayment::find($membershipID);
        } else {
            $MembershipPlan = MembershipPlan::find($membershipID);
        }

        $paymentSetting = $this->paymentSettings();

        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->first();

        if ($MembershipPlan) {
            try {
                $detail = [
                    'txref' => $pay_id,
                    'SECKEY' => $paymentSetting['flutterwave_secret_key'],
                ];
                $url = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify";
                $headersData = ['Content-Type' => 'application/json'];
                $bodyData = \Unirest\Request\Body::json($detail);
                $responseData = \Unirest\Request::post($url, $headersData, $bodyData);

                if (!empty($responseData)) {
                    $responseData = json_decode($responseData->raw_body, true);
                }

                if (isset($responseData['status']) && $responseData['status'] == 'success') {
                    $amountPaid = $responseData['data']['amount'];
                    $expectedAmount = $request->query('amount'); // Get amount from request

                    if ($amountPaid < $expectedAmount) {
                        return redirect()->back()->with('error', __('Payment amount mismatch! Expected: ') . $expectedAmount);
                    }

                    $membershipTransId = uniqid('', true);

                    $payment['payment_id'] = paymentNumber();
                    $payment['member_id'] = $member->id;
                    $payment['plan_id'] = $MembershipPlan->plan_id;
                    $payment['transaction_id'] = $membershipTransId;
                    $payment['payment_type'] = 'Flutterwave';
                    $payment['amount'] = $amountPaid;
                    $payment['receipt'] = '';
                    $payment['notes'] = 'Flutterwave Payment';

                    Membership::addPayment($payment);


                    if (!empty($request->type) && $request->type == 'member_payment') {
                        return redirect()->back()->with('success', __('Membership payment successfully completed.'));
                    } else {
                        return redirect()->back()->with('success', __('Membership payment successfully completed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Transaction failed!'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }


    public function invoicePaystack(Request $request, $ids)
    {

        // dd($request->all());

        $payment_setting = $this->paymentSettings();
        $currency = $payment_setting['CURRENCY'] ?? 'USD';
        $id = Crypt::decrypt($ids);


        if (!empty($request->type) && $request->type == 'member_payment') {
            $membership = MembershipPayment::find($id);
        } else {
            $membership = MembershipPlan::find($id);
        }

        if (!$membership) {
            return response()->json([
                'flag' => 0,
                'message' => __('Membership Payment not found.')
            ]);
        }

        $amount = $request->amount;

        // dd($amount);
        if ($amount <= 0) {
            return response()->json([
                'flag' => 0,
                'message' => __('Amount must be greater than 0.')
            ]);
        }

        return response()->json([
            'flag' => 1,
            'email' => auth()->user()->email,
            'total_price' => $amount,
            'currency' => $currency,
        ]);
    }


    public function invoicePaystackStatus(Request $request, $pay_id, $membership_id_encrypted)
    {

        try {

            if (!empty($request->type) && $request->type == 'member_payment') {
                $MembershipPlan = MembershipPayment::find(Crypt::decrypt($membership_id_encrypted));
            } else {
                $MembershipPlan = MembershipPlan::find(Crypt::decrypt($membership_id_encrypted));
            }

            $user = Auth::user();
            $member = Member::where('user_id', $user->id)->first();

            // dd($membership);
            if (!$MembershipPlan) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }

            $secretKey = $this->paymentSettings()['paystack_secret_key'] ?? '';
            $verifyUrl = "https://api.paystack.co/transaction/verify/$pay_id";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $verifyUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $secretKey],
            ]);
            $response = curl_exec($ch);

            curl_close($ch);
            $result = $response ? json_decode($response, true) : [];

            if (!($result['status'] ?? false) || ($result['data']['status'] !== 'success')) {
                return redirect()->back()->with('error', __('Transaction failed or cancelled.'));
            }

            $payment = [
                'payment_id'     => paymentNumber(),
                'member_id'     => $member->id,
                'plan_id'     => $MembershipPlan->plan_id,
                'transaction_id' => uniqid('', true),
                'payment_type'   => 'Paystack',
                'amount'         => $result['data']['amount'] / 100,
                'receipt'        => '',
                'notes'          => 'Paystack Payment',
            ];

            Membership::addPayment($payment);

            if (!empty($request->type) && $request->type == 'member_payment') {
                return redirect()->back()->with('success', __('Membership payment successfully completed.'));
            } else {
                return redirect()->back()->with('success', __('Membership payment successfully completed.'));
            }
        } catch (\Exception $e) {

            // dd($e);
            return redirect()->back()->with('error', __('Something went wrong while verifying the payment.'));
        }
    }


    public function invoiceBankTransferAction($id, $status)
    {

        $membershipPayment = MembershipPayment::find($id);

        if ($status == 'accept') {

            if (!empty($membershipPayment)) {
                $membershipPayment->status = 'Paid';
                $membershipPayment->save();
            }
        } else {

            $membershipPayment->status = 'Unpaid';
            $membershipPayment->save();
        }

        return redirect()
            ->back()
            ->with('success', __('Membership payment status is ' . $status));
    }
}
