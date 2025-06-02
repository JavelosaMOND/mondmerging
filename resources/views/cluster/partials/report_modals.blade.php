<!-- View Modal -->
<div class="modal fade" id="viewReportModal{{ $report->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2" style="color: var(--primary);"></i>View Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><strong>Report Type</strong><br>{{ $report->reportType->name }}</div>
                <div class="mb-2"><strong>Submitted By</strong><br>{{ $report->user->name }}</div>
                <div class="mb-2"><strong>Submitted At</strong><br>{{ $report->created_at->format('M d, Y h:i A') }}
                    @php
                        $deadline = $report->reportType->deadline ? \Carbon\Carbon::parse($report->reportType->deadline) : null;
                        $isLate = $deadline && $report->created_at->gt($deadline);
                        $fileExt = strtolower(pathinfo($report->file_name, PATHINFO_EXTENSION));
                    @endphp
                    @if($deadline)
                        <span class="badge ms-2" style="background: {{ $isLate ? 'var(--danger-light)' : 'var(--success-light)' }}; color: {{ $isLate ? 'var(--danger)' : 'var(--success)' }};">
                            {{ $isLate ? 'Late' : 'On Time' }}
                        </span>
                    @endif
                </div>
                <div class="mb-2"><strong>Deadline</strong><br>{{ $deadline ? $deadline->format('M d, Y h:i A') : 'N/A' }}</div>
                <div class="mb-2"><strong>Status</strong><br>
                    <span class="status-badge {{ $report->status }}">{{ ucfirst($report->status) }}</span>
                </div>
                <div class="mb-2"><strong>File</strong><br>
                    <button type="button" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#filePreviewModal{{ $report->id }}">
                        <i class="fas fa-eye"></i> View File
                    </button>
                    <a href="{{ route('cluster.files.download', $report->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-download"></i> Download File
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal{{ $report->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2" style="color: var(--primary);"></i>{{ $report->file_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="min-height: 70vh;">
                @if($fileExt === 'pdf')
                    <iframe src="{{ asset('storage/' . $report->file_path) }}" width="100%" height="600px" style="border: none;"></iframe>
                @elseif(in_array($fileExt, ['docx','xlsx']))
                    <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode(asset('storage/' . $report->file_path)) }}" width="100%" height="600px" frameborder="0"></iframe>
                @else
                    <div class="alert alert-info">Preview not available for this file type. Please download to view.</div>
                @endif
            </div>
            <div class="modal-footer">
                <a href="{{ route('cluster.files.download', $report->id) }}" class="btn btn-info">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Update Modal -->
<div class="modal fade" id="updateReportModal{{ $report->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2" style="color: var(--primary);"></i>Update Report Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm{{ $report->id }}" action="{{ auth()->user()->role === 'Facilitator' ? route('facilitator.reports.update', $report->id) : route('cluster.reports.update', $report->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select status-select" name="status" required>
                            <option value="approved" {{ $report->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="resubmit" {{ $report->status == 'resubmit' ? 'selected' : '' }}>Resubmit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks">{{ $report->remarks }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var statusSelect = document.querySelector('#updateReportModal{{ $report->id }} .status-select');
    var freqFields = document.getElementById('frequencyFields{{ $report->id }}');
    if (!statusSelect || !freqFields) return;
    function toggleFreqFields() {
        if (statusSelect.value === 'resubmit') {
            freqFields.style.display = '';
        } else {
            freqFields.style.display = 'none';
        }
    }
    statusSelect.addEventListener('change', toggleFreqFields);
    toggleFreqFields();
});
</script> 