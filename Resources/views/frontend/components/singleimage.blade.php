@if(!empty($url))
  <a href="{{$url}}" title="{{$title}}" class="image-link w-100">
    @endif
  
        <img
        data-sizes="auto"
        data-src="{{$src}}"
        src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
        alt="{{$alt}}"
        data-srcset=" @php echo (!empty($smallSrc) ? $smallSrc." 300w,": '') @endphp
        @php echo (!empty($mediumSrc) ? $mediumSrc." 600w," : '') @endphp
        @php echo (!empty($largeSrc) ? $largeSrc." 900w," : '') @endphp
        @php echo (!empty($extraLargeSrc) ? $extraLargeSrc." 1200w," : '') @endphp
          "
      class="lazyload img-fluid {{$imgClasses}}"/>
  
    @if(!empty($url))
  </a>
@endif


