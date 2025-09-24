<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display admin settings
     */
    public function index()
    {
        $settings = [
            'payment' => [
                'razorpay_key_id' => Setting::get('razorpay_key_id', ''),
                'razorpay_key_secret' => Setting::get('razorpay_key_secret', ''),
            ],
            'company' => [
                'company_name' => Setting::get('company_name', 'IPDC STORE'),
                'company_address' => Setting::get('company_address', ''),
                'company_place' => Setting::get('company_place', ''),
                'company_email' => Setting::get('company_email', ''),
                'company_phone' => Setting::get('company_phone', ''),
            ],
            'shipping' => [
                'shiprocket_email' => Setting::get('shiprocket_email', ''),
                'shiprocket_password' => Setting::get('shiprocket_password', ''),
            ]
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'company_place' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'shiprocket_email' => 'nullable|email|max:255',
            'shiprocket_password' => 'nullable|string|max:255',
        ]);

        // Update payment settings
        Setting::set('razorpay_key_id', $request->razorpay_key_id, 'string', 'payment', 'Razorpay Key ID');
        Setting::set('razorpay_key_secret', $request->razorpay_key_secret, 'string', 'payment', 'Razorpay Key Secret');

        // Update company settings
        Setting::set('company_name', $request->company_name, 'string', 'company', 'Company Name');
        Setting::set('company_address', $request->company_address, 'text', 'company', 'Company Address');
        Setting::set('company_place', $request->company_place, 'string', 'company', 'Company Place');
        Setting::set('company_email', $request->company_email, 'string', 'company', 'Company Email');
        Setting::set('company_phone', $request->company_phone, 'string', 'company', 'Company Phone');

        // Update shipping settings
        Setting::set('shiprocket_email', $request->shiprocket_email, 'string', 'shipping', 'Shiprocket Email');
        Setting::set('shiprocket_password', $request->shiprocket_password, 'string', 'shipping', 'Shiprocket Password');

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully!');
    }
}
