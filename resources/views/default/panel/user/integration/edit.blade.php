@extends('panel.layout.app')
@section('title', __('Integration Edit'))
@section('titlebar_actions', '')

@section('content')
    <div class="page-body pt-6">
        <div class="container-xl">
            <div class="row ">
                <div class="col-md-5 mx-auto">
                    <form
                            class="@if (view()->exists('panel.admin.custom.layout.panel.header-top-bar')) bg-[--tblr-bg-surface] px-8 py-10 rounded-[--tblr-border-radius] @endif"
                            enctype="multipart/form-data"
                            method="post"
                            action="{{ route('dashboard.user.integration.update', $item->id) }}"
                    >
                        @csrf
                        @method('put')
                        @foreach($userItem->credentials as $field)
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for='{{ $field['name'] }}' class="form-label">{{ $field['label'] }}</label>
                                    <input type="{{ $field['type'] }}" class="form-control" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['value'] ?? "" }}">
                                </div>
                            </div>
                        @endforeach
                        <div class="col-md-12">
                            <x-button type="submit" class="w-100">
                                {{__('Save')}}
                            </x-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
