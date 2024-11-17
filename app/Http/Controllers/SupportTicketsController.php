<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class SupportTicketsController extends Controller
{

    /**
     * Display a listing of the support tickets.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $supportTickets = SupportTicket::where('status', 'open')->paginate(10);

        return view('support_tickets.index', compact('supportTickets'));
    }

    public function answered()
    {
        $supportTickets = SupportTicket::has('answers')->paginate(10);

        return view('support_tickets.index', compact('supportTickets'));
    }

    public function closed()
    {
        $supportTickets = SupportTicket::where('status', 'closed')->paginate(10);

        return view('support_tickets.index', compact('supportTickets'));
    }

    /**
     * Show the form for creating a new support ticket.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {


        return view('support_tickets.create');
    }

    /**
     * Store a new support ticket in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $data = $this->getData($request);
        if ($request->email){
            $user = User::where('email', $request->email)->first()->id;
        }
        else{
            $user = auth()->guard('customer')->user()->user_id;
        }
        $data['user_id'] = $user;
        SupportTicket::create($data);

        return redirect()->route('support_tickets.support_ticket.open')
            ->with('success_message', 'Support Ticket was successfully added.');
    }

    /**
     * Display the specified support ticket.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $supportTicket = SupportTicket::findOrFail($id);

        return view('support_tickets.show', compact('supportTicket'));
    }

    /**
     * Show the form for editing the specified support ticket.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $supportTicket = SupportTicket::findOrFail($id);


        return view('support_tickets.edit', compact('supportTicket'));
    }

    /**
     * Update the specified support ticket in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {

        $data = $this->getData($request);

        $supportTicket = SupportTicket::findOrFail($id);
        $supportTicket->update($data);

        return redirect()->route('support_tickets.support_ticket.open')
            ->with('success_message', 'Support Ticket was successfully updated.');
    }

    /**
     * Remove the specified support ticket from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $supportTicket = SupportTicket::findOrFail($id);
            $supportTicket->delete();

            return redirect()->route('support_tickets.support_ticket.index')
                ->with('success_message', 'Support Ticket was successfully deleted.');
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
                'title' => 'string|min:1|max:255|required|',
                'email' => 'email|nullable',
                'description' => 'string|min:1|max:1000|nullable',
                'status' => 'string|min:1|nullable',
                'department' => 'string|min:1|nullable',
                'is_active' => 'boolean|nullable',
        ];


        $data = $request->validate($rules);


        $data['is_active'] = $request->has('is_active');


        return $data;
    }

}
