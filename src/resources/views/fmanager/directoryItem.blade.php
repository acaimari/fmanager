<li data-path="{{ $directory['path'] }}" data-jstree='{ "opened" : true }'>
    {{ Str::afterLast($directory['path'], '/') }}
    @if (count($directory['subdirectories']) > 0)
        <ul>
            @foreach ($directory['subdirectories'] as $subdirectory)
                @include('fmanager::fmanager.directoryItem', ['directory' => $subdirectory])
            @endforeach
        </ul>
    @endif
</li>
