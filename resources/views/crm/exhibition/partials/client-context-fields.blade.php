@foreach(['client_timezone', 'screen_width', 'screen_height', 'viewport_width', 'viewport_height', 'pixel_ratio', 'hardware_concurrency', 'device_memory', 'touch_points'] as $field)
<input type="hidden" name="{{ $field }}" value="{{ old($field) }}" data-client-context="{{ $field }}">
@endforeach
