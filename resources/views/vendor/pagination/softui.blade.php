
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <a class="page-link" href="javascript:;" tabindex="-1">
                        <i class="fa fa-angle-left"></i>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                        <i class="fa fa-angle-left"></i>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>

            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><a class="page-link" href="javascript:;">{{ $element }}</a></li>
                @endif

            @endforeach
            <li class="page-item"><a class="page-link" href="javascript:;">1</a></li>
            <li class="page-item active"><a class="page-link" href="javascript:;">2</a></li>
            <li class="page-item"><a class="page-link" href="javascript:;">3</a></li>
            <li class="page-item">
                <a class="page-link" href="javascript:;">
                    <i class="fa fa-angle-right"></i>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
