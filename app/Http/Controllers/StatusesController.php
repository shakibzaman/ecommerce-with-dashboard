<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use Exception;

class StatusesController extends Controller
{

    /**
     * Display a listing of the statuses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $statuses = Status::paginate(25);

        return view('statuses.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new status.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        
        
        return view('statuses.create');
    }

    /**
     * Store a new status in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $data = $this->getData($request);
        
        Status::create($data);

        return redirect()->route('statuses.status.index')
            ->with('success_message', 'Status was successfully added.');
    }

    /**
     * Display the specified status.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $status = Status::findOrFail($id);

        return view('statuses.show', compact('status'));
    }

    /**
     * Show the form for editing the specified status.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $status = Status::findOrFail($id);
        

        return view('statuses.edit', compact('status'));
    }

    /**
     * Update the specified status in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {
        
        $data = $this->getData($request);
        
        $status = Status::findOrFail($id);
        $status->update($data);

        return redirect()->route('statuses.status.index')
            ->with('success_message', 'Status was successfully updated.');  
    }

    /**
     * Remove the specified status from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $status = Status::findOrFail($id);
            $status->delete();

            return redirect()->route('statuses.status.index')
                ->with('success_message', 'Status was successfully deleted.');
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
