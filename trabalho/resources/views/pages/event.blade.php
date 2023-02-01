@extends('layouts.app')

@push('page-scripts')
<script src="{{ asset('js/event_page.js') }}" defer> </script>

@if (Auth::user() != NULL && Auth::user()->userid == $host->userid)
<script src="{{ asset('js/event_host.js') }}" defer> </script>
@endif

@if (Auth::user() != NULL)
<script src="{{ asset('js/auth_user.js') }}" defer> </script>
@endif
@endpush

@section('content')

@include('partials.toast')

<div class="container-event-page">
    <div class="container" id="event-content">
        <input type="hidden" id="eventid" value="{{$event->eventid}}">
        <img src="{{$event -> photos[0]->path}}" alt="Event {{$event->name}} photo">
        <div class="wrapper-res">
            <div id="event-name"> {{$event->name}} </div>
            <div id="event-date"> {{date('Y-m-d', strtotime($event->date))}} </div>
        </div>

        @if (Auth::user() != NULL && Auth::user()->userid == $host->userid)
        <button href="#transferOwnershipModal" data-toggle="modal" class="btn btn-warning"> <a> <i class="fa fa-layer-group fa-fw"></i>
                Transfer Ownership</a></button>
        <button href="#del_Event_Modal" data-toggle="modal" id="del_event" class="btn btn-danger"> <a> <i class="fa fa-times fa-fw"></i>
                Delete Event </a></button>
        @elseif (Auth::user() != NULL && !Auth::user()->attendingEvents->contains($event->eventid))
        <button onclick="ajax_selfAddUser('{{Auth::user()->userid}}', '{{$event->eventid}}')" class="btn btn-info"> <a> <i class="fa fa-layer-group fa-fw"></i>
                Enroll Event </a></button>
        @elseif ((Auth::user() != NULL))
        <button onclick="ajax_selfRemoveUser('{{Auth::user()->userid}}', '{{$event->eventid}}')" class="btn btn-danger"> <a> <i class="fa fa-layer-group fa-fw"></i>
                Leave Event </a></button>
        @endif

        <div class="countdown-event">
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
        <nav>
            <ul id="menu-info">
                <li class="menu-info-item text-center">
                    <div> Location </div>
                    <p> {{$event->city->country->name}}, {{$event->city->name}} </p>
                </li>
                <li class="menu-info-item text-center">
                    <div> Capacity </div>
                    <p> {{$event->capacity}} places </p>
                </li>
                <li class="menu-info-item text-center">
                    <div> Address </div>
                    <p> {{$event->address}} </p>
                </li>
            </ul>
        </nav>
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    </div>

    @include ('partials.transferOwn')

    <div class="navigation">
        <ul>
            <li class="list active">
                <a href="#" onclick="showUserDiv()">
                    <span class="icon">
                        <ion-icon name="logo-steam"></ion-icon>
                    </span>
                    <span class="title">Event Host</span>
                </a>
            </li>
            <li class="list">
                <a href="#">
                    <span class="icon">
                        <ion-icon name="call-outline"></ion-icon>
                    </span>
                    <span class="title">Contact</span>
                </a>
            </li>

            <!-- @if (Auth::user() != NULL && in_array(Auth::user()->userid, $attendees))
            <li class="list">
                <a href="#" onclick="showAttendeesDiv()">
                    <span class="icon">
                        <ion-icon name="person-outline"></ion-icon>
                    </span>
                    <span class="title">Attendees</span>
                </a>
            </li>
            @endif -->

            @if (Auth::user() != NULL && Auth::user()->userid == $host->userid)
            <li class="list">
                <a href="#" onclick="showOutroDiv()">
                    <span class="icon">
                        <ion-icon name="person-outline"></ion-icon>
                    </span>
                    <span class="title">Attendees</span>
                </a>
            </li>

            @endif

            @if (Auth::user() != NULL)
            <li class="list">
                <a href="#" onclick="showInviteDiv()">
                    <span class="icon">
                        <ion-icon name="mail-outline"></ion-icon>
                    </span>
                    <span class="title">Send Invite</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

    <div id="info-navbar-container">

        <div id="userDiv" style="display:none; text-align:center;" class="answer_list">
            <button id="close-modal-button"></button>
            <div class="svg-background"></div>
            <div class="svg-background2"></div>
            <div class="circle"></div>

            <img class="profile-img" src="{{$host->profilepic}}" alt="Event host profile picture">
            <div class="text-container">
                <p class="title-text"> {{$host->name}}</p>
                <p class="info-text">Event Host</p>
                <p class="desc-text"> {{count($host->hostedEvents)}} events hosted </p>
            </div>

        </div>

        @if (Auth::user() != NULL && Auth::user()->userid == $host->userid)
        <div id="outroDiv" data-mdb-animation="slide-in-right" style="display:none;" class="answer_list">

            <input type="text" class="form-controller" id="search-users" name="search"></input>
            <table class="table table-bordered table-hover" style="margin-top:1rem;">
                <thead>
                    <tr>
                        <th>UserName</th>
                        <th>Email</th>
                        <th>ACTION </th>
                    </tr>
                </thead>
                <tbody id="table-user-res">
                </tbody>
            </table>
            <button id="close-modal-button"></button>
        </div>
        @endif

        <div id="attendeesDiv" data-mdb-animation="slide-in-right" style="display:none;" class="answer_list">

            <input type="text" class="form-controller" id="search-attendees" name="search"></input>
            <table class="table table-bordered table-hover" style="margin-top:1rem;">
                <thead>
                    <tr>
                        <th>UserName</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody id="table-attendees-res">
                </tbody>
            </table>
            <button id="close-modal-button"></button>
        </div>

        <div id="inviteDiv" data-mdb-animation="slide-in-right" style="display:none;" class="answer_list">
            Please enter the email of the user you wish to invite
            <button class="skrr" id="close-modal-button"></button>
            <div id="emailInvite">
                <input type="text" class="form-controller" id="sendInvite" name="email"></input>
                <button href="#" class="btn btn-success btn-sm btn-edit-event" type="submit" onClick="createInvite('{{$event->eventid}}')">Send Invite</button>
            </div>

        </div>
    </div>


    <div class="mx-auto col-lg-8" id="comment-section-container">
        @if ($event->comments() !== null)
        @if (Auth::user() != NULL)
        <div class="p-4 mb-2" id="new-comment">
            <!-- New Comment //-->
            <div class="">
                <img class="rounded-circle me-4" style="width:5rem;height:5rem; float:left;" src="{{Auth::user()->profilepic}}" alt="User profile picture">
                <div class="flex-grow-1">
                    <div class="gap-2">
                        <p href="#" class="fw-bold">{{Auth::user()->name}}</p>
                    </div>
                    <form action="" class="form-floating" style="margin-top: 5rem;">
                        <textarea class="form-control w-100" placeholder="Leave a comment here" id="my-comment" style="height:5rem;"></textarea>
                        <label for="my-comment">Leave a comment here</label>

                        <div class="hstack justify-content-end gap-2">
                            <button class="btn btn-sm btn-primary text-uppercase mt-3" type="submit">comment</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        @endif
        <div class="shadow-sm p-4" id="comments-section">
            <h4 class="mb-4"> @php $eventcomments = $event->comments()->get(); $unblockedcomments = array(); for ($i = 0; $i < count($eventcomments); $i++) {
                if ($eventcomments[$i]->userIsBlocked()) {
                    continue;
                }
                array_push($unblockedcomments, $eventcomments[$i]);
            } @endphp {{count($unblockedcomments)}} Comments</h4>
            <div class="">
                <!-- Comment //-->
                <div class="py-3" id="new-comments-container">
                    @foreach ($event->comments()->get() as $comment)
                    @if ($comment->userIsBlocked() == false)
                    <div class="d-flex comment">
                        <img class="rounded-circle comment-img" src="{{$comment->user->profilepic}}" alt="User profile picture" />
                        <div class="flex-grow-1 ms-3">
                            <div class="mb-1"><a href="#" class="fw-bold link-dark me-1">{{$comment->user->name}}</a>
                                <span class="text-muted text-nowrap"> {{$comment->date}}</span>
                            </div>
                            <div class="mb-2"> {{$comment->text}} </div>
                            <div class="hstack align-items-center mb-2">
                                <a class="link-primary me-2" href="#">
                                    @if (Auth::user() != NULL && $comment->hasUpvoted(Auth::user()->userid))
                                    <i onclick="removeUpvote('{{Auth::user()->userid}}','{{$comment->commentid}}'); return false;" class="icon-comments" id="like-full"></i>
                                    @elseif (Auth::user() != NULL)
                                    <i onclick="addUpvote('{{Auth::user()->userid}}','{{$comment->commentid}}'); return false;" class="icon-comments" id="like"></i>
                                    @else
                                    <i class="icon-comments" id="like"></i>
                                    @endif

                                </a>
                                <span class="me-3 small"> {{$comment->getUpvoteCount()}}</span>
                                @if (Auth::user() != NULL && Auth::user()->userid == $comment->user->userid)
                                <a class="link-danger small ms-3 __del_btn" href="#myModal" data-toggle="modal" value="{{$comment->commentid}}">delete</a>
                                @endif
                                <a class="link-danger small ms-3 __report_btn" href="#reportModal" data-toggle="modal" value="{{$comment->commentid}}">report</a>

                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach

                </div>
            </div>

        </div>
        @endif
        @include('partials.confirm_modal')
        @include('partials.reportModal')
        @include('partials.conf_del_event')
    </div>

</div>
<script type="text/javascript">
    // Polling for the Event Page Countdown display
    setTimeout(function() {
        displayCountdownEvent('{{$event->date}}');
    }, 1000);
</script>

@endsection
