<div class="modal fade" id="editModal{{$event->eventid}}" tabindex="-1" role="dialog" aria-labelledby="edit_Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="edit-modal-content">
                <div class="modal-header">
                    <button id="close-modal-button" data-dismiss="modal"></button>
                    <h4 class="modal-title" id="editLabel">Edit Event</h4>
                </div>
                <div class="modal-body">

                    <form id="teste-form" method='post' action="{{ route('updateEvent', ['eventid' => $event->eventid])}}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        @csrf
                        <!-- get value from event-id2 in laravel  -->
                        <div class="form-group mb-3 form-event-edit">
                            <label for="name" class="form-label">Event Name</label>
                            <input id="name" type="text" name="name" onKeyUp="handleNameChange({{$event->eventid}})" value="{{$event->name}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="description" class="form-label">Description</label>
                            <input id="description" type="text" name="description" value="{{$event->description}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="date" class="form-label">Date</label>
                            <input id="date" type="datetime-local" name="date" value="{{$event->date}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input id="capacity" type="number" name="capacity" onKeyUp="handleCapacityChange({{$event->eventid}})" value="{{$event->capacity}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="city" class="form-label">City</label>
                            <input id="city" type="text" name="city" onKeyUp="handleLocationChange({{$event->eventid}})" value="{{$event->city->name}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="country" class="form-label">Country</label>
                            <input id="country" type="text" name="country" onKeyUp="handleLocationChange({{$event->eventid}})" value="{{$event->city->country->name}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="price" class="form-label">Price</label>
                            <input id="price" type="number " min="1" step="any" name="price" value="{{$event->price}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="address" class="form-label">Address</label>
                            <input id="address" type="text" name="address" onKeyUp="handleAddressChange({{$event->eventid}})" value="{{$event->address}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3 form-event-edit">
                            <label for="tag" class="form-label">Event Tag</label>
                            <input id="tag" type="text" name="tag" value="{{$event->eventTag->name}}" class="input-group form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="input-group switch round blue-white-switch mt-2">
                            <div class="form-check form-switch" style="padding-top: 0.7rem;">
                                <input class="form-check-input" type="checkbox" role="switch" style='height: 1.5rem; width: 3rem;' id="flexSwitchCheckChecked" checked>
                                <label class="form-check-label" for="flexSwitchCheckChecked" style="padding-left: 2.1rem; font-size: 1.5rem"> Is the Event private? </label>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="input-group btn btn-primary">
                            Submit
                        </button>
                    </form>

                    <div id="preview-container" style="position:relative">

                        <img src="{{$event->photos[0]->path}}" id="preview-image" style="border-radius: 5%;">
                        <h1 id="preview-name"> {{$event->name}} </h1>
                        <h3 id="preview-date"> {{date('Y-m-d', strtotime($event->date))}} </h3>
                        <button class="btn btn-info"> <a> <i class="fa fa-layer-group fa-fw"></i>
                                BUY TICKETS </a></button>

                        <nav>
                            <ul id="menu-info">
                                <li class="menu-info-item text-center" style="width: 11rem;">
                                    <div> Location </div>
                                    <p style="font-size: 15px" id="preview-location"> {{$event->city->country->name}} , {{$event->city->name}} </p>
                                </li>
                                <li class="menu-info-item text-center" style="width: 11rem;">
                                    <div> Capacity </div>
                                    <p id="preview-capacity" style="font-size: 15px"> {{$event->capacity}} places </p>
                                </li>
                                <li class="menu-info-item text-center" style="width: 11rem;">
                                    <div> Address </div>
                                    <p id="preview-address" style="font-size: 15px"> {{$event->address}} </p>
                                </li>
                            </ul>
                        </nav>


                    </div>
                </div>
            </div>
        </div>
        <!-- End of Modal with bootstrap -->
    </div>
