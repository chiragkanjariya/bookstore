@extends('layouts.app')

@section('title', 'Payment Policies')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Policies</h1>
                <p class="text-gray-600">Last updated: {{ date('F d, Y') }}</p>
            </div>
            
            <div class="prose prose-lg max-w-none">
                <!-- Content will be provided by user -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div xss="removed"><div xss="removed"><b>Payment Policies for IPDC Online Courses<br><br></b></div><div>    <br/> Regarding any payment related queries(amount, date, type etc.), user can contact to IPDC Online Courses at service@ipdc.org</div><div>    <br/> While making an online payment, if the amount is deducted and IPDC Online Courses system reflects transaction failure, user should contact to their respective banks</div><div>    <br/> In the case of Payment Failure and the amount is debited, user may receive refund the amount back to the bank account used for payment within 72 hours of the bank working days. Regarding payment failure, the user have to wait for the next 72 hours of the bank working days before trying to pay again.</div><div>    <br/> In case, if the transaction is in the process state, it may take up to 48 hours of the bank working days to update the status in the IPDC Online Courses data. For any queries, user can contact to IPDC Online Courses at service@ipdc.org</div><div>    <br/> Transaction charges will be included in course fees. To know about transaction charges please contact to IPDC Online Courses at service@ipdc.org</div><div>    <br/> If user face any technical difficulties while making payment, please contact to IPDC Online Courses at service@ipdc.org</div><div>    <br/> If the payment is made using a card that you do not own, the permission of the card owner must be obtained.</div><div>    <br/> Information related to debit or credit cards is not accessed or stored by IPDC Online Courses.</div><div>    <br/> Refund will be processed within 5-7 working days. Respective payment gateway will initiate refund process to the issuing bank (user's bank) in processing batches. This process might take approximately 8-15 working days, depending on issuing bank's policies</div><div>    <br/> In case of Net banking/Card payment,<b> must make a note of reference/transaction details.</b></div><b><br></b><div><b>IMPORTANT: By submitting a payment through the IPDC Online Courses online-payments, you are agreeing to these Payment Policies.</b></div></div>
                        </div>
                    </div>
                </div>
                
                <!-- Placeholder content structure -->
                <div id="payment-content">
                    <!-- User content will go here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
