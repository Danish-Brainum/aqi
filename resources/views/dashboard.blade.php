@extends('layout.app')
@section('content')

@if (session('success'))
@include('alert.success')
@endif
@if (session('error'))
@include('alert.custom-error')
@endif
@if ($errors->any())
@include('alert.errors')
@endif
<div class="mb-6">
  <div class="inline-flex rounded-xl bg-slate-100 p-1 shadow-sm">
    <button data-tab="upload" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700 data-[active=true]:bg-white data-[active=true]:text-indigo-700 data-[active=true]:shadow">
      Email Campaigns (by Customer)
    </button>
    {{-- <button data-tab="messages" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700 data-[active=true]:bg-white data-[active=true]:text-indigo-700 data-[active=true]:shadow">
      Messages
    </button> --}}
    <button data-tab="aqi_info" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700 data-[active=true]:bg-white data-[active=true]:text-indigo-700 data-[active=true]:shadow">
      WhatsApp Campaigns (by City)
    </button>
    <button data-tab="whatsapp-recipients" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700 data-[active=true]:bg-white data-[active=true]:text-indigo-700 data-[active=true]:shadow">
      WhatsApp Recipients
    </button>
  </div>
</div>

@include('section.upload')

{{-- @include('section.custom-message') --}}
@include('section.aqi_info')
@include('section.whatsapp-recipients')
@include('modal.edit-row')
@endsection