<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Show Privacy Policy page
     */
    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    /**
     * Show Terms of Use page
     */
    public function termsOfUse()
    {
        return view('pages.terms-of-use');
    }

    /**
     * Show Payment Policies page
     */
    public function paymentPolicies()
    {
        return view('pages.payment-policies');
    }

    /**
     * Show About Us page
     */
    public function aboutUs()
    {
        return view('pages.about-us');
    }

    /**
     * Show Contact Us page
     */
    public function contactUs()
    {
        return view('pages.contact-us');
    }
}