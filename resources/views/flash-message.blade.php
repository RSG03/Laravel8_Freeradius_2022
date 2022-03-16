@if ($message = Session::get('xenos-success'))

    <div class="alert alert-success alert-block">

        {{--<button type="button" class="close" data-dismiss="alert">×</button>--}}

        <strong>{{ $message }}</strong>

    </div>

@endif


@if ($message = Session::get('xenos-error'))

    <div class="alert alert-danger alert-block">

        {{--<button type="button" class="close" data-dismiss="alert">×</button>--}}

        <strong>{{ $message }}</strong>

    </div>

@endif


@if ($message = Session::get('xenos-warning'))

    <div class="alert alert-warning alert-block">

        {{--<button type="button" class="close" data-dismiss="alert">×</button>--}}

        <strong>{{ $message }}</strong>

    </div>

@endif


@if ($message = Session::get('xenos-info'))

    <div class="alert alert-info alert-block">

        {{--<button type="button" class="close" data-dismiss="alert">×</button>--}}

        <strong>{{ $message }}</strong>

    </div>

@endif


@if ($errors->any())

    <div class="alert alert-danger">

        <button type="button" class="close" data-dismiss="alert">×</button>

        Please check the form below for errors

    </div>

@endif