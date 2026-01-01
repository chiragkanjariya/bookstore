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
                'company_name' => Setting::get('company_name', 'IPDC'),
                'company_address' => Setting::get('company_address', ''),
                'company_place' => Setting::get('company_place', ''),
                'company_email' => Setting::get('company_email', ''),
                'company_phone' => Setting::get('company_phone', ''),
            ],
            'shipping' => [
                'shiprocket_email' => Setting::get('shiprocket_email', ''),
                'shiprocket_password' => Setting::get('shiprocket_password', ''),
            ],
            'courier' => [
                'courier_provider' => Setting::get('courier_provider', 'shiprocket'),
                'shiprocket_enabled' => Setting::get('shiprocket_enabled', true),
                'shree_maruti_enabled' => Setting::get('shree_maruti_enabled', false),
                'shree_maruti_client_name' => Setting::get('shree_maruti_client_name', ''),
                'shree_maruti_client_code' => Setting::get('shree_maruti_client_code', ''),
                'shree_maruti_username' => Setting::get('shree_maruti_username', ''),
                'shree_maruti_password' => Setting::get('shree_maruti_password', ''),
                'shree_maruti_api_secret_key' => Setting::get('shree_maruti_api_secret_key', ''),
                'shree_maruti_environment' => Setting::get('shree_maruti_environment', 'beta'),
                'awb_number_prefix' => Setting::get('awb_number_prefix', 'IPDC'),
            ],
            'bulk' => [
                'min_bulk_purchase' => Setting::get('min_bulk_purchase', 10),
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
            'courier_provider' => 'required|in:shiprocket,shree_maruti,none',
            'shiprocket_enabled' => 'nullable|boolean',
            'shree_maruti_enabled' => 'nullable|boolean',
            'shree_maruti_client_name' => 'nullable|string|max:255',
            'shree_maruti_client_code' => 'nullable|string|max:255',
            'shree_maruti_username' => 'nullable|string|max:255',
            'shree_maruti_password' => 'nullable|string|max:255',
            'shree_maruti_api_secret_key' => 'nullable|string|max:255',
            'shree_maruti_environment' => 'nullable|in:beta,production',
            'awb_number_prefix' => 'nullable|string|max:10',
            'min_bulk_purchase' => 'required|integer|min:1',
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

        // Update courier settings
        Setting::set('courier_provider', $request->courier_provider, 'string', 'courier', 'Active Courier Provider');
        Setting::set('shiprocket_enabled', $request->has('shiprocket_enabled'), 'boolean', 'courier', 'Shiprocket Enabled');
        Setting::set('shree_maruti_enabled', $request->has('shree_maruti_enabled'), 'boolean', 'courier', 'Shree Maruti Enabled');
        Setting::set('shree_maruti_client_name', $request->shree_maruti_client_name, 'string', 'courier', 'Shree Maruti Client Name');
        Setting::set('shree_maruti_client_code', $request->shree_maruti_client_code, 'string', 'courier', 'Shree Maruti Client Code');
        Setting::set('shree_maruti_username', $request->shree_maruti_username, 'string', 'courier', 'Shree Maruti Username');
        Setting::set('shree_maruti_password', $request->shree_maruti_password, 'string', 'courier', 'Shree Maruti Password');
        Setting::set('shree_maruti_api_secret_key', $request->shree_maruti_api_secret_key, 'string', 'courier', 'Shree Maruti API Secret Key');
        Setting::set('shree_maruti_environment', $request->shree_maruti_environment, 'string', 'courier', 'Shree Maruti Environment');
        Setting::set('awb_number_prefix', $request->awb_number_prefix, 'string', 'courier', 'AWB Number Prefix');

        // Update bulk purchase settings
        Setting::set('min_bulk_purchase', $request->min_bulk_purchase, 'integer', 'bulk', 'Minimum Bulk Purchase Quantity');

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully!');
    }
}
