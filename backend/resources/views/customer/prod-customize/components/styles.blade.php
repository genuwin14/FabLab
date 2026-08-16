<style>
    /* Modern Utilities */
    .text-accent {
        color: #ffc508 !important;
    }

    .bg-accent {
        background-color: #ffc508 !important;
    }

    .border-white-10 {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .bg-dark-glass {
        background-color: rgba(5, 17, 26, 0.7);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }

    .bg-darker-glass {
        background-color: rgba(3, 10, 16, 0.8);
        backdrop-filter: blur(10px);
    }

    .bg-viewer {
        background-color: #030a10;
        background-image: radial-gradient(circle at 50% 50%, #0e2e45 0%, #030a10 70%);
    }

    .backdrop-blur {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .tiny {
        font-size: 0.7rem;
    }

    .line-height-1 {
        line-height: 1;
    }

    .shadow-gold {
        box-shadow: 0 4px 15px rgba(255, 197, 8, 0.2);
    }

    /* Customizer Sidebar Styles */
    .customizer-sidebar {
        z-index: 100;
    }

    .btn-config {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 12px;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .btn-config:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateX(5px);
        color: white;
    }

    .btn-config.active {
        background: rgba(255, 197, 8, 0.1);
        border-color: #ffc508;
        box-shadow: 0 0 15px rgba(255, 197, 8, 0.1);
        color: white;
    }

    .icon-box {
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #ffc508;
    }

    /**
     * "Add Text" / "Add Shape" / "Upload Logo".
     *
     * These carry .btn-outline-accent, which nothing ever defined, so they fell
     * back to a bare Bootstrap .btn — body-coloured text and a transparent
     * border, i.e. near-black on a near-black panel. Set the button's own custom
     * properties rather than plain rules so the hover, active and focus states
     * stay legible too; Bootstrap's :active selector outranks a flat class rule.
     */
    .btn-outline-accent {
        --bs-btn-color: #ffffff;
        --bs-btn-bg: rgba(255, 255, 255, 0.05);
        --bs-btn-border-color: rgba(255, 255, 255, 0.35);
        --bs-btn-hover-color: #ffc508;
        --bs-btn-hover-bg: rgba(255, 197, 8, 0.15);
        --bs-btn-hover-border-color: #ffc508;
        --bs-btn-active-color: #ffc508;
        --bs-btn-active-bg: rgba(255, 197, 8, 0.25);
        --bs-btn-active-border-color: #ffc508;
        --bs-btn-focus-shadow-rgb: 255, 197, 8;
        --bs-btn-disabled-color: rgba(255, 255, 255, 0.4);
        --bs-btn-disabled-bg: transparent;
        --bs-btn-disabled-border-color: rgba(255, 255, 255, 0.15);
        transition: all 0.2s ease;
    }

    .texture-option {
        border-radius: 50%;
        padding: 4px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .texture-option:hover {
        border-color: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .texture-option.active {
        border-color: #ffc508;
        box-shadow: 0 0 10px rgba(255, 197, 8, 0.3);
    }

    .texture-preview {
        aspect-ratio: 1/1;
        border-radius: 50%;
        width: 100%;
    }

    /* Plain colour swatches. Same affordance as a texture swatch — they sit in
       one either/or group, so they have to read as the same kind of control. */
    .color-option {
        border-radius: 50%;
        padding: 4px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .color-option:hover {
        border-color: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .color-option.active {
        border-color: #ffc508;
        box-shadow: 0 0 10px rgba(255, 197, 8, 0.3);
    }

    .color-preview {
        aspect-ratio: 1/1;
        border-radius: 50%;
        width: 100%;
        /* A near-white swatch would vanish against the panel without this. */
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.15);
    }

    .finish-group-label {
        letter-spacing: 0.08em;
    }

    /* Shape & Size Selection Styling */
    .btn-shape,
    .btn-size {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 10px;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-shape:hover,
    .btn-size:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .btn-shape.active,
    .btn-size.active {
        background: rgba(255, 197, 8, 0.1);
        border-color: #ffc508;
        color: #ffc508;
    }

    .shape-icon {
        font-size: 1.5rem;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Custom Switch Styling */
    .custom-switch .form-check-input {
        width: 40px;
        height: 20px;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        margin-left: 10px;
    }

    .custom-switch .form-check-input:checked {
        background-color: #ffc508;
        border-color: #ffc508;
    }

    /* Viewer Controls */
    .btn-control-round {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.7);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-control-round:hover {
        background: rgba(255, 197, 8, 0.1);
        color: #ffc508;
        transform: translateY(-2px);
    }

    .btn-control-round.active {
        background: #ffc508;
        color: #0e2e45;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: relative;
    }

    .status-indicator.online {
        background-color: #00ff88;
        box-shadow: 0 0 10px #00ff88;
    }

    .status-indicator::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 50%;
        border: 1px solid inherit;
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }

    @keyframes pulse-ring {
        0% {
            transform: scale(0.8);
            opacity: 1;
        }

        80%,
        100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    /* Scrollbar */
    .customizer-scrollbar::-webkit-scrollbar {
        width: 5px;
    }

    .customizer-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }

    .customizer-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    .customizer-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 197, 8, 0.3);
    }

    /* Loader Animation */
    .glitch-loader {
        animation: bounce 2s infinite ease-in-out;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @media (max-width: 768px) {
        /* Cap the stacked control panel and give it a clear divider
           against the 3D viewer below it. */
        .customizer-sidebar {
            max-height: 52vh;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Tighten the header and the big gaps between every step so the
           panel needs far less scrolling on a phone. */
        .customizer-sidebar h5 {
            font-size: 1rem;
        }
        .customizer-sidebar .mb-5 {
            margin-bottom: 1.5rem !important;
        }
        .customizer-sidebar label.text-uppercase {
            margin-bottom: 0.75rem !important;
        }

        /* Compact shape/size selectors. */
        .btn-shape,
        .btn-size {
            padding: 8px;
        }
        .shape-icon {
            font-size: 1.25rem;
            height: 26px;
        }

        /* Smaller price figure in the footer. */
        #total-price-display {
            font-size: 1.25rem;
        }
    }
</style>