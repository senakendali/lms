{{-- resources/views/student/courses/show.blade.php --}}
<x-app-layout>
  @php
    $progressPct = (int) ($progressPct ?? 0);

    $courseInstructor = $course->instructor?->name ?? 'Instructor';
    $modules = $course->modules ?? collect();

    $topicProgressMap = $topicProgressMap ?? [];
    $videoProgressMap = $videoProgressMap ?? [];

    // ✅ cek route SEKALI (tanpa "use")
    $hasMarkRoute = class_exists(\Illuminate\Support\Facades\Route::class)
      ? \Illuminate\Support\Facades\Route::has('student.topics.mark')
      : false;

    $hasVideoSaveRoute = class_exists(\Illuminate\Support\Facades\Route::class)
      ? \Illuminate\Support\Facades\Route::has('student.videos.progress')
      : false;

    $hasVideoGetRoute = class_exists(\Illuminate\Support\Facades\Route::class)
      ? \Illuminate\Support\Facades\Route::has('student.videos.progress.get')
      : false;

    $hasVideoStreamRoute = class_exists(\Illuminate\Support\Facades\Route::class)
      ? \Illuminate\Support\Facades\Route::has('student.videos.stream')
      : false;

    /**
     * VIDEO HELPERS
     */
    $extractDriveId = function($input){
      $s = trim((string)$input);
      if($s === '') return '';
      if(!str_contains($s, '/') && !str_contains($s, 'http')) return $s;
      if(preg_match('~/file/d/([^/]+)~i', $s, $m)) return $m[1] ?? '';
      if(preg_match('~[?&]id=([^&]+)~i', $s, $m)) return $m[1] ?? '';
      return '';
    };

    $isHtml5VideoUrl = function($url){
      $u = strtolower((string)$url);
      return (bool) preg_match('~\.(mp4|webm|ogg|m4v)(\?.*)?$~i', $u)
        || str_contains($u, '.m3u8')
        || str_contains($u, 'application/x-mpegurl')
        || str_contains($u, 'application/vnd.apple.mpegurl');
    };

    $youtubeEmbed = function($url){
      $u = trim((string)$url);
      if($u === '') return '';
      if(preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~i', $u, $m)) return "https://www.youtube.com/embed/".$m[1];
      if(preg_match('~v=([A-Za-z0-9_-]{6,})~i', $u, $m)) return "https://www.youtube.com/embed/".$m[1];
      if(preg_match('~/embed/([A-Za-z0-9_-]{6,})~i', $u, $m)) return "https://www.youtube.com/embed/".$m[1];
      return '';
    };

    $vimeoEmbed = function($url){
      $u = trim((string)$url);
      if($u === '') return '';
      if(preg_match('~vimeo\.com/(?:video/)?([0-9]{6,})~i', $u, $m)) return "https://player.vimeo.com/video/".$m[1];
      return '';
    };

    $normalizeDrivePreview = function($driveIdOrLink) use ($extractDriveId){
      $raw = trim((string)$driveIdOrLink);
      if($raw === '') return ['', ''];

      $id = $extractDriveId($raw);
      if($id !== ''){
        return [
          "https://drive.google.com/file/d/{$id}/preview",
          "https://drive.google.com/file/d/{$id}/view",
        ];
      }

      if(str_contains($raw, 'drive.google.com') && str_contains($raw, '/preview')){
        $open = preg_replace('~/preview(\?.*)?$~i', '/view', $raw);
        return [$raw, $open ?: $raw];
      }

      if(str_contains($raw, 'drive.google.com') && str_contains($raw, '/view')){
        $prev = preg_replace('~/view(\?.*)?$~i', '/preview', $raw);
        return [$prev ?: $raw, $raw];
      }

      return ['', ''];
    };

    $renderOutline = function($raw){
      $s = trim((string)$raw);
      if($s === '') return '';
      $hasHtml = $s !== strip_tags($s);
      return $hasHtml ? $s : nl2br(e($s));
    };

    $plural = function(int $n, string $singular, string $plural){
      return $n === 1 ? $singular : $plural;
    };
  @endphp

  {{-- ✅ TOAST (prod-ready) --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="appToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-start gap-2">
          <i id="toastIcon" class="bi mt-1"></i>
          <div>
            <div id="toastTitle" class="fw-semibold"></div>
            <div id="toastMsg" class="small opacity-75"></div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto d-none" id="toastCloseBtn" aria-label="Close"></button>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
    <div class="me-2" style="min-width: 260px;">
      <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3"
              style="width:38px;height:38px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
          <i class="bi bi-journal-bookmark-fill"></i>
        </span>

        <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">{{ $course->title }}</h4>

        @if((int)($course->is_active ?? 0) === 1)
          <span class="badge rounded-pill" style="background:rgba(76,184,83,.14);color:var(--brand-secondary);">
            <i class="bi bi-check-circle me-1"></i> Active
          </span>
        @else
          <span class="badge rounded-pill text-bg-light">
            <i class="bi bi-slash-circle me-1"></i> Not active
          </span>
        @endif
      </div>

      <div class="text-muted small">
        {{ $course->description ?: 'Course description is not available yet.' }}
      </div>

      <div class="d-flex flex-wrap gap-2 mt-2">
        <span class="badge rounded-pill text-bg-light">
          <i class="bi bi-person-workspace me-1"></i> {{ $courseInstructor }}
        </span>

        <span class="badge rounded-pill text-bg-light">
          <i class="bi bi-collection me-1"></i>
          {{ $modules->count() }} {{ $plural($modules->count(), 'module', 'modules') }}
        </span>

        @php $topicCount = (int) $modules->sum(fn($m) => $m->topics?->count() ?? 0); @endphp
        <span class="badge rounded-pill text-bg-light">
          <i class="bi bi-diagram-3 me-1"></i>
          {{ $topicCount }} {{ $plural($topicCount, 'topic', 'topics') }}
        </span>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap align-items-center">
      <a href="{{ route('student.courses.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Courses
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
            <span>Learning progress</span>
          </div>
          <div class="small text-muted">
            Progress updates automatically while you watch. You can also mark a topic as started or completed.
          </div>
        </div>
        <div class="fw-bold" style="color:var(--brand-primary)">{{ $progressPct }}%</div>
      </div>

      <div class="mt-3">
        <div class="progress" style="height:10px;">
          <div class="progress-bar" role="progressbar" style="width:{{ $progressPct }}%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1">
          <span>Completion</span><span>{{ $progressPct }}%</span>
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
                      {{ $module->topics?->count() ?? 0 }} {{ $plural((int)($module->topics?->count() ?? 0), 'topic', 'topics') }}
                    </span>
                  </div>

                  @if(!empty($module->learning_objectives))
                    <div class="small text-muted mt-1 module-obj">
                      <i class="bi bi-bullseye me-1"></i>
                      {{ $module->learning_objectives }}
                    </div>
                  @endif
                </button>

                <button class="btn btn-sm btn-outline-secondary module-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $moduleKey }}"
                        aria-controls="collapse-{{ $moduleKey }}"
                        aria-label="Toggle module">
                  <i class="bi bi-chevron-down"></i>
                </button>
              </div>
            </h2>

            <div id="collapse-{{ $moduleKey }}" class="accordion-collapse collapse" data-bs-parent="#studentModulesAcc">
              <div class="accordion-body p-3">

                <div class="d-flex flex-column gap-3">
                  @forelse(($module->topics ?? collect()) as $topic)
                    @php
                      $materials = $topic->materials ?? collect();

                      $video = $materials->firstWhere('type', 'video');
                      $files = $materials->where('type', 'file');
                      $links = $materials->where('type', 'link');

                      $subpointsRaw = $topic->focus_points ?? $topic->subtopic_points ?? $topic->subtopics ?? null;
                      $subpoints = is_string($subpointsRaw) ? $subpointsRaw : (string) $subpointsRaw;
                      $hasOutline = !empty(trim(strip_tags((string) $subpoints)));

                      $hasVideo = (bool) $video;
                      $fileCount = (int) $files->count();

                      $videoKind = null;
                      $videoPreviewUrl = null;
                      $videoOpenUrl    = null;
                      $videoHtml5Url   = null;
                      $videoTrackable  = false;

                      if($video){
                        $driveId  = (string)($video->drive_id ?? '');
                        $url      = (string)($video->url ?? '');
                        $filePath = (string)($video->file_path ?? '');

                        $driveSeed = $driveId !== '' ? $driveId : $url;
                        [$drivePrev, $driveOpen] = $normalizeDrivePreview($driveSeed);

                        if($drivePrev){
                          $videoKind = 'drive';
                          $videoPreviewUrl = $drivePrev;
                          $videoOpenUrl = $driveOpen ?: $drivePrev;
                        } else {
                          $yt = $youtubeEmbed($url);
                          if($yt){
                            $videoKind = 'youtube';
                            $videoPreviewUrl = $yt;
                            $videoOpenUrl = $url ?: $yt;
                          } else {
                            $vm = $vimeoEmbed($url);
                            if($vm){
                              $videoKind = 'vimeo';
                              $videoPreviewUrl = $vm;
                              $videoOpenUrl = $url ?: $vm;
                            } else {
                              if($url !== '' && $isHtml5VideoUrl($url)){
                                $videoKind = 'html5';
                                $videoHtml5Url = $url;
                                $videoOpenUrl = $url;
                                $videoTrackable = true;
                              } elseif($filePath !== ''){
                                $videoKind = 'html5';

                                // ✅ PRODUCTION: prefer stream route (Range/206) kalau ada
                                if($hasVideoStreamRoute){
                                  $videoHtml5Url = route('student.videos.stream', $video);
                                } else {
                                  $videoHtml5Url = \Illuminate\Support\Facades\Storage::url($filePath);
                                }

                                $videoOpenUrl = $videoHtml5Url;
                                $videoTrackable = true;
                              } else {
                                if($url !== ''){
                                  $videoKind = 'iframe';
                                  $videoPreviewUrl = $url;
                                  $videoOpenUrl = $url;
                                }
                              }
                            }
                          }
                        }
                      }

                      $assignments = $topic->assignments ?? collect();
                      $assignmentCount = (int) $assignments->count();

                      $delivery = $topic->delivery_type ?? 'video';

                      // topic progress
                      $tp = $topicProgressMap[$topic->id] ?? null;
                      $topicStatus = is_object($tp) ? ($tp->status ?? 'not_started') : ($tp['status'] ?? 'not_started');

                      $topicDone = $topicStatus === 'done';
                      $topicInProgress = $topicStatus === 'in_progress';
                      $statusLabel = $topicDone ? 'Completed' : ($topicInProgress ? 'In progress' : 'Not started');

                      // video progress map (key material_id)
                      $vp = $video ? ($videoProgressMap[$video->id] ?? null) : null;

                      $videoPct = 0;
                      $resumeSec = 0;
                      if (is_object($vp)) {
                        $videoPct = (int)($vp->progress_pct ?? 0);
                        $resumeSec = (int)($vp->watched_seconds ?? 0);
                      } elseif (is_array($vp)) {
                        $videoPct = (int)($vp['progress_pct'] ?? 0);
                        $resumeSec = (int)($vp['watched_seconds'] ?? 0);
                      }

                      $videoHasSource = (bool)($videoHtml5Url || $videoPreviewUrl);
                      $videoModalSrc  = $videoKind === 'html5' ? ($videoHtml5Url ?: '') : ($videoPreviewUrl ?: '');

                      $topicBadgeClass = $topicDone ? 'text-bg-success' : ($topicInProgress ? 'text-bg-primary' : 'text-bg-light');
                      $topicBadgeIcon  = $topicDone ? 'bi-check2-circle' : ($topicInProgress ? 'bi-play-circle' : 'bi-circle');

                      $videoSaveUrl = ($video && $videoTrackable && $hasVideoSaveRoute) ? route('student.videos.progress', $video) : '';
                      $videoGetUrl  = ($video && $videoTrackable && $hasVideoGetRoute)  ? route('student.videos.progress.get', $video) : '';

                      // file url helper
                      $fileUrlOf = function($m){
                        if(method_exists($m, 'fileUrl')) return $m->fileUrl();
                        $fp = (string)($m->file_path ?? '');
                        if($fp !== '') return \Illuminate\Support\Facades\Storage::url($fp);
                        $u = (string)($m->url ?? '');
                        return $u !== '' ? $u : null;
                      };

                      $badgeDelivery = strtoupper((string)$delivery);
                      $videoLabel = $videoTrackable ? 'Resume supported' : 'External video';
                    @endphp

                    <div class="card topic-card" data-topic-card="{{ (int)$topic->id }}">
                      <div class="card-body p-3">

                        {{-- TOPIC HEADER --}}
                        <div class="d-flex justify-content-between align-items-start gap-3">
                          <div class="flex-grow-1">
                            <div class="d-flex align-items-start gap-2">
                              <div class="topic-icon"><i class="bi bi-diagram-3"></i></div>

                              <div class="flex-grow-1">
                                <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                                  <span>{{ $topic->title }}</span>

                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-broadcast text-secondary"></i>
                                    <span class="text-uppercase">{{ $badgeDelivery }}</span>
                                  </span>

                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    @if($hasOutline)
                                      <i class="bi bi-check-circle text-success"></i><span>Outline ready</span>
                                    @else
                                      <i class="bi bi-exclamation-circle text-warning"></i><span>Outline missing</span>
                                    @endif
                                  </span>

                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    @if($hasVideo)
                                      <i class="bi bi-play-circle text-primary"></i><span>{{ $videoLabel }}</span>
                                    @else
                                      <i class="bi bi-dash-circle text-muted"></i><span>No video</span>
                                    @endif
                                  </span>

                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-paperclip text-secondary"></i>
                                    <span>{{ $fileCount }} {{ $plural($fileCount, 'file', 'files') }}</span>
                                  </span>

                                  <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-pencil-square text-secondary"></i>
                                    <span>{{ $assignmentCount }} {{ $plural($assignmentCount, 'assignment', 'assignments') }}</span>
                                  </span>

                                  <span id="topicBadge-{{ (int)$topic->id }}" class="badge rounded-pill {{ $topicBadgeClass }}">
                                    <i id="topicBadgeIcon-{{ (int)$topic->id }}" class="bi {{ $topicBadgeIcon }} me-1"></i>
                                    <span id="topicBadgeText-{{ (int)$topic->id }}">{{ $statusLabel }}</span>
                                  </span>

                                  @if($hasVideo)
                                    <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                      <i class="bi bi-bar-chart-line text-secondary"></i>
                                      <span id="videoPctSmall-{{ (int)$video->id }}">{{ $videoPct }}%</span>
                                    </span>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </div>

                          <button class="btn btn-sm btn-outline-secondary topic-toggle"
                                  type="button"
                                  data-bs-toggle="collapse"
                                  data-bs-target="#topic-{{ $topic->id }}"
                                  aria-expanded="false"
                                  aria-controls="topic-{{ $topic->id }}"
                                  aria-label="Toggle topic">
                            <i class="bi bi-chevron-down"></i>
                          </button>
                        </div>

                        {{-- DETAIL --}}
                        <div id="topic-{{ $topic->id }}" class="collapse mt-3">
                          <div class="topic-editor p-3 rounded-3">

                            {{-- Progress Actions --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                  <i class="bi bi-check2-square" style="color:var(--brand-primary)"></i>
                                  <span>Progress</span>
                                </div>

                                <span class="badge rounded-pill text-bg-light">
                                  <i class="bi bi-flag me-1"></i>
                                  <span id="topicStatusInline-{{ (int)$topic->id }}">{{ $statusLabel }}</span>
                                </span>
                              </div>

                              <div class="p-3 rounded-3 border bg-white">
                                <div class="small text-muted mb-2">Update your progress without reloading the page.</div>

                                @if($hasMarkRoute)
                                  <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-topic-action"
                                            data-topic-id="{{ (int)$topic->id }}"
                                            data-url="{{ route('student.topics.mark', $topic) }}"
                                            data-action="start">
                                      <i class="bi bi-play me-1"></i> Start
                                    </button>

                                    <button type="button" class="btn btn-sm btn-brand js-topic-action"
                                            data-topic-id="{{ (int)$topic->id }}"
                                            data-url="{{ route('student.topics.mark', $topic) }}"
                                            data-action="done">
                                      <i class="bi bi-check2-circle me-1"></i> Mark as completed
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-danger js-topic-action"
                                            data-topic-id="{{ (int)$topic->id }}"
                                            data-url="{{ route('student.topics.mark', $topic) }}"
                                            data-action="reset">
                                      <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                    </button>
                                  </div>

                                  <div class="small text-muted mt-2"><i class="bi bi-lightning me-1"></i> Changes are saved instantly.</div>
                                @else
                                  <div class="alert alert-warning small mb-0">
                                    Progress feature is not available yet.
                                  </div>
                                @endif
                              </div>
                            </div>

                            {{-- Outline --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-list-check" style="color:var(--brand-primary)"></i>
                                <span>Outline</span>
                              </div>
                              <div class="outline-view">
                                @if($hasOutline)
                                  <div class="outline-view-inner">{!! $renderOutline($subpoints) !!}</div>
                                @else
                                  <div class="small text-muted">No outline has been added for this topic yet.</div>
                                @endif
                              </div>
                            </div>

                            {{-- Video --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                  <i class="bi bi-play-btn" style="color:var(--brand-primary)"></i>
                                  <span>Video lesson</span>
                                </div>

                                @if($hasVideo)
                                  <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="bi bi-bar-chart-line"></i>
                                    <span>Watched: <b id="videoPctText-{{ $video?->id }}">{{ $videoPct }}%</b></span>
                                  </div>
                                @endif
                              </div>

                              <div class="p-3 rounded-3" style="background:rgba(91,62,142,.06);border:1px solid rgba(91,62,142,.12)">
                                @if($video && $videoHasSource)
                                  <a href="#"
                                     class="video-open text-decoration-none"
                                     data-bs-toggle="modal"
                                     data-bs-target="#videoPreviewModal"
                                     data-title="{{ e($video->title ?: $topic->title) }}"
                                     data-kind="{{ e($videoKind ?: 'iframe') }}"
                                     data-src="{{ e($videoModalSrc) }}"
                                     data-open="{{ e($videoOpenUrl ?: '') }}"
                                     data-trackable="{{ $videoTrackable ? '1' : '0' }}"
                                     data-material-id="{{ (int)($video->id ?? 0) }}"
                                     data-resume-seconds="{{ (int)($resumeSec ?? 0) }}"
                                     data-save-url="{{ e($videoSaveUrl) }}"
                                     data-get-url="{{ e($videoGetUrl) }}">
                                    <div class="d-flex gap-2 align-items-start">
                                      <i class="bi bi-play-circle mt-1" style="font-size:1.25rem;color:var(--brand-primary)"></i>
                                      <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark">{{ $video->title ?: 'Video lesson' }}</div>
                                        <div class="small text-muted">
                                          Open the player to continue watching.
                                          @if($videoTrackable)
                                            <span class="d-block mt-1">
                                              <i class="bi bi-shield-check me-1"></i>
                                              Your playback position will be saved and restored automatically.
                                            </span>
                                          @else
                                            <span class="d-block mt-1">
                                              <i class="bi bi-info-circle me-1"></i>
                                              Resume may not be available for external video sources.
                                            </span>
                                          @endif
                                        </div>
                                      </div>
                                      <div class="ms-auto">
                                        <span class="badge rounded-pill text-bg-light">
                                          <i class="bi bi-play-btn me-1"></i> Watch
                                        </span>
                                      </div>
                                    </div>
                                  </a>

                                  <div class="mt-2">
                                    <div class="progress" style="height:8px;">
                                      <div class="progress-bar" role="progressbar"
                                           id="videoBar-{{ $video?->id }}"
                                           style="width: {{ $videoPct }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                      <span>Video progress</span>
                                      <span id="videoPctRight-{{ $video?->id }}">{{ $videoPct }}%</span>
                                    </div>
                                  </div>
                                @else
                                  <div class="small text-muted">No video has been added to this topic yet.</div>
                                @endif
                              </div>
                            </div>

                            {{-- Files --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-paperclip" style="color:var(--brand-primary)"></i>
                                <span>Resources</span>
                              </div>

                              <div class="list-group list-group-flush">
                                @forelse($files as $material)
                                  @php $fileUrl = $fileUrlOf($material); @endphp
                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                      <i class="bi bi-file-earmark-text"></i>
                                      <div>
                                        <div class="fw-semibold">{{ $material->title }}</div>
                                        <div class="small text-muted">
                                          @if($fileUrl)
                                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener">Open resource</a>
                                          @else
                                            <span class="text-muted">Resource is not available yet</span>
                                          @endif
                                        </div>
                                      </div>
                                    </div>

                                    @if($fileUrl)
                                      <a class="btn btn-sm btn-outline-secondary" href="{{ $fileUrl }}" target="_blank" rel="noopener">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                                      </a>
                                    @else
                                      <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                                      </button>
                                    @endif
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">No resources.</div>
                                @endforelse
                              </div>
                            </div>

                            {{-- Links --}}
                            <div class="editor-block mb-3">
                              <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-link-45deg" style="color:var(--brand-primary)"></i>
                                <span>Links</span>
                              </div>

                              <div class="list-group list-group-flush">
                                @forelse($links as $material)
                                  @php $url = (string)($material->url ?? ''); @endphp
                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                      <i class="bi bi-globe2"></i>
                                      <div>
                                        <div class="fw-semibold">{{ $material->title ?: 'Link' }}</div>
                                        <div class="small text-muted">
                                          @if($url !== '')
                                            <a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a>
                                          @else
                                            <span class="text-muted">Link is not available yet</span>
                                          @endif
                                        </div>
                                      </div>
                                    </div>

                                    @if($url !== '')
                                      <a class="btn btn-sm btn-outline-secondary" href="{{ $url }}" target="_blank" rel="noopener">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                                      </a>
                                    @else
                                      <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                                      </button>
                                    @endif
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">No links.</div>
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
                                    $status = 'pending';
                                    $due = $as->due_at ? \Illuminate\Support\Carbon::parse($as->due_at)->format('d M Y, H:i') : null;
                                    $maxScore = (int)($as->max_score ?? 100);
                                  @endphp

                                  <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="me-3">
                                      <div class="fw-semibold">{{ $as->title }}</div>
                                      <div class="small text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $due ? "Due: {$due}" : 'No due date' }}
                                        <span class="ms-2">• Max score: {{ $maxScore }}</span>
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

                                      <a href="{{ route('student.assignments.show', $as) }}" class="btn btn-sm btn-brand">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Open
                                      </a>
                                    </div>
                                  </div>
                                @empty
                                  <div class="list-group-item text-muted small">No assignments for this topic.</div>
                                @endforelse
                              </div>
                            </div>

                          </div>
                        </div>

                      </div>
                    </div>
                  @empty
                    <div class="text-muted small">No topics in this module yet.</div>
                  @endforelse
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    @empty
      <div class="card">
        <div class="card-body p-5 text-center text-muted">No modules have been added to this course yet.</div>
      </div>
    @endforelse
  </div>

  {{-- ✅ Video Preview Modal --}}
  <div class="modal fade" id="videoPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" id="videoPreviewDialog">
      <div class="modal-content video-modal">
        <div class="modal-header video-modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="video-modal-badge"><i class="bi bi-play-circle"></i></span>
            <div class="lh-sm">
              <div class="video-modal-title" id="videoPreviewTitle">Video</div>
              <div class="video-modal-subtitle" id="videoPreviewSubtitle">Video player</div>
            </div>
          </div>

          <div class="ms-auto d-flex align-items-center gap-2">
            <a href="javascript:void(0)" target="_blank" class="btn btn-sm btn-modal" id="videoOpenNewTab" rel="noopener">
              <i class="bi bi-box-arrow-up-right me-1"></i> Open in new tab
            </a>

            <button type="button" class="btn btn-sm btn-modal" id="videoToggleFull" aria-label="Toggle fullscreen">
              <i class="bi bi-arrows-fullscreen me-1"></i> Fullscreen
            </button>

            <button type="button" class="btn btn-sm btn-modal" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i> Close
            </button>
          </div>
        </div>

        <div class="modal-body p-0">
          <div class="video-modal-body">
            <div class="p-3 bg-black">
              <video id="videoPreviewPlayer" class="d-none" controls playsinline preload="metadata"
                     style="width:100%;max-height:70vh;border-radius:.75rem;background:#000">
                <source id="videoPreviewSource" src="" type="video/mp4">
              </video>

              <div class="ratio ratio-16x9 bg-black d-none" id="videoIframeWrap">
                <iframe id="videoPreviewFrame" src=""
                        allow="autoplay; encrypted-media"
                        allowfullscreen
                        referrerpolicy="no-referrer"
                        style="border:0"></iframe>
              </div>
            </div>

            <div class="video-modal-hint" id="videoModalHint"></div>
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
    .outline-view-inner p{ margin:0 0 .6rem; }
    .outline-view-inner ul,
    .outline-view-inner ol{ margin:.25rem 0 .6rem 1.1rem; padding:0; }
    .outline-view-inner li{ margin:.15rem 0; }

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
      width: 38px; height: 38px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
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
    .video-modal-subtitle{ font-size: .8rem; color: rgba(0,0,0,.55); }
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
    .btn-modal:hover{ background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.18); }
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
    #videoPreviewDialog.is-fullscreen .modal-content{ height: 100vh; border-radius: 0; }
    #videoPreviewDialog.is-fullscreen .ratio{ height: calc(100vh - 118px); }

    /* toast theme */
    #appToast.toast-success { background: #198754; color: #fff; }
    #appToast.toast-danger  { background: #dc3545; color: #fff; }
    #appToast.toast-warning { background: #ffc107; color: #1f1f1f; }
    #appToast.toast-info    { background: #0d6efd; color: #fff; }
    #appToast .toast-body .bi { font-size: 1.05rem; }
  </style>

  {{-- JS --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const hasBootstrap = !!window.bootstrap;

      // =========
      // TOAST (prod-ready)
      // =========
      const toastEl    = document.getElementById('appToast');
      const toastIcon  = document.getElementById('toastIcon');
      const toastTitle = document.getElementById('toastTitle');
      const toastMsg   = document.getElementById('toastMsg');
      const toastCloseBtn = document.getElementById('toastCloseBtn');

      const toast = (hasBootstrap && toastEl && bootstrap.Toast)
        ? new bootstrap.Toast(toastEl, { delay: 3500 })
        : null;

      function showToast(type, title, message){
        if(!toastEl || !toastIcon || !toastTitle || !toastMsg) return;

        toastEl.classList.remove('toast-success','toast-danger','toast-warning','toast-info');
        toastIcon.className = 'bi mt-1';

        const t = (type || 'info').toLowerCase();

        if(t === 'success'){
          toastEl.classList.add('toast-success');
          toastIcon.classList.add('bi-check-circle-fill');
          toastTitle.textContent = title || 'Saved';
        } else if(t === 'danger' || t === 'error'){
          toastEl.classList.add('toast-danger');
          toastIcon.classList.add('bi-x-circle-fill');
          toastTitle.textContent = title || 'Error';
        } else if(t === 'warning'){
          toastEl.classList.add('toast-warning');
          toastIcon.classList.add('bi-exclamation-triangle-fill');
          toastTitle.textContent = title || 'Attention';
        } else {
          toastEl.classList.add('toast-info');
          toastIcon.classList.add('bi-info-circle-fill');
          toastTitle.textContent = title || 'Info';
        }

        toastMsg.textContent = message || '';

        if(toast){
          toast.show();
        }else{
          // fallback: alert
          alert((toastTitle.textContent + ': ' + toastMsg.textContent).trim());
        }
      }

      function csrfToken(){
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      }

      async function postJson(url, payload){
        return await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
          },
          body: JSON.stringify(payload || {})
        });
      }

      async function getJson(url){
        return await fetch(url, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });
      }

      // =====================
      // Module chevron toggle
      // =====================
      document.querySelectorAll('.module-toggle').forEach(btn => {
        const targetSel = btn.getAttribute('data-bs-target');
        const target = document.querySelector(targetSel);
        if(!target) return;

        const icon = btn.querySelector('i');
        target.addEventListener('show.bs.collapse', () => {
          icon?.classList.remove('bi-chevron-down');
          icon?.classList.add('bi-chevron-up');
        });
        target.addEventListener('hide.bs.collapse', () => {
          icon?.classList.remove('bi-chevron-up');
          icon?.classList.add('bi-chevron-down');
        });
      });

      // ====================
      // Topic chevron toggle
      // ====================
      document.querySelectorAll('.topic-toggle').forEach(btn => {
        const targetSel = btn.getAttribute('data-bs-target');
        const target = document.querySelector(targetSel);
        if(!target) return;

        const icon = btn.querySelector('i');
        target.addEventListener('show.bs.collapse', () => {
          icon?.classList.remove('bi-chevron-down');
          icon?.classList.add('bi-chevron-up');
        });
        target.addEventListener('hide.bs.collapse', () => {
          icon?.classList.remove('bi-chevron-up');
          icon?.classList.add('bi-chevron-down');
        });
      });

      // =========================
      // AJAX Topic Progress Action
      // =========================
      function setTopicBadge(topicId, status){
        const badge = document.getElementById(`topicBadge-${topicId}`);
        const icon  = document.getElementById(`topicBadgeIcon-${topicId}`);
        const text  = document.getElementById(`topicBadgeText-${topicId}`);
        const inline= document.getElementById(`topicStatusInline-${topicId}`);

        if(!badge || !icon || !text) return;

        badge.classList.remove('text-bg-success','text-bg-primary','text-bg-light');
        icon.classList.remove('bi-check2-circle','bi-play-circle','bi-circle');

        if(status === 'done'){
          badge.classList.add('text-bg-success');
          icon.classList.add('bi-check2-circle');
          text.textContent = 'Completed';
          if(inline) inline.textContent = 'Completed';
        } else if(status === 'in_progress'){
          badge.classList.add('text-bg-primary');
          icon.classList.add('bi-play-circle');
          text.textContent = 'In progress';
          if(inline) inline.textContent = 'In progress';
        } else {
          badge.classList.add('text-bg-light');
          icon.classList.add('bi-circle');
          text.textContent = 'Not started';
          if(inline) inline.textContent = 'Not started';
        }
      }

      document.querySelectorAll('.js-topic-action').forEach(btn => {
        btn.addEventListener('click', async () => {
          const topicId = btn.getAttribute('data-topic-id');
          const url     = btn.getAttribute('data-url');
          const action  = btn.getAttribute('data-action') || 'done';

          if(!topicId || !url){
            showToast('danger', 'Configuration error', 'Progress action is not configured properly.');
            return;
          }

          // optimistic UI
          if(action === 'done') setTopicBadge(topicId, 'done');
          else if(action === 'start') setTopicBadge(topicId, 'in_progress');
          else setTopicBadge(topicId, 'not_started');

          btn.disabled = true;

          try{
            const res = await postJson(url, { action });
            if(res.ok){
              if(action === 'done') showToast('success', 'Progress updated', 'Topic marked as completed.');
              else if(action === 'start') showToast('info', 'Progress updated', 'Topic marked as in progress.');
              else showToast('warning', 'Progress updated', 'Topic progress has been reset.');
            } else {
              showToast('danger', 'Could not save', 'Please refresh the page and try again.');
            }
          }catch(e){
            showToast('danger', 'Network error', 'Please check your connection and try again.');
          } finally {
            btn.disabled = false;
          }
        });
      });

      // ==========================
      // Video modal + tracking/resume
      // ==========================
      (function(){
        const modalEl = document.getElementById('videoPreviewModal');
        if(!modalEl || !hasBootstrap || !bootstrap.Modal) return;

        const titleEl = document.getElementById('videoPreviewTitle');
        const subEl   = document.getElementById('videoPreviewSubtitle');
        const hintEl  = document.getElementById('videoModalHint');

        const frameWrap = document.getElementById('videoIframeWrap');
        const frameEl   = document.getElementById('videoPreviewFrame');

        const player = document.getElementById('videoPreviewPlayer');
        const source = document.getElementById('videoPreviewSource');

        const openBtn = document.getElementById('videoOpenNewTab');
        const fullBtn = document.getElementById('videoToggleFull');
        const dialog  = document.getElementById('videoPreviewDialog');

        let trackable = false;
        let saveUrl = '';
        let getUrl = '';
        let materialId = 0;

        let resumeSeconds = 0;

        let lastSentSec = -1;
        let sentAt = 0;
        let autosaveTimer = null;

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

            // keep label clean
            fullBtn.querySelectorAll('span.__lbl').forEach(el => el.remove());
            const lbl = document.createElement('span');
            lbl.className = '__lbl';
            lbl.textContent = isOn ? ' Exit fullscreen' : ' Fullscreen';
            fullBtn.appendChild(lbl);
          }
        }

        function updateVideoUI(pct){
          if(!materialId) return;
          const bar   = document.getElementById(`videoBar-${materialId}`);
          const left  = document.getElementById(`videoPctText-${materialId}`);
          const right = document.getElementById(`videoPctRight-${materialId}`);
          const small = document.getElementById(`videoPctSmall-${materialId}`);

          if(bar) bar.style.width = `${pct}%`;
          if(left) left.textContent = `${pct}%`;
          if(right) right.textContent = `${pct}%`;
          if(small) small.textContent = `${pct}%`;
        }

        function calcPct(watched, duration){
          const d = Math.max(0, parseFloat(duration || 0) || 0);
          if(d <= 0) return 0;
          const w = Math.max(0, parseFloat(watched || 0) || 0);
          return Math.max(0, Math.min(100, Math.round((w / d) * 100)));
        }

        async function postProgressNow(reason){
          if(!trackable || !saveUrl || !player) return;

          const d = Math.floor(player.duration || 0);
          const t = Math.floor(player.currentTime || 0);

          const now = Date.now();
          const critical = (reason === 'close' || reason === 'pause' || reason === 'ended' || reason === 'hidden');

          if(!critical){
            if(now - sentAt < 2500) return;
            if(t === lastSentSec) return;
          }

          sentAt = now;
          lastSentSec = t;

          const pct = calcPct(t, d);
          updateVideoUI(pct);

          try{
            await postJson(saveUrl, {
              watched_seconds: t,
              duration_seconds: d,
              progress_pct: pct,
            });
          }catch(e){}
        }

        function stopAutoSave(){
          if(autosaveTimer){
            clearInterval(autosaveTimer);
            autosaveTimer = null;
          }
        }

        function startAutoSave(){
          stopAutoSave();
          autosaveTimer = setInterval(() => postProgressNow('tick'), 3000);
        }

        function showIframe(src){
          stopAutoSave();

          if(player){
            try{ player.pause(); }catch(e){}
            player.classList.add('d-none');
          }
          if(source) source.src = '';

          if(frameWrap) frameWrap.classList.remove('d-none');
          if(frameEl) frameEl.src = src || '';
        }

        function safeSeek(seconds){
          if(!player) return Promise.resolve(false);

          const target = Math.max(0, parseInt(seconds || 0, 10) || 0);
          if(target <= 0) return Promise.resolve(false);

          return new Promise((resolve) => {
            let tries = 0;

            const attempt = () => {
              tries++;

              try{
                const dur = Number(player.duration || 0);
                if(!dur || !isFinite(dur) || dur <= 0){
                  if(tries >= 18) return resolve(false);
                  return setTimeout(attempt, 150);
                }

                const seekTo = (target >= dur) ? Math.max(0, dur - 1) : target;

                try{
                  player.currentTime = seekTo;
                  return resolve(true);
                }catch(e){
                  if(tries >= 18) return resolve(false);
                  return setTimeout(attempt, 150);
                }
              }catch(e){
                if(tries >= 18) return resolve(false);
                return setTimeout(attempt, 150);
              }
            };

            attempt();
          });
        }

        function showHtml5(src){
          if(frameWrap) frameWrap.classList.add('d-none');
          if(frameEl) frameEl.src = '';

          if(source) source.src = src || '';

          if(!player) return;

          player.classList.remove('d-none');

          stopAutoSave();

          let started = false;

          const cleanup = () => {
            player.removeEventListener('loadeddata', onLoadedData);
            player.removeEventListener('canplay', onCanPlay);
            player.removeEventListener('error', onError);
          };

          const finalizeStart = async () => {
            if(started) return;
            started = true;

            if(resumeSeconds > 0){
              await safeSeek(resumeSeconds);
            }

            postProgressNow('meta');
            startAutoSave();

            try { await player.play(); } catch(e) {}
          };

          const onLoadedData = async () => { cleanup(); await finalizeStart(); };
          const onCanPlay = async () => { cleanup(); await finalizeStart(); };
          const onError = async () => {
            cleanup();
            stopAutoSave();
            showToast('danger', 'Playback error', 'Could not load the video. Please try again.');
          };

          player.addEventListener('loadeddata', onLoadedData);
          player.addEventListener('canplay', onCanPlay);
          player.addEventListener('error', onError);

          try{
            player.load(); // do not autoplay before seek
          }catch(e){}
        }

        // bind once
        if(player && player.dataset.bound !== '1'){
          player.dataset.bound = '1';

          player.addEventListener('pause', () => postProgressNow('pause'));
          player.addEventListener('ended', () => postProgressNow('ended'));

          document.addEventListener('visibilitychange', () => {
            if(document.visibilityState === 'hidden'){
              postProgressNow('hidden');
            }
          });
        }

        async function fetchResumeSeconds(){
          if(!trackable || !getUrl) return resumeSeconds;

          try{
            const res = await getJson(getUrl);
            if(!res.ok) return resumeSeconds;

            const data = await res.json().catch(() => null);
            if(!data) return resumeSeconds;

            const ws = (data.watched_seconds ?? data?.data?.watched_seconds ?? null);
            const val = parseInt(ws || 0, 10) || 0;
            if(val > 0) return val;

            return resumeSeconds;
          }catch(e){
            return resumeSeconds;
          }
        }

        modalEl.addEventListener('show.bs.modal', async (ev) => {
          const btn = ev.relatedTarget;
          if(!btn) return;

          const title = btn.getAttribute('data-title') || 'Video';
          const kind  = btn.getAttribute('data-kind') || 'iframe';
          const src   = btn.getAttribute('data-src') || '';
          const open  = btn.getAttribute('data-open') || '';

          trackable = (btn.getAttribute('data-trackable') === '1');
          materialId = parseInt(btn.getAttribute('data-material-id') || '0', 10) || 0;

          saveUrl = btn.getAttribute('data-save-url') || '';
          getUrl  = btn.getAttribute('data-get-url') || '';

          resumeSeconds = parseInt(btn.getAttribute('data-resume-seconds') || '0', 10) || 0;

          lastSentSec = -1;
          sentAt = 0;

          titleEl.textContent = title;
          if(subEl) subEl.textContent = trackable ? 'Video lesson (resume enabled)' : 'Video preview';

          if(openBtn){
            if(open){
              openBtn.classList.remove('disabled');
              openBtn.setAttribute('href', open);
            } else {
              openBtn.classList.add('disabled');
              openBtn.setAttribute('href', 'javascript:void(0)');
            }
          }

          if(hintEl){
            if(kind === 'drive'){
              hintEl.innerHTML = `<i class="bi bi-info-circle me-1"></i>
                If the video doesn't load, check the file permissions in Google Drive.`;
            } else if(trackable){
              hintEl.innerHTML = `<i class="bi bi-shield-check me-1"></i>
                Your viewing progress is saved automatically.`;
            } else {
              hintEl.innerHTML = `<i class="bi bi-info-circle me-1"></i>
                Resume may not be available for this video source.`;
            }
          }

          // ✅ ambil posisi terbaru dari DB (GET) sebelum play
          if(trackable && getUrl){
            resumeSeconds = await fetchResumeSeconds();
          }

          if(kind === 'html5') showHtml5(src);
          else showIframe(src);

          setFull(false);
        });

        modalEl.addEventListener('hidden.bs.modal', async () => {
          await postProgressNow('close');

          stopAutoSave();

          if(frameEl) frameEl.src = '';
          if(player){
            try{ player.pause(); }catch(e){}
            player.classList.add('d-none');
            try{ player.load(); }catch(e){}
          }
          if(source) source.src = '';
          if(frameWrap) frameWrap.classList.add('d-none');

          trackable = false;
          saveUrl = '';
          getUrl = '';
          materialId = 0;
          resumeSeconds = 0;

          setFull(false);
        });

        fullBtn?.addEventListener('click', (e) => {
          e.preventDefault();
          const isOn = dialog?.classList.contains('is-fullscreen');
          setFull(!isOn);
        });
      })();
    });
  </script>
</x-app-layout>