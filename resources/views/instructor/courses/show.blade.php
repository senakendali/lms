<x-app-layout>
    <h4 class="fw-bold" style="color:var(--brand-primary)">{{ $course->title }}</h4>
    <div class="text-muted">{{ $course->description }}</div>

    <div class="mt-3">
        <a class="btn btn-brand btn-sm" href="{{ route('instructor.courses.materials', $course) }}">
            Manage Materials
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('instructor.courses.index') }}">
            Back
        </a>
    </div>
</x-app-layout>
