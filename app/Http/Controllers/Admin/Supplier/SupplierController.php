<?php

namespace App\Http\Controllers\Admin\Supplier;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccount;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use function App\Helpers\createTransaction;

class SupplierController extends Controller
{
    public function index()
    {
        $users = User::with('account')->where('type', 3)->orderBy('id', 'desc')->get(); // Assuming type 3 is for suppliers
        return view('suppliers.index', compact('users'));
    }

    public function create()
    {
        return view('admin.supplier.create');
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
            $validatedData['type'] = 3; // 3 is for Supplier

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
                config('app.transaction_payable_type.supplier'),
                (int) $request->input('previous_due'),
                config('app.transaction_type.purchase'),
                FacadesAuth::user()->id,
                'Supplier Previous Due Created'
            );
            // info('transaction is ', [$transaction]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            info('Add details error', [$e]);
            return $e;
        }
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.supplier.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.supplier.edit', compact('user'));
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
                    config('app.transaction_payable_type.supplier'),
                    (int) $request->input('previous_due'),
                    config('app.transaction_type.purchase'),
                    FacadesAuth::user()->id,
                    'Supplier Previous Due Updated'
                );
            }

            DB::commit();
        } catch (Exception $e) {
            info('Supplier Update data error', [$e]);
            DB::rollBack();
            return $e;
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
