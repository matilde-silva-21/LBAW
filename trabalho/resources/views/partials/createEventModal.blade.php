<div class="modal fade" id="createEventModal" tabindex="-1" role="dialog" aria-labelledby="createEventModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="edit-modal-content">
            <div class="modal-header">
                <button id="close-modal-button" data-dismiss="modal"></button>
                <h4 class="modal-title" id="edit_Label">Edit Event</h4>
            </div>
            <div class="modal-body">

                <form id="teste-form" method='post' action="{{ route('storeEvent')}}" enctype="multipart/form-data">
                    @csrf
                    <!-- get value from event-id2 in laravel  -->
                    <div class="form-group mb-3 form-event-edit">
                        <label for="name" class="form-label">Event Name</label>
                        <input id="name" type="text" name="name" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="description" class="form-label">Description</label>
                        <input id="description" type="text" name="description" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="date" class="form-label">Date</label>
                        <input id="date" type="datetime-local" name="date" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input id="capacity" type="number" name="capacity" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="city" class="form-label">City</label>
                        <input id="city" type="text" name="city" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="country" class="form-label">Country</label>
                        <input id="country" type="text" name="country" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="price" class="form-label">Price</label>
                        <input id="price" type="number " min="1" step="any" name="price" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="address" class="form-label">Address</label>
                        <input id="address" type="text" name="address" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="tag" class="form-label">Event Tag</label>
                        <input id="tag" type="text" name="tag" value="" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 form-event-edit">
                        <label for="img" class="form-label">Event Image</label>
                        <input id="img" type="file" name="img" onChange="preview_image()" placeholder="Upload an image for you event" class="input-group form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="input-group switch round blue-white-switch mt-2">
                        <div class="form-check form-switch" style="padding-top: 0.7rem;">
                            <input class="form-check-input" type="checkbox" role="switch" style='height: 1.5rem; width: 3rem;' id="flexSwitchCheckChecked" checked>
                            <label class="form-check-label" for="flexSwitchCheckChecked" style="padding-left: 2.1rem; font-size: 1.5rem"> Is the Event private? </label>
                        </div>
                    </div>

                    <button type="submit" class="input-group btn btn-primary">
                        Submit
                    </button>
                </form>

                <div id="preview-container">

                    <img src="event_photos/default_event.png" id="preview-image">
                    <h1 id="preview-name"> Event Name </h1>
                    <h3 id="preview-date"> 2023-01-23 </h3>
                    <button class="btn btn-info"> <a> <i class="fa fa-layer-group fa-fw"></i>BUY TICKETS </a></button>

                    <nav>
                        <ul id="menu-info">
                            <li class="menu-info-item text-center" style="width: 11rem;">
                                <div> Location </div>
                                <p style="font-size: 15px" id="preview-location"> Country , City </p>
                            </li>
                            <li class="menu-info-item text-center" style="width: 11rem;">
                                <div> Capacity </div>
                                <p id="preview-capacity" style="font-size: 15px"> 4500 places </p>
                            </li>
                            <li class="menu-info-item text-center" style="width: 11rem;">
                                <div> Address </div>
                                <p id="preview-address" style="font-size: 15px"> Pavilhão Atlântico </p>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
