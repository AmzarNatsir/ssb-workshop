    <!-- Search Modal -->
    <div class="modal fade" id="searchModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-transparent">
                <div class="card shadow-none mb-0">
                    <div class="px-3 py-2 d-flex flex-row align-items-center" id="search-top">
                        <i class="ti ti-search fs-22"></i>
                        <input type="search" class="form-control border-0" placeholder="Search">
                        <button type="button" class="btn p-0" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x fs-22"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidenav Menu Start -->
    <div class="sidebar" id="sidebar">

        <!-- Start Logo -->
        <div class="sidebar-logo">
            <div>
                <!-- Logo Normal -->
                <a href="{{url('home')}}" class="logo logo-normal">
                    <img src="{{URL::asset('build/img/workshop_icon.png')}}" alt="Logo" class="me-2" height="30"><b>WORKSHOP</b>
                </a>

                <!-- Logo Small -->
                <a href="{{url('home')}}" class="logo-small">
                    <img src="{{URL::asset('build/img/workshop_icon.png')}}" alt="Logo" class="me-2" height="20">
                </a>

                <!-- Logo Dark -->
                <a href="{{url('home')}}" class="dark-logo">
                    <img src="{{URL::asset('build/img/logo-white.svg')}}" alt="Logo">
                </a>
            </div>
            <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn">
                <i class="ti ti-arrow-bar-to-left"></i>
            </button>

            <!-- Sidebar Menu Close -->
            <button class="sidebar-close">
                <i class="ti ti-x align-middle"></i>
            </button>
        </div>
        <!-- End Logo -->

        <!-- Sidenav Menu -->
        <div class="sidebar-inner" data-simplebar>
            <div id="sidebar-menu" class="sidebar-menu">
                <ul>
                    <li class="menu-title"><span>Main Menu</span></li>
                    <li>
                        <ul>
                            <li class=" {{ Request::is('home') ? 'active' : '' }}">
                                <a href="{{url('home')}}" ><i class="ti ti-home"></i><span>Home</span></a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <ul>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="{{ Request::is('index', '/','leads-dashboard','project-dashboard') ? 'active subdrop' : '' }}">
                                    <i class="ti ti-dashboard"></i><span>Dashboard</span><span class="menu-arrow"></span>
                                </a>
                                <ul>
                                    <li><a href="{{url('dashboard')}}" class="{{ Request::is('dashboard', '/') ? 'active' : '' }}">Operational Unit</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <ul>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="{{ Request::is('category*', 'brand*', 'unit-type*', 'documents*', 'meter-reading*', 'ownership-mode*', 'common/*', 'tool-type*', 'periodic-service-type*', 'master-activities*') ? 'active subdrop' : '' }}">
                                    <i class="ti ti-list-details"></i><span>Common</span><span class="menu-arrow"></span>
                                </a>
                                <ul>
                                    <li><a href="{{route('category.index')}}" class="{{ Request::is('category*') ? 'active' : '' }}">Category</a></li>
                                    <li><a href="{{route('merk.index')}}" class="{{ Request::is('merk*') ? 'active' : '' }}">Merk</a></li>
                                    <li><a href="{{route('status.index')}}" class="{{ Request::is('status*') ? 'active' : '' }}">Status</a></li>
                                    <li><a href="{{route('unit-type.index')}}" class="{{ Request::is('unit-type*') ? 'active' : '' }}">Unit Type</a></li>
                                    <li><a href="{{route('documents.index')}}" class="{{ Request::is('documents*') ? 'active' : '' }}">Documents</a></li>
                                    <li><a href="{{route('meter-reading.index')}}" class="{{ Request::is('meter-reading*') ? 'active' : '' }}">Meter Reading</a></li>
                                    <li><a href="{{route('ownership-mode.index')}}" class="{{ Request::is('ownership-mode*') ? 'active' : '' }}">Ownership Mode</a></li>
                                    <li><a href="{{route('racks.index')}}" class="{{ Request::is('racks*') ? 'active' : '' }}">Racks</a></li>
                                    <li><a href="{{route('tool-type.index')}}" class="{{ Request::is('tool-type*') ? 'active' : '' }}">Tool Type</a></li>
                                    <li><a href="{{route('periodic-service-type.index')}}" class="{{ Request::is('periodic-service-type*') ? 'active' : '' }}">Periodic Service Type</a></li>
                                    <li><a href="{{route('master-activities.index')}}" class="{{ Request::is('master-activities*') ? 'active' : '' }}">Master Mechanical Activ.</a></li>
                                    <li><a href="{{route('master-component-troubles.index')}}" class="{{ Request::is('master-component-troubles*') ? 'active' : '' }}">Master Component Trouble</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <ul>
                            <li class=" {{ Request::is('supplier*') ? 'active' : '' }}">
                                <a href="{{route('supplier.index')}}" ><i class="ti ti-truck-delivery"></i><span>Supplier</span></a>
                            </li>
                            <li class=" {{ Request::is('equipment', 'equipment-list', 'equipment-details', 'equipment-document') ? 'active' : '' }}">
                                <a href="{{url('equipment')}}" ><i class="ti ti-atom-2"></i><span>Equipments</span></a>
                            </li>
                            <li class="{{ Request::is('assets', 'assets-details', 'assets-list') ? 'active' : '' }}">
                                <a href="{{url('assets')}}"><i class="ti ti-atom-2"></i><span>Assets</span></a>
                            </li>
                            <li class=" {{ Request::is('tools*') ? 'active' : '' }}">
                                <a href="{{route('tools.index')}}" ><i class="ti ti-atom-2"></i><span>Tools</span></a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-title"><span>SETTING</span></li>
                    <li>
                        <ul>
                            <li class=" {{ Request::is('approval-setting', 'approval-setting-list', 'approval-setting-details') ? 'active' : '' }}">
                                <a href="{{url('approval-setting')}}" ><i class="ti ti-settings-cog"></i><span>Approval Setting</span></a>
                            </li>
                             <li class=" {{ Request::is('part-requirements', 'part-requirements-list', 'part-requirements-details', 'part-requirements-document', 'part-requirements*') ? 'active' : '' }}">
                                 <a href="{{url('part-requirements')}}" ><i class="ti ti-settings-cog"></i><span>Part Requirement</span></a>
                             </li>
                             <li class=" {{ Request::is('approval-matrix/work-request*') ? 'active' : '' }}">
                                 <a href="{{route('approval-matrix.work-request.index')}}" ><i class="ti ti-settings-cog"></i><span>Work Request Matrix</span></a>
                             </li>
                         </ul>
                    </li>
                    <li class="menu-title"><span>APPLICATION</span></li>
                    <li>
                        <ul>
                            <li class="{{ Request::is('operating-sheet') ? 'active' : '' }}" href="{{ url('operating-sheet') }}">
                                <a href="{{url('operating-sheet')}}"><i class="ti ti-timeline-event-exclamation"></i><span>Operating Sheet</span></a>
                            </li>
                            <li class="{{ Request::is('plan-service', 'plan-service-list', 'plan-service-details') ? 'active' : '' }}">
                                <a href="{{url('plan-service')}}"><i class="ti ti-brand-campaignmonitor"></i><span>Plan Service</span></a>
                            </li>
                             <li class="{{ Request::is('work-request*') ? 'active' : '' }}">
                                 <a href="{{url('work-request')}}"><i class="ti ti-file-report"></i><span>Work Request</span></a>
                             </li>
                             <li class="{{ Request::is('approval-center/work-request*') ? 'active' : '' }}">
                                 <a href="{{route('approval-center.work-request.index')}}"><i class="ti ti-checklist"></i><span>Approval Center</span></a>
                             </li>
                            <li class="{{ Request::is('work-order*') ? 'active' : '' }}">
                                <a href="{{route('work-order.index')}}"><i class="ti ti-file-invoice"></i><span>Work Order</span></a>
                            </li>
                            <li class="{{ Request::is('mechanic-jobs*') ? 'active' : '' }}">
                                <a href="{{route('mechanic-job.index')}}"><i class="ti ti-tools"></i><span>Mechanic Jobs</span></a>
                            </li>
                            <li class="{{ Request::is('mechanical-activities', 'mechanical-activities-list', 'mechanical-activities-details') ? 'active' : '' }}">
                                <a href="{{url('mechanical-activities')}}"><i class="ti ti-bounce-right"></i><span>Mechanical Activity</span></a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <ul>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="{{ Request::is('inspection-form', 'inspection-report') ? 'subdrop active' : '' }}">
                                    <i class="ti ti-report-analytics"></i><span>Periodic Inspection</span><span class="menu-arrow"></span>
                                </a>
                                <ul>
                                    <li><a class="{{ Request::is('inspection-form') ? 'active' : '' }}"
                                            href="{{ url('inspection-form') }}">Inspection Form</a></li>
                                    <li><a class="{{ Request::is('inspection-report') ? 'active' : '' }}"
                                            href="{{ url('inspection-report') }}">Inspection Report</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <ul>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="{{ Request::is('unit-request', 'unit-change') ? 'subdrop active' : '' }}">
                                    <i class="ti ti-report-analytics"></i><span>Operational Unit</span><span class="menu-arrow"></span>
                                </a>
                                <ul>
                                    <li><a class="{{ Request::is('unit-request') ? 'active' : '' }}"
                                            href="{{ url('unit-request') }}">Unit Request</a></li>
                                    <li><a class="{{ Request::is('unit-change') ? 'active' : '' }}"
                                            href="{{ url('unit-change') }}">Unit Change</a></li>
                                    <li><a class="{{ Request::is('mobilisasi') ? 'active' : '' }}"
                                            href="{{ url('mobilisasi') }}">Mobilisasi</a></li>
                                    <li><a class="{{ Request::is('unit-return') ? 'active' : '' }}"
                                            href="{{ url('unit-return') }}">Unit Return</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <ul>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="{{ Request::is('manajement-tools', 'unit-change') ? 'subdrop active' : '' }}">
                                    <i class="ti ti-report-analytics"></i><span>Manajement Tools</span><span class="menu-arrow"></span>
                                </a>
                                <ul>
                                    <li><a class="{{ Request::is('create-tool-card') ? 'active' : '' }}"
                                            href="{{ url('create-tool-card') }}">Create Tool Card</a></li>
                                    <li><a class="{{ Request::is('borrowing-tool') ? 'active' : '' }}"
                                            href="{{ url('borrowing-tool') }}">Borrowing Tool</a></li>
                                    <li><a class="{{ Request::is('tools-return') ? 'active' : '' }}"
                                            href="{{ url('tools-return') }}">Tools Return</a></li>
                                    <li><a class="{{ Request::is('minutes-of-tools') ? 'active' : '' }}"
                                            href="{{ url('minutes-of-tools') }}">Minutes of Tools</a></li>
                                    <li><a class="{{ Request::is('opname-tools') ? 'active' : '' }}"
                                            href="{{ url('opname-tools') }}">Opname Tools</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>



                    <li class="menu-title"><span>Reports</span></li>
                    <li>
                        <ul>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="{{ Request::is('lead-reports', 'deal-reports', 'contact-reports', 'company-reports', 'project-reports', 'task-reports') ? 'subdrop active' : '' }}">
                                    <i class="ti ti-report-analytics"></i><span>Reports</span><span class="menu-arrow"></span>
                                </a>
                                <ul>
                                    <li><a class="{{ Request::is('tools-reports') ? 'active' : '' }}"
                                            href="{{ url('tools-reports') }}">Tools Reports</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-title"><span>User Management</span></li>
                    <li>
                        <ul>
                            <li class="{{ Request::is('users') ? 'active' : '' }}"><a href="{{url('users')}}"><i class="ti ti-users"></i><span>Manage Users</span></a></li>
                            <li class="{{ Request::is('roles','permissions') ? 'active' : '' }}"><a href="{{url('roles')}}"><i class="ti ti-user-shield"></i><span>Roles & Permissions</span></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

    </div>
    <!-- Sidenav Menu End -->
