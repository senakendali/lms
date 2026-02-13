<x-app-layout>
  @php
    // fallback aman
    $progressPct = (int) ($progressPct ?? 0);

    // helpers
    $courseInstructor = $course->instructor?->name ?? 'Instructor';
    $modules = $course->modules ?? collect();
  @endphp

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3"
              style="width:36px;height:36px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
          <i class="bi bi-journal-bookmark-fill"></i>
        </span>

        <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">
          {{ $course->title }}
        </h4>

        @if((int)($course->is_active ?? 0) === 1)
          <span class="badge rounded-pill"
                style="background:rgba(76,184,83,.14);color:var(--brand-secondary);">
            <i class="bi bi-check-circle me-1"></i> Active
          </span>
        @else
          <span class="badge rounded-pill text-bg-light">
            <i class="bi bi-slash-circle me-1"></i> Inactive
          </span>
        @endif
      </div>

      <div class="text-muted small">
        {{ $course->description ?: 'Tidak ada deskripsi untuk course ini.' }}
      </div>

      <div class="d-flex flex-wrap gap-2 mt-2">
        <span class="badge rounded-pill text-bg-light">
          <i class="bi bi-person-workspace me-1"></i> {{ $courseInstructor }}
        </span>

        <span class="badge rounded-pill text-bg-light">
          <i class="bi bi-collection me-1"></i>
          {{ $modules->count() }} module
        </span>

        <span class="badge rounded-pill text-bg-light">
          <i class="bi bi-diagram-3 me-1"></i>
          {{ $modules->sum(fn($m) => $m->topics?->count() ?? 0) }} topic
        </span>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('student.courses.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
      </a>

      <a href="{{ route('student.assignments.index') }}" class="btn btn-brand btn-sm">
        <i class="bi bi-pencil-square me-1"></i> Assignments
      </a>
    </div>
  </div>

  {{-- Progress Card --}}
  <div class="card mb-3">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <div class="fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-bar-chart-line" style="color:var(--brand-primary)"></i>
            <span>Progress</span>
          </div>
          <div class="small text-muted">
            Progress akan keisi otomatis setelah fitur tracking (topic/video/material) aktif.
          </div>
        </div>

        <div class="fw-bold" style="color:var(--brand-primary)">{{ $progressPct }}%</div>
      </div>

      <div class="mt-3">
        <div class="progress" style="height:10px;">
          <div class="progress-bar" role="progressbar" style="width:{{ $progressPct }}%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1">
          <span>Completion</span>
          <span>{{ $progressPct }}%</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Modules + Topics --}}
  <div class="accordion" id="studentModulesAcc">
    @forelse($modules as $module)
      @php $moduleKey = 'm'.$module->id; @endphp

      <div class="accordion-item mb-3 border-0">
        <div class="card">
          <div class="card-body p-0">

            {{-- Module Header --}}
            <h2 class="accordion-header">
              <div class="d-flex align-items-start justify-content-between px-3 py-2">
                <button class="btn p-0 text-start flex-grow-1"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $moduleKey }}"
                        style="box-shadow:none">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open text-muted"></i>
                    <span class="fw-semibold">{{ $module->title }}</span>
                    <span class="badge rounded-pill text-bg-light ms-1">
                      {{ $module->topics?->count() ?? 0 }} topic
                    </span>
                  </div>

                  @if(!empty($module->learning_objectives))
                    <div class="small text-muted mt-1 module-obj">
                      <i class="bi bi-bullseye me-1"></i>
                      {{ $module->learning_objectives }}
                    </div>
                  @endif
                </button>

                <button class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $moduleKey }}">
                  <i class="bi bi-chevron-down"></i>
                </button>
              </div>
            </h2>

            <div id="collapse-{{ $moduleKey }}" class="accordion-collapse collapse"
                 data-bs-parent="#studentModulesAcc">
              <div class="accordion-body p-3">

                {{-- Topics --}}
                <div class="d-flex flex-column gap-3">
                  @forelse(($module->topics ?? collect()) as $topic)
                    @php
                      $materials = $topic->materials ?? collect();

                      $video = $materials->firstWhere('type', 'video');
                      $files = $materials->where('type', 'file');
                      $links = $materials->where('type', 'link');

                      // outline
                      $subpoints = $topic->subtopics ?? $topic->focus_points ?? $topic->subtopic_points ?? null;
                      $hasOutline = !empty(trim(strip_tags((string) $subpoints)));

                      $hasVideo = (bool) $video;
                      $fileCount = $files->count();

                      $videoPreviewUrl = $video?->drive_id ? "https://drive.google.com/file/d/{$video->drive_id}/preview" : null;
                      $videoOpenUrl    = $video?->drive_id ? "https://drive.google.com/file/d/{$video->drive_id}/view" : null;

                      // assignments
                      $assignments = $topic->assignments ?? collect();
                      $assignmentCount = $assignments->count();

                      // progress placeholder per topic (nanti nyambung ke table progress)
                      $topicDone = false;
                    @endphp

                    <div class="card topic-card">
                      <div class="card-body p-3">

                        {{-- TOPIC HEADER --}}
                        <div class="d-flex justify-content-between align-items-start gap-3">
                          <div class="flex-grow-1">
                            <div class="d-flex align-items-start gap-2">
                              <div class="topic-icon">
                                <i class="bi bi-diagram-3"></i>
                              </div>

                              <div class="flex-grow-1">
                                <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                                  <span>{{ $topic->title }}</span>

                                  {{-- Outline --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    @if($hasOutline)
                                      <i class="bi bi-check-circle text-success"></i>
                                      <span>Outline</span>
                                    @else
                                      <i class="bi bi-exclamation-circle text-warning"></i>
                                      <span>Outline kosong</span>
                                    @endif
                                  </span>

                                  {{-- Video --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    @if($hasVideo)
                                      <i class="bi bi-play-circle text-primary"></i>
                                      <span>Video</span>
                                    @else
                                      <i class="bi bi-dash-circle text-muted"></i>
                                      <span>Video</span>
                                    @endif
                                  </span>

                                  {{-- Files --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-paperclip text-secondary"></i>
                                    <span>{{ $fileCount }} file</span>
                                  </span>

                                  {{-- Assignments --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-pencil-square text-secondary"></i>
                                    <span>{{ $assignmentCount }} tugas</span>
                                  </span>

                                  {{-- Topic progress placeholder --}}
                                  <span class="badge rounded-pill {{ $topicDone ? 'text-bg-success' : 'text-bg-light' }}">
                                    <i class="bi {{ $topicDone ? 'bi-check2-circle' : 'bi-circle' }} me-1"></i>
                                    {{ $topicDone ? 'Done' : 'Not started' }}
                                  </span>

                                </div>
                              </div>
                            </div>
                          </div>

                          <button class="btn btn-sm btn-outline-secondary topic-toggle"
                                  type="button"
                                  data-bs-toggle="collapse"
                                  data-bs-target="#topic-{{ $topic->id }}"
                                  aria-expanded="false"
                                  aria-controls="topic-{{ $topic->id }}">
                            <i class="bi bi-chevron-down"></i>
                          </button>
                        </div>

                        {{-- TOPIC DETAIL --}}
                        <div id="topic-{{ $topic->id }}" class="collapse mt-3">
                          <div class="topic-editor p-3 rounded-3">

                            {{-- Outline --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-list-check" style="color:var(--brand-primary)"></i>
                                <span>Outline / Sub Topic</span>
                              </div>

                              <div class="outline-view">
                                @if($hasOutline)
                                  <div class="outline-view-inner">
                                    {!! $subpoints !!}
                                  </div>
                                @else
                                  <div class="small text-muted">
                                    Belum ada outline untuk topic ini.
                                  </div>
                                @endif
                              </div>
                            </div>

                            {{-- Video --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-play-btn" style="color:var(--brand-primary)"></i>
                                <span>Video</span>
                              </div>

                              <div class="p-3 rounded-3"
                                   style="background:rgba(91,62,142,.06);border:1px solid rgba(91,62,142,.12)">
                                @if($video && $videoPreviewUrl)
                                  <a href="#"
                                     class="video-open text-decoration-none"
                                     data-bs-toggle="modal"
                                     data-bs-target="#videoPreviewModal"
                                     data-title="{{ e($video->title ?: $topic->title) }}"
                                     data-preview="{{ e($videoPreviewUrl) }}"
                                     data-open="{{ e($videoOpenUrl) }}">
                                    <div class="d-flex gap-2 align-items-start">
                                      <i class="bi bi-play-circle mt-1"
                                         style="font-size:1.25rem;color:var(--brand-primary)"></i>
                                      <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark">{{ $video->title ?: 'Video' }}</div>
                                        <div class="small text-muted">
                                          Klik untuk buka video (preview).
                                        </div>
                                      </div>
                                      <div class="ms-auto">
                                        <span class="badge rounded-pill text-bg-light">
                                          <i class="bi bi-box-arrow-up-right me-1"></i> Preview
                                        </span>
                                      </div>
                                    </div>
                                  </a>
                                @else
                                  <div class="small text-muted">
                                    Belum ada video untuk topic ini.
                                  </div>
                                @endif
                              </div>
                            </div>

                            {{-- Files --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-paperclip" style="color:var(--brand-primary)"></i>
                                <span>Files</span>
                              </div>

                              <div class="list-group list-group-flush">
                                @forelse($files as $material)
                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                      <i class="bi bi-file-earmark-text"></i>
                                      <div>
                                        <div class="fw-semibold">{{ $material->title }}</div>
                                        <div class="small text-muted">
                                          <a href="{{ method_exists($material,'fileUrl') ? $material->fileUrl() : '#' }}"
                                             target="_blank">
                                            Open file
                                          </a>
                                        </div>
                                      </div>
                                    </div>

                                    <a class="btn btn-sm btn-outline-secondary"
                                       href="{{ method_exists($material,'fileUrl') ? $material->fileUrl() : '#' }}"
                                       target="_blank">
                                      <i class="bi bi-download me-1"></i> Open
                                    </a>
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">
                                    Belum ada file.
                                  </div>
                                @endforelse
                              </div>
                            </div>

                            {{-- Assignments --}}
                            <div class="editor-block">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-pencil-square" style="color:var(--brand-primary)"></i>
                                <span>Assignments</span>
                              </div>

                              <div class="list-group list-group-flush">
                                @forelse($assignments as $as)
                                  @php
                                    // status submission nanti dari table assignment_submissions
                                    $status = 'pending'; // pending|submitted|graded
                                    $due = $as->due_at ? \Illuminate\Support\Carbon::parse($as->due_at)->format('d M Y, H:i') : null;
                                  @endphp

                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="me-3">
                                      <div class="fw-semibold">{{ $as->title }}</div>
                                      <div class="small text-muted">
                                        @if($due)
                                          <i class="bi bi-clock me-1"></i> Due: {{ $due }}
                                        @else
                                          <i class="bi bi-clock me-1"></i> No due date
                                        @endif
                                        <span class="ms-2">• Max: {{ (int)($as->max_score ?? 100) }}</span>
                                      </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                      @if($status === 'graded')
                                        <span class="badge rounded-pill text-bg-success">Graded</span>
                                      @elseif($status === 'submitted')
                                        <span class="badge rounded-pill text-bg-primary">Submitted</span>
                                      @else
                                        <span class="badge rounded-pill text-bg-warning">Pending</span>
                                      @endif

                                      <a href="{{ route('student.assignments.show', $as) }}"
                                         class="btn btn-sm btn-brand">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Open
                                      </a>
                                    </div>
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">
                                    Belum ada tugas pada topic ini.
                                  </div>
                                @endforelse
                              </div>
                            </div>

                          </div>
                        </div>

                      </div>
                    </div>
                  @empty
                    <div class="text-muted small">Belum ada topic pada module ini.</div>
                  @endforelse
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    @empty
      <div class="card">
        <div class="card-body p-5 text-center text-muted">
          Belum ada module untuk course ini.
        </div>
      </div>
    @endforelse
  </div>

  {{-- ✅ Video Preview Modal --}}
  <div class="modal fade" id="videoPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" id="videoPreviewDialog">
      <div class="modal-content video-modal">
        <div class="modal-header video-modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="video-modal-badge">
              <i class="bi bi-play-circle"></i>
            </span>
            <div class="lh-sm">
              <div class="video-modal-title" id="videoPreviewTitle">Video</div>
              <div class="video-modal-subtitle">Preview Video</div>
            </div>
          </div>

          <div class="ms-auto d-flex align-items-center gap-2">
            <a href="#" target="_blank" class="btn btn-sm btn-modal" id="videoOpenNewTab">
              <i class="bi bi-box-arrow-up-right me-1"></i> Open
            </a>

            <button type="button" class="btn btn-sm btn-modal" id="videoToggleFull">
              <i class="bi bi-arrows-fullscreen me-1"></i> Full
            </button>

            <button type="button" class="btn btn-sm btn-modal" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i> Close
            </button>
          </div>
        </div>

        <div class="modal-body p-0">
          <div class="video-modal-body">
            <div class="ratio ratio-16x9 bg-black">
              <iframe id="videoPreviewFrame"
                      src=""
                      allow="autoplay; encrypted-media"
                      allowfullscreen
                      referrerpolicy="no-referrer"
                      style="border:0"></iframe>
            </div>

            <div class="video-modal-hint">
              <i class="bi bi-info-circle me-1"></i>
              Jika video tidak tampil, pastikan pengaturan akses Drive disetel ke
              <b>“Anyone with the link can view / Siapa pun yang memiliki link dapat melihat”</b>.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- CSS --}}
  <style>
    .accordion-header .btn:focus { box-shadow:none; }
    .module-obj { white-space: pre-line; max-width: 980px; }

    .topic-card { border: 1px solid rgba(0,0,0,.08); }
    .topic-icon{
      width:36px;height:36px;border-radius:12px;
      display:flex;align-items:center;justify-content:center;
      background:rgba(0,0,0,.04);
      color:var(--brand-primary);
      flex:0 0 auto;
    }
    .topic-editor{ background: #fafafa; border:1px solid rgba(0,0,0,.08); }
    .quill-editor{ min-height: 180px; background: #fff; }

    /* clickable video row feel */
    .video-open{ border-radius:.75rem; padding:.25rem .5rem; display:block; }
    .video-open:hover{ background:rgba(0,0,0,.035); }

    /* outline view */
    .outline-view{
      background:#fff;
      border:1px solid rgba(0,0,0,.10);
      border-radius:.75rem;
      padding:.85rem .95rem;
    }
    .outline-view-inner{
      font-weight:400;
      color:#222;
      font-size:.95rem;
      line-height:1.55;
    }
    .outline-view-inner p{ margin:0 0 .6rem; }
    .outline-view-inner ul,
    .outline-view-inner ol{
      margin:.25rem 0 .6rem 1.1rem;
      padding:0;
    }
    .outline-view-inner li{ margin:.15rem 0; }

    /* video modal */
    .video-modal{
      border: 1px solid rgba(0,0,0,.08);
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 18px 60px rgba(0,0,0,.25);
      background: #fff;
    }
    .video-modal-header{
      background: linear-gradient(180deg, rgba(91,62,142,.10), rgba(91,62,142,.04));
      border-bottom: 1px solid rgba(0,0,0,.08);
      padding: .85rem 1rem;
    }
    .video-modal-badge{
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: rgba(91,62,142,.14);
      color: var(--brand-primary);
      flex: 0 0 auto;
      font-size: 1.1rem;
    }
    .video-modal-title{
      font-weight: 700;
      font-size: 1rem;
      color: #1f1f1f;
      max-width: 520px;
      overflow:hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .video-modal-subtitle{
      font-size: .8rem;
      color: rgba(0,0,0,.55);
    }
    .btn-modal{
      border: 1px solid rgba(0,0,0,.12);
      background: #fff;
      border-radius: .75rem;
      padding: .4rem .65rem;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      gap: .25rem;
    }
    .btn-modal:hover{
      background: rgba(0,0,0,.03);
      border-color: rgba(0,0,0,.18);
    }
    .btn-modal:focus{ box-shadow: none; }

    .video-modal-body{ background: #0b0b0b; }
    .video-modal-hint{
      background: #fff;
      padding: .65rem 1rem;
      font-size: .85rem;
      color: rgba(0,0,0,.65);
      border-top: 1px solid rgba(0,0,0,.08);
    }

    /* Fullscreen state */
    #videoPreviewDialog.is-fullscreen{
      width: 100vw !important;
      max-width: 100vw !important;
      height: 100vh !important;
      margin: 0 !important;
      align-items: stretch !important;
    }
    #videoPreviewDialog.is-fullscreen .modal-content{
      height: 100vh;
      border-radius: 0;
    }
    #videoPreviewDialog.is-fullscreen .ratio{
      height: calc(100vh - 118px);
    }
  </style>

  {{-- JS --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const hasBootstrap = !!window.bootstrap;

      // ✅ Video preview modal binding + fullsize toggle
      (function(){
        const modalEl = document.getElementById('videoPreviewModal');
        if(!modalEl || !hasBootstrap || !bootstrap.Modal) return;

        const titleEl = document.getElementById('videoPreviewTitle');
        const frameEl = document.getElementById('videoPreviewFrame');
        const openBtn = document.getElementById('videoOpenNewTab');
        const fullBtn = document.getElementById('videoToggleFull');
        const dialog = document.getElementById('videoPreviewDialog');

        function setFull(on){
          if(!dialog) return;
          dialog.classList.toggle('is-fullscreen', !!on);

          if(fullBtn){
            const icon = fullBtn.querySelector('i');
            const isOn = dialog.classList.contains('is-fullscreen');

            if(icon){
              icon.classList.toggle('bi-arrows-fullscreen', !isOn);
              icon.classList.toggle('bi-arrows-angle-contract', isOn);
            }

            // reset text nodes
            fullBtn.childNodes.forEach(n => { if(n.nodeType === 3) n.remove(); });
            fullBtn.insertAdjacentText('beforeend', isOn ? ' Exit' : ' Full');
          }

          setTimeout(() => {
            try{ frameEl?.contentWindow?.postMessage('resize', '*'); }catch(e){}
          }, 120);
        }

        modalEl.addEventListener('show.bs.modal', (ev) => {
          const btn = ev.relatedTarget;
          if(!btn) return;

          const title = btn.getAttribute('data-title') || 'Video';
          const preview = btn.getAttribute('data-preview') || '';
          const open = btn.getAttribute('data-open') || '#';

          titleEl.textContent = title;
          frameEl.src = preview;
          openBtn.href = open;

          setFull(false);
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
          frameEl.src = '';
          openBtn.href = '#';
          setFull(false);
        });

        fullBtn?.addEventListener('click', (e) => {
          e.preventDefault();
          const isOn = dialog?.classList.contains('is-fullscreen');
          setFull(!isOn);
        });
      })();

      // Toggle chevron up/down on topic expand
      document.querySelectorAll('.topic-toggle').forEach(btn => {
        const targetSel = btn.getAttribute('data-bs-target');
        const target = document.querySelector(targetSel);
        if(!target) return;

        target.addEventListener('show.bs.collapse', () => {
          btn.querySelector('i')?.classList.remove('bi-chevron-down');
          btn.querySelector('i')?.classList.add('bi-chevron-up');
        });

        target.addEventListener('hide.bs.collapse', () => {
          btn.querySelector('i')?.classList.remove('bi-chevron-up');
          btn.querySelector('i')?.classList.add('bi-chevron-down');
        });
      });
    });
  </script>
</x-app-layout>
