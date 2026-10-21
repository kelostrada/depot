{{--
    searchable_select: Backpack's free `select` field (1-n relation) with
    type-to-search, as a free stand-in for Backpack PRO's `select2` field.
    Takes the same options as `select` (entity, attribute, model, options,
    allows_null). With Backpack PRO installed, use 'type' => 'select2' instead
    and this file can go.

    Search UI: Tom Select 2.6.2 (Apache-2.0), vendored unmodified from the npm
    package into public/vendor/tom-select/ (no CDN at runtime).
--}}
@php
    $field['wrapper']['data-init-function'] ??= 'bpFieldInitSearchableSelect';
@endphp

@include('crud::fields.select')

{{-- Loaded once per page, however many of these fields the form has. --}}
@push('crud_fields_styles')
    @basset(public_path('vendor/tom-select/tom-select.bootstrap5.min.css'))
@endpush

@push('crud_fields_scripts')
    @basset(public_path('vendor/tom-select/tom-select.complete.min.js'))
    @bassetBlock('depot/fields/searchable-select-field.js')
    <script>
        function bpFieldInitSearchableSelect(element) {
            var select = element.find('select')[0];
            if (!select || select.tomselect) {
                return;
            }
            new TomSelect(select, {
                maxOptions: null,       // match against all options, not just the first 50
                allowEmptyOption: true, // keep the "-" option of nullable columns
                create: false,
            });
        }
    </script>
    @endBassetBlock
@endpush
