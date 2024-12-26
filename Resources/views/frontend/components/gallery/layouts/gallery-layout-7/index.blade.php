@if(count($gallery) > 0)
  <div id="{{$id}}WithVerticalThumbs">
    <div class="row">
      @if(count($gallery) > 1)
      <div class="col-auto d-none d-sm-block">
        <div class="arrow arrow-up" > {!! json_decode($navText)[0] ?? "<i class='fa fa-angle-up'></i>" !!} </div>
        <div id="{{$id}}vertical" class="vertical">
          @foreach($gallery as $key=>$item)
            <div class="item">
              <x-media::single-image :isMedia="true" :mediaFiles="$item" :dataFancybox="$dataFancybox"
                                     :data-slide-to="$key" imgClasses="gallery-image-mini"
                                     :dataTarget="'#'.$id.'PrimaryCarousel'" :autoplayVideo="$autoplayVideo"
                                     :loopVideo="$loopVideo" :mutedVideo="$mutedVideo"              />
            </div>
          @endforeach
        </div>
        <div class="arrow arrow-down"> {!! json_decode($navText)[1] ?? "<i class='fa fa-angle-down'></i>" !!} </i></div>
      </div>
      @endif
      <div class="col {{count($gallery) > 1 ? 'pl-sm-0' : ''}}">
        <div id="{{$id}}PrimaryCarousel" class="carousel slide" data-ride="carousel">
          @if(count($gallery) > 1)
          <a class="carousel-control-prev" href="#{{$id}}PrimaryCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#{{$id}}PrimaryCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
          @endif
          <div class="carousel-inner">
            @foreach($gallery as $key=>$item)
              <div class="carousel-item @if($key == 0) active @endif">
                <x-media::single-image :isMedia="true" :mediaFiles="$item" :dataFancybox="$dataFancybox"
                                       :autoplayVideo="$autoplayVideo" :loopVideo="$loopVideo"
                                       :mutedVideo="$mutedVideo" imgClasses="gallery-image-large"
																			 :showDescription="$showDescription"
								/>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
  <style>
    #{{$id}}WithVerticalThumbs .gallery-image-large {
      height: {{$heightItems + 38 }}px;
      object-fit: contain;
    }
    #{{$id}}WithVerticalThumbs .gallery-image-mini {
      width:auto; height:100px;
      aspect-ratio: 4/3;
      object-fit: cover;
      border: 1px solid #eee;
      margin:5px 0;
      display: block;
      background-color: #eee;
    }
    #{{$id}}WithVerticalThumbs #{{$id}}vertical {
      height: {{$heightItems }}px;
      overflow-y: scroll;
    }
    #{{$id}}WithVerticalThumbs .arrow {
      height: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 20px;
      &:hover {
       color: var(--primary);
       cursor: pointer;
     }
    }
    #{{$id}}WithVerticalThumbs #{{$id}}PrimaryCarousel {
      border: 1px solid #eee;
    }
    /* Scroll Menu */
    #{{$id}}WithVerticalThumbs #{{$id}}vertical::-webkit-scrollbar {
      width: 0;
    }
  </style>
  <script>
    $(document).ready(function(){
      $("#{{$id}}WithVerticalThumbs .arrow-up").click(function(){
        $("#{{$id}}vertical").animate({
          scrollTop: "-=50"
        }, "slow");
      });
      $("#{{$id}}WithVerticalThumbs .arrow-down").click(function(){
        $("#{{$id}}vertical").animate({
          scrollTop: "+=50"
        }, "slow");
      });

    });
  </script>
@endif
