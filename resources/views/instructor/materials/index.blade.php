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
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <textarea class="form-control js-autogrow"
                      name="learning_objectives"
                      rows="2"
                      placeholder="Tulis objective singkat (boleh bullet/newline)..."></textarea>
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

                  {{-- Delete Module (bootstrap confirm) --}}
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
                      class="d-flex gap-2 mb-3">
                  @csrf
                  <input type="hidden" name="module_id" value="{{ $module->id }}">
                  <input class="form-control" name="title" placeholder="Tambah topic baru (misal: Setup Project)">
                  <button class="btn btn-outline-secondary">
                    <i class="bi bi-plus-lg me-1"></i> Add Topic
                  </button>
                </form>

                {{-- Topics --}}
                <div class="d-flex flex-column gap-3">
                  @forelse($module->topics as $topic)
                    @php
                      $video = $topic->materials->firstWhere('type','video');
                      $files = $topic->materials->where('type','file');
                      $links = $topic->materials->where('type','link');

                      // Prioritas field outline yg mungkin ada
                      $subpoints = $topic->subtopics ?? $topic->focus_points ?? $topic->subtopic_points ?? null;

                      $hasOutline = !empty(trim(strip_tags((string)$subpoints)));
                      $hasVideo = !!$video;
                      $fileCount = $files->count();
                    @endphp

                    <div class="card topic-card">
                      <div class="card-body p-3">

                        {{-- TOPIC HEADER (summary + actions) --}}
                        <div class="d-flex justify-content-between align-items-start gap-3">
                          <div class="flex-grow-1">
                            <div class="d-flex align-items-start gap-2">
                              <div class="topic-icon">
                                <i class="bi bi-diagram-3"></i>
                              </div>

                              <div class="flex-grow-1">
                                <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                                  <span>{{ $topic->title }}</span>

                                  <span class="badge rounded-pill text-bg-light">
                                    {!! $hasOutline ? '✅ Outline' : '⚠️ Outline kosong' !!}
                                  </span>

                                  <span class="badge rounded-pill text-bg-light">
                                    {!! $hasVideo ? '🎬 Video' : '— Video' !!}
                                  </span>

                                  <span class="badge rounded-pill text-bg-light">
                                    📎 {{ $fileCount }} file
                                  </span>

                                  @if($links->count())
                                    <span class="badge rounded-pill text-bg-light">
                                      🔗 {{ $links->count() }} link
                                    </span>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </div>

                          {{-- Actions (konsisten) --}}
                          <div class="d-flex gap-2 topic-actions">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editTopicModal"
                                    data-id="{{ $topic->id }}"
                                    data-title="{{ e($topic->title) }}">
                              <i class="bi bi-pencil-square"></i>
                            </button>

                            {{-- Delete Topic (bootstrap confirm) --}}
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

                            {{-- 1) OUTLINE (Quill Editor) --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                  <i class="bi bi-list-check" style="color:var(--brand-primary)"></i>
                                  <span>Outline / Sub Topic (poin bahasan)</span>
                                </div>

                                <button type="submit"
                                        form="outline-form-{{ $topic->id }}"
                                        class="btn btn-sm btn-brand">
                                  <i class="bi bi-save2 me-1"></i> Save
                                </button>
                              </div>

                              <form method="POST"
                                    action="{{ route('instructor.topics.update', $topic) }}"
                                    id="outline-form-{{ $topic->id }}"
                                    class="js-outline-form">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="title" value="{{ $topic->title }}">

                                {{-- ini yang disubmit ke backend --}}
                                <input type="hidden"
                                       name="subtopics"
                                       class="js-outline-input"
                                       value="{{ old('subtopics', $subpoints) }}">

                                <div class="quill-wrap"
                                     data-topic="{{ $topic->id }}"
                                     data-initial="{{ e(old('subtopics', $subpoints)) }}">
                                  <div id="quill-{{ $topic->id }}" class="quill-editor"></div>
                                </div>

                                {{-- fallback kalau quill gagal load, user masih bisa ngisi --}}
                                <noscript>
                                  <div class="alert alert-warning small mt-2 mb-0">
                                    JavaScript mati. Outline pakai textarea:
                                  </div>
                                </noscript>
                                <textarea class="form-control form-control-sm mt-2 d-none js-outline-fallback"
                                          rows="5"
                                          placeholder="Kalau editor tidak muncul, isi di sini..."></textarea>
                              </form>
                            </div>

                            {{-- 2) VIDEO --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-play-btn" style="color:var(--brand-primary)"></i>
                                <span>Video</span>
                              </div>

                              <div class="p-3 rounded-3"
                                   style="background:rgba(91,62,142,.06);border:1px solid rgba(91,62,142,.12)">
                                @if($video)
                                  <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex gap-2">
                                      <i class="bi bi-play-circle mt-1"></i>
                                      <div>
                                        <div class="fw-semibold">{{ $video->title }}</div>
                                        <div class="small text-muted">
                                          Drive ID: <span class="font-monospace">{{ $video->drive_id }}</span>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                      <button type="button"
                                              class="btn btn-sm btn-outline-secondary"
                                              data-bs-toggle="modal"
                                              data-bs-target="#editMaterialModal"
                                              data-id="{{ $video->id }}"
                                              data-title="{{ e($video->title) }}"
                                              data-type="{{ $video->type }}"
                                              data-drive_id="{{ e($video->drive_id) }}"
                                              data-url="{{ e($video->url) }}">
                                        <i class="bi bi-pencil-square"></i>
                                      </button>

                                      {{-- Delete Video (bootstrap confirm) --}}
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
                                  <form method="POST"
                                        action="{{ route('instructor.materials.store') }}"
                                        class="row g-2 align-items-end">
                                    @csrf
                                    <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                                    <input type="hidden" name="type" value="video">

                                    <div class="col-12 col-md-4">
                                      <label class="form-label small mb-1">Judul Video</label>
                                      <input class="form-control form-control-sm" name="title"
                                             placeholder="Misal: Video — {{ $topic->title }}">
                                    </div>

                                    <div class="col-12 col-md-5">
                                      <label class="form-label small mb-1">Drive File ID</label>
                                      <input class="form-control form-control-sm" name="drive_id"
                                             placeholder="1AbC... (ID file Drive)">
                                    </div>

                                    <div class="col-12 col-md-3">
                                      <button class="btn btn-sm btn-brand w-100">
                                        <i class="bi bi-save2 me-1"></i> Save Video
                                      </button>
                                    </div>
                                  </form>
                                @endif
                              </div>
                            </div>

                            {{-- 3) FILES + LINKS --}}
                            <div class="editor-block">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-paperclip" style="color:var(--brand-primary)"></i>
                                <span>Files & Links</span>
                              </div>

                              {{-- Upload multi file --}}
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

                              {{-- Existing files --}}
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
                                              data-drive_id="{{ e($material->drive_id) }}"
                                              data-url="{{ e($material->url) }}">
                                        <i class="bi bi-pencil-square"></i>
                                      </button>

                                      {{-- Delete File (bootstrap confirm) --}}
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

                              {{-- Links --}}
                              <div class="list-group list-group-flush mb-3">
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
                                              data-drive_id="{{ e($material->drive_id) }}"
                                              data-url="{{ e($material->url) }}">
                                        <i class="bi bi-pencil-square"></i>
                                      </button>

                                      {{-- Delete Link (bootstrap confirm) --}}
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
                                    class="row g-2 align-items-end">
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

  {{-- Global Confirm Modal (Bootstrap) --}}
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

  {{-- Edit Topic Modal (title only) --}}
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
      <form method="POST" class="modal-content" id="editMaterialForm" enctype="multipart/form-data">
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
              <option value="video">Video (Drive)</option>
              <option value="file">File</option>
              <option value="link">Link</option>
            </select>
          </div>

          <div class="mb-2" id="editMaterialDriveWrap">
            <label class="form-label">Drive File ID</label>
            <input class="form-control" name="drive_id" id="editMaterialDriveId">
          </div>

          <div class="mb-2 d-none" id="editMaterialFileWrap">
            <label class="form-label">Replace File (optional)</label>
            <input class="form-control" type="file" name="file">
            <div class="small text-muted mt-1">Kosongkan jika tidak ingin mengganti file.</div>
          </div>

          <div class="mb-2 d-none" id="editMaterialUrlWrap">
            <label class="form-label">URL</label>
            <input class="form-control" name="url" id="editMaterialUrl">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-brand" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  {{-- PAGE-LEVEL CSS (tanpa load quill css lagi, karena sudah global di layout) --}}
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

    .topic-editor{
      background: #fafafa;
      border:1px solid rgba(0,0,0,.08);
    }

    .topic-actions .btn{ min-width: 36px; }

    /* ✅ PENTING: biar area ngetik keliatan */
    .quill-editor{
      min-height: 180px;
      background: #fff;
    }

    /* Quill styling biar nyatu sama Bootstrap */
    .quill-wrap .ql-toolbar{
      border-radius: .5rem .5rem 0 0;
      border-color: rgba(0,0,0,.12);
      background: #fff;
    }
    .quill-wrap .ql-container{
      border-radius: 0 0 .5rem .5rem;
      border-color: rgba(0,0,0,.12);
      background: #fff;
      font-size: .95rem;
    }
    .ql-editor{ padding: .75rem; }
  </style>

  {{-- PAGE-LEVEL JS (tanpa load quill js lagi, karena sudah global di layout) --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const hasBootstrap = !!window.bootstrap;

      // ===============================
      // Flash auto dismiss
      // ===============================
      (function(){
        const alerts = document.querySelectorAll('.js-flash');
        if(!alerts.length) return;

        setTimeout(() => {
          alerts.forEach(a => {
            if(hasBootstrap && bootstrap.Alert){
              try{
                bootstrap.Alert.getOrCreateInstance(a).close();
              }catch(e){}
            } else {
              a.remove();
            }
          });
        }, 3500);
      })();

      // ===============================
      // Bootstrap Confirm (global)
      // ===============================
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

      // ===============================
      // Auto-grow textarea
      // ===============================
      function autoGrow(el){
        if(!el) return;
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
      }
      document.querySelectorAll('.js-autogrow').forEach(el => {
        autoGrow(el);
        el.addEventListener('input', () => autoGrow(el));
      });

      // ===============================
      // Toggle chevron up/down on topic expand
      // ===============================
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

      // ===============================
      // Edit Module modal bind
      // ===============================
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

      // ===============================
      // Edit Topic modal bind (title only)
      // ===============================
      const editTopicModal = document.getElementById('editTopicModal');
      editTopicModal?.addEventListener('show.bs.modal', (ev) => {
        const btn = ev.relatedTarget;
        if(!btn) return;

        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title') || '';

        document.getElementById('editTopicTitle').value = title;
        document.getElementById('editTopicForm').action = `{{ url('instructor/topics') }}/${id}`;
      });

      // ===============================
      // Edit Resource modal bind + field toggle
      // ===============================
      function toggleEditMaterialFields(type) {
        document.getElementById('editMaterialDriveWrap')?.classList.toggle('d-none', type !== 'video');
        document.getElementById('editMaterialFileWrap')?.classList.toggle('d-none', type !== 'file');
        document.getElementById('editMaterialUrlWrap')?.classList.toggle('d-none', type !== 'link');
      }

      const editMaterialModal = document.getElementById('editMaterialModal');
      editMaterialModal?.addEventListener('show.bs.modal', (ev) => {
        const btn = ev.relatedTarget;
        if(!btn) return;

        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title') || '';
        const type = btn.getAttribute('data-type') || 'file';
        const driveId = btn.getAttribute('data-drive_id') || '';
        const url = btn.getAttribute('data-url') || '';

        document.getElementById('editMaterialTitle').value = title;
        document.getElementById('editMaterialType').value = type;
        document.getElementById('editMaterialDriveId').value = driveId;
        document.getElementById('editMaterialUrl').value = url;

        toggleEditMaterialFields(type);
        document.getElementById('editMaterialForm').action = `{{ url('instructor/materials') }}/${id}`;
      });

      document.getElementById('editMaterialType')?.addEventListener('change', (e) => {
        toggleEditMaterialFields(e.target.value);
      });

      // ===============================
      // Quill init (fix editor kosong)
      // - init saat DOM ready
      // - init lagi saat collapse dibuka (case hidden height 0)
      // ===============================
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

        if(QUILL_INSTANCES.has(topicId)) return;

        if(!window.Quill){
          console.warn('Quill belum ter-load.');
          // fallback textarea muncul
          const form = wrap.closest('form');
          const fallback = form?.querySelector('.js-outline-fallback');
          fallback?.classList.remove('d-none');
          return;
        }

        // ensure visible height
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

        QUILL_INSTANCES.set(topicId, quill);

        const form = wrap.closest('form');
        const input = form?.querySelector('.js-outline-input');
        const fallback = form?.querySelector('.js-outline-fallback');

        // sinkron hidden input
        if(input){
          input.value = quill.root.innerHTML;
          quill.on('text-change', () => {
            input.value = quill.root.innerHTML;
            if(fallback) fallback.value = quill.root.innerHTML;
          });
        }

        // fallback textarea tetap sync (kalau mau)
        if(fallback){
          fallback.classList.add('d-none'); // karena quill sukses
          fallback.value = quill.root.innerHTML;
        }

        form?.addEventListener('submit', () => {
          if(input) input.value = quill.root.innerHTML;
        });
      }

      // init semua wrap yang ada di DOM (meski masih collapse)
      document.querySelectorAll('.quill-wrap').forEach(wrap => initQuillForWrap(wrap));

      // init ulang pas collapse kebuka
      document.querySelectorAll('[id^="topic-"]').forEach(collapseEl => {
        collapseEl.addEventListener('shown.bs.collapse', () => {
          collapseEl.querySelectorAll('.quill-wrap').forEach(wrap => initQuillForWrap(wrap));
        });
      });
    });
  </script>
</x-app-layout>
