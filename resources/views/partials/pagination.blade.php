<ul class="pagination">
    @if ($pagination['currentPage'] > 1)
        <li class="page-item">
            <a class="page-link" href="#" data-page="{{ $pagination['currentPage'] - 1 }}">Previous</a>
        </li>
    @endif
    @for ($i = 1; $i <= ceil($pagination['total'] / $pagination['perPage']); $i++)
        <li class="page-item {{ $pagination['currentPage'] == $i ? 'active' : '' }}">
            <a class="page-link" href="#" data-page="{{ $i }}">{{ $i }}</a>
        </li>
    @endfor
    @if ($pagination['currentPage'] < ceil($pagination['total'] / $pagination['perPage']))
        <li class="page-item">
            <a class="page-link" href="#" data-page="{{ $pagination['currentPage'] + 1 }}">Next</a>
        </li>
    @endif
</ul>
