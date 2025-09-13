{{-- resources/views/layouts/sidebar.blade.php --}}

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('/') }}" class="brand-link">
        <img src="{{ asset('vendor/adminlte/dist/img/Logo_Siega.png') }}" alt="Logo"
             class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('app.name', 'Arsip Surat SIEGA') }}</span>
    </a>

    <div class="sidebar">
        @auth
            {{-- Panel user --}}
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('vendor/adminlte/dist/img/profile.jpg') }}" class="img-circle elevation-2" alt="User">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->nama_lengkap }}</a>
                </div>
            </div>
        @endauth

        @php
            // Aman jika suatu saat dipanggil tanpa auth
            $peranId = optional(Auth::user())->peran_id;

            // Helper aktif (pakai routeIs + fallback path)
            $isRoute = fn(...$names) => request()->routeIs(...$names);
            $isPath  = fn(...$paths) => collect($paths)->contains(fn($p) => Request::is($p));
        @endphp

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ url('/home') }}"
                       class="nav-link {{ $isRoute('home') || $isPath('home') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- CRUD Pengguna (hanya Admin TU) --}}
                @if ($peranId === 1)
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                           class="nav-link {{ $isRoute('users.*') || $isPath('users*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Pengguna</p>
                        </a>
                    </li>
                @endif

                {{-- Jika User Biasa (peran_id = 4) --}}
                @if ($peranId === 4)
                    <li class="nav-item">
                        <a href="{{ route('surat_tugas.mine') }}"
                           class="nav-link {{ $isRoute('surat_tugas.mine') || $isPath('surat_tugas/tugas_saya') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Surat Tugas Saya</p>
                        </a>
                    </li>
                @endif

                {{-- ===== Surat Tugas (Admin TU) ===== --}}
                @if ($peranId === 1)
                    @php
                        $stAdminOpen = $isRoute('surat_tugas.all','surat_tugas.mine') || $isPath('surat_tugas/semua*','surat_tugas/tugas_saya');
                    @endphp
                    <li class="nav-item has-treeview {{ $stAdminOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $stAdminOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>
                                Surat Tugas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Daftar Semua / Input --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.all') }}"
                                   class="nav-link {{ $isRoute('surat_tugas.all') || $isPath('surat_tugas/semua') ? 'active' : '' }}">
                                    <i class="fas fa-cogs nav-icon"></i>
                                    <p>Input Surat Tugas</p>
                                </a>
                            </li>
                            {{-- Surat Tugas Saya --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.mine') }}"
                                   class="nav-link {{ $isRoute('surat_tugas.mine') || $isPath('surat_tugas/tugas_saya') ? 'active' : '' }}">
                                    <i class="fas fa-user nav-icon"></i>
                                    <p>Surat Tugas Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- ===== Surat Tugas (Dekan/Wakil) ===== --}}
                @if (in_array($peranId, [2, 3]))
                    @php
                        $stApproverOpen = $isRoute('surat_tugas.approveList','surat_tugas.mine') || $isPath('surat_tugas/approve-list','surat_tugas/tugas_saya');
                    @endphp
                    <li class="nav-item has-treeview {{ $stApproverOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $stApproverOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>
                                Surat Tugas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Approve Surat Tugas --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.approveList') }}"
                                   class="nav-link {{ $isRoute('surat_tugas.approveList') || $isPath('surat_tugas/approve-list') ? 'active' : '' }}">
                                    <i class="fas fa-check nav-icon"></i>
                                    <p>Approve Surat Tugas</p>
                                </a>
                            </li>
                            {{-- Surat Tugas Saya --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.mine') }}"
                                   class="nav-link {{ $isRoute('surat_tugas.mine') || $isPath('surat_tugas/tugas_saya') ? 'active' : '' }}">
                                    <i class="fas fa-user nav-icon"></i>
                                    <p>Surat Tugas Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- -------- SURAT KEPUTUSAN --------- --}}

                {{-- Jika User Biasa (peran_id = 4) --}}
                @if ($peranId === 4)
                    <li class="nav-item">
                        <a href="{{ route('surat_keputusan.mine') }}"
                           class="nav-link {{ $isRoute('surat_keputusan.mine') || $isPath('surat_keputusan/keputusan_saya') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Surat Keputusan Saya</p>
                        </a>
                    </li>
                @endif

                {{-- Surat Keputusan (Admin TU) --}}
                @if ($peranId === 1)
                    @php
                        $skAdminOpen = $isRoute('surat_keputusan.all','surat_keputusan.mine') || $isPath('surat_keputusan/semua*','surat_keputusan/keputusan_saya');
                    @endphp
                    <li class="nav-item has-treeview {{ $skAdminOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $skAdminOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Surat Keputusan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Daftar Semua / Input --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.all') }}"
                                   class="nav-link {{ $isRoute('surat_keputusan.all') || $isPath('surat_keputusan/semua') ? 'active' : '' }}">
                                    <i class="fas fa-cogs nav-icon"></i>
                                    <p>Input Surat Keputusan</p>
                                </a>
                            </li>
                            {{-- Surat Keputusan Saya --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.mine') }}"
                                   class="nav-link {{ $isRoute('surat_keputusan.mine') || $isPath('surat_keputusan/keputusan_saya') ? 'active' : '' }}">
                                    <i class="fas fa-user nav-icon"></i>
                                    <p>Surat Keputusan Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Surat Keputusan (Dekan/Wakil) --}}
                @if (in_array($peranId, [2, 3]))
                    @php
                        $skApproverOpen = $isRoute('surat_keputusan.approveList','surat_keputusan.mine') || $isPath('surat_keputusan/approve-list','surat_keputusan/keputusan_saya');
                    @endphp
                    <li class="nav-item has-treeview {{ $skApproverOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $skApproverOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Surat Keputusan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Approve Surat Keputusan --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.approveList') }}"
                                   class="nav-link {{ $isRoute('surat_keputusan.approveList') || $isPath('surat_keputusan/approve-list') ? 'active' : '' }}">
                                    <i class="fas fa-check nav-icon"></i>
                                    <p>Approve Surat Keputusan</p>
                                </a>
                            </li>
                            {{-- Surat Keputusan Saya --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.mine') }}"
                                   class="nav-link {{ $isRoute('surat_keputusan.mine') || $isPath('surat_keputusan/keputusan_saya') ? 'active' : '' }}">
                                    <i class="fas fa-user nav-icon"></i>
                                    <p>Surat Keputusan Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- ===== Pengaturan Kop Surat (Admin TU) ===== --}}
                @if ($peranId === 1)
                    <li class="nav-item">
                        <a href="{{ route('kop.index') }}"
                           class="nav-link {{ $isRoute('kop.*') || $isPath('pengaturan/kop-surat') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Pengaturan Kop Surat</p>
                        </a>
                    </li>
                @endif

                {{-- ===== TTD Saya (Dekan/Wakil) ===== --}}
                @if (in_array($peranId, [2, 3]))
                    <li class="nav-item">
                        <a href="{{ route('kop.ttd.edit') }}"
                           class="nav-link {{ $isRoute('kop.ttd.edit') || $isPath('kop-surat/ttd-saya') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-signature"></i>
                            <p>TTD Saya</p>
                        </a>
                    </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>
