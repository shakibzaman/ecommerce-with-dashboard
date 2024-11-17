<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use Illuminate\Http\Request;
use Exception;

class DeliveryCompaniesController extends Controller
{

    /**
     * Display a listing of the delivery companies.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $deliveryCompanies = DeliveryCompany::paginate(25);

        return view('delivery_companies.index', compact('deliveryCompanies'));
    }

    /**
     * Show the form for creating a new delivery company.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        
        
        return view('delivery_companies.create');
    }

    /**
     * Store a new delivery company in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $data = $this->getData($request);
        
        DeliveryCompany::create($data);

        return redirect()->route('delivery_companies.delivery_company.index')
            ->with('success_message', 'Delivery Company was successfully added.');
    }

    /**
     * Display the specified delivery company.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $deliveryCompany = DeliveryCompany::findOrFail($id);

        return view('delivery_companies.show', compact('deliveryCompany'));
    }

    /**
     * Show the form for editing the specified delivery company.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $deliveryCompany = DeliveryCompany::findOrFail($id);
        

        return view('delivery_companies.edit', compact('deliveryCompany'));
    }

    /**
     * Update the specified delivery company in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {
        
        $data = $this->getData($request);
        
        $deliveryCompany = DeliveryCompany::findOrFail($id);
        $deliveryCompany->update($data);

        return redirect()->route('delivery_companies.delivery_company.index')
            ->with('success_message', 'Delivery Company was successfully updated.');  
    }

    /**
     * Remove the specified delivery company from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $deliveryCompany = DeliveryCompany::findOrFail($id);
            $deliveryCompany->delete();

            return redirect()->route('delivery_companies.delivery_company.index')
                ->with('success_message', 'Delivery Company was successfully deleted.');
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
