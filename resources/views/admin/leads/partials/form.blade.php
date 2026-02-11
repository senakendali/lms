<div class="row g-3 mb-3">
  <div class="col-12 col-md-6">
    <label class="form-label">Name</label>
    <input class="form-control" name="name" value="{{ old('name', $lead->name) }}" required>
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label">Email (wajib kalau mau convert)</label>
    <input class="form-control" name="email" value="{{ old('email', $lead->email) }}" placeholder="email@example.com">
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label">Phone</label>
    <input class="form-control" name="phone" value="{{ old('phone', $lead->phone) }}" placeholder="08xxxx">
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label">Source</label>
    <input class="form-control" name="source" value="{{ old('source', $lead->source) }}" placeholder="Website / WA / IG / Referensi">
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label">Status</label>
    <select class="form-select" name="status">
      @foreach($statuses as $st)
        <option value="{{ $st }}" @selected(old('status', $lead->status ?: 'new') === $st)>{{ ucfirst($st) }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-12">
    <label class="form-label">Notes</label>
    <textarea class="form-control" rows="4" name="notes" placeholder="Catatan singkat...">{{ old('notes', $lead->notes) }}</textarea>
  </div>
</div>
