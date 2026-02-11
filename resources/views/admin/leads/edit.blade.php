<x-app-layout>
  <div class="row g-3">
    <div class="col-12">
      <div class="card">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
          <div>
            <h4 class="fw-bold mb-1" style="color:var(--brand-primary)">Edit Lead</h4>
            <div class="text-muted small">Update data calon student</div>
          </div>
          <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
          </a>
        </div>
      </div>
    </div>

    <div class="col-12">
      @if($errors->any())
        <div class="alert alert-danger py-2 small rounded-3">
          <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first() }}
        </div>
      @endif

      <div class="card">
        <div class="card-body p-4">
          <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
            @csrf @method('PUT')
            @include('admin.leads.partials.form', ['lead' => $lead])
            <div class="d-flex gap-2">
              <button class="btn btn-brand">
                <i class="bi bi-save2 me-1"></i> Save
              </button>
              <a class="btn btn-outline-secondary" href="{{ route('admin.leads.index') }}">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
