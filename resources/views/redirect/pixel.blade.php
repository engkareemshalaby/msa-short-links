<!doctype html>
<html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url={{ $destination }}">@foreach($pixels as $pixel){!! $pixel->snippet !!}@endforeach</head><body><script>window.location.replace(@json($destination));</script></body></html>
