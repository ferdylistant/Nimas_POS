@if (isset($errors) && count($errors) > 0)
    <div id="message">
        <div style="padding: 5px;">
            <div id="inner-message" class="alert alert-danger bg-gradient-danger text-white d-flex flex-row" role="alert">
                <div class="p-2 align-middle">
                    <h5 class="float-right text-white"><i class="fa fa-exclamation-circle"></i></h5>
                </div>
                <div class="p-2">
                    <ul class="list-unstyled mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

@if (Session::get('success', false))
    <?php $data = Session::get('success'); ?>
    @if (is_array($data))
        @foreach ($data as $msg)
            <div class="alert alert-success bg-gradient-success" role="alert">
                <i class="fa fa-check"></i>
                {{ $msg }}
            </div>
        @endforeach
    @else
        <div class="alert alert-warning bg-gradient-warning text-white" role="alert">
            <i class="fa fa-check"></i>
            {{ $data }}
        </div>
    @endif
@endif
