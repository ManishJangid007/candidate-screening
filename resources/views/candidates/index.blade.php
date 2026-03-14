@extends('layouts.app')

@section('content')
{{-- Header Section --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Candidate Screening Dashboard</h1>

    @if(Auth::user()->isAdmin())
    <div>
        <button type="button" onclick="document.getElementById('excel-file-input').click();" class="btn-primary">
            Upload Excel
        </button>
        <form id="excel-upload-form" action="{{ route('excel.upload') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" id="excel-file-input" name="file" accept=".xlsx,.xls,.csv" onchange="document.getElementById('excel-upload-form').submit();" />
        </form>
    </div>
    @endif
</div>

{{-- Stats Section --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="card flex items-center justify-center">
        <div class="card-content pt-4 pb-4 text-center">
            <p class="text-2xl font-bold" id="stat-total">{{ $stats['total'] }}</p>
            <p class="text-xs text-muted-foreground mt-1">Total Candidates</p>
        </div>
    </div>
    <div class="card">
        <div class="card-header pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Round Status</h3>
        </div>
        <div class="card-content">
            <div class="grid grid-cols-2 gap-3 text-center">
                <div>
                    <p class="text-2xl font-bold text-yellow-600" id="stat-pending">{{ $stats['pending'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Pending</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600" id="stat-not-cleared">{{ $stats['not_cleared'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Not Cleared</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Final Result</h3>
        </div>
        <div class="card-content">
            <div class="grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-2xl font-bold text-blue-600" id="stat-in-progress">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">In Progress</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600" id="stat-rejected">{{ $stats['rejected'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Rejected</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600" id="stat-final-selected">{{ $stats['final_selected'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Final Selected</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Section --}}
<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">Filters</h2>
    </div>
    <div class="card-content">
        <div id="filter-form">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label for="candidate_id" class="block text-sm font-medium text-muted-foreground mb-1">Candidate ID</label>
                    <input type="text" name="candidate_id" id="candidate_id" placeholder="Enter ID" class="form-input w-full filter-input" />
                </div>
                <div>
                    <label for="student_name" class="block text-sm font-medium text-muted-foreground mb-1">Search Name</label>
                    <input type="text" name="student_name" id="student_name" placeholder="Enter student name" class="form-input w-full filter-input" />
                </div>
                <div>
                    <label for="current_round" class="block text-sm font-medium text-muted-foreground mb-1">Round</label>
                    <select name="current_round" id="current_round" class="form-select w-full filter-select">
                        <option value="">All Rounds</option>
                        <option value="1">Round 1</option>
                        <option value="2">Round 2</option>
                        <option value="3">Round 3</option>
                        <option value="4">Round 4</option>
                    </select>
                </div>
                <div>
                    <label for="round_status" class="block text-sm font-medium text-muted-foreground mb-1">Status</label>
                    <select name="round_status" id="round_status" class="form-select w-full filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="not_cleared">Not Cleared</option>
                    </select>
                </div>
                @if(Auth::user()->isAdmin())
                <div>
                    <label for="interviewer" class="block text-sm font-medium text-muted-foreground mb-1">Interviewer</label>
                    <select name="interviewer" id="interviewer" class="form-select w-full filter-select">
                        <option value="">All Interviewers</option>
                        <option value="__unassigned__">Unassigned</option>
                        @foreach($interviewers as $interviewer)
                            <option value="{{ $interviewer }}">{{ $interviewer }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="final_result" class="block text-sm font-medium text-muted-foreground mb-1">Final Result</label>
                    <select name="final_result" id="final_result" class="form-select w-full filter-select">
                        <option value="">All Results</option>
                        <option value="in_progress">In Progress</option>
                        <option value="rejected">Rejected</option>
                        <option value="final_selected">Final Selected</option>
                    </select>
                </div>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-4">
                <button type="button" id="reset-filters" class="btn-outline">Reset Filters</button>
            </div>
        </div>
    </div>
</div>

{{-- Table Section --}}
<div class="card">
    <div class="card-header flex flex-row items-center justify-between">
        <h2 class="card-title">Candidates</h2>
        <span class="text-sm text-muted-foreground" id="table-info"></span>
    </div>
    <div class="card-content p-0">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Aptitude</th>
                        <th>Test</th>
                        <th>Video</th>
                        <th>Round</th>
                        <th>Status</th>
                        <th>Final Result</th>
                        <th>Interviewer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="candidates-tbody">
                    <tr>
                        <td colspan="10" class="text-center text-muted-foreground py-8">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-4 flex items-center justify-between" id="pagination-wrapper">
</div>

{{-- Config --}}
<script>
    const APP_CONFIG = {
        isAdmin: @json(Auth::user()->isAdmin()),
        indexUrl: @json(route('candidates.index')),
        csrfToken: @json(csrf_token()),
        interviewers: @json($interviewers),
    };
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentPage = 1;
    let debounceTimer = null;

    // Fetch candidates via AJAX
    function fetchCandidates(page) {
        currentPage = page || 1;
        const params = new URLSearchParams();

        document.querySelectorAll('.filter-input, .filter-select').forEach(function (el) {
            if (el.value) {
                params.set(el.name, el.value);
            }
        });

        params.set('page', currentPage);

        const tbody = document.getElementById('candidates-tbody');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted-foreground py-8">Loading...</td></tr>';

        fetch(APP_CONFIG.indexUrl + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            renderTable(data.data);
            renderStats(data.stats);
            renderPagination(data.pagination);
        })
        .catch(function (err) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-red-600 py-8">Error loading data.</td></tr>';
        });
    }

    // Render table rows
    function renderTable(candidates) {
        const tbody = document.getElementById('candidates-tbody');

        if (!candidates || candidates.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted-foreground py-8">No candidates found.</td></tr>';
            return;
        }

        let html = '';
        candidates.forEach(function (c) {
            html += '<tr>';
            html += '<td>' + escHtml(c.candidate_id) + '</td>';
            html += '<td><a href="' + c.show_url + '" class="font-medium text-primary hover:underline">' + escHtml(c.student_name) + '</a></td>';
            html += '<td>' + (c.aptitude_score ?? '-') + '</td>';
            html += '<td>' + (c.test_score ?? '-') + '</td>';
            html += '<td>' + (c.video_score ?? '-') + '</td>';
            html += '<td>Round ' + c.current_round + '</td>';
            html += '<td><span class="badge ' + c.round_status_badge + '">' + escHtml(c.round_status_label) + '</span></td>';
            html += '<td><span class="badge ' + c.final_result_badge + '">' + escHtml(c.final_result_label) + '</span></td>';

            // Interviewer column
            if (APP_CONFIG.isAdmin) {
                html += '<td>';
                html += '<select class="form-select btn-sm assign-interviewer" data-url="' + c.assign_url + '">';
                html += '<option value="">Assign</option>';
                APP_CONFIG.interviewers.forEach(function (name) {
                    var selected = (c.interviewer === name) ? ' selected' : '';
                    html += '<option value="' + escHtml(name) + '"' + selected + '>' + escHtml(name) + '</option>';
                });
                html += '</select>';
                html += '</td>';
            } else {
                html += '<td>' + escHtml(c.interviewer || '-') + '</td>';
            }

            html += '<td><a href="' + c.show_url + '" class="btn-outline btn-sm">View</a></td>';
            html += '</tr>';
        });

        tbody.innerHTML = html;

        // Bind assign interviewer events
        tbody.querySelectorAll('.assign-interviewer').forEach(function (select) {
            select.addEventListener('change', function () {
                assignInterviewer(this);
            });
        });
    }

    // Render stats
    function renderStats(stats) {
        document.getElementById('stat-total').textContent = stats.total;
        document.getElementById('stat-pending').textContent = stats.pending;
        document.getElementById('stat-not-cleared').textContent = stats.not_cleared;
        document.getElementById('stat-in-progress').textContent = stats.in_progress;
        document.getElementById('stat-rejected').textContent = stats.rejected;
        document.getElementById('stat-final-selected').textContent = stats.final_selected;
    }

    // Render pagination
    function renderPagination(pg) {
        const wrapper = document.getElementById('pagination-wrapper');
        const info = document.getElementById('table-info');

        if (pg.total === 0) {
            wrapper.innerHTML = '';
            info.textContent = '';
            return;
        }

        info.textContent = 'Showing ' + pg.from + '-' + pg.to + ' of ' + pg.total;

        if (pg.last_page <= 1) {
            wrapper.innerHTML = '';
            return;
        }

        let html = '<div class="flex items-center gap-1">';

        // Previous
        if (pg.current_page > 1) {
            html += '<button class="btn-outline btn-sm page-btn" data-page="' + (pg.current_page - 1) + '">Prev</button>';
        } else {
            html += '<button class="btn-outline btn-sm opacity-50 cursor-not-allowed" disabled>Prev</button>';
        }

        // Page numbers
        var pages = getPageNumbers(pg.current_page, pg.last_page);
        pages.forEach(function (p) {
            if (p === '...') {
                html += '<span class="px-2 text-muted-foreground">...</span>';
            } else if (p === pg.current_page) {
                html += '<button class="btn-primary btn-sm">' + p + '</button>';
            } else {
                html += '<button class="btn-outline btn-sm page-btn" data-page="' + p + '">' + p + '</button>';
            }
        });

        // Next
        if (pg.current_page < pg.last_page) {
            html += '<button class="btn-outline btn-sm page-btn" data-page="' + (pg.current_page + 1) + '">Next</button>';
        } else {
            html += '<button class="btn-outline btn-sm opacity-50 cursor-not-allowed" disabled>Next</button>';
        }

        html += '</div>';
        wrapper.innerHTML = html;

        // Bind page clicks
        wrapper.querySelectorAll('.page-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                fetchCandidates(parseInt(this.dataset.page));
            });
        });
    }

    // Get smart page number array
    function getPageNumbers(current, last) {
        if (last <= 7) {
            var arr = [];
            for (var i = 1; i <= last; i++) arr.push(i);
            return arr;
        }
        var pages = [1];
        if (current > 3) pages.push('...');
        for (var i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
            pages.push(i);
        }
        if (current < last - 2) pages.push('...');
        pages.push(last);
        return pages;
    }

    // Assign interviewer via AJAX
    function assignInterviewer(select) {
        var url = select.dataset.url;
        var value = select.value;
        if (!value) return;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': APP_CONFIG.csrfToken,
            },
            body: JSON.stringify({ interviewer: value }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                fetchCandidates(currentPage);
            }
        });
    }

    // Escape HTML
    function escHtml(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Filter: instant on select change
    document.querySelectorAll('.filter-select').forEach(function (select) {
        select.addEventListener('change', function () {
            fetchCandidates(1);
        });
    });

    // Filter: debounced on text input
    document.querySelectorAll('.filter-input').forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                fetchCandidates(1);
            }, 400);
        });
    });

    // Reset filters
    document.getElementById('reset-filters').addEventListener('click', function () {
        document.querySelectorAll('.filter-input').forEach(function (el) { el.value = ''; });
        document.querySelectorAll('.filter-select').forEach(function (el) { el.value = ''; });
        fetchCandidates(1);
    });

    // Initial load
    fetchCandidates(1);
});
</script>
@endsection
