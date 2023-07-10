@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/finder/css/finder.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/finder/js/contrib/jquery.ba-throttle-debounce.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/js/contrib/jquery.detect_swipe.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/js/cwd_popups.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/js/cwd_tables.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/js/jquery.mustache.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/vendor/mustache/mustache.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/js/app.js') }}"></script>
@endpush

@push('meta')
<!-- OpenGraph -->
<meta property="og:title" content="{{ config('module.finder.title', trans('finder::finder.module name')) }}" />
<meta property="og:type" content="article" />
<meta property="og:url" content="{{ request()->url() }}" />
<meta property="og:site_name" content="RCAC" />
<meta property="og:description" content="This tool will offer recommendations of solutions appropriate to {{ trans('finder::finder.institution') }} researchers, staff, and students usage needs and the data security constraints." />
@endpush

@php
app('pathway')->append(
    config('module.finder.title', trans('finder::finder.module name')),
    request()->url()
);
@endphp

@section('title'){{ trans('finder::finder.module name') }}@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div id="app">
            <div class="row">
                <div class="col-md-12 app-title">
                    <h2 class="title" id="pagetitle">{{ config('module.finder.title', trans('finder::finder.module name')) }}</h2>
                    <p class="lead" id="pagesubtitle">{{ trans('finder::finder.institution') }} researchers, staff, and students have a variety of options to store and collaborate with their data. This tool will offer recommendations of solutions appropriate to your usage needs and the data security constraints.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <hr class="section-break">

                    <div class="cd-row">
                        <div class="cd-cell cd-questions">
                            <div class="cd-overflow">
                                <div class="step-header questions-header sr-only">
                                    <h3 class="sub-heading">Filter Questions</h3>

                                    <p>Answer these questions to help identify storage solutions and services that are most suitable for your needs.</p>

                                    <div class="text-center">
                                        <button class="btn btn-secondary btn-sm btn-clear-filters ">Clear Answers</button>
                                    </div>
                                </div>
                                <ol id="questionlist">
                                    <li class="sr-only">Loading questions...</li>
                                </ol>
                            </div>
                        </div>
                        <div class="cd-cell cd-services">
                            <div class="sr-only">
                                <h3 class="sub-heading">Services</h3>

                                <div class="step-header services-header">
                                    <p>Select data storage solutions you would like to compare in details.</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <button class="btn btn-sm btn-secondary btn-select-all selectall-button">Select All</button>
                                <button class="btn btn-sm btn-select-none clear-button">Clear Selections</button>
                            </div>

                            <div id="modularstorage-services"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="container34" class="comparisons">
                <hr class="section-break" />
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="comparisonchart-wrapper-wrapper sub-heading" id="pagechartheader">
                            Select data storage solutions you would like to compare.
                        </h2>

                        <fieldset>
                            <legend class="sr-only">Present in comparison table?</legend>
                            <div class="comparisonlist-wrapper"></div>
                        </fieldset>
                        <div class="comparisonchart-wrapper">
                            <table class="table table-striped table-bordered scrolling" id="comparisonchart">
                                <caption class="sr-only">Comparison of services</caption>
                                <thead><tr><th scope="col">Select from services above to see comparisons.</th></tr></thead>
                                <tbody><tr><td>Select from services above to see comparisons.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acknowledgements">
            <hr class="section-break" />

            <h3>Acknowledgements</h3>

            <ul>
                <li>Cornell University Research Data Management Service Group and Cornell Information Technologies Custom Development Group (2018). Port of the <a href="https://github.com/CU-CommunityApps/CD-finder">Finder Module. Drupal 8</a>.</li>
            </ul>
        </div>
    </div>
</div>
@stop