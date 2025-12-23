@if($permissions->count() > 0)
    @foreach($permissions as $group => $groupPermissions)
        <div class="tree-node mb-2" data-group="{{ $group }}">
            <!-- Group Header -->
            <div class="tree-group-header d-flex align-items-center justify-content-between p-2 rounded cursor-pointer bg-white border" 
                 data-bs-toggle="collapse" 
                 data-bs-target="#group{{ $loop->index }}" 
                 aria-expanded="true">
                <div class="d-flex align-items-center">
                    <i class="bi bi-chevron-down me-2 text-primary tree-icon" style="font-size: 0.875rem;"></i>
                    <i class="bi bi-folder-fill me-2 text-primary"></i>
                    <span class="fw-semibold small">{{ $groupPermissions->first()->group_label }}</span>
                    <span class="badge bg-primary ms-2 small">{{ $groupPermissions->count() }}</span>
                </div>
            </div>
            <!-- Group Content -->
            <div class="collapse show ms-3 mt-1" id="group{{ $loop->index }}">
                <div class="tree-children">
                    @foreach($groupPermissions as $permission)
                        <div class="tree-item d-flex align-items-center p-2 mb-1 rounded bg-white border">
                            <input class="form-check-input me-2 permission-checkbox" 
                                   type="checkbox" 
                                   name="permissions[]" 
                                   value="{{ $permission->id }}" 
                                   id="permission_{{ $permission->id }}"
                                   data-group="{{ $group }}"
                                   style="width: 18px; height: 18px; cursor: pointer; margin-top: 0;">
                            <label class="form-check-label flex-grow-1 cursor-pointer small" for="permission_{{ $permission->id }}" style="margin-bottom: 0;">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold mb-0">{{ $permission->label }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $permission->description }}</div>
                                        <code class="text-muted" style="font-size: 0.7rem;">{{ $permission->name }}</code>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-warning text-center mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        لم يتم العثور على صلاحيات تطابق البحث "{{ $search }}"
    </div>
@endif
