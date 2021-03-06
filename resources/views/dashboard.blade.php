@extends ('layouts.twinleaf')

@section ('title', 'Dashboard')

@section ('js')
@parent
@if ($settings->gmaps_key)
<script>
    var map, bounds;

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 0, lng: 0},
            mapTypeId: google.maps.MapTypeId.MAP,
            streetViewControl: false,
            rotateControl: false,
            zoom: 3
        });

        bounds = new google.maps.LatLngBounds();

        @foreach ($maps as $map)
        @foreach ($map->areas as $area)

        @foreach (json_decode($area->geofence) as $marker)
        bounds.extend(new google.maps.LatLng({{ $marker->lat }}, {{ $marker->lng }}))
        @endforeach

        var colour;
        @if (!$area->is_enabled)
            colour = '#666';
        @else
            colour = '{{ $area->isUp() ? 'green' : 'red' }}';
        @endif

        var {{ $area->slug }} = new google.maps.Polygon({
            paths: JSON.parse('{!! $area->geofence !!}'),
            fillColor: colour,
            fillOpacity: 0.1,
            strokeColor: colour,
            strokeOpacity: 0.8,
            strokeWeight: 2
        });
        {{ $area->slug }}.setMap(map);

        @endforeach
        @endforeach

        @if (isset($area))
        map.setCenter({lat: {{ $area->lat }}, lng: {{ $area->lng }}});
        google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
            panandzoom();
        });
        @endif
    }

    function justzoom() {
        map.setZoom(map.getZoom()+1);
        setTimeout(panandzoom, 100);
    }

    function panandzoom() {
        map.setCenter(bounds.getNorthEast());

        var cb = map.getBounds();
        if (cb.contains(bounds.getNorthEast()) && cb.contains(bounds.getSouthWest())) {
            setTimeout(justzoom, 75);
        } else {
            setTimeout(map.panTo(bounds.getCenter()), 75);
        }
    }
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ $settings->gmaps_key }}&callback=initMap">
</script>
@endif

<script>
    $(function() {
        $('#activateLures').bind('click', function() {
            $.post('{{ route('long-lures.enable') }}', function(data) {
                if (data.success === true) {
                    $('.lure-container').toggleClass('hidden');
                }
            });
        });

        $('#deactivateLures').bind('click', function() {
            $.post('{{ route('long-lures.disable') }}', function(data) {
                if (data.success === true) {
                    $('.lure-container').toggleClass('hidden');
                }
            });
        });
    });
</script>
@stop

@section ('content_header')
<h1>Dashboard</h1>
@stop

@section ('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Lure Duration</h3>
            </div>
            <div class="lure-container box-body {{ $settings->long_lures ? '' : 'hidden' }}">
                <button id="deactivateLures" class="btn btn-danger pull-right">Deactivate</button>
                <p class="lead">6-hour Lures are <span class="text-success">active</span>!</p>
            </div>
            <div class="lure-container box-body {{ $settings->long_lures ? 'hidden' : '' }}">
                <button id="activateLures" class="btn btn-success pull-right">Activate</button>
                <p class="lead">6-hour Lures are <span class="text-danger">inactive</span>!</p>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Current Locations</h3>
            </div>
            @if ($settings->gmaps_key)
            <div class="box-body no-padding">
                <div id="map"></div>
            </div>
            @else
            <div class="box-body">
                <p class="lead">
                    Please <a href="{{ route('settings.index') }}">set your Google Maps key</a> first.
                </p>
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <h3 class="mt-0">Recent Activity</h3>
        @if ($logsByDate)
        <ul class="timeline">
            @foreach ($logsByDate as $date => $logs)
            <li class="time-label">
                <span class="bg-purple">{{ (new Carbon\Carbon($date))->toFormattedDateString() }}</span>
            </li>
            @foreach ($logs as $log)
            <li>
                @php $data = json_decode($log->data) @endphp
                <i class="fa fa-{{ $log->getIcon() }}"></i>
                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> {{ $log->created_at }}</span>

                    <h3 class="timeline-header">{!! $log->description !!}</h3>
                </div>
            </li>
            @endforeach
            @endforeach
            <li><i class="fa fa-clock-o bg-gray"></i></li>
        </ul>
        @else
        <p class="lead">No history to display.</p>
        @endif
    </div>
</div>
@stop
