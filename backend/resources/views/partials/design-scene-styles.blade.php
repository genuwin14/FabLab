{{-- The Design Inspection popup: the recipe (and, for admins, the charges)
     float inside the 3D scene on the right, over it rather than beside it.
     No panel background — the scene's own dark gradient carries the text —
     so the model keeps the whole width and the details read as an overlay. --}}
<style>
    .design-scene-panel {
        position: absolute;
        top: 16px;
        right: 16px;
        bottom: 16px;
        width: min(320px, 42%);
        z-index: 3;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0 6px 0 0;
        color: rgba(255, 255, 255, 0.85);
        /* Fade the text out at the top and bottom edges as it scrolls under them. */
        -webkit-mask-image: linear-gradient(to bottom, transparent, #000 14px, #000 calc(100% - 14px), transparent);
                mask-image: linear-gradient(to bottom, transparent, #000 14px, #000 calc(100% - 14px), transparent);
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
    }
    .design-scene-panel::-webkit-scrollbar { width: 6px; }
    .design-scene-panel::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.25); border-radius: 999px; }
    .design-scene-panel::-webkit-scrollbar-track { background: transparent; }

    .design-scene-panel-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 14px 0 8px;
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.7);
    }

    .design-scene-recipe {
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.68rem;
        line-height: 1.5;
        color: #6ee7ff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
    }

    /* The charges list was written for a light box; on the scene it is light on dark. */
    .design-scene-charges { color: rgba(255, 255, 255, 0.85); text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6); }
    .design-scene-charges .text-muted { color: rgba(255, 255, 255, 0.55) !important; }
    .design-scene-charges .text-dark,
    .design-scene-charges .fw-bold { color: #fff !important; }
    .design-scene-charges .border-top { border-color: rgba(255, 255, 255, 0.15) !important; }
    .design-scene-charges .fst-italic { color: rgba(255, 255, 255, 0.55); }

    /* On a phone the scene is short and narrow: the panel sits along the
       bottom instead, given a little tint so the text stays readable over
       whatever part of the model it covers. */
    @media (max-width: 767.98px) {
        .design-scene-panel {
            top: auto;
            left: 12px;
            right: 12px;
            bottom: 12px;
            width: auto;
            max-height: 46%;
            padding: 0 10px;
            border-radius: 10px;
            background-color: rgba(5, 17, 26, 0.72);
            -webkit-mask-image: none;
                    mask-image: none;
        }
    }
</style>
