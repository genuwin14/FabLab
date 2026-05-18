<div class="modal fade profile-modal" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="profile-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="profile-eyebrow">Staff</span>
                        <span class="profile-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="profileModalLabel">My Profile</h5>
                    </div>
                    <button type="button" class="profile-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-0">
                        <!-- Left: Photo & Identity -->
                        <div class="col-md-4 profile-side-panel p-4 text-center d-flex flex-column justify-content-center">
                            <div class="mb-3 position-relative d-inline-block mx-auto">
                                <div class="profile-avatar-frame">
                                    <img src="{{ auth()->user() && auth()->user()->photo ? (Str::startsWith(auth()->user()->photo, 'http') ? auth()->user()->photo : asset('storage/' . auth()->user()->photo)) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->fullname ?? 'Staff') . '&background=0e2e45&color=ffc508' }}"
                                        alt="Profile Photo" class="w-100 h-100 rounded-circle object-fit-cover"
                                        id="staffProfilePreview">
                                </div>
                                <label for="staffProfilePhotoInput" class="profile-photo-edit">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" name="photo" id="staffProfilePhotoInput" class="d-none"
                                    accept="image/*" onchange="previewImage(this, 'staffProfilePreview')">
                            </div>

                            <h5 class="fw-bold text-dark mb-1">{{ auth()->user()->fullname ?? 'Staff Name' }}</h5>
                            <span class="profile-role-badge mb-3">
                                <i class="bi bi-person-workspace me-1"></i>Staff Member
                            </span>
                            <p class="text-muted small mb-0 text-truncate">
                                <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email ?? 'staff@example.com' }}
                            </p>
                        </div>

                        <!-- Right: Form Fields -->
                        <div class="col-md-8 p-4">
                            <h6 class="profile-section-title">
                                <i class="bi bi-person-vcard me-2"></i>Personal Information
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <div class="form-floating profile-field">
                                        <input type="text" name="fullname" class="form-control"
                                            id="staffName" placeholder="Full Name"
                                            value="{{ auth()->user()->fullname ?? '' }}" required>
                                        <label for="staffName">Full Name</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating profile-field">
                                        <input type="text" name="address" class="form-control"
                                            id="staffAddress" placeholder="Address"
                                            value="{{ auth()->user()->address ?? '' }}">
                                        <label for="staffAddress">Address</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating profile-field">
                                        <input type="email" name="email" class="form-control"
                                            id="staffEmail" placeholder="name@example.com"
                                            value="{{ auth()->user()->email ?? '' }}" required>
                                        <label for="staffEmail">Email Address</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating profile-field">
                                        <input type="text" name="contact_number" class="form-control"
                                            id="staffPhone" placeholder="Mobile Number"
                                            value="{{ auth()->user()->contact_number ?? '' }}">
                                        <label for="staffPhone">Contact Number</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="profile-section-title">
                                <i class="bi bi-shield-lock me-2"></i>Security
                            </h6>

                            <div class="row g-3">
                                @if(empty(auth()->user()->password))
                                    <div class="col-12" id="staffPasswordSetupContainer">
                                        <button type="button" class="btn profile-btn-outline w-100 fw-semibold"
                                            onclick="toggleStaffPasswordFields()">
                                            <i class="bi bi-shield-lock me-2"></i>Set Account Password
                                        </button>
                                        <div class="text-muted small mt-2 text-center">
                                            <i class="bi bi-info-circle me-1"></i>No password set. Add one to enable email login.
                                        </div>
                                    </div>

                                    <div class="d-none w-100 row g-3" id="staffPasswordFields">
                                        <div class="col-md-6">
                                            <div class="form-floating profile-field">
                                                <input type="password" name="password" class="form-control"
                                                    id="staffPass" placeholder="New Password">
                                                <label for="staffPass">New Password</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating profile-field">
                                                <input type="password" name="password_confirmation" class="form-control"
                                                    id="staffConfirm" placeholder="Confirm Password">
                                                <label for="staffConfirm">Confirm Password</label>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-12">
                                        <div class="profile-secured-card">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="profile-secured-icon">
                                                    <i class="bi bi-shield-fill-check"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="fw-bold mb-1 text-dark">Password Secured</p>
                                                    <p class="small mb-0 text-muted">
                                                        To change your password, use
                                                        <a href="{{ route('password.request') }}" target="_blank"
                                                           class="profile-link">Forgot Password</a>
                                                        on the login page.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="profile-modal-footer">
                        <button type="button" class="btn profile-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn profile-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-modal .modal-content { border-radius: 18px; }

    .profile-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 18px 24px;
        position: relative;
    }
    .profile-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
    }
    .profile-eyebrow {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 197, 8, 0.85);
    }
    .profile-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

    .profile-close-btn {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.85);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .profile-close-btn:hover {
        background: rgba(255, 197, 8, 0.12);
        color: #ffc508;
        border-color: rgba(255, 197, 8, 0.3);
    }

    .profile-side-panel {
        background-color: #f8f9fa;
        border-right: 1px solid rgba(0, 0, 0, 0.05);
    }

    .profile-avatar-frame {
        width: 130px;
        height: 130px;
        padding: 4px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0e2e45, #ffc508);
        display: inline-block;
    }
    .profile-avatar-frame img {
        background-color: #fff;
        border: 3px solid #fff;
    }
    .profile-photo-edit {
        position: absolute;
        bottom: 4px; right: 4px;
        width: 36px; height: 36px;
        border-radius: 50%;
        background-color: #0e2e45;
        color: #ffc508;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #fff;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    .profile-photo-edit:hover {
        background-color: #ffc508;
        color: #0e2e45;
    }

    .profile-role-badge {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
        background: rgba(255, 197, 8, 0.12);
        color: #b8860b;
        border: 1px solid rgba(255, 197, 8, 0.3);
    }

    .profile-section-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6c757d;
        padding-bottom: 10px;
        margin-bottom: 14px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    .profile-field .form-control {
        background-color: #f8f9fa;
        border: 1px solid transparent;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .profile-field .form-control:focus {
        background-color: #fff;
        border-color: #ffc508;
        box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12);
    }
    .profile-field label { color: #6c757d; }

    .profile-btn-outline {
        background-color: transparent;
        border: 1.5px dashed rgba(14, 46, 69, 0.3);
        color: #0e2e45;
        border-radius: 12px;
        padding: 12px;
        transition: all 0.2s ease;
    }
    .profile-btn-outline:hover {
        background-color: rgba(255, 197, 8, 0.08);
        border-color: #ffc508;
        color: #0e2e45;
    }

    .profile-secured-card {
        padding: 14px 16px;
        border-radius: 12px;
        background-color: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .profile-secured-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .profile-link {
        color: #0e2e45;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1px dashed rgba(14, 46, 69, 0.4);
    }
    .profile-link:hover { color: #ffc508; border-bottom-color: #ffc508; }

    .profile-modal-footer {
        background-color: #fff;
        padding: 16px 24px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .profile-btn-cancel {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        font-weight: 600;
    }
    .profile-btn-cancel:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }
    .profile-btn-save {
        background-color: #0e2e45;
        border: 1px solid #0e2e45;
        color: #fff;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .profile-btn-save:hover {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }

    /* ── Mobile ( < lg / 992px ) — ResponsiveMobileNote.md §6 ──
       The two columns already stack via col-md-*; here we tighten
       spacing/type so the modal isn't cramped on a phone. */
    @media (max-width: 991.98px) {
        .profile-modal .modal-dialog { margin: 0.5rem; }
        .profile-modal .modal-title { font-size: 1rem; }
        .profile-modal-header { padding: 14px 16px; }
        .profile-modal-footer { padding: 12px 16px; }
        .profile-modal .profile-side-panel { padding: 1.25rem !important; }
        .profile-modal .col-md-8.p-4 { padding: 1.25rem !important; }
        .profile-modal .row.g-3 { --bs-gutter-y: 0.5rem; }
        .profile-modal .form-control,
        .profile-modal .profile-field label,
        .profile-modal .btn,
        .profile-modal small,
        .profile-modal .small { font-size: 0.85rem; }
        .profile-avatar-frame { width: 104px; height: 104px; }
    }
    @media (max-width: 575.98px) {
        .profile-modal-footer { flex-direction: column; }
        .profile-modal-footer .btn { width: 100%; }
    }
</style>

<script>
    if (typeof previewImage !== 'function') {
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById(previewId).src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    }

    var profileModal = document.getElementById('profileModal');
    if (profileModal) {
        document.body.appendChild(profileModal);
    }

    function toggleStaffPasswordFields() {
        var container = document.getElementById('staffPasswordFields');
        if (container) {
            container.classList.toggle('d-none');
            container.classList.toggle('d-flex');
        }
    }
</script>
