@extends('layouts.app')

@push('page-scripts')
<script src="{{ asset('js/pagination.js') }}" defer> </script>
<script src="{{ asset('js/home.js') }}" defer> </script>
@endpush

@section('content')

<div class="container" style="margin-top: 40px">
    <!-- CAROUSEL SLIDER -->
    <div id="carouselSlider" class="carousel slide">
        <ol class="carousel-indicators">
            <li data-target="#carouselSlider" data-slide-to="0" class="active"></li>
            <li data-target="#carouselSlider" data-slide-to="1"></li>
            <li data-target="#carouselSlider" data-slide-to="2"></li>
        </ol>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{$events[0]->photos[0]->path}}" class="w-100 h-200" alt="Event {{$events[0]->name}} photo">
                <div class="carousel-caption ">
                    <input type="hidden" name="event-date" value="{{$events[0]->date}}">

                    <h5>{{$events[0]->name}}</h5>
                    <p>{{$events[0] -> description}}</p>
                    <!-- button to buy tickets -->
                    <a href="{{route('event.show', $events[0]->eventid)}}" class="btn btn-primary">View Event</a>
                </div>
            </div>
            @for ($i = 1; $i <= 2; $i++) <div class="carousel-item">
                <img src="{{$events[$i]->photos[0]->path}}" class="w-100 h-200 " alt="Event {{$events[$i]->name}} photo">
                <div class="carousel-caption ">
                    <input type="hidden" name="event-date" value="{{$events[$i]->date}}">
                    <h5>{{$events[$i] -> name}}</h5>
                    <p>{{$events[$i] -> description}}</p>
                    <!-- button to buy tickets -->
                    <a href="{{route('event.show', $events[$i]->eventid)}}" class="btn btn-primary">View Event</a>
                </div>
        </div>
        @endfor
    </div>
    <a class="carousel-control-prev " href="#carouselSlider" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon "></span>
        <span>Previous</span>
    </a>
    <a class="carousel-control-next " href="#carouselSlider" role="button" data-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span>Next</span>
    </a>
</div>
<!-- END CAROUSEL SLIDER -->

<!-- COUNTDOWN TIMER -->
<div class="countdown">
    <div>
        <span class="number months"></span>
        <span>Months</span>
    </div>
    <div>
        <span class="number days"></span>
        <span>Days</span>
    </div>
    <div>
        <span class="number hours"></span>
        <span>Hours</span>
    </div>
    <div>
        <span class="number minutes"></span>
        <span>Minutes</span>
    </div>
    <div>
        <span class="number seconds"></span>
        <span>Seconds</span>
    </div>
</div>
<!-- END COUNTDOWN TIMER -->

<!-- Return false disables the page scrolling to the top automatically after the ajax request  -->

<nav id="categories-navbar" class="">
    <ul>
        <li>
            <a onclick="getDataFromTag('all'); return false;">All</a>
        </li>
        <li>
            <a onclick="getDataFromTag('sports'); return false;">Sports</a>
        </li>
        <li>
            <a onclick="getDataFromTag('music'); return false;">Music</a>
        </li>
        <li>
            <a onclick="getDataFromTag('family'); return false;">Family</a>
        </li>
        <li>
            <a onclick="getDataFromTag('technology'); return false;">Technology</a>
        </li>
    </ul>
</nav>

<!-- CREATE GRID OF CARDS WITH RANDOM IMAGES -->
<div id="container-other-events-title"> Other Events </div>
<div class="container-other-events">

    @foreach ($events as $event)
    <a href="{{route('event.show', $event->eventid)}}">
        <div class="event-card">
            <img loading="lazy" src="{{$event -> photos[0]->path}}" class="card-image" alt="Event {{$event->name}} photo">
            <h3 class="card-title"> {{$event->name}} </h3>
        </div>
    </a>
    @endforeach
</div>

<nav class="pagination-container pagination">
    <button class="pagination-button" id="prev-button" title="Previous page" aria-label="Previous page">
        &lt;
    </button>

    <div id="pagination-numbers">

    </div>

    <button class="pagination-button" id="next-button" title="Next page" aria-label="Next page">
        &gt;
    </button>
</nav>
<!-- Full screen modal -->
<div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" data-backdrop="false" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="height: 18rem;">
            <div class="form-group">
                <input type="text" class="form-controller" id="searchInput" name="search">
            </div>
            <table class="table table-bordered table-hover" style="margin-top:1rem;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>City</th>
                    </tr>
                </thead>
                <tbody id="table-res">
                </tbody>
            </table>
            <button id="close-modal-button" data-dismiss="modal"></button>

        </div>
    </div>
</div>

</div>
@endsection
