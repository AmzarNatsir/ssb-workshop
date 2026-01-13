<div class="equipment-info mb-4">
    <div class="row">
        <div class="col-6">
            <p class="mb-1 text-muted">Equipment Code</p>
            <h6 class="fw-bold">{{ $equipment->code }}</h6>
        </div>
        <div class="col-6">
            <p class="mb-1 text-muted">Name</p>
            <h6 class="fw-bold">{{ $equipment->name }}</h6>
        </div>
    </div>
</div>

<div class="card shadow-none border mb-4">
    <div class="card-body">
        <h6 class="mb-3">Upload New Documents</h6>
        <div class="mb-3">
            <label class="form-label">Document Type <span class="text-danger">*</span></label>
            <select class="form-select" id="dropzone-document-type">
                @foreach($documentTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Select the type of document before uploading.</small>
        </div>
        
        <form action="{{ route('equipment.documents.upload') }}" class="dropzone" id="equipment-dropzone">
            @csrf
            <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">
            <input type="hidden" name="document_type_id" id="hidden-document-type-id">
            <div class="dz-message needsclick">
                <div class="mb-3">
                    <i class="ti ti-upload fs-1 text-muted"></i>
                </div>
                <h5>Drop files here or click to upload.</h5>
                <span class="text-muted">Maximum file size: 10MB</span>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Existing Documents</h6>
        <span class="badge bg-primary">{{ count($equipment->documents) }} Files</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="docs-list-table">
                <thead class="bg-light">
                    <tr>
                        <th>Type</th>
                        <th>File Name</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment->documents as $doc)
                        @php
                            $extension = pathinfo($doc->document_path, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-soft-primary">{{ $doc->documentType->name }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-file-text me-2 fs-4 text-primary"></i>
                                    @if($isImage)
                                        <a href="{{ Storage::url($doc->document_path) }}" data-fancybox="equipment-docs" data-caption="{{ $doc->documentType->name }} - {{ basename($doc->document_path) }}" class="text-truncate" style="max-width: 200px;">
                                            {{ basename($doc->document_path) }}
                                        </a>
                                    @else
                                        <a href="{{ Storage::url($doc->document_path) }}" target="_blank" class="text-truncate" style="max-width: 200px;">
                                            {{ basename($doc->document_path) }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $doc->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($isImage)
                                        <a href="{{ Storage::url($doc->document_path) }}" data-fancybox="equipment-docs" data-caption="{{ $doc->documentType->name }} - {{ basename($doc->document_path) }}" class="btn btn-sm btn-icon btn-light">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ Storage::url($doc->document_path) }}" target="_blank" class="btn btn-sm btn-icon btn-light">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @endif
                                    <button class="btn btn-sm btn-icon btn-danger delete-doc-btn" data-id="{{ $doc->id }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No documents uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Sync document type ID
    $('#hidden-document-type-id').val($('#dropzone-document-type').val());
    $('#dropzone-document-type').on('change', function() {
        $('#hidden-document-type-id').val($(this).val());
    });

    // Initialize Dropzone
    if (typeof Dropzone !== 'undefined') {
        // Prevent Dropzone from auto discovering element
        Dropzone.autoDiscover = false;
        
        var myDropzone = new Dropzone("#equipment-dropzone", {
            url: "{{ route('equipment.documents.upload') }}",
            paramName: "file",
            maxFilesize: 10, // MB
            acceptedFiles: ".pdf,.jpg,.jpeg,.png",
            addRemoveLinks: true,
            sending: function(file, xhr, formData) {
                // Ensure the latest document type is sent
                formData.append('document_type_id', $('#dropzone-document-type').val());
            },
            success: function(file, response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Uploaded!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Reload the documents list
                    loadDocuments({{ $equipment->id }});
                }
            },
            error: function(file, response) {
                let message = 'An error occurred during upload.';
                if (typeof response === 'object' && response.message) {
                    message = response.message;
                } else if (typeof response === 'string') {
                    message = response;
                }
                Swal.fire({ icon: 'error', title: 'Error!', text: message });
            }
        });
    }

    // Delete document
    $('.delete-doc-btn').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete document?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("equipment/documents") }}/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadDocuments({{ $equipment->id }});
                        }
                    }
                });
            }
        });
    });
});
</script>
