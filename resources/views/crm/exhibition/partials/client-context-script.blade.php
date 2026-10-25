<script>
(() => {
    const form = document.querySelector('form.crm-form');
    if (!form) return;

    const values = {
        client_timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || '',
        screen_width: window.screen?.width || '',
        screen_height: window.screen?.height || '',
        viewport_width: window.innerWidth || '',
        viewport_height: window.innerHeight || '',
        pixel_ratio: window.devicePixelRatio || '',
        hardware_concurrency: navigator.hardwareConcurrency || '',
        device_memory: navigator.deviceMemory || '',
        touch_points: navigator.maxTouchPoints ?? '',
    };

    Object.entries(values).forEach(([name, value]) => {
        const input = form.querySelector(`[data-client-context="${name}"]`);
        if (input) input.value = value;
    });
})();
</script>
