<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Premium Dark & Gold Header -->
            <div class="modal-header text-white border-0 py-3"
                style="background: linear-gradient(135deg, #1e1e24 0%, #2b2b35 100%); border-bottom: 2px solid #d4af37 !important;">
                <h5 class="modal-title fw-bold" id="profileModalLabel" style="color: #d4af37;"><i
                        class="bi bi-person-badge-fill me-2"></i> Admin Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-0">
                        <!-- Left Sidebar: Photo & Summary -->
                        <div
                            class="col-md-4 bg-light border-end p-4 text-center d-flex flex-column justify-content-center">
                            <div class="mb-3 position-relative d-inline-block mx-auto">
                                <div class="rounded-circle shadow-sm overflow-hidden p-1 bg-white"
                                    style="width: 140px; height: 140px; border: 2px solid #d4af37;">
                                    <img src="{{ auth()->user() && auth()->user()->photo ? (Str::startsWith(auth()->user()->photo, 'http') ? auth()->user()->photo : asset('storage/' . auth()->user()->photo)) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->fullname ?? 'Admin') . '&background=1e1e24&color=d4af37' }}"
                                        alt="Profile Photo" class="w-100 h-100 rounded-circle object-fit-cover"
                                        id="adminProfilePreview">
                                </div>
                                <label for="adminProfilePhotoInput"
                                    class="position-absolute bottom-0 end-0 text-white rounded-circle p-2 shadow-sm hover-scale cursor-pointer"
                                    style="background-color: #d4af37; cursor: pointer; transition: transform 0.2s;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" name="photo" id="adminProfilePhotoInput" class="d-none"
                                    accept="image/*" onchange="previewImage(this, 'adminProfilePreview')">
                            </div>

                            <h5 class="fw-bold text-dark mb-1">{{ auth()->user()->fullname ?? 'Admin Name' }}</h5>
                            <!-- <span class="badge bg-dark text-warning rounded-pill px-3 py-1 mb-3 border border-warning">
                                Administrator
                            </span> -->

                            <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i> {{
                                auth()->user()->email ?? 'admin@example.com' }}</p>
                        </div>

                        <!-- Right Content: Edit Form -->
                        <div class="col-md-8 p-4">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">Personal
                                Information</h6>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="fullname" class="form-control bg-light border-0"
                                            id="floatingName" placeholder="Full Name"
                                            value="{{ auth()->user()->fullname ?? '' }}" required>
                                        <label for="floatingName">Full Name</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="address" class="form-control bg-light border-0"
                                            id="floatingAddress" placeholder="Address"
                                            value="{{ auth()->user()->address ?? '' }}">
                                        <label for="floatingAddress">Address</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" class="form-control bg-light border-0"
                                            id="floatingEmail" placeholder="name@example.com"
                                            value="{{ auth()->user()->email ?? '' }}" required>
                                        <label for="floatingEmail">Email Address</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="contact_number" class="form-control bg-light border-0"
                                            id="floatingPhone" placeholder="Mobile Number"
                                            value="{{ auth()->user()->contact_number ?? '' }}">
                                        <label for="floatingPhone">Contact Number</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">Security</h6>

                            <div class="row g-3">
                                @if(empty(auth()->user()->password))
                                    <!-- User has NO password -->
                                    <div class="col-12" id="adminPasswordSetupContainer">
                                        <button type="button" class="btn btn-outline-warning w-100 fw-bold border-2"
                                            onclick="toggleAdminPasswordFields()">
                                            <i class="bi bi-shield-lock me-2"></i> Set Account Password
                                        </button>
                                        <div class="text-muted small mt-2 text-center">
                                            <i class="bi bi-info-circle me-1"></i> No password set. Add one to enable email
                                            login.
                                        </div>
                                    </div>

                                    <div class="d-none w-100 row g-3" id="adminPasswordFields">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="password" name="password"
                                                    class="form-control bg-light border-0" id="floatingPass"
                                                    placeholder="New Password">
                                                <label for="floatingPass">New Password</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="password" name="password_confirmation"
                                                    class="form-control bg-light border-0" id="floatingConfirm"
                                                    placeholder="Confirm Password">
                                                <label for="floatingConfirm">Confirm Password</label>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- User HAS password -->
                                    <div class="col-12 text-center text-muted border rounded p-3 bg-light">
                                        <i class="bi bi-shield-lock-fill fs-3 d-block mb-2 text-dark opacity-50"></i>
                                        <p class="small fw-bold mb-1">Password Secured</p>
                                        <p class="small mb-0 fst-italic">Your account is secured with a password. To change
                                            it, please use the
                                            <a href="{{ route('password.request') }}"
                                                class="text-decoration-none fw-bold text-dark" target="_blank">Forgot
                                                Password</a>
                                            feature on the login page.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 bg-light py-3">
                        <button type="button" class="btn btn-white border fw-bold"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-dark fw-bold px-4 shadow-sm"
                            style="background-color: #1e1e24; border-color: #1e1e24;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<script>
    // Move modal to body to avoid z-index/backdrop issues when included in navbars
    var profileModal = document.getElementById('profileModal');
    if (profileModal) {
        document.body.appendChild(profileModal);
    }
</script>
<script>
    function toggleAdminPasswordFields() {
        var container = document.getElementById('adminPasswordFields');
        if (container) {
            container.classList.toggle('d-none');
            container.classList.toggle('d-flex');
        }
    }
</script>