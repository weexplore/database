<form method="POST" id="{{ $formId }}" class="hidden">
    @csrf
    @method('DELETE')

    <input type="hidden" name="return_to" value="{{ url()->full() }}">

    @if(!empty($query))
        @foreach($query as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    @endif
</form>