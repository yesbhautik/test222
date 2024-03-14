@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Integrations'))
@section('titlebar_actions', '')

@push('css')
    <style>
        .max-width-150 {
            max-width: 150px;
        }
    </style>
@endpush
@section('content')
    <div class="page-body pt-6">
        <div class="container-xl">
            <p class="mb-4 d-flex justify-content-center">
                {{ __('Send blog posts directly to your CMS') }}
            </p>
            <div class="lqd-extension-grid grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3">
                @foreach($items as $item)
                    <div class="lqd-card text-card-foreground w-full lqd-card-outline border border-card-border lqd-card-roundness-default lqd-extension relative flex flex-col rounded-[20px] transition-all hover:-translate-y-1 hover:shadow-lg">
                        <div class="card text-center">
                            <div class="card-body p-4">
                                <img
                                    src="{{ asset($item->image) }}"
                                    alt="HubSpot"
                                    class="img-fluid mx-auto d-block max-width-150"
                                >
                                <h3 class="mt-3">{{ $item->app }}</h3>
                                <p class='my-4'>{{ $item->description }}</p>

                                <div class="flex justify-between">


                                    @if($item->hasExtension)
                                        <a href='{{ route("dashboard.user.integration.edit", $item->id) }}'>
                                            <button class="btn btn-primary">Integrate</button>
                                        </a>
                                    @else
                                        <span>No installed extension</span>
                                    @endif

{{--                                    @if($item->status == 1)--}}
{{--                                        <button class="btn btn-secondary">Active</button>--}}
{{--                                    @else--}}
{{--                                        <button class="btn btn-secondary">Inactive</button>--}}
{{--                                    @endif--}}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('script')
@endpush
