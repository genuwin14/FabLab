{{--
    Texture catalogue for pages that render a saved design in 3D.

    A recipe stores only a texture_id; rendering.js resolves the image out of
    CustomizerConfig.textures. A page that omits this renders every design as a
    blank white model, however it actually looks.

    Merges rather than assigns, so it does not matter whether this runs before
    or after a page sets its own CustomizerConfig keys.
--}}
<script>
    window.CustomizerConfig = Object.assign(window.CustomizerConfig || {}, {
        textures: @json($customizerTextures ?? [])
    });
</script>
