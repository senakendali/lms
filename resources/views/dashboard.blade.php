<x-app-layout>
    @if(auth()->user()->role === 'admin')
        @include('dashboards.admin')
    @elseif(auth()->user()->role === 'instructor')
        @include('dashboards.instructor')
    @else
        @include('dashboards.student')
    @endif
</x-app-layout>
