<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Customer;
use App\Models\LifetimePackage;
use App\Models\MembershipLog;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
  use ValidatesRequests;

  public function index()
  {
    $countries = Country::select('id','name')->where('is_active',1)->get();

    return view('admin.membership.index',compact('countries'));
  }

  public function switchPlan()
  {
    $users = Customer::select('id','name')->where('status',1)->get();
    $packages = LifetimePackage::select('id','name')->get();

    return view('admin.membership.switch',compact('packages','users'));
  }

  public function UpdatePlan(Request $request)
  {
    $this->validate($request,[
      'user_id' => 'required',
      'package_id' => 'required',
    ]);

    $user = Customer::find($request->user_id);

    $user->update(['lifetime_package' => $request->package_id]);

    return redirect()->back()->with('success', 'Package updated successfully');
  }

  public function log()
  {
    $logs = MembershipLog::orderBy('id','desc')->get();
    return view('admin.membership.log',compact('logs'));
  }

  public function logData()
  {
    return 'hello';
  }
}
