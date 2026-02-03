<style>
    #contact {
        background: radial-gradient(circle at center, #0e2e45 0%, #05111a 60%);
        position: relative;
    }

    .contact-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.5);
    }

    .form-glass {
        background: rgba(0, 0, 0, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        transition: all 0.3s ease;
        font-weight: 300;
    }

    .form-glass:focus {
        background: rgba(0, 0, 0, 0.4) !important;
        border-color: rgba(255, 197, 8, 0.4) !important;
        box-shadow: 0 0 0 4px rgba(255, 197, 8, 0.1) !important;
    }

    .form-glass::placeholder {
        color: rgba(255, 255, 255, 0.3) !important;
    }

    .contact-icon-box {
        width: 48px;
        height: 48px;
        background: rgba(255, 197, 8, 0.1);
        color: #ffc508;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .contact-item:hover .contact-icon-box {
        background: #ffc508;
        color: #05111a;
        transform: rotate(-10deg);
    }
</style>

<section id="contact" class="py-5 min-vh-100 d-flex align-items-center position-relative overflow-hidden">
    <!-- Top Fade for Seamless Transition -->
    <div class="position-absolute top-0 start-0 w-100"
        style="height: 150px; background: linear-gradient(to top, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>
    <!-- Ambient Background Elements -->
    <div class="position-absolute top-50 start-50 translate-middle rounded-circle"
        style="width: 1000px; height: 1000px; background: radial-gradient(circle, rgba(14, 46, 69, 0.2) 0%, transparent 60%); filter: blur(120px); z-index: 0;">
    </div>

    <div class="container py-5 position-relative z-1">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <span
                    class="badge bg-opacity-10 bg-white text-white border border-white border-opacity-10 px-3 py-2 rounded-pill mb-3 uppercase tracking-wider">
                    Contact Us
                </span>
                <h2 class="display-4 fw-bold mt-2 text-white mb-3">Get in <span class="text-gradient-gold">Touch</span>
                </h2>
                <p class="text-white-50 lead fw-light">Have questions? We'd love to hear from you. Our team is always
                    here to chat.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="contact-card rounded-4 p-4 p-lg-5 overflow-hidden position-relative">
                    <!-- Accent Decoration -->
                    <div class="position-absolute top-0 end-0 p-3 opacity-25">
                        <i class="bi bi-chat-square-quote-fill display-1 text-white"></i>
                    </div>

                    <div class="row g-5 position-relative z-1">
                        <div class="col-md-6">
                            <h4 class="fw-bold mb-4 text-white">Send us a message</h4>
                            <form>
                                <div class="mb-3">
                                    <label for="name"
                                        class="form-label small fw-bold text-white-50 text-uppercase tracking-wider">Full
                                        Name</label>
                                    <input type="text" class="form-control form-glass py-2 rounded-3" id="name"
                                        placeholder="John Doe">
                                </div>
                                <div class="mb-3">
                                    <label for="email"
                                        class="form-label small fw-bold text-white-50 text-uppercase tracking-wider">Email
                                        Address</label>
                                    <input type="email" class="form-control form-glass py-2 rounded-3" id="email"
                                        placeholder="name@company.com">
                                </div>
                                <div class="mb-4">
                                    <label for="message"
                                        class="form-label small fw-bold text-white-50 text-uppercase tracking-wider">Message</label>
                                    <textarea class="form-control form-glass rounded-3" id="message" rows="4"
                                        placeholder="How can we help?"></textarea>
                                </div>
                                <button type="submit"
                                    class="btn btn-accent px-4 py-3 w-100 fw-bold rounded-3 text-uppercase tracking-wider btn-glow-accent">
                                    Send Message
                                </button>
                            </form>
                        </div>

                        <div
                            class="col-md-6 d-flex flex-column justify-content-center border-start-md border-white border-opacity-10 ps-md-5">
                            <div class="mb-5 contact-item">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="contact-icon-box">
                                        <i class="bi bi-geo-alt-fill fs-5"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0 text-white">Our Office</h5>
                                </div>
                                <p class="text-white-50 ps-5 mb-0 fw-light">123 Business Avenue, Tech
                                    District<br>Innovation City, 10001</p>
                            </div>

                            <div class="mb-5 contact-item">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="contact-icon-box">
                                        <i class="bi bi-envelope-fill fs-5"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0 text-white">Email Us</h5>
                                </div>
                                <p class="text-white-50 ps-5 mb-0 fw-light">support@ims-v2.com<br>sales@ims-v2.com</p>
                            </div>

                            <div class="contact-item">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="contact-icon-box">
                                        <i class="bi bi-telephone-fill fs-5"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0 text-white">Call Us</h5>
                                </div>
                                <p class="text-white-50 ps-5 mb-0 fw-light">+1 (555) 123-4567<br>Mon-Fri, 9am - 6pm</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom Fade for Seamless Transition -->
    <div class="position-absolute bottom-0 start-0 w-100"
        style="height: 150px; background: linear-gradient(to bottom, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>
</section>