@extends(backpack_view('blank'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Upload invoice - copy paste invoice contents below') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.import_stock') }}" aria-label="{{ __('Upload') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="company" class="col-sm-4 col-form-label text-md-right">{{ __('Company') }}</label>
                                <div class="col-md-6">
                                    <select class="form-control" name="company" required autofocus>
                                      <option>Blackfire</option>
                                      <option>Ynaris</option>
                                      <option>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="invoice_content" class="col-sm-4 col-form-label text-md-right">{{ __('Invoice content') }}</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" name="invoice_content" required autofocus></textarea>
                                </div>
                            </div>

                            <script src="https://cdn.ckeditor.com/4.15.1/standard/ckeditor.js"></script>

                            <script>
                                CKEDITOR.replace( 'invoice_content' );
                            </script>

                            <style>
                                .cke_inner .cke_top {
                                    display: none;
                                }
                            </style>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Upload') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
