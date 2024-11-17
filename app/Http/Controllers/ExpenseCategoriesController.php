<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Exception;

class ExpenseCategoriesController extends Controller
{

    /**
     * Display a listing of the expense categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $expenseCategories = ExpenseCategory::paginate(25);

        return view('expense_categories.index', compact('expenseCategories'));
    }

    /**
     * Show the form for creating a new expense category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        
        
        return view('expense_categories.create');
    }

    /**
     * Store a new expense category in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $data = $this->getData($request);
        
        ExpenseCategory::create($data);

        return redirect()->route('expense_categories.expense_category.index')
            ->with('success_message', 'Expense Category was successfully added.');
    }

    /**
     * Display the specified expense category.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $expenseCategory = ExpenseCategory::findOrFail($id);

        return view('expense_categories.show', compact('expenseCategory'));
    }

    /**
     * Show the form for editing the specified expense category.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $expenseCategory = ExpenseCategory::findOrFail($id);
        

        return view('expense_categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified expense category in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {
        
        $data = $this->getData($request);
        
        $expenseCategory = ExpenseCategory::findOrFail($id);
        $expenseCategory->update($data);

        return redirect()->route('expense_categories.expense_category.index')
            ->with('success_message', 'Expense Category was successfully updated.');  
    }

    /**
     * Remove the specified expense category from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $expenseCategory = ExpenseCategory::findOrFail($id);
            $expenseCategory->delete();

            return redirect()->route('expense_categories.expense_category.index')
                ->with('success_message', 'Expense Category was successfully deleted.');
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
                'name' => 'string|min:1|max:255|nullable', 
        ];

        
        $data = $request->validate($rules);




        return $data;
    }

}
