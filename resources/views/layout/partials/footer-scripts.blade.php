    <!-- jQuery -->
    <script src="{{URL::asset('build/js/jquery-3.7.1.min.js')}}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{URL::asset('build/js/bootstrap.bundle.min.js')}}"></script>

    <script src="{{URL::asset('build/js/moment.min.js')}}"></script>
	<script src="{{URL::asset('build/plugins/daterangepicker/daterangepicker.js')}}"></script>
    <!-- Select2 JS -->
    <script src="{{URL::asset('build/plugins/select2/js/select2.min.js')}}"></script>

    <!-- Datatable JS -->
    <script src="{{URL::asset('build/plugins/datatables/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/datatables/js/dataTables.bootstrap5.min.js')}}"></script>

    <!-- Mobile Input -->
    <script src="{{URL::asset('build/plugins/intltelinput/js/intlTelInput.js')}}"></script>
    <!-- Simplebar JS -->
	<script src="{{URL::asset('build/plugins/simplebar/simplebar.min.js')}}"></script>

    <!-- Sweetalert2 JS -->
    <script src="{{URL::asset('build/plugins/sweetalert2/sweetalert2.all.min.js')}}"></script>

    <!-- Main JS -->
    <script src="{{URL::asset('build/js/script.js')}}"></script>

    @if (Route::is(['category-list']))
        <script src="{{URL::asset('build/json/common/category-list.js')}}?v={{ time() }}"></script>
    @endif
    @if (Route::is(['merks-list']))
        <script src="{{URL::asset('build/json/common/merks-list.js')}}?v={{ time() }}"></script>
    @endif
    @if (Route::is(['type-unit-list']))
        <script src="{{URL::asset('build/json/common/unit-type.js')}}?v={{ time() }}"></script>
    @endif
    @if (Route::is(['status-list']))
        <script src="{{URL::asset('build/json/common/status.js')}}?v={{ time() }}"></script>
    @endif
    @if (Route::is(['document-list']))
        <script src="{{URL::asset('build/json/common/document.js')}}?v={{ time() }}"></script>
    @endif

    @stack('scripts')
