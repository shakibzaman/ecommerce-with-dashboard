<?php

namespace App\Http\Controllers\Admin\Wholesaler;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserAccount;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use function App\Helpers\createTransaction;

class WholesalerController extends Controller
{
    public function index()
    {
        $users = User::with('account')->where('type', 4)->orderBy('id', 'desc')->get(); // Assuming type 4 is for Wholesaler
        return view('wholesalers.index', compact('users'));
    }

    public function create()
    {
        return view('admin.wholesalers.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15|min:3',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'status' => 'required|string|max:1',
                'previous_due' => 'nullable|integer|min:0'
            ]);
            info('Validate data', [$validatedData]);
            $validatedData['password'] = Hash::make($validatedData['password']);
            $validatedData['type'] = 4; // 4 is for wholesaler

            $user = User::create($validatedData);

            // Create associated user_account
            $userAccount = UserAccount::create([
                'user_id' => $user->id,
                'previous_due' => $request->input('previous_due', 0) ?? 0,
                'total_due' => $request->input('previous_due', 0) ?? 0,
            ]);
            info('userAccount is ', [$userAccount]);
            // Pass the transaction note dynamically
            $transactionCreate = createTransaction(
                $user->id,
                config('app.transaction_payable_type.wholesaler'),
                (int) $request->input('previous_due'),
                config('app.transaction_type.sell'),
                FacadesAuth::user()->id,
                'Wholesaler Previous Due Created'
            );
            // info('transaction is ', [$transaction]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            info('Add details error', [$e]);
            return $e;
        }
        return redirect()->route('wholesalers.index')->with('success', 'wholesaler created successfully.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.wholesaler.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.wholesaler.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'status' => 'required|string|max:1',
                'previous_due' => 'nullable|integer|min:0'
            ]);

            if ($request->filled('password')) {
                $validatedData['password'] = Hash::make($request->input('password'));
            } else {
                unset($validatedData['password']);
            }

            $user->update($validatedData);

            // Access the UserAccount model
            $account = $user->account; // This retrieves the related UserAccount instance
            $previous_due_balance = $user->account->previous_due;
            // Update associated user_account
            $previousDue = $request->input('previous_due', $account->previous_due);
            $account->update([
                'previous_due' => $previousDue,
                'total_due' => $account->total_due == 0 ? $previousDue : $account->total_due + $previousDue,
            ]);

            // Check if the previous_due has changed, then create a transaction
            if ($previous_due_balance != (int) $request->input('previous_due')) {
                $transactionCreate = createTransaction(
                    $id,
                    config('app.transaction_payable_type.wholesaler'),
                    (int) $request->input('previous_due'),
                    config('app.transaction_type.sell'),
                    FacadesAuth::user()->id,
                    'Wholesaler Previous Due Updated'
                );
            }

            DB::commit();
        } catch (Exception $e) {
            info('wholesaler Update data error', [$e]);
            DB::rollBack();
            return $e;
        }

        return redirect()->route('wholesalers.index')->with('success', 'wholesaler updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('wholesalers.index')->with('success', 'wholesaler deleted successfully.');
    }
    public function order()
    {
        $orders = Invoice::with('causer', 'products')->where('invoice_type', config('app.user_type.wholesaler'))->get();
        $order_type = config('app.user_type.wholesaler');
        return view('invoices.orders.index', compact('orders', 'order_type'));
    }
}
