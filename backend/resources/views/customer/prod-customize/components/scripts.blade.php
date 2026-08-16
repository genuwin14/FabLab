@push('scripts')
    <!-- Three.js Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

    @php
        // Same shape the preview pages ship — see partials/customizer-textures.
        $texturesData = \App\Models\Texture::customizerPayload($textures);
    @endphp
    <script>
        /**
         * Customizer Configuration
         * This object bridges Laravel server-side data to pure JavaScript files.
         */
        window.CustomizerConfig = {
            productId: '{{ $product->product_id ?? '' }}',
            productPrice: {{ $product->price ?? 0 }},
            initialShape: '{{ $initialShape ?? 't-shirt' }}',
            csrfToken: '{{ csrf_token() }}',
            designId: '{{ $design->custom_design_id ?? '' }}',
            designRecipe: @json($design?->recipe ?? null),
            requiresSelection: {{ $requiresSelection ? 'true' : 'false' }},
            textures: @json($texturesData),
            // Admin → Customization Pricing. The live quote must use these and
            // not its own numbers, or it stops matching what the cart charges.
            rates: @json($rates ?? []),
            routes: {
                save: "{{ route('customer.customize.save') }}",
                addToCart: "{{ route('customer.cart.add') }}"
            }
        };
    </script>

    <!-- Modular Customizer Scripts (Public JS) -->
    <script src="{{ asset('js/customizer/state.js') }}"></script>
    <script src="{{ asset('js/customizer/core.js') }}"></script>

    <!-- Model Loaders -->
    <script src="{{ asset('js/customizer/models/mug.js') }}"></script>
    <script src="{{ asset('js/customizer/models/t-shirt.js') }}"></script>
    <script src="{{ asset('js/customizer/models/shorts.js') }}"></script>
    <script src="{{ asset('js/customizer/models/umbrella.js') }}"></script>
    <script src="{{ asset('js/customizer/models/bag.js') }}"></script>

    <!-- Logic & Rendering -->
    <script src="{{ asset('js/customizer/rendering.js') }}"></script>
    <script src="{{ asset('js/customizer/logic.js') }}"></script>
    <script src="{{ asset('js/customizer/persistence.js') }}"></script>

    <!-- UI Handlers & Initialization (Must be last) -->
    <script src="{{ asset('js/customizer/handlers.js') }}"></script>
@endpush