{{-- resources/views/instructor/courses/materials.blade.php --}}

<x-app-layout>
  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3"
              style="width:36px;height:36px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
          <i class="bi bi-collection-play"></i>
        </span>

        <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">
          {{ $course->title }}
        </h4>

        <span class="badge rounded-pill text-bg-light ms-1">Materials</span>
      </div>

      <div class="text-muted small">
        Kelola module, topic, dan materi pembelajaran untuk course ini.
      </div>
    </div>

    <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back
    </a>
  </div>

  {{-- Flash (auto dismiss) --}}
  @if(session('status'))
    <div class="alert alert-success alert-dismissible fade show py-2 small rounded-3 js-flash" role="alert">
      <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 small rounded-3 js-flash" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i>
      {{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Add Module --}}
  <div class="card mb-3">
    <div class="card-body p-3">
      <form method="POST" action="{{ route('instructor.modules.store') }}">
        @csrf
        <input type="hidden" name="course_id" value="{{ $course->id }}">

        <div class="row g-2 align-items-end">
          <div class="col-12 col-lg-4">
            <label class="form-label small mb-1">Module Title</label>
            <input class="form-control" name="title" placeholder="Misal: Week 1 — Introduction">
          </div>

          <div class="col-12 col-lg-6">
            <label class="form-label small mb-1">Learning Objective</label>
            <input class="form-control" name="learning_objectives" placeholder="Tulis objective singkat">
          </div>

          <div class="col-12 col-lg-2">
            <button class="btn btn-brand w-100">
              <i class="bi bi-plus-lg me-1"></i> Add Module
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Modules --}}
  <div class="accordion" id="modulesAcc">
    @forelse($course->modules as $module)
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
                      {{ $module->topics->count() }} topic
                    </span>
                  </div>

                  @if(!empty($module->learning_objectives))
                    <div class="small text-muted mt-1 module-obj">
                      <i class="bi bi-bullseye me-1"></i>
                      {{ $module->learning_objectives }}
                    </div>
                  @endif
                </button>

                <div class="d-flex align-items-center gap-2 ms-3">
                  <button type="button"
                          class="btn btn-sm btn-outline-secondary"
                          data-bs-toggle="modal"
                          data-bs-target="#editModuleModal"
                          data-id="{{ $module->id }}"
                          data-title="{{ e($module->title) }}"
                          data-objectives="{{ e($module->learning_objectives) }}">
                    <i class="bi bi-pencil"></i>
                  </button>

                  {{-- Delete Module --}}
                  <button type="button"
                          class="btn btn-sm btn-outline-danger js-confirm"
                          data-bs-title="Hapus module?"
                          data-bs-message="Semua topic & materi di dalam module ini ikut terhapus."
                          data-form="#delete-module-{{ $module->id }}">
                    <i class="bi bi-trash"></i>
                  </button>

                  <form id="delete-module-{{ $module->id }}"
                        method="POST"
                        action="{{ route('instructor.modules.destroy', $module) }}"
                        class="d-none">
                    @csrf @method('DELETE')
                  </form>

                  <button class="btn btn-sm btn-outline-secondary"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#collapse-{{ $moduleKey }}">
                    <i class="bi bi-chevron-down"></i>
                  </button>
                </div>
              </div>
            </h2>

            <div id="collapse-{{ $moduleKey }}" class="accordion-collapse collapse"
                 data-bs-parent="#modulesAcc">
              <div class="accordion-body p-3">

                {{-- Add Topic --}}
                <form method="POST" action="{{ route('instructor.topics.store') }}"
                      class="row g-2 align-items-end mb-3">
                  @csrf
                  <input type="hidden" name="module_id" value="{{ $module->id }}">

                  <div class="col-12 col-lg-7">
                    <label class="form-label small mb-1">Topic Title</label>
                    <input class="form-control" name="title" placeholder="Tambah topic baru (misal: Setup Project)">
                  </div>

                  <div class="col-12 col-lg-3">
                    <label class="form-label small mb-1">Tipe Materi</label>
                    <select class="form-select" name="delivery_type">
                      <option value="video">Video</option>
                      <option value="live">Live Session</option>
                      <option value="hybrid">Hybrid</option>
                    </select>
                  </div>

                  <div class="col-12 col-lg-2">
                    <button class="btn btn-outline-secondary w-100">
                      <i class="bi bi-plus-lg me-1"></i> Add Topic
                    </button>
                  </div>
                </form>

                {{-- Topics --}}
                <div class="d-flex flex-column gap-3">
                  @forelse($module->topics as $topic)
                    @php
                      $video = $topic->materials->firstWhere('type','video');
                      $files = $topic->materials->where('type','file');
                      $links = $topic->materials->where('type','link');

                      $subpoints = $topic->subtopics ?? $topic->focus_points ?? $topic->subtopic_points ?? null;

                      $hasOutline = !empty(trim(strip_tags((string)$subpoints)));
                      $hasVideo = !!$video;
                      $fileCount = $files->count();

                      $delivery = $topic->delivery_type ?? 'video';

                      // Delivery badge
                      $deliveryLabel = 'Video';
                      $deliveryIcon = 'bi-play-circle';
                      if($delivery === 'live'){
                        $deliveryLabel = 'Live Session';
                        $deliveryIcon = 'bi-broadcast';
                      } elseif($delivery === 'hybrid'){
                        $deliveryLabel = 'Hybrid';
                        $deliveryIcon = 'bi-intersect';
                      }

                      // VIDEO SOURCE DETECTION
                      $videoSource = 'local'; // local|drive
                      $driveId = null;

                      if($video){
                        $driveId = $video->drive_id ?? null;

                        if(!empty($driveId)){
                          $videoSource = 'drive';
                        } else {
                          // kalau url drive juga dianggap drive
                          $u = (string)($video->url ?? '');
                          if(str_contains($u, 'drive.google.com')){
                            $videoSource = 'drive';
                          }
                        }
                      }

                      // VIDEO URL
                      $videoUrl = null;
                      if($video){
                        if($videoSource === 'drive'){
                          // kalau ada drive_id -> pakai preview
                          if(!empty($driveId)){
                            $videoUrl = "https://drive.google.com/file/d/{$driveId}/preview";
                          } else {
                            // fallback ke url kalau user tempel link drive
                            $videoUrl = $video->url ?: null;
                          }
                        } else {
                          // local/url biasa
                          if(!empty($video->url)){
                            $videoUrl = $video->url;
                          } elseif(!empty($video->file_path)){
                            $videoUrl = \Illuminate\Support\Facades\Storage::url($video->file_path);
                          }
                        }
                      }

                      $outlineHasContent = !empty(trim(strip_tags((string)$subpoints)));
                      $outlineInitial = old('subtopics', $subpoints);

                      $assignments = $topic->assignments ?? collect();
                      $assignmentCount = method_exists($assignments, 'count') ? $assignments->count() : 0;

                      // default input mode for Materi Utama
                      // Video -> local, Hybrid -> drive
                      $defaultMainVideoMode = ($delivery === 'hybrid') ? 'drive' : 'local';
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

                                  {{-- Delivery badge --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $deliveryIcon }} text-secondary"></i>
                                    <span>{{ $deliveryLabel }}</span>
                                  </span>

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

                                  {{-- Video badge --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    @if(($delivery ?? 'video') === 'live')
                                      <i class="bi bi-dash-circle text-muted"></i>
                                      <span>Video (optional)</span>
                                    @else
                                      @if($hasVideo && $videoUrl)
                                        <i class="bi bi-play-circle text-primary"></i>
                                        <span>Video</span>
                                      @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                        <span>Video</span>
                                      @endif
                                    @endif
                                  </span>

                                  {{-- Files --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-paperclip text-secondary"></i>
                                    <span>{{ $fileCount }} file</span>
                                  </span>

                                  {{-- Links --}}
                                  @if($links->count())
                                    <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                      <i class="bi bi-link-45deg text-secondary"></i>
                                      <span>{{ $links->count() }} link</span>
                                    </span>
                                  @endif

                                  {{-- Assignments --}}
                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-journal-check text-secondary"></i>
                                    <span>{{ $assignmentCount }} tugas</span>
                                  </span>
                                </div>
                              </div>

                            </div>
                          </div>

                          {{-- Actions --}}
                          <div class="d-flex gap-2 topic-actions">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editTopicModal"
                                    data-id="{{ $topic->id }}"
                                    data-title="{{ e($topic->title) }}"
                                    data-delivery_type="{{ e($topic->delivery_type ?? 'video') }}">
                              <i class="bi bi-pencil-square"></i>
                            </button>

                            {{-- Delete Topic --}}
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger js-confirm"
                                    data-bs-title="Hapus topic?"
                                    data-bs-message="Semua file & materi di dalam topic ini ikut terhapus."
                                    data-form="#delete-topic-{{ $topic->id }}">
                              <i class="bi bi-trash"></i>
                            </button>

                            <form id="delete-topic-{{ $topic->id }}"
                                  method="POST"
                                  action="{{ route('instructor.topics.destroy', $topic) }}"
                                  class="d-none">
                              @csrf @method('DELETE')
                            </form>

                            <button class="btn btn-sm btn-outline-secondary topic-toggle"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#topic-{{ $topic->id }}"
                                    aria-expanded="false"
                                    aria-controls="topic-{{ $topic->id }}">
                              <i class="bi bi-chevron-down"></i>
                            </button>
                          </div>
                        </div>

                        {{-- TOPIC EDITOR --}}
                        <div id="topic-{{ $topic->id }}" class="collapse mt-3">
                          <div class="topic-editor p-3 rounded-3">

                            {{-- 1) OUTLINE --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                  <i class="bi bi-list-check" style="color:var(--brand-primary)"></i>
                                  <span>Outline / Sub Topic (poin bahasan)</span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                  <button type="button"
                                          class="btn btn-sm btn-outline-secondary js-outline-edit {{ $outlineHasContent ? '' : 'd-none' }}"
                                          data-topic="{{ $topic->id }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                  </button>

                                  <button type="button"
                                          class="btn btn-sm btn-outline-secondary js-outline-cancel d-none"
                                          data-topic="{{ $topic->id }}">
                                    <i class="bi bi-x-lg me-1"></i> Cancel
                                  </button>

                                  <button type="submit"
                                          form="outline-form-{{ $topic->id }}"
                                          class="btn btn-sm btn-brand js-outline-save {{ $outlineHasContent ? 'd-none' : '' }}"
                                          data-topic="{{ $topic->id }}">
                                    <i class="bi bi-save2 me-1"></i> Save
                                  </button>
                                </div>
                              </div>

                              <form method="POST"
                                    action="{{ route('instructor.topics.update', $topic) }}"
                                    id="outline-form-{{ $topic->id }}"
                                    class="js-outline-form"
                                    data-topic="{{ $topic->id }}">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="title" value="{{ $topic->title }}">
                                <input type="hidden" name="delivery_type" value="{{ $topic->delivery_type ?? 'video' }}">

                                <input type="hidden"
                                       name="subtopics"
                                       class="js-outline-input"
                                       value="{{ $outlineInitial }}">

                                <div class="outline-view {{ $outlineHasContent ? '' : 'd-none' }}"
                                     data-topic="{{ $topic->id }}">
                                  <div class="outline-view-inner">
                                    {!! $outlineInitial !!}
                                  </div>
                                </div>

                                <div class="outline-edit {{ $outlineHasContent ? 'd-none' : '' }}"
                                     data-topic="{{ $topic->id }}">
                                  <div class="quill-wrap"
                                       data-topic="{{ $topic->id }}"
                                       data-initial="{{ e($outlineInitial) }}">
                                    <div id="quill-{{ $topic->id }}" class="quill-editor"></div>
                                  </div>

                                  <noscript>
                                    <div class="alert alert-warning small mt-2 mb-0">
                                      JavaScript mati. Outline pakai textarea:
                                    </div>
                                  </noscript>
                                  <textarea class="form-control form-control-sm mt-2 d-none js-outline-fallback"
                                            rows="5"
                                            placeholder="Kalau editor tidak muncul, isi di sini..."></textarea>
                                </div>
                              </form>
                            </div>

                            {{-- 2) MATERI UTAMA (VIDEO) --}}
                            @if(($topic->delivery_type ?? 'video') !== 'live')
                              <div class="editor-block mb-3"
                                   data-topic-video-block
                                   data-delivery="{{ e($delivery) }}"
                                   data-default-mode="{{ e($defaultMainVideoMode) }}">
                                <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                  <i class="bi bi-play-btn" style="color:var(--brand-primary)"></i>
                                  <span>Materi Utama</span>

                                  @if(($topic->delivery_type ?? 'video') === 'hybrid')
                                    <span class="badge rounded-pill text-bg-light ms-1">
                                      <i class="bi bi-intersect me-1"></i> Hybrid (default: Drive)
                                    </span>
                                  @endif
                                </div>

                                <div class="p-3 rounded-3"
                                     style="background:rgba(91,62,142,.06);border:1px solid rgba(91,62,142,.12)">

                                  {{-- MODE SWITCH --}}
                                  <div class="d-flex flex-wrap gap-3 align-items-center mb-2">
                                    <div class="small text-muted">Sumber video:</div>

                                    <div class="form-check">
                                      <input class="form-check-input js-video-mode"
                                             type="radio"
                                             name="video_mode_{{ $topic->id }}"
                                             id="modeLocal-{{ $topic->id }}"
                                             value="local"
                                             {{ ($hasVideo ? ($videoSource === 'local') : ($defaultMainVideoMode === 'local')) ? 'checked' : '' }}>
                                      <label class="form-check-label small" for="modeLocal-{{ $topic->id }}">
                                        Local / URL
                                      </label>
                                    </div>

                                    <div class="form-check">
                                      <input class="form-check-input js-video-mode"
                                             type="radio"
                                             name="video_mode_{{ $topic->id }}"
                                             id="modeDrive-{{ $topic->id }}"
                                             value="drive"
                                             {{ ($hasVideo ? ($videoSource === 'drive') : ($defaultMainVideoMode === 'drive')) ? 'checked' : '' }}>
                                      <label class="form-check-label small" for="modeDrive-{{ $topic->id }}">
                                        Google Drive
                                      </label>
                                    </div>
                                  </div>

                                  @if($video && $videoUrl)
                                    {{-- EXISTING VIDEO --}}
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                      <a href="#"
                                         class="video-open flex-grow-1 text-decoration-none"
                                         data-bs-toggle="modal"
                                         data-bs-target="#videoPreviewModal"
                                         data-title="{{ e($video->title ?: $topic->title) }}"
                                         data-src="{{ e($videoUrl) }}"
                                         data-kind="{{ $videoSource === 'drive' ? 'drive' : 'video' }}">
                                        <div class="d-flex gap-2 align-items-start">
                                          <i class="bi bi-play-circle mt-1" style="font-size:1.25rem;color:var(--brand-primary)"></i>
                                          <div>
                                            <div class="fw-semibold text-dark">{{ $video->title ?: 'Video' }}</div>
                                            <div class="small text-muted">
                                              Klik untuk preview ({{ $videoSource === 'drive' ? 'Drive' : 'Local/URL' }})
                                            </div>
                                            <div class="small text-muted">
                                              <code>
                                                @if($videoSource === 'drive')
                                                  {{ $video->drive_id ?: ($video->url ?: '-') }}
                                                @else
                                                  {{ $video->url ?: ($video->file_path ?: '-') }}
                                                @endif
                                              </code>
                                            </div>
                                          </div>
                                        </div>
                                      </a>

                                      <div class="d-flex gap-2 flex-shrink-0">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editMaterialModal"
                                                data-id="{{ $video->id }}"
                                                data-title="{{ e($video->title) }}"
                                                data-type="{{ $video->type }}"
                                                data-file_path="{{ e($video->file_path) }}"
                                                data-url="{{ e($video->url) }}"
                                                data-drive_id="{{ e($video->drive_id ?? '') }}">
                                          <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger js-confirm"
                                                data-bs-title="Hapus video?"
                                                data-bs-message="Video akan dihapus dari topic ini."
                                                data-form="#delete-material-{{ $video->id }}">
                                          <i class="bi bi-trash"></i>
                                        </button>

                                        <form id="delete-material-{{ $video->id }}"
                                              method="POST"
                                              action="{{ route('instructor.materials.destroy', $video) }}"
                                              class="d-none">
                                          @csrf @method('DELETE')
                                        </form>
                                      </div>
                                    </div>

                                  @else
                                    {{-- ADD VIDEO FORM --}}
                                    <form method="POST"
                                          action="{{ route('instructor.materials.store') }}"
                                          class="row g-2 align-items-end js-video-create-form"
                                          data-topic="{{ $topic->id }}">
                                      @csrf
                                      <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                                      <input type="hidden" name="type" value="video">

                                      <div class="col-12 col-md-4">
                                        <label class="form-label small mb-1">Judul Video</label>
                                        <input class="form-control form-control-sm" name="title"
                                               placeholder="Misal: Video — {{ $topic->title }}">
                                      </div>

                                      {{-- LOCAL/URL INPUT --}}
                                      <div class="col-12 col-md-5 js-video-local-wrap">
                                        <label class="form-label small mb-1">Video URL / Path</label>
                                        <input class="form-control form-control-sm js-video-local"
                                               type="text"
                                               name="video_ref"
                                               placeholder="videos/intro.mp4 atau /storage/videos/intro.mp4 atau https://domain.com/intro.mp4">
                                        
                                      </div>

                                      {{-- DRIVE INPUT --}}
                                      <div class="col-12 col-md-5 d-none js-video-drive-wrap">
                                        <label class="form-label small mb-1">Google Drive Link / File ID</label>
                                        <input class="form-control form-control-sm js-video-drive"
                                               type="text"
                                               name="drive_ref"
                                               placeholder="Paste link Drive atau file id (contoh: 1AbC...xyz)">
                                        

                                        {{-- fallback: biar controller lama yang cuma nerima video_ref tetap jalan --}}
                                        <input type="hidden" name="video_ref" class="js-video-ref-mirror" value="">
                                      </div>

                                      <div class="col-12 col-md-3">
                                        <button class="btn btn-sm btn-brand w-100">
                                          <i class="bi bi-save2 me-1"></i> Save
                                        </button>
                                      </div>
                                    </form>
                                  @endif
                                </div>
                              </div>
                            @else
                              <div class="editor-block mb-3">
                                <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                  <i class="bi bi-broadcast" style="color:var(--brand-primary)"></i>
                                  <span>Live Session</span>
                                </div>

                                <div class="p-3 rounded-3"
                                     style="background:rgba(91,62,142,.06);border:1px solid rgba(91,62,142,.12)">
                                  <div class="small text-muted">
                                    Topic ini bertipe <b>Live Session</b>, jadi video tidak wajib.
                                    <br>Kalau butuh video + live, ubah tipe ke <b>Hybrid</b> lewat tombol edit topic.
                                  </div>
                                </div>
                              </div>
                            @endif

                            {{-- 3) FILES + LINKS --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-paperclip" style="color:var(--brand-primary)"></i>
                                <span>Supporting Files</span>
                              </div>

                              <div class="p-3 rounded-3 border mb-3 bg-white">
                                <form method="POST"
                                      action="{{ route('instructor.materials.store') }}"
                                      enctype="multipart/form-data"
                                      class="row g-2 align-items-end">
                                  @csrf
                                  <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                                  <input type="hidden" name="type" value="file">

                                  <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Judul (opsional)</label>
                                    <input class="form-control form-control-sm" name="title"
                                           placeholder="Misal: Slides / PDF / Source Code">
                                  </div>

                                  <div class="col-12 col-md-5">
                                    <label class="form-label small mb-1">Upload Files</label>
                                    <input class="form-control form-control-sm"
                                           type="file"
                                           name="files[]"
                                           multiple>
                                  </div>

                                  <div class="col-12 col-md-3">
                                    <button class="btn btn-sm btn-brand w-100">
                                      <i class="bi bi-upload me-1"></i> Upload
                                    </button>
                                  </div>
                                </form>
                              </div>

                              <div class="list-group list-group-flush mb-3">
                                @forelse($files as $material)
                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                      <i class="bi bi-file-earmark-text"></i>
                                      <div>
                                        <div class="fw-semibold">{{ $material->title }}</div>
                                        <div class="small text-muted">
                                          <a href="{{ $material->fileUrl() }}" target="_blank">Open file</a>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                      <button type="button"
                                              class="btn btn-sm btn-outline-secondary"
                                              data-bs-toggle="modal"
                                              data-bs-target="#editMaterialModal"
                                              data-id="{{ $material->id }}"
                                              data-title="{{ e($material->title) }}"
                                              data-type="{{ $material->type }}"
                                              data-file_path="{{ e($material->file_path) }}"
                                              data-url="{{ e($material->url) }}"
                                              data-drive_id="{{ e($material->drive_id ?? '') }}">
                                        <i class="bi bi-pencil-square"></i>
                                      </button>

                                      <button type="button"
                                              class="btn btn-sm btn-outline-danger js-confirm"
                                              data-bs-title="Hapus file?"
                                              data-bs-message="File ini akan dihapus dari topic."
                                              data-form="#delete-material-{{ $material->id }}">
                                        <i class="bi bi-trash"></i>
                                      </button>

                                      <form id="delete-material-{{ $material->id }}"
                                            method="POST"
                                            action="{{ route('instructor.materials.destroy', $material) }}"
                                            class="d-none">
                                        @csrf @method('DELETE')
                                      </form>
                                    </div>
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">
                                    Belum ada file.
                                  </div>
                                @endforelse
                              </div>

                              {{-- Links (tetap d-none sesuai kode lu) --}}
                              <div class="list-group list-group-flush mb-3 d-none">
                                @forelse($links as $material)
                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                      <i class="bi bi-link-45deg"></i>
                                      <div>
                                        <div class="fw-semibold">{{ $material->title }}</div>
                                        <div class="small text-muted">
                                          <a href="{{ $material->url }}" target="_blank">{{ $material->url }}</a>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                      <button type="button"
                                              class="btn btn-sm btn-outline-secondary"
                                              data-bs-toggle="modal"
                                              data-bs-target="#editMaterialModal"
                                              data-id="{{ $material->id }}"
                                              data-title="{{ e($material->title) }}"
                                              data-type="{{ $material->type }}"
                                              data-file_path="{{ e($material->file_path) }}"
                                              data-url="{{ e($material->url) }}"
                                              data-drive_id="{{ e($material->drive_id ?? '') }}">
                                        <i class="bi bi-pencil-square"></i>
                                      </button>

                                      <button type="button"
                                              class="btn btn-sm btn-outline-danger js-confirm"
                                              data-bs-title="Hapus link?"
                                              data-bs-message="Link ini akan dihapus dari topic."
                                              data-form="#delete-material-{{ $material->id }}">
                                        <i class="bi bi-trash"></i>
                                      </button>

                                      <form id="delete-material-{{ $material->id }}"
                                            method="POST"
                                            action="{{ route('instructor.materials.destroy', $material) }}"
                                            class="d-none">
                                        @csrf @method('DELETE')
                                      </form>
                                    </div>
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">
                                    Belum ada link.
                                  </div>
                                @endforelse
                              </div>

                              <form method="POST"
                                    action="{{ route('instructor.materials.store') }}"
                                    class="row g-2 align-items-end d-none">
                                @csrf
                                <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                                <input type="hidden" name="type" value="link">

                                <div class="col-12 col-md-4">
                                  <label class="form-label small mb-1">Judul Link</label>
                                  <input class="form-control form-control-sm" name="title" placeholder="Misal: Dokumentasi / Repo">
                                </div>

                                <div class="col-12 col-md-5">
                                  <label class="form-label small mb-1">URL</label>
                                  <input class="form-control form-control-sm" name="url" placeholder="https://...">
                                </div>

                                <div class="col-12 col-md-3">
                                  <button class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="bi bi-plus-lg me-1"></i> Add Link
                                  </button>
                                </div>
                              </form>
                            </div>

                            {{-- 4) ASSIGNMENTS --}}
                            <div class="editor-block">
                              <div class="fw-semibold d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                  <i class="bi bi-journal-check" style="color:var(--brand-primary)"></i>
                                  <span>Assignments</span>
                                </div>

                                <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                  <i class="bi bi-hash"></i>
                                  <span>{{ $assignmentCount }} tugas</span>
                                </span>
                              </div>

                              <div class="p-3 rounded-3 assignment-wrap">
                                {{-- Add Assignment --}}
                                <form method="POST"
                                      action="{{ route('instructor.assignments.store') }}"
                                      class="row g-2 align-items-end mb-3">
                                  @csrf
                                  <input type="hidden" name="topic_id" value="{{ $topic->id }}">

                                  <div class="col-12 col-lg-5">
                                    <label class="form-label small mb-1">Judul Tugas</label>
                                    <input class="form-control form-control-sm" name="title"
                                           placeholder="Misal: Buat CRUD + Validation">
                                  </div>

                                  <div class="col-6 col-lg-2">
                                    <label class="form-label small mb-1">Max Score</label>
                                    <input class="form-control form-control-sm" name="max_score" type="number" min="0" value="100">
                                  </div>

                                  <div class="col-6 col-lg-3">
                                    <label class="form-label small mb-1">Deadline (opsional)</label>
                                    <input class="form-control form-control-sm" name="due_at" type="datetime-local">
                                  </div>

                                  <div class="col-12 col-lg-2">
                                    <button class="btn btn-sm btn-brand w-100">
                                      <i class="bi bi-plus-lg me-1"></i> Add
                                    </button>
                                  </div>

                                  <div class="col-12">
                                    <label class="form-label small mb-1">Deskripsi (opsional)</label>
                                    <textarea class="form-control form-control-sm js-autogrow"
                                              name="description"
                                              rows="5"
                                              placeholder="Instruksi singkat tugas..."></textarea>
                                  </div>

                                  <div class="col-12 d-flex align-items-center gap-2">
                                    <div class="form-check form-switch">
                                      <input class="form-check-input" type="checkbox" role="switch" id="pub-{{ $topic->id }}" name="is_published" value="1">
                                      <label class="form-check-label small" for="pub-{{ $topic->id }}">Publish</label>
                                    </div>
                                    <div class="small text-muted">
                                      Publish = terlihat oleh student.
                                    </div>
                                  </div>
                                </form>

                                {{-- Existing Assignments --}}
                                <div class="list-group list-group-flush">
                                  @forelse($assignments as $as)
                                    @php
                                      $isPub = (bool)($as->is_published ?? false);
                                      $due = $as->due_at ?? null;
                                      $max = $as->max_score ?? 100;
                                    @endphp

                                    <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                                      <div class="d-flex align-items-start gap-2">
                                        <div class="assign-icon">
                                          <i class="bi bi-journal-text"></i>
                                        </div>

                                        <div>
                                          <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                                            <span>{{ $as->title }}</span>

                                            <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                              <i class="bi bi-trophy text-secondary"></i>
                                              <span>Max {{ $max }}</span>
                                            </span>

                                            <span class="badge rounded-pill {{ $isPub ? 'text-bg-success' : 'text-bg-secondary' }} d-inline-flex align-items-center gap-1">
                                              <i class="bi {{ $isPub ? 'bi-eye' : 'bi-eye-slash' }}"></i>
                                              <span>{{ $isPub ? 'Published' : 'Draft' }}</span>
                                            </span>

                                            @if($due)
                                              <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-calendar-event text-secondary"></i>
                                                <span>{{ \Illuminate\Support\Carbon::parse($due)->format('d M Y, H:i') }}</span>
                                              </span>
                                            @endif
                                          </div>

                                          @if(!empty(trim(strip_tags((string)($as->description ?? '')))))
                                            <div class="small text-muted mt-1 assignment-desc">
                                              {!! $as->description !!}
                                            </div>
                                          @endif
                                        </div>
                                      </div>

                                      <div class="d-flex gap-2 flex-shrink-0">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editAssignmentModal"
                                                data-id="{{ $as->id }}"
                                                data-title="{{ e($as->title) }}"
                                                data-description="{{ e($as->description) }}"
                                                data-max_score="{{ e($as->max_score) }}"
                                                data-due_at="{{ $as->due_at ? \Illuminate\Support\Carbon::parse($as->due_at)->format('Y-m-d\TH:i') : '' }}"
                                                data-is_published="{{ (int)($as->is_published ?? 0) }}">
                                          <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger js-confirm"
                                                data-bs-title="Hapus assignment?"
                                                data-bs-message="Tugas ini akan dihapus."
                                                data-form="#delete-assignment-{{ $as->id }}">
                                          <i class="bi bi-trash"></i>
                                        </button>

                                        <form id="delete-assignment-{{ $as->id }}"
                                              method="POST"
                                              action="{{ route('instructor.assignments.destroy', $as) }}"
                                              class="d-none">
                                          @csrf @method('DELETE')
                                        </form>
                                      </div>
                                    </div>
                                  @empty
                                    <div class="list-group-item text-muted small">
                                      Belum ada assignment di topic ini.
                                    </div>
                                  @endforelse
                                </div>
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
          Belum ada module. Tambahkan module pertama untuk mulai menyusun materi.
        </div>
      </div>
    @endforelse
  </div>

  {{-- ================= MODALS ================= --}}

  {{-- Video Preview Modal (LOCAL/URL + DRIVE) --}}
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
              <div class="video-modal-subtitle" id="videoPreviewSubtitle">Preview Video</div>
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
            <div class="p-3 bg-black">

              {{-- HTML5 VIDEO --}}
              <video id="videoPreviewPlayer"
                     class="d-none"
                     controls
                     playsinline
                     style="width:100%;max-height:70vh;border-radius:.75rem;background:#000">
                <source id="videoPreviewSource" src="" type="video/mp4">
              </video>

              {{-- DRIVE IFRAME --}}
              <iframe id="drivePreviewFrame"
                      class="d-none"
                      src=""
                      style="width:100%;height:70vh;border-radius:.75rem;border:0;background:#000"
                      allow="autoplay; encrypted-media"
                      allowfullscreen></iframe>
            </div>

            <div class="video-modal-hint" id="videoModalHint">
              <i class="bi bi-info-circle me-1"></i>
              Video bisa berasal dari <code>/storage/videos</code>, URL, atau Google Drive.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Confirm Modal --}}
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalTitle">Konfirmasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0" style="color:#dc3545">
              <i class="bi bi-exclamation-triangle fs-4"></i>
            </div>
            <div>
              <div class="fw-semibold" id="confirmModalMessage">Yakin?</div>
              <div class="small text-muted mt-1">Tindakan ini tidak bisa dibatalkan.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmModalOk">
            <i class="bi bi-trash me-1"></i> Yes, Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Edit Module Modal --}}
  <div class="modal fade" id="editModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" class="modal-content" id="editModuleForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Module</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" id="editModuleTitle">
          </div>

          <div class="mb-2">
            <label class="form-label">Learning Objective</label>
            <textarea class="form-control js-autogrow"
                      name="learning_objectives"
                      id="editModuleObjectives"
                      rows="3"
                      placeholder="Tulis objective singkat (boleh bullet)..."></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-brand" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Edit Topic Modal --}}
  <div class="modal fade" id="editTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" class="modal-content" id="editTopicForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Topic</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Title</label>
          <input class="form-control" name="title" id="editTopicTitle">

          <div class="mt-2">
            <label class="form-label">Tipe Materi</label>
            <select class="form-select" name="delivery_type" id="editTopicDeliveryType">
              <option value="video">Video</option>
              <option value="live">Live Session</option>
              <option value="hybrid">Hybrid</option>
            </select>
            <div class="small text-muted mt-1">
              Live = tanpa video wajib. Hybrid = default Drive (tapi bisa switch ke Local).
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-brand" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Edit Material Modal --}}
  <div class="modal fade" id="editMaterialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" class="modal-content" id="editMaterialForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Resource</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" id="editMaterialTitle">
          </div>

          <div class="mb-2">
            <label class="form-label">Type</label>
            <select class="form-select" name="type" id="editMaterialType">
              <option value="video">Video</option>
              <option value="file">File</option>
              <option value="link">Link</option>
            </select>
          </div>

          {{-- VIDEO MODE --}}
          <div class="mb-2 d-none" id="editMaterialVideoWrap">
            <label class="form-label">Video Source</label>

            <div class="d-flex gap-3 flex-wrap align-items-center">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="video_source" id="editVideoLocal" value="local" checked>
                <label class="form-check-label" for="editVideoLocal">Local / URL</label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="radio" name="video_source" id="editVideoDrive" value="drive">
                <label class="form-check-label" for="editVideoDrive">Google Drive</label>
              </div>
            </div>

            <div class="mt-2" id="editVideoLocalWrap">
              <label class="form-label">Video URL / Path</label>
              <input class="form-control" type="text" name="video_ref" id="editMaterialVideoRef"
                     placeholder="videos/intro.mp4 atau /storage/videos/intro.mp4 atau https://...">
            </div>

            <div class="mt-2 d-none" id="editVideoDriveWrap">
              <label class="form-label">Google Drive Link / File ID</label>
              <input class="form-control" type="text" name="drive_ref" id="editMaterialDriveRef"
                     placeholder="Paste link Drive atau file id (contoh: 1AbC...xyz)">
              <input type="hidden" name="video_ref" id="editVideoRefMirror" value="">
            </div>

            <input type="hidden" name="drive_id" id="editMaterialDriveId" value="">
          </div>

          {{-- FILE --}}
          <div class="mb-2 d-none" id="editMaterialFileWrap">
            <label class="form-label">Replace File (optional)</label>
            <input class="form-control" type="file" name="file">
            <div class="small text-muted mt-1">Kosongkan jika tidak ingin mengganti file.</div>
          </div>

          {{-- LINK --}}
          <div class="mb-2 d-none" id="editMaterialUrlWrap">
            <label class="form-label">URL</label>
            <input class="form-control" name="url" id="editMaterialUrl">
          </div>

          <input type="hidden" name="file_path" id="editMaterialFilePath">
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-brand" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Edit Assignment Modal --}}
  <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" class="modal-content" id="editAssignmentForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-journal-check" style="color:var(--brand-primary)"></i>
            Edit Assignment
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-2">
            <div class="col-12 col-lg-8">
              <label class="form-label">Title</label>
              <input class="form-control" name="title" id="editAssignmentTitle">
            </div>

            <div class="col-6 col-lg-2">
              <label class="form-label">Max Score</label>
              <input class="form-control" type="number" min="0" name="max_score" id="editAssignmentMaxScore">
            </div>

            <div class="col-6 col-lg-2">
              <label class="form-label">Publish</label>
              <select class="form-select" name="is_published" id="editAssignmentPublished">
                <option value="0">Draft</option>
                <option value="1">Published</option>
              </select>
            </div>

            <div class="col-12 col-lg-6">
              <label class="form-label">Deadline (optional)</label>
              <input class="form-control" type="datetime-local" name="due_at" id="editAssignmentDueAt">
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control js-autogrow" rows="4" name="description" id="editAssignmentDescription"
                        placeholder="Instruksi tugas..."></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-brand" type="submit">
            <i class="bi bi-save2 me-1"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- PAGE-LEVEL CSS --}}
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
    .topic-actions .btn{ min-width: 36px; }
    .quill-editor{ min-height: 180px; background: #fff; }

    .video-open{ border-radius:.75rem; padding:.25rem .5rem; display:block; }
    .video-open:hover{ background:rgba(0,0,0,.035); }

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
      display:flex;align-items:center;justify-content:center;
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
  </style>

  {{-- PAGE-LEVEL JS --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const hasBootstrap = !!window.bootstrap;

      // Flash auto dismiss
      (function(){
        const alerts = document.querySelectorAll('.js-flash');
        if(!alerts.length) return;

        setTimeout(() => {
          alerts.forEach(a => {
            if(hasBootstrap && bootstrap.Alert){
              try{ bootstrap.Alert.getOrCreateInstance(a).close(); }catch(e){}
            } else {
              a.remove();
            }
          });
        }, 3500);
      })();

      // ---------- VIDEO SOURCE TOGGLER (Materi Utama) ----------
      function applyVideoMode(topicId, mode){
        const form = document.querySelector(`.js-video-create-form[data-topic="${topicId}"]`);
        if(!form) return;

        const localWrap = form.querySelector('.js-video-local-wrap');
        const driveWrap = form.querySelector('.js-video-drive-wrap');
        const localInput = form.querySelector('.js-video-local');
        const driveInput = form.querySelector('.js-video-drive');
        const mirror = form.querySelector('.js-video-ref-mirror');

        const isDrive = mode === 'drive';

        localWrap?.classList.toggle('d-none', isDrive);
        driveWrap?.classList.toggle('d-none', !isDrive);

        if(isDrive){
          // mirror drive_ref -> video_ref agar controller lama tetap bisa nerima
          if(mirror && driveInput){
            mirror.value = driveInput.value || '';
            driveInput.addEventListener('input', () => mirror.value = driveInput.value || '');
          }
          if(localInput) localInput.value = '';
        }else{
          if(driveInput) driveInput.value = '';
          if(mirror) mirror.value = '';
        }
      }

      // init per topic block: default mode berdasar delivery
      document.querySelectorAll('[data-topic-video-block]').forEach(block => {
        const topicId = block.closest('[id^="topic-"]')?.id?.replace('topic-','');
        if(!topicId) return;

        const radios = block.querySelectorAll(`.js-video-mode[name="video_mode_${topicId}"]`);
        if(!radios.length) return;

        const checked = block.querySelector(`.js-video-mode[name="video_mode_${topicId}"]:checked`);
        const mode = checked?.value || block.getAttribute('data-default-mode') || 'local';
        applyVideoMode(topicId, mode);

        radios.forEach(r => {
          r.addEventListener('change', () => applyVideoMode(topicId, r.value));
        });
      });

      // ---------- VIDEO PREVIEW MODAL (video vs drive iframe) ----------
      (function(){
        const modalEl = document.getElementById('videoPreviewModal');
        if(!modalEl || !hasBootstrap || !bootstrap.Modal) return;

        const titleEl = document.getElementById('videoPreviewTitle');
        const subEl = document.getElementById('videoPreviewSubtitle');
        const openBtn = document.getElementById('videoOpenNewTab');
        const fullBtn = document.getElementById('videoToggleFull');
        const dialog = document.getElementById('videoPreviewDialog');

        const player = document.getElementById('videoPreviewPlayer');
        const source = document.getElementById('videoPreviewSource');
        const frame = document.getElementById('drivePreviewFrame');

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
            fullBtn.childNodes.forEach(n => { if(n.nodeType === 3) n.remove(); });
            fullBtn.insertAdjacentText('beforeend', isOn ? ' Exit' : ' Full');
          }
        }

        function isDriveSrc(src){
          return !!src && src.includes('drive.google.com');
        }

        modalEl.addEventListener('show.bs.modal', (ev) => {
          const btn = ev.relatedTarget;
          if(!btn) return;

          const title = btn.getAttribute('data-title') || 'Video';
          const src = btn.getAttribute('data-src') || '';

          titleEl.textContent = title;
          if(openBtn) openBtn.href = src || '#';

          const drive = isDriveSrc(src);

          // toggle mode
          player?.classList.toggle('d-none', drive);
          frame?.classList.toggle('d-none', !drive);
          if(subEl) subEl.textContent = drive ? 'Preview Google Drive' : 'Preview Video';

          if(drive){
            if(frame) frame.src = src;
            try{ player?.pause(); }catch(e){}
            if(source) source.src = '';
          }else{
            if(source) source.src = src;
            if(frame) frame.src = '';
            if(player){
              try{
                player.load();
                player.currentTime = 0;
                player.play().catch(() => {});
              }catch(e){}
            }
          }

          setFull(false);
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
          try{ player?.pause(); }catch(e){}
          if(source) source.src = '';
          if(frame) frame.src = '';
          try{ player?.load(); }catch(e){}
          if(openBtn) openBtn.href = '#';
          setFull(false);
        });

        fullBtn?.addEventListener('click', (e) => {
          e.preventDefault();
          const isOn = dialog?.classList.contains('is-fullscreen');
          setFull(!isOn);
        });
      })();

      // Bootstrap Confirm
      (function(){
        const modalEl = document.getElementById('confirmModal');
        if(!modalEl) return;

        if(!hasBootstrap || !bootstrap.Modal){
          console.warn('Bootstrap Modal tidak tersedia. Confirm modal dimatikan.');
          return;
        }

        const modal = new bootstrap.Modal(modalEl);
        const titleEl = document.getElementById('confirmModalTitle');
        const msgEl = document.getElementById('confirmModalMessage');
        const okBtn = document.getElementById('confirmModalOk');

        let targetForm = null;

        document.addEventListener('click', (e) => {
          const btn = e.target.closest('.js-confirm');
          if(!btn) return;

          titleEl.textContent = btn.getAttribute('data-bs-title') || 'Konfirmasi';
          msgEl.textContent = btn.getAttribute('data-bs-message') || 'Yakin?';

          const formSel = btn.getAttribute('data-form');
          targetForm = formSel ? document.querySelector(formSel) : null;

          modal.show();
        });

        okBtn?.addEventListener('click', () => {
          if(targetForm) targetForm.submit();
          modal.hide();
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
          targetForm = null;
        });
      })();

      // Auto-grow textarea
      function autoGrow(el){
        if(!el) return;
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
      }
      document.querySelectorAll('.js-autogrow').forEach(el => {
        autoGrow(el);
        el.addEventListener('input', () => autoGrow(el));
      });

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

      // Edit Module modal bind
      const editModuleModal = document.getElementById('editModuleModal');
      editModuleModal?.addEventListener('show.bs.modal', (ev) => {
        const btn = ev.relatedTarget;
        if(!btn) return;

        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title') || '';
        const obj = btn.getAttribute('data-objectives') || '';

        document.getElementById('editModuleTitle').value = title;
        document.getElementById('editModuleObjectives').value = obj;
        autoGrow(document.getElementById('editModuleObjectives'));

        document.getElementById('editModuleForm').action = `{{ url('instructor/modules') }}/${id}`;
      });

      // Edit Topic modal bind
      const editTopicModal = document.getElementById('editTopicModal');
      editTopicModal?.addEventListener('show.bs.modal', (ev) => {
        const btn = ev.relatedTarget;
        if(!btn) return;

        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title') || '';
        const delivery = btn.getAttribute('data-delivery_type') || 'video';

        document.getElementById('editTopicTitle').value = title;
        const sel = document.getElementById('editTopicDeliveryType');
        if(sel) sel.value = delivery;

        document.getElementById('editTopicForm').action = `{{ url('instructor/topics') }}/${id}`;
      });

      // Edit Assignment modal bind
      const editAssignmentModal = document.getElementById('editAssignmentModal');
      editAssignmentModal?.addEventListener('show.bs.modal', (ev) => {
        const btn = ev.relatedTarget;
        if(!btn) return;

        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title') || '';
        const desc = btn.getAttribute('data-description') || '';
        const maxScore = btn.getAttribute('data-max_score') || '100';
        const dueAt = btn.getAttribute('data-due_at') || '';
        const isPub = btn.getAttribute('data-is_published') || '0';

        document.getElementById('editAssignmentTitle').value = title;
        document.getElementById('editAssignmentDescription').value = desc;
        document.getElementById('editAssignmentMaxScore').value = maxScore;
        document.getElementById('editAssignmentDueAt').value = dueAt;
        document.getElementById('editAssignmentPublished').value = isPub;

        autoGrow(document.getElementById('editAssignmentDescription'));
        document.getElementById('editAssignmentForm').action = `{{ url('instructor/assignments') }}/${id}`;
      });

      // Edit Resource modal bind + field toggle
      function toggleEditMaterialFields(type) {
        const v = document.getElementById('editMaterialVideoWrap');
        const f = document.getElementById('editMaterialFileWrap');
        const u = document.getElementById('editMaterialUrlWrap');

        v?.classList.toggle('d-none', type !== 'video');
        f?.classList.toggle('d-none', type !== 'file');
        u?.classList.toggle('d-none', type !== 'link');
      }

      function setEditVideoSource(source){
        const local = document.getElementById('editVideoLocalWrap');
        const drive = document.getElementById('editVideoDriveWrap');
        const mirror = document.getElementById('editVideoRefMirror');
        const driveRef = document.getElementById('editMaterialDriveRef');

        const isDrive = source === 'drive';
        local?.classList.toggle('d-none', isDrive);
        drive?.classList.toggle('d-none', !isDrive);

        if(isDrive && mirror && driveRef){
          mirror.value = driveRef.value || '';
          driveRef.addEventListener('input', () => mirror.value = driveRef.value || '');
        }else{
          if(mirror) mirror.value = '';
        }
      }

      const editMaterialModal = document.getElementById('editMaterialModal');
      editMaterialModal?.addEventListener('show.bs.modal', (ev) => {
        const btn = ev.relatedTarget;
        if(!btn) return;

        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title') || '';
        const type = btn.getAttribute('data-type') || 'file';
        const filePath = btn.getAttribute('data-file_path') || '';
        const url = btn.getAttribute('data-url') || '';
        const driveId = btn.getAttribute('data-drive_id') || '';

        document.getElementById('editMaterialTitle').value = title;
        document.getElementById('editMaterialType').value = type;
        document.getElementById('editMaterialFilePath').value = filePath;
        document.getElementById('editMaterialUrl').value = url;
        document.getElementById('editMaterialDriveId').value = driveId;

        toggleEditMaterialFields(type);
        document.getElementById('editMaterialForm').action = `{{ url('instructor/materials') }}/${id}`;

        if(type === 'video'){
          // auto-detect drive
          const isDrive = !!driveId || (url && url.includes('drive.google.com'));

          document.getElementById('editVideoLocal').checked = !isDrive;
          document.getElementById('editVideoDrive').checked = isDrive;

          document.getElementById('editMaterialVideoRef').value = isDrive ? '' : (url || filePath || '');
          document.getElementById('editMaterialDriveRef').value = isDrive ? (driveId || url || '') : '';

          setEditVideoSource(isDrive ? 'drive' : 'local');
        }
      });

      document.getElementById('editMaterialType')?.addEventListener('change', (e) => {
        toggleEditMaterialFields(e.target.value);
      });

      document.getElementById('editVideoLocal')?.addEventListener('change', () => setEditVideoSource('local'));
      document.getElementById('editVideoDrive')?.addEventListener('change', () => setEditVideoSource('drive'));

      // Quill init (tetap sama)
      const QUILL_INSTANCES = new Map();

      function decodeHtml(html) {
        if(!html) return '';
        const t = document.createElement('textarea');
        t.innerHTML = html;
        return t.value;
      }

      function initQuillForWrap(wrap){
        const topicId = wrap.getAttribute('data-topic');
        const editorEl = document.getElementById(`quill-${topicId}`);
        if(!topicId || !editorEl) return;

        if(QUILL_INSTANCES.has(String(topicId))) return;

        if(!window.Quill){
          console.warn('Quill belum ter-load.');
          const form = wrap.closest('form');
          const fallback = form?.querySelector('.js-outline-fallback');
          fallback?.classList.remove('d-none');
          return;
        }

        editorEl.style.minHeight = '180px';

        const quill = new Quill(editorEl, {
          theme: 'snow',
          modules: {
            toolbar: [
              ['bold', 'italic'],
              [{ list: 'ordered' }, { list: 'bullet' }],
              ['clean']
            ]
          }
        });

        const initialRaw = wrap.getAttribute('data-initial') || '';
        const initial = decodeHtml(initialRaw);
        if(initial.trim()){
          quill.clipboard.dangerouslyPasteHTML(initial);
        }

        QUILL_INSTANCES.set(String(topicId), quill);

        const form = wrap.closest('form');
        const input = form?.querySelector('.js-outline-input');
        const fallback = form?.querySelector('.js-outline-fallback');

        if(input){
          input.value = quill.root.innerHTML;
          quill.on('text-change', () => {
            input.value = quill.root.innerHTML;
            if(fallback) fallback.value = quill.root.innerHTML;
          });
        }

        if(fallback){
          fallback.classList.add('d-none');
          fallback.value = quill.root.innerHTML;
        }

        form?.addEventListener('submit', () => {
          if(input) input.value = quill.root.innerHTML;
        });
      }

      document.querySelectorAll('.quill-wrap').forEach(wrap => initQuillForWrap(wrap));

      document.querySelectorAll('[id^="topic-"]').forEach(collapseEl => {
        collapseEl.addEventListener('shown.bs.collapse', () => {
          collapseEl.querySelectorAll('.quill-wrap').forEach(wrap => initQuillForWrap(wrap));
        });
      });

      // OUTLINE toggle
      (function(){
        function qs(sel, root=document){ return root.querySelector(sel); }

        function showOutlineEdit(topicId){
          const view = qs(`.outline-view[data-topic="${topicId}"]`);
          const edit = qs(`.outline-edit[data-topic="${topicId}"]`);
          const btnEdit = qs(`.js-outline-edit[data-topic="${topicId}"]`);
          const btnCancel = qs(`.js-outline-cancel[data-topic="${topicId}"]`);
          const btnSave = qs(`.js-outline-save[data-topic="${topicId}"]`);

          view?.classList.add('d-none');
          edit?.classList.remove('d-none');

          btnEdit?.classList.add('d-none');
          btnCancel?.classList.remove('d-none');
          btnSave?.classList.remove('d-none');

          const wrap = qs(`.outline-edit[data-topic="${topicId}"] .quill-wrap`);
          if(wrap){
            initQuillForWrap(wrap);
            const quill = QUILL_INSTANCES.get(String(topicId));
            setTimeout(() => { try{ quill?.focus(); }catch(e){} }, 80);
          }
        }

        function showOutlineView(topicId){
          const view = qs(`.outline-view[data-topic="${topicId}"]`);
          const edit = qs(`.outline-edit[data-topic="${topicId}"]`);
          const btnEdit = qs(`.js-outline-edit[data-topic="${topicId}"]`);
          const btnCancel = qs(`.js-outline-cancel[data-topic="${topicId}"]`);
          const btnSave = qs(`.js-outline-save[data-topic="${topicId}"]`);

          const form = qs(`#outline-form-${topicId}`);
          const input = form?.querySelector('.js-outline-input');
          const quill = QUILL_INSTANCES.get(String(topicId));
          if(quill && input){
            try{ quill.root.innerHTML = input.value || ''; }catch(e){}
          }

          edit?.classList.add('d-none');
          view?.classList.remove('d-none');

          btnCancel?.classList.add('d-none');
          btnSave?.classList.add('d-none');
          btnEdit?.classList.remove('d-none');
        }

        document.addEventListener('click', (e) => {
          const editBtn = e.target.closest('.js-outline-edit');
          if(editBtn){
            e.preventDefault();
            const topicId = editBtn.getAttribute('data-topic');
            if(topicId) showOutlineEdit(topicId);
            return;
          }

          const cancelBtn = e.target.closest('.js-outline-cancel');
          if(cancelBtn){
            e.preventDefault();
            const topicId = cancelBtn.getAttribute('data-topic');
            if(topicId) showOutlineView(topicId);
            return;
          }
        });
      })();
    });
  </script>
</x-app-layout>
