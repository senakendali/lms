<x-app-layout>
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:36px;height:36px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
                    <i class="bi bi-journal-richtext"></i>
                </span>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">
                    My Courses
                </h4>
            </div>

            <div class="text-muted small">
                Daftar course yang Anda ajar dan kelola materinya.
            </div>
        </div>

        {{-- Search (vanilla, optional UX) --}}
        <div class="w-100 w-md-auto" style="min-width: 280px;">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="courseSearch" class="form-control"
                       placeholder="Cari course...">
            </div>
            <div class="small text-muted mt-1 d-none" id="courseSearchHint">
                Tidak ada course yang cocok.
            </div>
        </div>
    </div>

    {{-- Course List --}}
    <div class="row g-4" id="courseGrid">
        @forelse($courses as $course)
            <div class="col-md-4 course-card">
                <div class="card h-100">
                    <div class="card-body p-4 d-flex flex-column">

                        {{-- Title + badge --}}
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="fw-bold fs-6 course-title text-break" style="line-height:1.25;">
                                {{ $course->title }}
                            </div>

                            <span class="badge rounded-pill"
                                  style="background:rgba(76,184,83,.14);color:var(--brand-secondary);">
                                <i class="bi bi-check-circle me-1"></i>
                                Active
                            </span>
                        </div>

                        {{-- Description --}}
                        <div class="text-muted small mb-4 flex-grow-1 course-desc">
                            {{ $course->description ?: 'Tidak ada deskripsi untuk course ini.' }}
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge rounded-pill text-bg-light">
                                <i class="bi bi-person-workspace me-1"></i>
                                Instructor
                            </span>

                            <a href="{{ route('instructor.courses.materials', $course) }}"
                               class="btn btn-brand btn-sm">
                                <i class="bi bi-collection-play me-1"></i>
                                Kelola Materi
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-5 text-center">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                  style="width:64px;height:64px;background:rgba(91,62,142,.10);">
                                <i class="bi bi-journal-x fs-2" style="color:var(--brand-primary)"></i>
                            </span>
                        </div>

                        <div class="fw-semibold mb-1">
                            Belum ada course
                        </div>

                        <div class="text-muted small mb-3">
                            Course yang Anda ampu akan muncul di halaman ini setelah di-assign oleh admin.
                        </div>

                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Styling kecil khusus halaman (boleh dipindah ke brand.css kalau mau) --}}
    <style>
        /* Clamp text biar card rapi */
        .course-desc{
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.6em;
        }

        .course-title{
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Hover halus */
        .course-card .card{
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .course-card .card:hover{
            transform: translateY(-2px);
            box-shadow: 0 14px 40px rgba(16,24,40,.10);
        }
    </style>

    {{-- Vanilla JS search --}}
    <script>
        (function () {
            const input = document.getElementById('courseSearch');
            const cards = document.querySelectorAll('#courseGrid .course-card');
            const hint = document.getElementById('courseSearchHint');

            if (!input || !cards.length) return;

            function normalize(s){ return (s || '').toLowerCase().trim(); }

            input.addEventListener('input', () => {
                const q = normalize(input.value);
                let visible = 0;

                cards.forEach(card => {
                    const title = normalize(card.querySelector('.course-title')?.textContent);
                    const desc  = normalize(card.querySelector('.course-desc')?.textContent);
                    const match = !q || title.includes(q) || desc.includes(q);

                    card.classList.toggle('d-none', !match);
                    if (match) visible++;
                });

                if (hint) hint.classList.toggle('d-none', visible !== 0);
            });
        })();
    </script>
</x-app-layout>
