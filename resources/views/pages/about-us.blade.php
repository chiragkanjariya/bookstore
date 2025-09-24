@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">About Us</h1>
                <p class="text-gray-600">Learn more about IPDC Store</p>
            </div>
            
            <div class="prose prose-lg max-w-none">
                <div class="space-y-8">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8 border border-blue-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Who We Are?</h2>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            The Integrated Personality Development Course (IPDC) is an educational initiative of B. A. P. S. VISION, created with voluntary effort to empower India by nurturing ethical & moral values in people, especially in youth.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Every individual can change the world, change himself. The purity of knowledge is what unlocks that ability. Learning in all its forms is forever illuminating and liberating. Committed to knowledge, BAPS Swaminarayan Sanstha, a global socio-spiritual NGO, is invested in fostering education. Foreseeing the increased importance of holistic education, BAPS has spent the last five years harnessing its expertise by providing holistic education to a wider audience.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
