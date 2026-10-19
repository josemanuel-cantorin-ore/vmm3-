<form action="{{ $action }}" method="POST" class="d-flex flex-column flex-sm-row gap-2 report-export-form">
    @csrf
    @foreach($exportValues as $name => $value)
        @if(is_array($value))
            @foreach($value as $item)
                <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach
    <button class="btn btn-outline-danger btn-lg" type="submit" name="formato" value="pdf">
        <i data-feather="file-text" class="me-2"></i> Generar PDF
    </button>
    <button class="btn btn-outline-success btn-lg" type="submit" name="formato" value="excel">
        <i data-feather="grid" class="me-2"></i> Generar Excel
    </button>
</form>
