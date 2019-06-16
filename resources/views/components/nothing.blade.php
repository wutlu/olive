<div class="not-found {{ @$size ? $size : '' }}">
	<img alt="not-found" src="{{ asset('img/no_data.svg') }}" />
	<!--
	    <i class="material-icons {{ @$cloud_class }}">{{ @$cloud ? $cloud : 'cloud' }}</i>
	    <i class="material-icons {{ @$cloud_class }}">{{ @$cloud ? $cloud : 'cloud' }}</i>
	    <i class="material-icons {{ @$sun_class }}">{{ @$sun ? $sun : 'wb_sunny' }}</i>
	-->
</div>
@isset($text)
	<p class="center-align {{ @$text_class }}">{!! $text !!}</p>
@endisset
