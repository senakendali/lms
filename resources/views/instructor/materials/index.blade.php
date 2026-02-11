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

                <span class="badge rounded-pill text-bg-light ms-1">
                    Materials
                </span>
            </div>

            <div class="text-muted small">
                Kelola module, topic, dan materi pembelajaran untuk course ini.
            </div>
        </div>

        <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success py-2 small rounded-3">{{ session('status') }}</div>
    @endif

    {{-- Add Module --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="POST" action="{{ route('instructor.modules.store') }}">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">

                <div class="row d-flex  g-2 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label small mb-1">Module Title</label>
                        <input class="form-control"
                               name="title"
                               placeholder="Misal: Week 1 — Introduction">
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label small mb-1">Learning Objective</label>
                        {{-- ✅ textarea proper --}}
                         <input class="form-control"
                               name="learning_objectives"
                               placeholder="Misal: Week 1 — Introduction">
                        
                       
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

                        {{-- ====== Module Header (rapi: title + actions sejajar) ====== --}}
                        <h2 class="accordion-header">
                            <div class="d-flex align-items-start justify-content-between px-3 py-2">

                                {{-- LEFT: Toggle (Title + objective) --}}
                                <button class="btn p-0 text-start flex-grow-1"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $moduleKey }}"
                                        style="box-shadow:none">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-folder2-open text-muted"></i>
                                        <span class="fw-semibold">{{ $module->title }}</span>
                                    </div>

                                    {{-- ✅ tampilkan objective kalau ada --}}
                                    @if(!empty($module->learning_objectives))
                                        <div class="small text-muted mt-1 module-obj">
                                            <i class="bi bi-bullseye me-1"></i>
                                            {{ $module->learning_objectives }}
                                        </div>
                                    @endif
                                </button>

                                {{-- RIGHT: Actions --}}
                                <div class="d-flex align-items-center gap-2 ms-3">

                                    {{-- Edit --}}
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModuleModal"
                                            data-id="{{ $module->id }}"
                                            data-title="{{ e($module->title) }}"
                                            data-objectives="{{ e($module->learning_objectives) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- Delete --}}
                                    <form method="POST"
                                          action="{{ route('instructor.modules.destroy', $module) }}"
                                          onsubmit="return confirm('Hapus module ini? Semua topic & materi di dalamnya ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                    {{-- Chevron --}}
                                    <button class="btn btn-sm btn-light"
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
                                    <input class="form-control" name="title"
                                           placeholder="Tambah topic baru (misal: Setup Project)">
                                    <button class="btn btn-outline-secondary">
                                        <i class="bi bi-plus-lg me-1"></i> Add Topic
                                    </button>
                                </form>

                                {{-- Topics --}}
                                <div class="d-flex flex-column gap-3">
                                    @forelse($module->topics as $topic)
                                        <div class="card">
                                            <div class="card-body p-3">

                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="fw-semibold">
                                                        <i class="bi bi-diagram-3 me-2"></i>{{ $topic->title }}
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editTopicModal"
                                                                data-id="{{ $topic->id }}"
                                                                data-title="{{ e($topic->title) }}">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>

                                                        <form method="POST"
                                                              action="{{ route('instructor.topics.destroy', $topic) }}"
                                                              onsubmit="return confirm('Hapus topic ini? Semua materi di dalamnya ikut terhapus.')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                {{-- Materials list --}}
                                                <div class="list-group list-group-flush mb-3">
                                                    @forelse($topic->materials as $material)
                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center gap-2">
                                                                @if($material->type === 'video')
                                                                    <i class="bi bi-play-btn"></i>
                                                                @elseif($material->type === 'file')
                                                                    <i class="bi bi-file-earmark-text"></i>
                                                                @else
                                                                    <i class="bi bi-link-45deg"></i>
                                                                @endif

                                                                <div>
                                                                    <div class="fw-semibold">{{ $material->title }}</div>

                                                                    <div class="small text-muted">
                                                                        @if($material->type === 'video')
                                                                            Drive ID: {{ $material->drive_id }}
                                                                        @elseif($material->type === 'file')
                                                                            File: <a href="{{ $material->fileUrl() }}" target="_blank">Open</a>
                                                                        @else
                                                                            <a href="{{ $material->url }}" target="_blank">{{ $material->url }}</a>
                                                                        @endif
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

                                                                <form method="POST"
                                                                      action="{{ route('instructor.materials.destroy', $material) }}"
                                                                      onsubmit="return confirm('Hapus material ini?')">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="list-group-item text-muted small">
                                                            Belum ada materi pada topic ini.
                                                        </div>
                                                    @endforelse
                                                </div>

                                                {{-- Add Material --}}
                                                <form method="POST"
                                                      action="{{ route('instructor.materials.store') }}"
                                                      enctype="multipart/form-data"
                                                      class="row g-2 align-items-end">
                                                    @csrf
                                                    <input type="hidden" name="topic_id" value="{{ $topic->id }}">

                                                    <div class="col-12 col-md-4">
                                                        <label class="form-label small mb-1">Judul Materi</label>
                                                        <input class="form-control form-control-sm" name="title"
                                                               placeholder="Misal: Install Laravel 12">
                                                    </div>

                                                    <div class="col-6 col-md-2">
                                                        <label class="form-label small mb-1">Tipe</label>
                                                        <select class="form-select form-select-sm material-type" name="type">
                                                            <option value="video">Video Utama (Drive)</option>
                                                            <option value="file">Lampiran (File)</option>
                                                            <option value="link">Lampiran (Link)</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12 col-md-3 material-drive">
                                                        <label class="form-label small mb-1">Drive File ID</label>
                                                        <input class="form-control form-control-sm" name="drive_id"
                                                               placeholder="1AbC... (ID file)">
                                                    </div>

                                                    <div class="col-12 col-md-3 material-file d-none">
                                                        <label class="form-label small mb-1">Upload File</label>
                                                        <input class="form-control form-control-sm" type="file" name="file">
                                                    </div>

                                                    <div class="col-12 col-md-3 material-link d-none">
                                                        <label class="form-label small mb-1">URL</label>
                                                        <input class="form-control form-control-sm" name="url"
                                                               placeholder="https://...">
                                                    </div>

                                                    <div class="col-12 col-md-3">
                                                        <button class="btn btn-sm btn-brand w-100">
                                                            <i class="bi bi-plus-lg me-1"></i> Add Material
                                                        </button>
                                                    </div>
                                                </form>

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
                        {{-- ✅ textarea --}}
                        <textarea class="form-control js-autogrow"
                                  name="learning_objectives"
                                  id="editModuleObjectives"
                                  rows="3"
                                  placeholder="Tulis objective singkat (boleh bullet)..."></textarea>
                    </div>

                    <div class="small text-muted">
                        Tips: gunakan bullet biar gampang dibaca student.
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
                    <h5 class="modal-title">Edit Material</h5>
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
                            <option value="video">Video Utama (Drive)</option>
                            <option value="file">Lampiran (File)</option>
                            <option value="link">Lampiran (Link)</option>
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

    {{-- Page CSS --}}
    <style>
        .accordion-header .btn:focus { box-shadow:none; }
        .accordion-header button.btn-light { background:transparent; border:none; }
        .accordion-header button.btn-light:hover { background:rgba(0,0,0,.05); }

        /* Objective tampil rapih */
        .module-obj{
            white-space: pre-line; /* biar bullet/newline kebaca */
            max-width: 980px;
        }
    </style>

    <script>
        // Auto-grow textarea
        function autoGrow(el){
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        }
        document.querySelectorAll('.js-autogrow').forEach(el => {
            autoGrow(el);
            el.addEventListener('input', () => autoGrow(el));
        });

        // Toggle fields on Add Material forms
        function toggleMaterialFields(container, type) {
            const drive = container.querySelector('.material-drive');
            const file = container.querySelector('.material-file');
            const link = container.querySelector('.material-link');

            if (!drive || !file || !link) return;

            drive.classList.toggle('d-none', type !== 'video');
            file.classList.toggle('d-none', type !== 'file');
            link.classList.toggle('d-none', type !== 'link');
        }

        document.querySelectorAll('form').forEach(form => {
            const sel = form.querySelector('.material-type');
            if (sel) {
                toggleMaterialFields(form, sel.value);
                sel.addEventListener('change', () => toggleMaterialFields(form, sel.value));
            }
        });

        // Edit Module modal bind (title + objectives)
        const editModuleModal = document.getElementById('editModuleModal');
        editModuleModal?.addEventListener('show.bs.modal', (ev) => {
            const btn = ev.relatedTarget;
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title') || '';
            const obj = btn.getAttribute('data-objectives') || '';

            document.getElementById('editModuleTitle').value = title;
            document.getElementById('editModuleObjectives').value = obj;

            // auto grow objectives
            autoGrow(document.getElementById('editModuleObjectives'));

            document.getElementById('editModuleForm').action = `{{ url('instructor/modules') }}/${id}`;
        });

        // Edit Topic modal bind
        const editTopicModal = document.getElementById('editTopicModal');
        editTopicModal?.addEventListener('show.bs.modal', (ev) => {
            const btn = ev.relatedTarget;
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            document.getElementById('editTopicTitle').value = title;
            document.getElementById('editTopicForm').action = `{{ url('instructor/topics') }}/${id}`;
        });

        // Edit Material modal bind + field toggle
        function toggleEditMaterialFields(type) {
            document.getElementById('editMaterialDriveWrap').classList.toggle('d-none', type !== 'video');
            document.getElementById('editMaterialFileWrap').classList.toggle('d-none', type !== 'file');
            document.getElementById('editMaterialUrlWrap').classList.toggle('d-none', type !== 'link');
        }

        const editMaterialModal = document.getElementById('editMaterialModal');
        editMaterialModal?.addEventListener('show.bs.modal', (ev) => {
            const btn = ev.relatedTarget;
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const type = btn.getAttribute('data-type');
            const driveId = btn.getAttribute('data-drive_id');
            const url = btn.getAttribute('data-url');

            document.getElementById('editMaterialTitle').value = title;
            document.getElementById('editMaterialType').value = type;
            document.getElementById('editMaterialDriveId').value = driveId || '';
            document.getElementById('editMaterialUrl').value = url || '';

            toggleEditMaterialFields(type);
            document.getElementById('editMaterialForm').action = `{{ url('instructor/materials') }}/${id}`;
        });

        document.getElementById('editMaterialType')?.addEventListener('change', (e) => {
            toggleEditMaterialFields(e.target.value);
        });
    </script>
</x-app-layout>
