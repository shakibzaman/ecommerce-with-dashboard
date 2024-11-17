<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendKycStatusNotification;
use App\Models\Customer;
use App\Models\Kyc;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

use function App\Helpers\createKycHistory;

class KycsController extends Controller
{

    /**
     * Display a listing of the kycs.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $kycs = Kyc::with('customer')->orderBy('id', 'desc')->paginate(25);

        return view('kycs.index', compact('kycs'));
    }

    /**
     * Show the form for creating a new kyc.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $customers = Customer::pluck('name', 'id')->all();
        $documentTypes = config('app.document_types');
        $documentStatuses = config('app.document_statuses');

        return view('kycs.create', compact('customers', 'documentTypes', 'documentStatuses'));
    }

    /**
     * Store a new kyc in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {

        $data = $this->getData($request);

        Kyc::create($data);

        return redirect()->route('customer-kyc-view')
            ->with('success_message', 'Kyc was successfully added.');
    }

    /**
     * Display the specified kyc.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $kyc = Kyc::with('customer', 'histories.creator')->findOrFail($id);

        return view('kycs.show', compact('kyc'));
    }

    /**
     * Show the form for editing the specified kyc.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $documentTypes = config('app.document_types');
        $documentStatuses = config('app.document_statuses');
        $kyc = Kyc::findOrFail($id);
        $customers = Customer::pluck('name', 'id')->all();

        return view('kycs.edit', compact('kyc', 'customers', 'documentTypes', 'documentStatuses'));
    }

    /**
     * Update the specified kyc in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {
        try {
            $data = $this->getData($request);
            $data['kyc_id'] = $id;

            $kyc = Kyc::findOrFail($id);
            if ($request->status != $kyc->status) {
                logger('Need to update kyc status');
                createKycHistory($data);
                SendKycStatusNotification::dispatch($data);
            }
            $kyc->update($data);

            return redirect()->route('customer-kyc-index')
                ->with('success_message', 'Kyc was successfully updated.');
        } catch (Exception $e) {
            info('Error updating KYC', [$e]);
            return ['Error updating KYC' . $e];
        }
    }

    /**
     * Remove the specified kyc from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $kyc = Kyc::findOrFail($id);
            $kyc->delete();

            return redirect()->route('customer-kyc-index')
                ->with('success_message', 'Kyc was successfully deleted.');
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
            'document_type' => 'required',
            'document_number' => 'numeric|nullable',
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048', 'nullable', 'file'],
        ];
        if ($request->has('status')) {
            $rules['status'] = 'required|string|in:pending,approved,rejected|nullable';
        }

        if ($request->route()->getAction()['as'] == 'customer-kyc-store' || $request->has('custom_delete_image')) {
            array_push($rules['image'], 'required');
        }
        $data = $request->validate($rules);

        if ($request->has('custom_delete_image')) {
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $this->moveFile($request->file('image'));
        }



        return $data;
    }

    /**
     * Moves the attached file to the server.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return string
     */
    protected function moveFile($file)
    {
        if (!$file->isValid()) {
            return '';
        }

        $path = config('laravel-code-generator.files_upload_path', 'uploads');
        $saved = $file->store('public/' . $path, config('filesystems.default'));

        return substr($saved, 7);
    }

    public function typeList($type)
    {
        $check_status =  in_array($type, array_keys(config('app.document_statuses')));
        if (!$check_status) {
            // should to redirect 404 page 
            return ['status' => 404, 'message' => 'Sorry Page not Found'];
        }
        $kycs = Kyc::with('customer')->where('status', $type)->orderBy('id', 'desc')->paginate(25);

        return view('kycs.index', compact('kycs'));
    }

    public function view()
    {
        $customer = Auth::guard('customer')->user();
        if ($customer) {
            $customer->load('kyc');
            return view('kycs.view', compact('customer'));
        } else {
            return ['status' => 200, 'message' => 'Sorry this is only for Customer'];
        }
    }
}
