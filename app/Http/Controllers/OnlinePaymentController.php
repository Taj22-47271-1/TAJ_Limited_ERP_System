<?php
namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
    public function show(Request $request, OnlineOrder $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->payment_method === 'online', 404);
        return view('customer.payment', compact('order'));
    }

    public function pay(Request $request, OnlineOrder $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->payment_method === 'online', 404);
        $data = $request->validate([
            'payment_channel'=>['required','in:card,bkash,nagad'],
            'transaction_reference'=>['nullable','string','max:100'],
        ]);
        if ($order->payment_status === 'paid') return redirect()->route('customer.orders.show',$order)->with('status','Payment is already completed.');
        if ($order->order_status !== 'pending') return back()->withErrors(['payment'=>'Payment cannot be changed after order processing starts.']);

        // Local-development sandbox: records a successful payment without calling a real gateway.
        $order->update([
            'payment_channel'=>$data['payment_channel'],
            'payment_status'=>'paid',
            'payment_reference'=>$data['transaction_reference'] ?: 'DEMO-'.strtoupper($data['payment_channel']).'-'.now()->format('YmdHis'),
        ]);
        return redirect()->route('customer.orders.show',$order)->with('status','Online payment recorded in demo mode. Your order is waiting for approval.');
    }
}
