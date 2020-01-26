@extends(backpack_view('blank'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">File Upload</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.import_stock') }}" aria-label="{{ __('Upload') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row">
                                <label for="title" class="col-sm-4 col-form-label text-md-right">{{ __('Invoice') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="invoice" required autofocus />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="title" class="col-sm-4 col-form-label text-md-right">{{ __('Date') }}</label>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" name="date" required autofocus />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="title" class="col-sm-4 col-form-label text-md-right">{{ __('Invoice File') }}</label>
                                <div class="col-md-6">
                                    <input type="file" class="custom-file-input" id="invoice_file"
                                           aria-describedby="invoice_file" name="invoice_file">
                                    <label class="custom-file-label" for="invoice_file">Choose file</label>
                                </div>
                            </div>

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
