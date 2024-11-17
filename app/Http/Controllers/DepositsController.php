<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Hash;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositsController extends Controller
{

    /**
     * Display a listing of the deposits.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $deposits = Deposit::with('customer')->paginate(25);

        return view('deposits.index', compact('deposits'));
    }

    public function depositType($type)
    {
        $deposits = Deposit::with('customer')->where('status', $type)->paginate(25);

        return view('deposits.index', compact('deposits'));
    }
    public function customerDepositList()
    {
        $customer = Auth::guard('customer')->user();
        $deposits = Deposit::with('customer')->where('customer_id', $customer->id)->paginate(25);

        return view('deposits.index', compact('deposits'));
    }

    /**
     * Show the form for creating a new deposit.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $gateways = ['1' => 'Bank', '2' => 'wallet'];
        return view('deposits.create', compact('gateways'));
    }

    /**
     * Store a new deposit in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->getData($request);
            $customer = Auth::guard('customer')->user();
            if ($customer) {
                $data['hash_id'] = $customer->id . '_' . now()->timestamp;
                $data['customer_id'] = $customer->id;
                $data['transaction_id'] = "TRA-" . $customer->id . '_' . now()->timestamp;;

                Deposit::create($data);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            info('Deposit Failled', [$e->getMessage()]);
            return ['status' => 400, 'message' => 'Deposit failled' . $e->getMessage()];
        }
        return redirect()->route('user.deposits.deposit.list')
            ->with('success_message', 'Deposit was successfully added.');
    }

    /**
     * Display the specified deposit.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $deposit = Deposit::with('customer', 'changedby')->findOrFail($id);

        return view('deposits.show', compact('deposit'));
    }

    /**
     * Show the form for editing the specified deposit.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $deposit = Deposit::findOrFail($id);

        return view('deposits.edit', compact('deposit', 'hashes', 'customers', 'transactions'));
    }

    /**
     * Update the specified deposit in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $deposit =  Deposit::where('id', $id)->first();
            $prev_status = $deposit->status;
            if ($deposit) {
                if ($deposit->status == $request->status) {
                    return redirect()->back()->with('success_message', 'Deposit Already updated.');
                }
                $data['status'] = $request->status;
                $data['status_change_by'] = Auth::user()->id;
                $data['change_date'] = now();
                $update_deposit = $deposit->update($data);

                if ($update_deposit) {
                    if ($request->status == 'approved') {
                        $customer = Customer::where('id', $deposit->customer_id)->first();
                        $customer->balance += $deposit->amount;
                        $customer->save();
                    }
                    if ($request->status == 'rejected' && $prev_status == 'approved') {
                        $customer = Customer::where('id', $deposit->customer_id)->first();
                        $customer->balance -= $deposit->amount;
                        $customer->save();
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            info('Deposit Updated Failled', [$e->getMessage()]);
            return ['status' => 400, 'message' => 'Deposit Updated failled' . $e->getMessage()];
        }
        return redirect()->back()->with('success_message', 'Deposit was successfully updated.');
    }

    /**
     * Remove the specified deposit from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $deposit = Deposit::findOrFail($id);
            $deposit->delete();

            return redirect()->route('deposits.deposit.index')
                ->with('success_message', 'Deposit was successfully deleted.');
        } catch (Exception $exception) {

            return back()->withInput()
                ->withErrors(['unexpected_error' => 'Unexpected error occurred while trying to process your request.']);
        }
    }


    /**
     * Get the request's data from the request.
     *
     * @param Illuminate\Http\Request\Request $request 
     * @return array
     */
    protected function getData(Request $request)
    {
        $rules = [
            'amount' => 'string|min:1|nullable',
            'gateway' => 'string|min:1|nullable',
        ];


        $data = $request->validate($rules);




        return $data;
    }
}
