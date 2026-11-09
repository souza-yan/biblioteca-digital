<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8" data-skeleton-variant="{{ $isTeacher ? 'teacher' : ($isStaff ? 'staff' : 'admin') }}">
    @if ($isTeacher)
        @include('dashboard.teacher-skeleton')
    @elseif ($isStaff)
        @include('dashboard.staff-skeleton')
    @else
        @include('dashboard.admin-skeleton')
    @endif
</div>
