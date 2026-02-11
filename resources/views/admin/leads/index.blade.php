<x-app-layout>
  <div class="container-fluid p-0">
    <div class="row g-3">

      {{-- Header --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="width:40px;height:40px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
                <i class="bi bi-person-lines-fill"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Potential Students</h4>
                <div class="text-muted small">List calon student + convert jadi student</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.leads.create') }}" class="btn btn-brand btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Lead
              </a>
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-people me-1"></i> View Users
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- FLASH (ONLY IF EXISTS) --}}
      @if(session('status') || $errors->any())
        <div class="col-12">
          @if(session('status'))
            <div class="alert alert-success py-2 small rounded-3 mb-0">
              <i class="bi bi-check-circle me-1"></i> {!! nl2br(e(session('status'))) !!}
            </div>
          @endif

          @if($errors->any())
            <div class="alert alert-danger py-2 small rounded-3 mb-0">
              <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first() }}
            </div>
          @endif
        </div>
      @endif

      {{-- Filters --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-3">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.leads.index') }}">
              <div class="col-12 col-md-5">
                <label class="form-label small mb-1">Search</label>
                <input class="form-control"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nama / email / phone">
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label small mb-1">Status</label>
                <select class="form-select" name="status">
                  <option value="">All</option>
                  @foreach($statuses as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>
                      {{ ucfirst($st) }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-outline-secondary w-100" type="submit">
                  <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a class="btn btn-outline-secondary w-100" href="{{ route('admin.leads.index') }}">
                  Reset
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3" style="width:26%">Name</th>
                    <th>Email</th>
                    <th style="width:14%">Phone</th>
                    <th style="width:12%">Source</th>
                    <th style="width:12%">Status</th>
                    <th class="text-end pe-3" style="width:24%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                @forelse($leads as $lead)
                  @php
                    $badge = match($lead->status) {
                      'new' => 'text-bg-light',
                      'contacted' => 'text-bg-info',
                      'interested' => 'text-bg-warning',
                      'converted' => 'text-bg-success',
                      'rejected' => 'text-bg-danger',
                      default => 'text-bg-light',
                    };
                  @endphp

                  <tr>
                    <td class="ps-3">
                      <div class="fw-semibold">{{ $lead->name }}</div>
                      @if($lead->notes)
                        <div class="text-muted small">
                          {{ \Illuminate\Support\Str::limit($lead->notes, 60) }}
                        </div>
                      @endif
                    </td>

                    <td class="small">{{ $lead->email ?: '—' }}</td>
                    <td class="small">{{ $lead->phone ?: '—' }}</td>
                    <td class="small">{{ $lead->source ?: '—' }}</td>

                    <td>
                      <span class="badge rounded-pill {{ $badge }}">{{ $lead->status }}</span>
                    </td>

                    <td class="text-end pe-3">
                      <div class="d-inline-flex gap-2">
                        <a class="btn btn-sm btn-outline-secondary"
                           href="{{ route('admin.leads.edit', $lead) }}">
                          <i class="bi bi-pencil-square"></i>
                        </a>

                        <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
                              onsubmit="return confirm('Hapus lead ini?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>

                        <form method="POST" action="{{ route('admin.leads.convert', $lead) }}"
                              onsubmit="return confirm('Convert lead ini jadi student?')">
                          @csrf
                          <button class="btn btn-sm btn-brand" type="submit"
                                  @disabled($lead->status === 'converted')>
                            <i class="bi bi-person-check me-1"></i> Convert
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted p-4">
                      Belum ada lead. Klik <b>Add Lead</b> untuk mulai.
                    </td>
                  </tr>
                @endforelse
                </tbody>
              </table>
            </div>

            <div class="p-3">
              {{ $leads->links() }}
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
