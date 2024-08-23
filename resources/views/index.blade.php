@extends('layouts.app')

@section('title')

@section('css')
    <style>
        /* Dark theme styles */
        .card-header {
            background-color: #18181a !important;
            color: #ffffff !important;
            border-radius: 0 !important;
            padding: 20px !important;
        }

        .card {
            border-radius: 0 !important;
        }

        .truncate {
            max-width: 500px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            position: relative;
            /* Ensure position context for button */
            padding-right: 30px;
            /* Add padding to the right for button */
        }

        .copy-btn {
            cursor: pointer;
            color: #838383;
            border: none;
            background: transparent;
            /* Make background transparent */
            font-size: 16px;
            /* Adjust size if necessary */
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            padding: 0 5px;
            /* Space around the button */
            margin-left: 5px;
            /* Space between description and button */
        }

        .copy-btn:hover {
            text-decoration: underline;
        }

        /* Custom scrollbar for textarea */
        textarea::-webkit-scrollbar {
            width: 12px !important;
        }

        textarea::-webkit-scrollbar-thumb {
            background-color: #adb5bd !important;
            border-radius: 6px !important;
        }

        textarea::-webkit-scrollbar-thumb:hover {
            background-color: #6c757d !important;
        }

        /* Styling for pagination */
        .pagination {
            justify-content: center !important;
        }

        /* Log Level Icons */
        .log-level {
            display: inline-flex;
            align-items: center;
        }

        .log-level .icon {
            margin-right: 5px;
        }

        .text-error {
            color: #dc3545 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .bg-info {
            background-color: #d1ecf1 !important;
            color: #0c5460 !important;
        }
    </style>
@endsection

@section('contents')
    <div class="row">
        <!-- Sidebar for Viewing Laravel Log File -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Log File Viewer</h5>
                </div>
                <div class="card-body">
                    <!-- File Selector -->
                    <form id="fileSelectorForm" class="mb-3">
                        <select id="fileSelector" name="file" class="form-select form-select-sm">
                            @foreach ($logFiles as $file)
                                <option value="{{ $file }}" {{ $file === $selectedFile ? 'selected' : '' }}>
                                    {{ basename($file) }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <div class="d-flex justify-content-end">
                        <a id="copyToClipboardButton" class="text text-muted text-decoration-none" style="cursor: pointer;">
                            <i class="fas fa-copy"></i> Copy
                        </a>
                    </div>

                    <!-- Display Log File Contents -->
                    <div class="border p-2 rounded mt-2" style="min-height: 200px !important;">
                        <textarea id="logFileContents" class="form-control" rows="20" readonly>{{ $logFileContents }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content for Log Entries -->
        <div class="col-lg-9 col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Log Entries</h5>
                    <form id="filterForm" class="d-flex" method="GET" action="{{ route('logger.index') }}">
                        @csrf
                        <select id="filterSelector" name="filter" class="form-select form-select-sm me-2">
                            <option value="">All</option>
                            <option value="debug" {{ request('filter') === 'debug' ? 'selected' : '' }}>Debug</option>
                            <option value="info" {{ request('filter') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ request('filter') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ request('filter') === 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm btn-modern" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Filter Logs">
                            <i class="fa fa-magnifying-glass"></i>
                        </button>
                        <button id="reloadButton" type="button" class="btn btn-secondary btn-sm btn-modern ms-2"
                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Refresh Logs">
                            <i class="fa fa-undo"></i>
                        </button>
                        <button id="clearLogsButton" type="button" class="btn btn-warning btn-sm btn-modern ms-2"
                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Clear Logs">
                            <i class="fa fa-broom"></i>
                        </button>
                        <button id="downloadLogsButton" type="button" class="btn btn-success btn-sm btn-modern ms-2"
                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Download Log">
                            <i class="fa fa-download"></i>
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="logsTable" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th class="table-header-custom text-muted">Level</th>
                                    <th class="table-header-custom text-muted">Time</th>
                                    <th class="table-header-custom text-muted">Env</th>
                                    <th class="table-header-custom text-muted">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTable will populate this -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul id="pagination" class="pagination">
                            <!-- Pagination will populate this -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#fileSelector').select2({
                theme: 'bootstrap-5'
            });

            // Handle log file selection change
            $('#fileSelector').on('change', function() {
                const selectedFile = $(this).val();

                // Update log file contents
                fetchContents(selectedFile);

                // Optionally, if you need to reload the DataTable
                table.ajax.reload(); // Reload DataTable, if needed
            });

            let table = $('#logsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [
                    [1, 'desc']
                ], // 'asc' for ascending order, 'desc' for descending order
                ajax: {
                    url: '{{ route('logs.data') }}',
                    data: function(d) {
                        d.filter = $('#filterSelector').val();
                        d.file = $('#fileSelector').val();
                    }
                },
                columns: [{
                        data: 'level',
                        name: 'level',
                        render: function(data, type, row) {
                            return data;
                        }
                    },
                    {
                        data: 'time',
                        name: 'time',
                        render: function(data, type, row) {
                            return data;
                            // return new Date(data).toLocaleString(); // Format date as needed
                        }
                    },
                    {
                        data: 'env',
                        name: 'env',
                        render: function(data, type, row) {
                            return data; // Display the environment
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data, type, row) {
                            return data;
                        }
                    }
                ],
                initComplete: function() {
                    // Initialize tooltips after DataTable is loaded
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // Handle file selection change
            // $('#fileSelector').on('change', function() {
            //     $('#fileSelectorForm').submit();
            // });

            // Handle filter change
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Handle reload button click
            $('#reloadButton').on('click', function() {
                $('#filterSelector').val('').trigger('change');
                fetchContents($('#fileSelector').val());
                table.ajax.reload();
            });

            // Handle clear logs button click using SweetAlert
            $('#clearLogsButton').on('click', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action will clear all the log files!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('logs.clear') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                file: $('#fileSelector').val()
                            },
                            success: function() {
                                toastr.success('Logs cleared successfully!');
                                table.ajax.reload(); // Reload DataTable

                                // Reload the log file contents
                                $.ajax({
                                    url: '{{ route('logs.fetch') }}', // API route to fetch log file contents
                                    method: 'GET',
                                    data: {
                                        file: $('#fileSelector').val()
                                    },
                                    success: function(response) {
                                        $('#logFileContents').val(response
                                            .logFileContents
                                        ); // Update log file contents
                                    },
                                    error: function() {
                                        toastr.error(
                                            'Failed to load log file contents.'
                                        );
                                    }
                                });
                            },
                            error: function() {
                                toastr.error('Failed to clear logs.');
                            }
                        });
                    }
                });
            });

            // Handle download logs button click
            $('#downloadLogsButton').on('click', function() {
                // window.location.href = '{{ route('logs.download', ['file' => $selectedFile]) }}';
                const selectedFile = encodeURIComponent($('#fileSelector').val());
                const downloadUrl = `/logs/download/${selectedFile}`;
                window.location.href = downloadUrl;
            });

            // Handle copy button clicks using a class
            $(document).on('click', '.copy-btn', function() {
                const textToCopy = $(this).data('copy-text');
                CopyHandler.copyToClipboard(textToCopy);
            });

            // Handle copy button click in side panel
            $('#copyToClipboardButton').on('click', function() {
                // Select the textarea
                let $textarea = $('#logFileContents');

                // Select the content
                $textarea.select();
                $textarea[0].setSelectionRange(0, 99999); // For mobile devices

                try {
                    // Copy the text
                    let successful = document.execCommand('copy');
                    if (successful) {
                        toastr.success('Copied to clipboard!', '', {
                            timeOut: 1500, // Display for 1.5 seconds
                            progressBar: true, // Show a progress bar
                            positionClass: 'toast-top-right', // Position at the top right
                            closeButton: true, // Show a close button
                            extendedTimeOut: 1000 // Extend the timeOut if the mouse is hovered over the toast
                        });
                    } else {
                        // Error callback
                        toastr.error('Failed to copy to clipboard.', '', {
                            timeOut: 1500, // Display for 1.5 seconds
                            progressBar: true, // Show a progress bar
                            positionClass: 'toast-top-right', // Position at the top right
                            closeButton: true, // Show a close button
                            extendedTimeOut: 1000 // Extend the timeOut if the mouse is hovered over the toast
                        });
                    }
                } catch (err) {
                    // Error callback
                    toastr.error('Failed to copy to clipboard.', '', {
                        timeOut: 1500, // Display for 1.5 seconds
                        progressBar: true, // Show a progress bar
                        positionClass: 'toast-top-right', // Position at the top right
                        closeButton: true, // Show a close button
                        extendedTimeOut: 1000 // Extend the timeOut if the mouse is hovered over the toast
                    });
                }
            });
        });

        function getLevelHtml(level) {
            switch (level.toLowerCase()) {
                case 'error':
                    return '<span class="log-level text-error"><i class="bi bi-exclamation-circle icon"></i> Error</span>';
                case 'info':
                    return '<span class="log-level text-info"><i class="bi bi-info-circle icon"></i> Info</span>';
                case 'warning':
                    return '<span class="log-level text-warning"><i class="bi bi-exclamation-triangle icon"></i> Warning</span>';
                case 'debug':
                    return '<span class="log-level text-info"><i class="bi bi-info-circle icon"></i> Debug</span>';
                default:
                    return level;
            }
        }

        function fetchContents(file) {
            $.ajax({
                url: '{{ route('logs.fetch') }}', // API route to fetch log file contents
                method: 'GET',
                data: {
                    file: file
                },
                success: function(response) {
                    $('#logFileContents').val(response
                        .logFileContents); // Update log file contents
                },
                error: function() {
                    toastr.error('Failed to load log file contents.');
                }
            });
        }

        // Define a class to handle copy operations
        class CopyHandler {
            static copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    // Success callback
                    toastr.success('Copied to clipboard!', '', {
                        timeOut: 1500, // Display for 1.5 seconds
                        progressBar: true, // Show a progress bar
                        positionClass: 'toast-top-right', // Position at the top right
                        closeButton: true, // Show a close button
                        extendedTimeOut: 1000 // Extend the timeOut if the mouse is hovered over the toast
                    });
                }, function() {
                    // Error callback
                    toastr.error('Failed to copy to clipboard.', '', {
                        timeOut: 1500, // Display for 1.5 seconds
                        progressBar: true, // Show a progress bar
                        positionClass: 'toast-top-right', // Position at the top right
                        closeButton: true, // Show a close button
                        extendedTimeOut: 1000 // Extend the timeOut if the mouse is hovered over the toast
                    });
                });
            }
        }
    </script>
@endsection
