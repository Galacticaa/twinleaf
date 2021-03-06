host: 0.0.0.0
port: {{ 8000 + $map->id }}

name: {{ $map->name }}
@if (str_contains($map->url, 'map/'))
meta-url: {{ str_replace('map/', '', $map->url) }}
web-uri: map/
@else
meta-url: {{ $map->url }}
@endif
meta-description: {{ $map->description }}
meta-image: {{ $map->image_url }}

gmaps-key: {{ $config->gmaps_key }}
@if ($map->analytics_key)
analytics-key: {{ $map->analytics_key }}
@endif

location: {{ $map->location }}

gym-info
only-server
print-status: logs

@if ($config->manual_captchas)
captcha-solving
manual-captcha-domain: {{ $map->url }}
@if ($config->captcha_refresh)
manual-captcha-refresh: {{ $config->captcha_refresh }}
@endif
@if ($config->captcha_timeout)
manual-captcha-timeout: {{ $config->captcha_timeout }}
@endif
@endif

db-type: mysql
db-host: localhost
db-name: {{ $map->db_name }}
db-user: {{ $map->db_user }}
db-pass: {{ $map->db_pass }}
db-port: 3306
db-threads: 1
